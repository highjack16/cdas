<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['superadmin']);
$pageTitle  = 'Documents';
$activePage = 'dashboard';

$totalDocs    = $conn->query("SELECT COUNT(*) FROM documents WHERE status='active'")->fetch_row()[0];
$totalUsers   = $conn->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetch_row()[0];
$totalUnits   = $conn->query("SELECT COUNT(*) FROM units")->fetch_row()[0];
$pendingReq   = $conn->query("SELECT COUNT(*) FROM print_requests WHERE status='pending'")->fetch_row()[0];
$storageBytes = $conn->query("SELECT COALESCE(SUM(file_size),0) FROM documents WHERE status='active'")->fetch_row()[0];

$catDocs      = $conn->query("SELECT c.category_name, c.category_id, COUNT(d.doc_id) as cnt FROM categories c LEFT JOIN documents d ON d.category_id=c.category_id AND d.status='active' GROUP BY c.category_id ORDER BY cnt DESC LIMIT 6");
$unitShortcuts= $conn->query("SELECT * FROM units ORDER BY unit_name");
$recentUploads= $conn->query("SELECT * FROM v_document_details WHERE status='active' ORDER BY created_at DESC LIMIT 10");
$recentLogs   = $conn->query("SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON al.user_id=u.user_id ORDER BY al.created_at DESC LIMIT 8");

include __DIR__ . '/../../includes/header.php';
?>

<div class="page-title-row">
  <h1 class="page-h1">Documents</h1>
  <div class="page-actions">
    <a href="/cdas/modules/superadmin/users.php" class="btn btn-ghost">Users</a>
    <a href="/cdas/modules/superadmin/reports.php" class="btn btn-primary">Reports</a>
  </div>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-box">
    <div class="val"><?= number_format($totalDocs) ?></div>
    <div class="lbl">Total Documents</div>
    <div class="chg">Active Archive</div>
  </div>
  <div class="stat-box">
    <div class="val"><?= number_format($totalUsers) ?></div>
    <div class="lbl">Active Users</div>
  </div>
  <div class="stat-box">
    <div class="val"><?= number_format($totalUnits) ?></div>
    <div class="lbl">Units / Offices</div>
  </div>
  <div class="stat-box" style="--sky:var(--orange);">
    <div class="val" style="color:var(--orange);"><?= number_format($pendingReq) ?></div>
    <div class="lbl">Pending Print Requests</div>
  </div>
  <div class="stat-box">
    <div class="val" style="font-size:18px;padding-top:4px;"><?= formatBytes((int)$storageBytes) ?></div>
    <div class="lbl">Storage Used</div>
  </div>
</div>

<!-- Category cards -->
<div class="section-title">
  Document Categories
  <span class="count"><?= $catDocs->num_rows ?> categories</span>
</div>
<div class="doc-grid">
<?php
$icons = ['C','S','P','D','N','F'];
$ci = 0; $catDocs->data_seek(0);
while ($cat = $catDocs->fetch_assoc()):
?>
<div class="doc-card">
  <div class="doc-card-icon"><?= $icons[$ci%6] ?></div>
  <div class="doc-card-title"><?= htmlspecialchars($cat['category_name']) ?></div>
  <div class="doc-card-desc"><?= $cat['cnt'] ?> document<?= $cat['cnt']!=1?'s':'' ?> in this category</div>
  <div class="doc-card-footer">
    <a href="/cdas/modules/superadmin/documents.php?cat=<?= $cat['category_id'] ?>" class="connect-btn">View All</a>
  </div>
</div>
<?php $ci++; endwhile; ?>
</div>

<!-- Unit shortcuts -->
<div class="section-title">Unit Shortcuts</div>
<div class="shortcut-grid">
<?php
$unitShortcuts->data_seek(0);
$fe = ['F','F','F','F','F','F'];
$fi = 0;
while ($u = $unitShortcuts->fetch_assoc()):
?>
<div class="shortcut-item">
  <div class="shortcut-folder"><?= $fe[$fi%6] ?></div>
  <div class="shortcut-label"><?= htmlspecialchars($u['unit_name']) ?></div>
</div>
<?php $fi++; endwhile; ?>
</div>

<!-- Recent uploads table -->
<div class="panel">
  <div class="panel-header">
    <span class="panel-title">Recent Document Uploads</span>
    <a href="/cdas/modules/superadmin/documents.php" class="btn btn-ghost btn-sm">View All</a>
  </div>
  <table class="data-table">
    <thead><tr><th>Document</th><th>Unit</th><th>Category</th><th>Type</th><th>Size</th><th>Uploaded By</th><th>Date</th></tr></thead>
    <tbody>
    <?php while ($doc = $recentUploads->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($doc['doc_title']) ?></td>
      <td><?= htmlspecialchars($doc['unit_name']) ?></td>
      <td><?= htmlspecialchars($doc['category_name']??'-') ?></td>
      <td><span class="badge badge-<?= $doc['file_type'] ?>"><?= strtoupper($doc['file_type']) ?></span></td>
      <td style="font-family:'JetBrains Mono',monospace;font-size:11px;"><?= formatBytes((int)$doc['file_size']) ?></td>
      <td><?= htmlspecialchars($doc['uploaded_by_name']) ?></td>
      <td style="font-size:11px;"><?= date('M d, Y', strtotime($doc['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Activity log -->
<div class="panel">
  <div class="panel-header">
    <span class="panel-title">Recent Activity</span>
    <a href="/cdas/modules/superadmin/logs.php" class="btn btn-ghost btn-sm">View All</a>
  </div>
  <table class="data-table">
    <thead><tr><th>Action</th><th>User</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
    <tbody>
    <?php while ($log = $recentLogs->fetch_assoc()): ?>
    <tr>
      <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--sky2);"><?= htmlspecialchars($log['action']) ?></td>
      <td><?= htmlspecialchars($log['full_name']??'System') ?></td>
      <td style="font-size:11px;"><?= htmlspecialchars(substr($log['details'],0,50)) ?></td>
      <td style="font-family:'JetBrains Mono',monospace;font-size:10px;"><?= htmlspecialchars($log['ip_address']) ?></td>
      <td style="font-size:11px;"><?= date('M d, g:ia', strtotime($log['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
