<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin']);

$pageTitle  = 'Unit Dashboard';
$activePage = 'dashboard';
$user       = currentUser();
$unitId     = $user['unit_id'];

// Handle approve / deny
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reqId   = (int)$_POST['request_id'];
    $action  = $_POST['action'];
    $remarks = clean($_POST['remarks'] ?? '');
    $status  = $action === 'approve' ? 'approved' : 'denied';

    $stmt = $conn->prepare(
        "UPDATE print_requests SET status=?, reviewed_by=?, remarks=? WHERE request_id=?"
    );
    $stmt->bind_param('sisi', $status, $user['user_id'], $remarks, $reqId);
    $stmt->execute();
    $stmt->close();

    // Notify requesting user
    $reqRow = $conn->query("SELECT * FROM print_requests WHERE request_id=$reqId")->fetch_assoc();
    $docTitle = $conn->query("SELECT doc_title FROM documents WHERE doc_id={$reqRow['doc_id']}")->fetch_row()[0];
    $notifMsg = $status === 'approved'
        ? "Your request to print \"$docTitle\" has been approved."
        : "Your request to print \"$docTitle\" has been denied. Remarks: $remarks";
    sendNotification($conn, $reqRow['requested_by'], ucfirst($status).' Print Request', $notifMsg, $status, $reqId);
    logActivity($conn, $user['user_id'], strtoupper($status), 'print_request', $reqId, "$status print request for: $docTitle");

    $msg = "✅ Request $status successfully.";
}

// Stats for this unit
$totalDocs  = $conn->query("SELECT COUNT(*) FROM documents WHERE unit_id=$unitId AND status='active'")->fetch_row()[0];
$pendingReq = $conn->query("SELECT COUNT(*) FROM print_requests pr JOIN documents d ON pr.doc_id=d.doc_id WHERE d.unit_id=$unitId AND pr.status='pending'")->fetch_row()[0];
$totalStaff = $conn->query("SELECT COUNT(*) FROM users WHERE unit_id=$unitId AND status='active'")->fetch_row()[0];

// Pending print requests for this unit
$requests = $conn->query(
    "SELECT * FROM v_print_requests WHERE unit_name='{$user['unit_name']}' AND status='pending' ORDER BY created_at ASC"
);

// Recent docs
$recentDocs = $conn->query(
    "SELECT * FROM v_document_details WHERE unit_name='{$user['unit_name']}' ORDER BY created_at DESC LIMIT 6"
);

include __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom:8px;color:var(--gray-400);font-size:14px;">
  🏢 <?= htmlspecialchars($user['unit_name']) ?>
</div>

<?php if ($msg): ?>
<div style="background:rgba(26,122,74,.1);border:1px solid #1A7A4A;color:#1A7A4A;border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:14px;"><?= $msg ?></div>
<?php endif; ?>

<!-- STAT CARDS -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon navy">📂</div>
    <div class="stat-info">
      <div class="value"><?= $totalDocs ?></div>
      <div class="name">Unit Documents</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon warn">🔔</div>
    <div class="stat-info">
      <div class="value"><?= $pendingReq ?></div>
      <div class="name">Pending Print Requests</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon sky">👥</div>
    <div class="stat-info">
      <div class="value"><?= $totalStaff ?></div>
      <div class="name">Active Staff</div>
    </div>
  </div>
</div>

<!-- PRINT REQUESTS -->
<div class="card">
  <div class="card-title">🔔 Pending Print Requests</div>
  <?php if ($requests->num_rows === 0): ?>
    <p style="color:var(--gray-400);font-size:14px;">No pending requests. ✅</p>
  <?php else: ?>
  <table>
    <thead>
      <tr><th>Document</th><th>Type</th><th>Requested By</th><th>Reason</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php while ($req = $requests->fetch_assoc()): ?>
      <tr>
        <td style="font-weight:500;color:#1A2540;"><?= htmlspecialchars($req['doc_title']) ?></td>
        <td><span class="badge badge-<?= $req['file_type'] ?>"><?= strtoupper($req['file_type']) ?></span></td>
        <td><?= htmlspecialchars($req['requested_by_name']) ?></td>
        <td style="font-size:13px;"><?= htmlspecialchars($req['reason'] ?? '—') ?></td>
        <td style="font-size:12px;"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
        <td>
          <form method="POST" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
            <input type="text" name="remarks" placeholder="Remarks (optional)"
                   style="padding:6px 10px;border:1px solid var(--gray-200);border-radius:6px;font-size:12px;width:150px;font-family:'DM Sans',sans-serif;">
            <button name="action" value="approve" class="btn btn-sm btn-sky">✔ Approve</button>
            <button name="action" value="deny"    class="btn btn-sm btn-danger">✖ Deny</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- RECENT DOCS -->
<div class="card">
  <div class="card-title">📄 Recent Unit Documents</div>
  <table>
    <thead>
      <tr><th>Title</th><th>Category</th><th>Type</th><th>Uploaded By</th><th>Date</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php while ($doc = $recentDocs->fetch_assoc()): ?>
      <tr>
        <td style="font-weight:500;color:#1A2540;"><?= htmlspecialchars($doc['doc_title']) ?></td>
        <td><?= htmlspecialchars($doc['category_name'] ?? '—') ?></td>
        <td><span class="badge badge-<?= $doc['file_type'] ?>"><?= strtoupper($doc['file_type']) ?></span></td>
        <td><?= htmlspecialchars($doc['uploaded_by_name']) ?></td>
        <td><?= date('M d, Y', strtotime($doc['created_at'])) ?></td>
        <td><span class="badge badge-<?= $doc['status'] ?>"><?= ucfirst($doc['status']) ?></span></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
