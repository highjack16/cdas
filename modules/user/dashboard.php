<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['user']);
$pageTitle  = 'Documents';
$activePage = 'dashboard';
$user       = currentUser();
$unitId     = (int)$user['unit_id'];

$totalDocs  = $conn->query("SELECT COUNT(*) FROM documents WHERE unit_id=$unitId AND status='active'")->fetch_row()[0];
$myUploads  = $conn->query("SELECT COUNT(*) FROM documents WHERE uploaded_by={$user['user_id']} AND status='active'")->fetch_row()[0];
$myPending  = $conn->query("SELECT COUNT(*) FROM print_requests WHERE requested_by={$user['user_id']} AND status='pending'")->fetch_row()[0];

$catCards   = $conn->query("SELECT c.category_name, c.category_id, COUNT(d.doc_id) as cnt FROM categories c LEFT JOIN documents d ON d.category_id=c.category_id AND d.unit_id=$unitId AND d.status='active' GROUP BY c.category_id HAVING cnt>0 ORDER BY cnt DESC LIMIT 6");
$shortcuts  = $conn->query("SELECT * FROM categories ORDER BY category_name LIMIT 7");
$recentDocs = $conn->query("SELECT * FROM v_document_details WHERE status='active' AND unit_name='{$user['unit_name']}' ORDER BY created_at DESC LIMIT 6");
$myRequests = $conn->query("SELECT pr.*, d.doc_title, d.file_type FROM print_requests pr JOIN documents d ON pr.doc_id=d.doc_id WHERE pr.requested_by={$user['user_id']} ORDER BY pr.created_at DESC LIMIT 5");
$pdfCnt     = $conn->query("SELECT COUNT(*) FROM documents WHERE unit_id=$unitId AND file_type='pdf' AND status='active'")->fetch_row()[0];
$docxCnt    = $conn->query("SELECT COUNT(*) FROM documents WHERE unit_id=$unitId AND file_type='docx' AND status='active'")->fetch_row()[0];
$imgCnt     = $conn->query("SELECT COUNT(*) FROM documents WHERE unit_id=$unitId AND file_type='image' AND status='active'")->fetch_row()[0];

include __DIR__ . '/../../includes/header.php';
?>

<div class="page-title-row">
  <div>
    <h1 class="page-h1">Documents</h1>
    <div style="font-size:12px;color:var(--text3);margin-top:4px;">🏢 <?= htmlspecialchars($user['unit_name']) ?></div>
  </div>
  <div class="page-actions">
    <a href="/cdas/modules/user/upload.php"    class="btn btn-ghost">⬆️ Upload</a>
    <a href="/cdas/modules/user/documents.php" class="btn btn-primary">📂 Browse All</a>
  </div>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-box">
    <div class="val"><?= $totalDocs ?></div>
    <div class="lbl">Unit Documents</div>
  </div>
  <div class="stat-box">
    <div class="val"><?= $myUploads ?></div>
    <div class="lbl">My Uploads</div>
  </div>
  <div class="stat-box">
    <div class="val" style="color:var(--orange);"><?= $myPending ?></div>
    <div class="lbl">Pending Print Requests</div>
  </div>
</div>

<!-- Category cards -->
<div class="section-title">
  Document Categories
  <span class="count">in <?= htmlspecialchars($user['unit_name']) ?></span>
</div>
<div class="doc-grid">
<?php
$icons = ['📋','⚙️','🔌','🎨','📝','⚡'];
$ci = 0;
while ($cat = $catCards->fetch_assoc()):
?>
<div class="doc-card" onclick="location.href='/cdas/modules/user/documents.php?cat=<?= $cat['category_id'] ?>'">
  <div class="doc-card-icon"><?= $icons[$ci%6] ?></div>
  <div class="doc-card-title"><?= htmlspecialchars($cat['category_name']) ?></div>
  <div class="doc-card-desc"><?= $cat['cnt'] ?> document<?= $cat['cnt']!=1?'s':'' ?> available</div>
  <div class="doc-card-footer"><span class="connect-btn">Open</span></div>
</div>
<?php $ci++; endwhile; ?>
<div class="doc-card" onclick="location.href='/cdas/modules/user/upload.php'" style="border-color:rgba(91,175,214,.25);border-style:dashed;">
  <div class="doc-card-icon" style="background:rgba(91,175,214,.1);border-color:rgba(91,175,214,.2);">➕</div>
  <div class="doc-card-title" style="color:var(--sky3);">Upload New File</div>
  <div class="doc-card-desc">Add a PDF, Word doc, or image to your unit archive</div>
  <div class="doc-card-footer"><span class="connect-btn">Upload</span></div>
