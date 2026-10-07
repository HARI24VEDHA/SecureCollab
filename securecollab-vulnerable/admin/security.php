<?php
$pageTitle = 'Compliance Report';
require_once __DIR__ . '/../includes/header.php';
require_admin();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Security Compliance Report</h3>
        <p class="text-muted small mb-0">Application security control verification and audit results</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-clipboard-check text-primary me-2"></i>Control Assessment</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width:28%">Security Control</th>
                    <th style="width:22%">Application Feature</th>
                    <th style="width:28%">Implementation</th>
                    <th style="width:22%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-semibold">Output Encoding — Reflected Data</td>
                    <td>Project Search</td>
                    <td><code>htmlspecialchars()</code> on search parameter</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                </tr>
                <tr>
                    <td class="fw-semibold">Output Encoding — Persistent Data</td>
                    <td>Discussion Comments</td>
                    <td><code>htmlspecialchars()</code> on stored comment output</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                </tr>
                <tr>
                    <td class="fw-semibold">DOM Sink Sanitization</td>
                    <td>Project Note Preview</td>
                    <td>Safe <code>textContent</code> DOM assignment</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                </tr>
                <tr>
                    <td class="fw-semibold">Cross-Origin Request Forgery Protection</td>
                    <td>Email Update Form</td>
                    <td>Synchronizer token pattern with <code>hash_equals()</code></td>
                    <td><span class="badge bg-warning">Pending</span></td>
                </tr>
                <tr>
                    <td class="fw-semibold">Frame Embedding Restriction</td>
                    <td>All Authenticated Pages</td>
                    <td><code>X-Frame-Options</code> + CSP <code>frame-ancestors</code></td>
                    <td><span class="badge bg-warning">Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>