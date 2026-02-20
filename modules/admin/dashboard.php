<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin']);

$pageTitle  = 'Unit Dashboard';
$activePage = 'dashboard';
$user       = currentUser();
$unitId     = (int)$user['unit_id'];

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reqId   = (int)$_POST['request_id'];
    $action  = $_POST['action'];
    $remarks = clean($_POST['remarks'] ?? '');
    $status  = $action === 'approve' ? 'approved' : 'denied';
    $stmt = $conn->prepare("UPDATE print_requests SET status=?, reviewed_by=?, remarks=? WHERE request_id=?");
    $stmt->bind_param('sisi', $status, $user['user_id'], $remarks, $reqId);
    $stmt->execute();
    $stmt->close();
    $reqRow   = $conn->query("SELECT * FROM print_requests WHERE request_id=$reqId")->fetch_assoc();
    $docTitle = $conn->query("SELECT doc_title FROM documents WHERE doc_id={$reqRow['doc_id']}")->fetch_row()[0];
    $notifMsg = $status === 'approved'
        ? "Your request to print \"$docTitle\" has been approved."
        : "Your request to print \"$docTitle\" has been denied. Remarks: $remarks";
    sendNotification($conn, $reqRow['requested_by'], ucfirst($status).' Print Request', $notifMsg, $status, $reqId);
    logActivity($conn, $user['user_id'], strtoupper($status), 'print_request', $reqId, "$status print request for: $docTitle");
    $msg = $status;
}

$totalDocs   = (int)$conn->query("SELECT COUNT(*) FROM documents WHERE unit_id=$unitId AND status='active'")->fetch_row()[0];
$pendingReq  = (int)$conn->query("SELECT COUNT(*) FROM print_requests pr JOIN documents d ON pr.doc_id=d.doc_id WHERE d.unit_id=$unitId AND pr.status='pending'")->fetch_row()[0];
$totalStaff  = (int)$conn->query("SELECT COUNT(*) FROM users WHERE unit_id=$unitId AND status='active'")->fetch_row()[0];
$approvedReq = (int)$conn->query("SELECT COUNT(*) FROM print_requests pr JOIN documents d ON pr.doc_id=d.doc_id WHERE d.unit_id=$unitId AND pr.status='approved'")->fetch_row()[0];

$unitName   = $conn->real_escape_string($user['unit_name']);
$requests   = $conn->query("SELECT * FROM v_print_requests WHERE unit_name='$unitName' AND status='pending' ORDER BY created_at ASC LIMIT 5");
$recentDocs = $conn->query("SELECT * FROM v_document_details WHERE unit_name='$unitName' AND status='active' ORDER BY created_at DESC LIMIT 4");
$topCats    = $conn->query("SELECT c.category_name, COUNT(d.doc_id) as cnt FROM documents d JOIN categories c ON d.category_id=c.category_id WHERE d.unit_id=$unitId AND d.status='active' GROUP BY d.category_id ORDER BY cnt DESC LIMIT 4");
$staffList  = $conn->query("SELECT user_id, full_name, role, status FROM users WHERE unit_id=$unitId ORDER BY role, full_name LIMIT 5");

// Day-by-day upload counts
$days = [];
for ($i = 4; $i >= 0; $i--) {
    $date  = date('Y-m-d', strtotime("-$i days"));
    $label = $i === 0 ? 'Today' : date('D', strtotime("-$i days"));
    $cnt   = (int)$conn->query("SELECT COUNT(*) FROM documents WHERE unit_id=$unitId AND DATE(created_at)='$date'")->fetch_row()[0];
    $days[] = ['label' => $label, 'date' => date('M d', strtotime("-$i days")), 'cnt' => $cnt];
}
$maxCnt = max(1, max(array_column($days, 'cnt')));

include __DIR__ . '/../../includes/header.php';
?>


<?php if ($msg): ?>

<?php if ($msg): ?>
<div class="toast show <?= $msg ?>" id="toast">
  <?= $msg==='approved' ? 'Request approved successfully.' : 'Request denied.' ?>
</div>
<script>setTimeout(()=>document.getElementById('toast').classList.remove('show'),3000);</script>
<?php endif; ?>