</div>
</div>

<!-- Shortcuts -->
<div class="section-title">Shortcut</div>
<div class="shortcut-grid">
<?php
$shortcuts->data_seek(0);
$fe = ['📁','📂','🗂️','📋','📊','📌','📎'];
$fi = 0;
while ($s = $shortcuts->fetch_assoc()):
?>
<div class="shortcut-item">
  <div class="shortcut-folder"><?= $fe[$fi%7] ?></div>
  <div class="shortcut-label"><?= htmlspecialchars($s['category_name']) ?></div>
</div>
<?php $fi++; endwhile; ?>
</div>

<!-- Charts -->
<div class="chart-section">
  <div class="chart-box">
    <div class="ctitle">Document Upload Trend</div>
    <canvas id="uploadChart" height="150"></canvas>
  </div>
  <div class="chart-box">
    <div class="ctitle">Views &amp; Edits by Type</div>
    <canvas id="typeChart" height="150"></canvas>
  </div>
</div>

<!-- Recent docs -->
<div class="panel">
  <div class="panel-header">
    <span class="panel-title">📄 Recent Unit Documents</span>
    <a href="/cdas/modules/user/documents.php" class="btn btn-ghost btn-sm">View All →</a>
  </div>
  <table class="data-table">
    <thead><tr><th>Title</th><th>Type</th><th>Uploaded By</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php $recentDocs->data_seek(0); while ($doc = $recentDocs->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($doc['doc_title']) ?></td>
      <td><span class="badge badge-<?= $doc['file_type'] ?>"><?= strtoupper($doc['file_type']) ?></span></td>
      <td><?= htmlspecialchars($doc['uploaded_by_name']) ?></td>
      <td style="font-size:11px;"><?= date('M d, Y', strtotime($doc['created_at'])) ?></td>
      <td>
        <?php if ($doc['file_type']==='pdf'): ?>
          <a href="/cdas/modules/user/request_print.php?doc_id=<?= $doc['doc_id'] ?>" class="btn btn-ghost btn-sm">🖨️ Print</a>
        <?php elseif ($doc['file_type']==='docx'): ?>
          <a href="/cdas/uploads/<?= $doc['file_path'] ?>" class="btn btn-ghost btn-sm" download>⬇️ Edit</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- My print requests -->
<div class="panel">
  <div class="panel-header">
    <span class="panel-title">🖨️ My Recent Print Requests</span>
    <a href="/cdas/modules/user/print_requests.php" class="btn btn-ghost btn-sm">View All →</a>
  </div>
  <?php if ($myRequests->num_rows===0): ?>
  <div class="panel-body" style="color:var(--text3);font-size:13px;">No print requests yet.</div>
  <?php else: ?>
  <table class="data-table">
    <thead><tr><th>Document</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php while ($req=$myRequests->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars(substr($req['doc_title'],0,40)) ?></td>
      <td><span class="badge badge-<?= $req['file_type'] ?>"><?= strtoupper($req['file_type']) ?></span></td>
      <td><span class="badge badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span></td>
      <td style="font-size:11px;"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#3d5a78';
Chart.defaults.borderColor = 'rgba(190,227,240,0.07)';

new Chart(document.getElementById('uploadChart'),{
  type:'line',
  data:{
    labels:['Week 1','Week 2','Week 3','Week 4'],
    datasets:[{label:'Uploads',data:[2,5,3,<?= $myUploads ?>],
      borderColor:'#5BAFD6',backgroundColor:'rgba(91,175,214,0.12)',
      fill:true,tension:0.4,pointBackgroundColor:'#BEE3F0',pointRadius:5}]
  },
  options:{responsive:true,plugins:{legend:{display:false}},
    scales:{x:{grid:{color:'rgba(190,227,240,0.05)'}},y:{grid:{color:'rgba(190,227,240,0.05)'},beginAtZero:true}}}
});

new Chart(document.getElementById('typeChart'),{
  type:'doughnut',
  data:{
    labels:['PDF','DOCX','Images'],
    datasets:[{data:[<?= $pdfCnt ?>,<?= $docxCnt ?>,<?= $imgCnt ?>],
      backgroundColor:['#d95b5b','#5BAFD6','#3dbf82'],
      borderWidth:0,hoverOffset:4}]
  },
  options:{responsive:true,cutout:'60%',
    plugins:{legend:{position:'bottom',labels:{color:'#7a9ab5',font:{size:10},padding:10}}}}
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
