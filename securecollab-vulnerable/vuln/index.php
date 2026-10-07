<?php
$pageTitle = 'Vulnerability Lab';
require_once __DIR__ . '/../includes/header.php';
require_login();

$db  = get_db();
$tab = $_GET['v'] ?? 'reflected';
$allowed = ['reflected', 'stored', 'dom', 'csrf', 'clickjacking'];
if (!in_array($tab, $allowed, true)) $tab = 'reflected';

/* ---------- 1. REFLECTED XSS (Project Search) ---------- *
 * Vulnerable sink: raw $_GET['q'] echoed back with no encoding. */
$reflected_q = $_GET['q'] ?? '';

/* ---------- 2. STORED XSS (Project Discussion / Comments) ---------- *
 * Vulnerable sink: comment stored in DB, then echoed with no encoding. */
$discussionId = 1;
if ($tab === 'stored' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lab_comment'])) {
    $text = $_POST['lab_comment'];
    $db->prepare("INSERT INTO comments (discussion_id, user_id, comment) VALUES (?,?,?)")
       ->execute([$discussionId, $user['id'], $text]);
    log_activity('Lab: Stored XSS Comment Posted', 'Discussion #' . $discussionId);
    header("Location: index.php?v=stored");
    exit;
}
$stmt = $db->prepare("SELECT c.*, u.name author FROM comments c JOIN users u ON c.user_id=u.id WHERE c.discussion_id=? ORDER BY c.created_at DESC LIMIT 5");
$stmt->execute([$discussionId]);
$labComments = $stmt->fetchAll();

/* ---------- 4. CSRF (Change Email) ---------- *
 * No token generated/validated here — handled by profile/change_email.php */

/* ---------- 5. Clickjacking (Archive Project) ---------- *
 * No frame-busting headers sent anywhere in this vulnerable app. */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-bug text-danger me-2"></i>Vulnerability Lab</h3>
        <p class="text-muted mb-0">Single-page module to demonstrate all five intentional vulnerabilities of this build.</p>
    </div>
    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
        <i class="fa-solid fa-unlock me-1"></i> VULNERABLE BUILD
    </span>
</div>

<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item"><a class="nav-link <?php echo $tab==='reflected'?'active':''; ?>" href="index.php?v=reflected">1. Reflected XSS</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $tab==='stored'?'active':''; ?>" href="index.php?v=stored">2. Stored XSS</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $tab==='dom'?'active':''; ?>" href="index.php?v=dom">3. DOM XSS</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $tab==='csrf'?'active':''; ?>" href="index.php?v=csrf">4. CSRF</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $tab==='clickjacking'?'active':''; ?>" href="index.php?v=clickjacking">5. Clickjacking</a></li>
</ul>

<div class="card border-0 shadow-sm">
<div class="card-body p-4">

<?php if ($tab === 'reflected'): ?>
    <h5 class="fw-bold mb-1">Reflected XSS — Project Search</h5>
    <p class="text-muted small">Vulnerable code: <code>vuln/index.php</code> echoes <code>$_GET['q']</code> directly with no output encoding (the real <code>projects/search.php</code> feature has the same flaw).</p>
    <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="v" value="reflected">
        <div class="col-md-9">
            <input type="text" name="q" class="form-control" value="<?php echo $reflected_q; /* intentionally unescaped */ ?>" placeholder='Try: <script>alert(1)</script>'>
        </div>
        <div class="col-md-3"><button class="btn btn-danger w-100" type="submit">Run Reflected Test</button></div>
    </form>
    <?php if ($reflected_q !== ''): ?>
    <div class="alert alert-light border">
        Reflected output: <?php echo $reflected_q; /* VULNERABLE: no htmlspecialchars() */ ?>
    </div>
    <?php endif; ?>
    <pre class="bg-dark text-light p-3 rounded small">echo $_GET['q']; // no encoding — script tags execute</pre>