<!-- Hero Banner — flat navy -->
<div class="hero-banner">
  <div>
    <div class="hb-tag"><?= htmlspecialchars($user['unit_name']) ?></div>
    <div class="hb-title">Unit Document Hub</div>
    <div class="hb-sub">Manage, review, and archive documents for your unit. Approve print requests and track your team's activity.</div>
    <div class="hb-actions">
      <a href="/cdas/modules/admin/documents.php" class="hb-btn hb-btn-sky">Browse Documents</a>
      <a href="/cdas/modules/admin/print_requests.php" class="hb-btn hb-btn-ghost">
        Print Requests
        <?php if ($pendingReq > 0): ?>
          <span style="background:var(--red);color:#fff;border-radius:20px;padding:0 7px;font-size:10px;line-height:18px;display:inline-block;"><?= $pendingReq ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>
  <div class="hb-stats">
    <div class="hb-stat">
      <span class="v"><?= $totalDocs ?></span>
      <span class="l">Documents</span>
    </div>
    <div class="hb-stat">
      <span class="v warn"><?= $pendingReq ?></span>
      <span class="l">Pending</span>
    </div>
    <div class="hb-stat">
      <span class="v"><?= $totalStaff ?></span>
      <span class="l">Staff</span>
    </div>
  </div>
</div>

