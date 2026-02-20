<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['user']);

$pageTitle  = 'Request to Print';
$activePage = 'requests';
$user       = currentUser();
$msg        = '';

$docId = (int)($_GET['doc_id'] ?? 0);
if (!$docId) redirect('/cdas/modules/user/documents.php');

// Verify doc belongs to user's unit
$doc = $conn->query(
    "SELECT * FROM documents WHERE doc_id=$docId AND unit_id={$user['unit_id']} AND status='active'"
)->fetch_assoc();

if (!$doc) redirect('/cdas/modules/user/documents.php');

// Check for existing pending request
$existing = $conn->query(
    "SELECT * FROM print_requests WHERE doc_id=$docId AND requested_by={$user['user_id']} AND status='pending'"
)->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($existing) {
        $msg = '⚠️ You already have a pending request for this document.';
    } else {
        $reason = clean($_POST['reason']);
        $stmt   = $conn->prepare(
            "INSERT INTO print_requests (doc_id, requested_by, reason) VALUES (?,?,?)"
        );
        $stmt->bind_param('iis', $docId, $user['user_id'], $reason);
        $stmt->execute();
        $reqId = $stmt->insert_id;
        $stmt->close();

        // Find head of unit for this unit
        $hod = $conn->query(
            "SELECT user_id FROM users WHERE unit_id={$user['unit_id']} AND role='admin' AND status='active' LIMIT 1"
        )->fetch_assoc();

        if ($hod) {
            sendNotification(
                $conn, $hod['user_id'],
                'New Print Request',
                "{$user['full_name']} has requested to print \"{$doc['doc_title']}\".",
                'print_request', $reqId
            );
        }

        logActivity($conn, $user['user_id'], 'PRINT_REQ', 'print_request', $reqId, "Requested to print: {$doc['doc_title']}");
        $msg = '✅ Print request submitted! Awaiting approval from your Head of Unit.';
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:540px;">
  <div class="card-title">🖨️ Request to Print</div>

  <?php if ($msg): ?>
  <div style="background:<?= str_starts_with($msg,'✅') ? 'rgba(26,122,74,.1)' : (str_starts_with($msg,'⚠️') ? 'rgba(183,119,13,.1)' : 'rgba(192,57,43,.1)') ?>;
              border:1px solid <?= str_starts_with($msg,'✅') ? '#1A7A4A' : '#B7770D' ?>;
              color:<?= str_starts_with($msg,'✅') ? '#1A7A4A' : '#B7770D' ?>;
              border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:14px;"><?= $msg ?>
    <?php if (str_starts_with($msg,'✅')): ?>
      <br><a href="/cdas/modules/user/documents.php" style="color:var(--navy);font-weight:600;">← Back to Documents</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Document Info -->
  <div style="background:var(--sky-pale);border:1px solid var(--sky-mid);border-radius:10px;padding:18px;margin-bottom:24px;">
    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--sky-mid);margin-bottom:4px;">Document</div>
    <div style="font-weight:600;color:var(--navy);font-size:16px;"><?= htmlspecialchars($doc['doc_title']) ?></div>
    <div style="font-size:12px;color:var(--gray-400);margin-top:4px;">
      <span class="badge badge-pdf">PDF</span>&nbsp;
      <?= formatBytes((int)$doc['file_size']) ?> &nbsp;·&nbsp;
      Uploaded <?= date('M d, Y', strtotime($doc['created_at'])) ?>
    </div>
  </div>

  <?php if (!$existing && !str_starts_with($msg,'✅')): ?>
  <form method="POST">
    <div style="margin-bottom:18px;">
      <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:8px;">Reason for Printing *</label>
      <textarea name="reason" required rows="4" placeholder="Briefly explain why you need a printed copy..."
                style="width:100%;padding:12px 16px;border:1.5px solid var(--gray-200);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;resize:vertical;"></textarea>
      <div style="font-size:12px;color:var(--gray-400);margin-top:6px;">
        Your request will be sent to your Head of Unit for approval.
      </div>
    </div>
    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn btn-primary">Send Request 🔔</button>
      <a href="/cdas/modules/user/documents.php" class="btn btn-outline">Cancel</a>
    </div>
  </form>
  <?php elseif ($existing): ?>
    <div style="background:rgba(183,119,13,.1);border:1px solid #B7770D;color:#B7770D;border-radius:10px;padding:14px;font-size:14px;">
      ⚠️ You already have a pending print request for this document. Please wait for your Head of Unit to respond.
    </div>
    <a href="/cdas/modules/user/documents.php" class="btn btn-outline" style="margin-top:16px;display:inline-block;">← Back to Documents</a>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