<?php elseif ($tab === 'stored'): ?>
    <h5 class="fw-bold mb-1">Stored XSS — Project Discussion Comments</h5>
    <p class="text-muted small">Vulnerable code: comment is saved to MySQL, then rendered with <code>echo $c['comment'];</code> — no encoding on output (same flaw as <code>discussions/view.php</code>).</p>
    <form method="POST" class="mb-3">
        <textarea name="lab_comment" class="form-control mb-2" rows="2" placeholder='Try: <img src=x onerror=alert(1)>'></textarea>
        <button class="btn btn-danger" type="submit">Post Comment (Stored)</button>
    </form>
    <?php foreach ($labComments as $c): ?>
        <div class="border rounded p-2 mb-2">
            <strong><?php echo htmlspecialchars($c['author']); ?>:</strong>
            <?php echo $c['comment']; /* VULNERABLE: no htmlspecialchars() */ ?>
        </div>
    <?php endforeach; ?>
    <pre class="bg-dark text-light p-3 rounded small">echo $comment['comment']; // stored input rendered unescaped</pre>

<?php elseif ($tab === 'dom'): ?>
    <h5 class="fw-bold mb-1">DOM-Based XSS — Dynamic Project Preview</h5>
    <p class="text-muted small">Vulnerable sink: client-side JS reads <code>location.search</code> / <code>location.hash</code> and assigns it to <code>element.innerHTML</code>.</p>
    <form id="dom-lab-form" class="row g-2 mb-3">
        <div class="col-md-9">
            <input type="text" id="dom_note_input" class="form-control" placeholder='Try: <img src=x onerror=alert(1)>'>
        </div>
        <div class="col-md-3"><button type="button" class="btn btn-danger w-100" onclick="runDomLab()">Render via innerHTML</button></div>
    </form>
    <div id="dom-lab-output" class="p-3 border rounded bg-light" style="min-height:80px;"></div>
    <pre class="bg-dark text-light p-3 rounded small mt-3">element.innerHTML = params.get('note'); // unsafe DOM sink</pre>
    <script>
    function runDomLab(){
        var note = document.getElementById('dom_note_input').value;
        window.location.hash = encodeURIComponent(note);
        document.getElementById('dom-lab-output').innerHTML = note; // VULNERABLE sink
    }
    document.addEventListener('DOMContentLoaded', function(){
        if (window.location.hash) {
            var note = decodeURIComponent(window.location.hash.slice(1));
            document.getElementById('dom_note_input').value = note;
            document.getElementById('dom-lab-output').innerHTML = note; // VULNERABLE sink
        }
    });
    </script>

<?php elseif ($tab === 'csrf'): ?>
    <h5 class="fw-bold mb-1">CSRF — Change Email</h5>
    <p class="text-muted small">Vulnerable endpoint: <code>profile/change_email.php</code> changes the logged-in user's email on any POST, with no CSRF token check — it trusts the session cookie alone.</p>
    <p>Current email: <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
    <div class="alert alert-warning small">
        While logged in here, open the attacker page below in a <strong>new tab</strong> (same browser, same session) and click "Sync Account Now" — your email will change even though you never filled this app's own form.
    </div>
    <a href="../demos/csrf_poc.html" target="_blank" class="btn btn-danger mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Open Attacker Page (csrf_poc.html)</a>
    <pre class="bg-dark text-light p-3 rounded small">// profile/change_email.php
// No CSRF token verification — trusts session cookie only
$email = trim($_POST['email'] ?? '');</pre>

<?php elseif ($tab === 'clickjacking'): ?>
    <h5 class="fw-bold mb-1">Clickjacking — Archive Project</h5>
    <p class="text-muted small">No <code>X-Frame-Options</code> or <code>Content-Security-Policy: frame-ancestors</code> header is sent, so <code>projects/archive.php</code> can be embedded in a hostile iframe and its button clicked invisibly.</p>
    <a href="../demos/clickjacking_poc.html" target="_blank" class="btn btn-danger mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Open Framing Test Page</a>
    <div class="border rounded p-2">
        <small class="text-muted d-block mb-1">Live inline frame test of the archive page:</small>
        <iframe src="../projects/archive.php?id=1" style="width:100%;height:260px;border:1px solid #dee2e6;"></iframe>
    </div>
    <pre class="bg-dark text-light p-3 rounded small mt-3">// no anti-framing headers sent anywhere in this app</pre>
<?php endif; ?>

</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