<!-- 2-column layout -->
<div class="adm-layout">

  <!-- LEFT -->
  <div>

    <!-- Recent Documents -->
    <div class="sec-hd">
      <span class="sec-hd-title">Recent Documents</span>
      <a href="/cdas/modules/admin/documents.php" class="sec-hd-link">See all</a>
    </div>

    <div class="tab-bar">
      <button class="tab active">Recently Added</button>
      <button class="tab">PDF Files</button>
      <button class="tab">Word Docs</button>
      <button class="tab">Archived</button>
    </div>

    <div class="doc-grid">
    <?php
    $recentDocs->data_seek(0);
    $di = 0;
    while ($doc = $recentDocs->fetch_assoc()):
    $di++;
    ?>
    <div class="doc-card">
      <div class="doc-thumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--sky2)" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <div class="doc-body">
        <div class="doc-title"><?= htmlspecialchars($doc['doc_title']) ?></div>
        <div class="doc-meta">
          <span class="badge badge-<?= $doc['file_type'] ?>"><?= strtoupper($doc['file_type']) ?></span>
          <span><?= htmlspecialchars($doc['uploaded_by_name']) ?></span>
        </div>
        <div class="doc-footer">
          <span class="doc-cat"><?= htmlspecialchars($doc['category_name'] ?? 'Uncategorized') ?></span>
          <a href="/cdas/uploads/<?= urlencode($doc['file_path']) ?>" class="dl-btn" download>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download
          </a>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if ($di === 0): ?>
    <div style="grid-column:1/-1;padding:24px;text-align:center;color:var(--text3);font-size:13px;">No documents uploaded yet.</div>
    <?php endif; ?>
    </div>

    <!-- Top Categories -->
    <div class="sec-hd">
      <span class="sec-hd-title">Top Categories</span>
      <a href="/cdas/modules/admin/documents.php" class="sec-hd-link">See all</a>
    </div>
    <div class="cat-grid">
    <?php
    $ci = 0;
    while ($cat = $topCats->fetch_assoc()):
    $ci++;
    ?>
    <div class="cat-card">
      <div class="cat-icon-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div class="cat-name"><?= htmlspecialchars($cat['category_name']) ?></div>
      <div class="cat-count"><?= $cat['cnt'] ?> document<?= $cat['cnt'] != 1 ? 's' : '' ?></div>
    </div>
    <?php endwhile;
    for ($fi = $ci; $fi < 4; $fi++): ?>
    <div class="cat-card" style="opacity:.35;">
      <div class="cat-icon-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div class="cat-name">No category</div>
      <div class="cat-count">0 documents</div>
    </div>
    <?php endfor; ?>
    </div>

    <!-- Pending Print Requests -->
    <div class="req-panel">
      <div class="req-panel-hd">
        <span class="req-panel-title">Pending Print Requests</span>
        <?php if ($pendingReq > 0): ?>
        <span class="req-count-pill"><?= $pendingReq ?> awaiting</span>
        <?php else: ?>
        <span style="font-size:11px;color:var(--green);">All clear</span>
        <?php endif; ?>
      </div>

      <?php if ($requests->num_rows === 0): ?>
      <div class="req-empty">No pending requests.</div>
      <?php else: ?>
      <?php while ($req = $requests->fetch_assoc()): ?>
      <div class="req-row">
        <div class="req-icon-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        </div>
        <div class="req-body">
          <div class="req-title"><?= htmlspecialchars($req['doc_title']) ?></div>
          <div class="req-by"><?= htmlspecialchars($req['requested_by_name']) ?> &nbsp;&middot;&nbsp; <?= htmlspecialchars(substr($req['reason'] ?? 'No reason', 0, 40)) ?></div>
        </div>
        <div class="req-date"><?= date('M d', strtotime($req['created_at'])) ?></div>
        <div class="req-acts">
          <form method="POST" style="display:flex;gap:6px;align-items:center;">
            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
            <input type="text" name="remarks" placeholder="Remarks..." class="ra-input">
            <button type="submit" name="action" value="approve" class="ra-btn ra-approve">Approve</button>
            <button type="submit" name="action" value="deny"    class="ra-btn ra-deny">Deny</button>
          </form>
        </div>
      </div>
      <?php endwhile; ?>
      <?php endif; ?>
    </div>

  </div><!-- /left -->

  <!-- RIGHT -->
  <div class="adm-right">

    <!-- Unit Overview -->
    <div class="mini-card">
      <div class="mini-card-hd">Unit Overview</div>
      <div class="mini-row">
        <span class="mini-lbl">Total Documents</span>
        <span class="mini-val"><?= $totalDocs ?></span>
      </div>
      <div class="mini-row">
        <span class="mini-lbl">Approved Prints</span>
        <span class="mini-val" style="color:var(--green);"><?= $approvedReq ?></span>
      </div>
      <div class="mini-row">
        <span class="mini-lbl">Pending</span>
        <span class="mini-val" style="color:var(--orange);"><?= $pendingReq ?></span>
      </div>
      <div class="mini-row">
        <span class="mini-lbl">Active Staff</span>
        <span class="mini-val"><?= $totalStaff ?></span>
      </div>
    </div>

    <!-- Staff Members -->
    <div class="staff-card">
      <div class="staff-card-hd">
        Staff Members
        <a href="/cdas/modules/admin/staff.php">See all</a>
      </div>
      <?php $staffList->data_seek(0); while ($s = $staffList->fetch_assoc()): ?>
      <div class="staff-row">
        <div class="s-av">
          <?= strtoupper(substr($s['full_name'], 0, 1)) ?>
          <div class="s-dot <?= $s['status']==='active' ? 'on' : 'off' ?>"></div>
        </div>
        <div>
          <div class="s-name"><?= htmlspecialchars(explode(' ', $s['full_name'])[0]) ?> <?= substr(explode(' ', $s['full_name'])[1] ?? '', 0, 1) ?>.</div>
          <div class="s-role"><?= $s['role']==='admin' ? 'Head of Unit' : 'Employee' ?></div>
        </div>
        <div class="s-right">
          <span class="badge badge-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <!-- Activity Statistics -->
    <div class="act-card">
      <div class="act-card-hd">Upload Activity</div>
      <?php
      $actColors = ['var(--sky3)','var(--sky2)','var(--sky3)','var(--sky2)','var(--sky)'];
      foreach ($days as $k => $day):
      ?>
      <div class="act-row">
        <div class="act-top">
          <span class="act-day"><?= $day['label'] ?> <span style="color:var(--text3);font-size:10px;"><?= $day['date'] ?></span></span>
          <span class="act-count"><?= $day['cnt'] ?> <span style="color:var(--text3);font-weight:400;"><?= $day['cnt']>0 ? round(($day['cnt']/$maxCnt)*100).'%' : '—' ?></span></span>
        </div>
        <div class="act-track">
          <div class="act-fill" style="width:<?= $maxCnt>0 ? round(($day['cnt']/$maxCnt)*100) : 2 ?>%;background:<?= $actColors[$k] ?>;"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div><!-- /right -->

</div><!-- /adm-layout -->

<?php include __DIR__ . '/../../includes/footer.php'; ?>