<?php
$pageTitle = 'Security Lab';
require_once __DIR__ . '/../includes/header.php';
require_login();

$db  = get_db();
$tab = $_GET['v'] ?? 'reflected';
$allowed = ['reflected', 'stored', 'dom', 'csrf', 'clickjacking'];
if (!in_array($tab, $allowed, true)) $tab = 'reflected';

/* ---------- 1. Reflected XSS — mitigated with htmlspecialchars() ---------- */
$reflected_q = $_GET['q'] ?? '';

/* ---------- 2. Stored XSS — mitigated on output, not on storage ---------- */
$discussionId = 1;
if ($tab === 'stored' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lab_comment']) && verify_csrf_token()) {
    $text = $_POST['lab_comment'];
    $db->prepare("INSERT INTO comments (discussion_id, user_id, comment) VALUES (?,?,?)")
       ->execute([$discussionId, $user['id'], $text]);
    log_activity('Lab: Stored XSS Comment Posted (secure)', 'Discussion #' . $discussionId);
    header("Location: index.php?v=stored");
    exit;
}
$stmt = $db->prepare("SELECT c.*, u.name author FROM comments c JOIN users u ON c.user_id=u.id WHERE c.discussion_id=? ORDER BY c.created_at DESC LIMIT 5");
$stmt->execute([$discussionId]);
$labComments = $stmt->fetchAll();

/* ---------- 4. CSRF — token generated/verified via functions.php ---------- */

/* ---------- 5. Clickjacking — X-Frame-Options / CSP sent in includes/header.php ---------- */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-shield-halved text-success me-2"></i>Security Lab</h3>
        <p class="text-muted mb-0">Single-page module demonstrating the mitigation for each of the five vulnerabilities.</p>
    </div>
    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
        <i class="fa-solid fa-lock me-1"></i> SECURE BUILD
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
    <h5 class="fw-bold mb-1">Reflected XSS — Mitigated (Project Search)</h5>
    <p class="text-muted small">Mitigation: every reflected value passes through <code>htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')</code> before output.</p>
    <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="v" value="reflected">
        <div class="col-md-9">
            <input type="text" name="q" class="form-control" value="<?php echo e($reflected_q); ?>" placeholder='Try: <script>alert(1)</script>'>
        </div>
        <div class="col-md-3"><button class="btn btn-success w-100" type="submit">Run Same Test</button></div>
    </form>
    <?php if ($reflected_q !== ''): ?>
    <div class="alert alert-light border">
        Reflected output (safely encoded): <?php echo e($reflected_q); ?>
    </div>
    <?php endif; ?>
    <pre class="bg-dark text-light p-3 rounded small">echo htmlspecialchars($_GET['q'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
// tags are shown as text, never execute</pre>

<?php elseif ($tab === 'stored'): ?>
    <h5 class="fw-bold mb-1">Stored XSS — Mitigated (Discussion Comments)</h5>
    <p class="text-muted small">Comments are still stored exactly as typed (no input sanitization). The fix is entirely on the <em>output</em> side: <code>htmlspecialchars()</code> at render time.</p>
    <form method="POST" class="mb-3">
        <?php echo generate_csrf_token(); ?>
        <textarea name="lab_comment" class="form-control mb-2" rows="2" placeholder='Try: <img src=x onerror=alert(1)>'></textarea>
        <button class="btn btn-success" type="submit">Post Comment (Stored, Safely Rendered)</button>
    </form>
    <?php foreach ($labComments as $c): ?>
        <div class="border rounded p-2 mb-2">
            <strong><?php echo e($c['author']); ?>:</strong>
            <?php echo e($c['comment']); ?>
        </div>
    <?php endforeach; ?>
    <pre class="bg-dark text-light p-3 rounded small">echo htmlspecialchars($comment['comment'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');</pre>

<?php elseif ($tab === 'dom'): ?>
    <h5 class="fw-bold mb-1">DOM-Based XSS — Mitigated (Dynamic Project Preview)</h5>
    <p class="text-muted small">Mitigation: the same URL-sourced value is assigned with <code>element.textContent</code> instead of <code>innerHTML</code>, so markup is never parsed.</p>
    <form id="dom-lab-form" class="row g-2 mb-3">
        <div class="col-md-9">
            <input type="text" id="dom_note_input" class="form-control" placeholder='Try: <img src=x onerror=alert(1)>'>
        </div>
        <div class="col-md-3"><button type="button" class="btn btn-success w-100" onclick="runDomLab()">Render via textContent</button></div>
    </form>
    <div id="dom-lab-output" class="p-3 border rounded bg-light" style="min-height:80px;"></div>
    <pre class="bg-dark text-light p-3 rounded small mt-3">element.textContent = params.get('note'); // safe DOM sink</pre>
    <script>
    function runDomLab(){
        var note = document.getElementById('dom_note_input').value;
        window.location.hash = encodeURIComponent(note);
        document.getElementById('dom-lab-output').textContent = note; // SAFE sink
    }
    document.addEventListener('DOMContentLoaded', function(){
        if (window.location.hash) {
            var note = decodeURIComponent(window.location.hash.slice(1));
            document.getElementById('dom_note_input').value = note;
            document.getElementById('dom-lab-output').textContent = note; // SAFE sink
        }
    });
    </script>

<?php elseif ($tab === 'csrf'): ?>
    <h5 class="fw-bold mb-1">CSRF — Mitigated (Change Email)</h5>
    <p class="text-muted small">Mitigation: <code>profile/change_email.php</code> requires a per-session token, generated with <code>bin2hex(random_bytes(32))</code> and verified with <code>hash_equals()</code>. Requests without a valid token are rejected with HTTP 403.</p>
    <p>Current email: <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
    <div class="alert alert-success small">
        Open the same attacker page used against the vulnerable app. Because it cannot supply this session's CSRF token, the request is rejected.
    </div>
    <a href="../demos/csrf_poc.html" target="_blank" class="btn btn-outline-success mb-3"><i class="fa-solid fa-flask me-1"></i> Open Attacker Page (attack is blocked)</a>
    <pre class="bg-dark text-light p-3 rounded small">$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $token)) { http_response_code(403); die('Blocked'); }</pre>

<?php elseif ($tab === 'clickjacking'): ?>
    <h5 class="fw-bold mb-1">Clickjacking — Mitigated (Archive Project)</h5>
    <p class="text-muted small">Mitigation: every response in this app sends <code>X-Frame-Options: SAMEORIGIN</code> and <code>Content-Security-Policy: frame-ancestors 'self'</code> (set in <code>includes/header.php</code>), so the browser refuses to render it inside a foreign frame.</p>
    <a href="../demos/clickjacking_poc.html" target="_blank" class="btn btn-outline-success mb-3"><i class="fa-solid fa-flask me-1"></i> Open Framing Test Page (frame will be blocked)</a>
    <div class="border rounded p-2">
        <small class="text-muted d-block mb-1">Live inline frame test of the archive page (browser blocks it):</small>
        <iframe src="../projects/archive.php?id=1" style="width:100%;height:120px;border:1px solid #dee2e6;"></iframe>
    </div>
    <pre class="bg-dark text-light p-3 rounded small mt-3">header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: frame-ancestors 'self';");</pre>
<?php endif; ?>

</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
