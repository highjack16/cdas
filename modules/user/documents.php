<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['user']);

$pageTitle  = 'Unit Documents';
$activePage = 'documents';
$user       = currentUser();
$unitId     = $user['unit_id'];
$msg        = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $docId = (int)$_POST['doc_id'];
    $stmt  = $conn->prepare("UPDATE documents SET status='deleted' WHERE doc_id=? AND unit_id=?");
    $stmt->bind_param('ii', $docId, $unitId);
    $stmt->execute();
    $stmt->close();
    logActivity($conn, $user['user_id'], 'DELETE', 'document', $docId, 'Soft-deleted document');
    $msg = '✅ Document removed from archive.';
}

// Handle edit metadata
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $docId  = (int)$_POST['doc_id'];
    $title  = clean($_POST['doc_title']);
    $desc   = clean($_POST['description']);
    $catId  = (int)$_POST['category_id'] ?: null;
    $stmt   = $conn->prepare("UPDATE documents SET doc_title=?, description=?, category_id=? WHERE doc_id=? AND unit_id=?");
    $stmt->bind_param('ssiii', $title, $desc, $catId, $docId, $unitId);
    $stmt->execute();
    $stmt->close();
    $msg = '✅ Document updated.';
}

// Build search/filter query
$search   = clean($_GET['q']    ?? '');
$typeFilter = clean($_GET['type'] ?? '');
$where    = "unit_id=$unitId AND status='active'";
if ($search)     $where .= " AND (doc_title LIKE '%$search%' OR description LIKE '%$search%')";
if ($typeFilter) $where .= " AND file_type='$typeFilter'";

$docs = $conn->query("SELECT d.*, c.category_name, u.full_name AS uploader 
                      FROM documents d 
                      LEFT JOIN categories c ON d.category_id = c.category_id
                      LEFT JOIN users u ON d.uploaded_by = u.user_id
                      WHERE $where ORDER BY d.created_at DESC");

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

include __DIR__ . '/../../includes/header.php';
?>

<?php if ($msg): ?>
<div style="background:rgba(26,122,74,.1);border:1px solid #1A7A4A;color:#1A7A4A;border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:14px;"><?= $msg ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
  <div style="color:var(--gray-400);font-size:14px;">🏢 <?= htmlspecialchars($user['unit_name']) ?></div>
  <a href="/cdas/modules/user/upload.php" class="btn btn-primary">⬆️ Upload New File</a>
</div>

<!-- SEARCH BAR -->
<form method="GET" class="search-bar">
  <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="🔍 Search by name or description...">
  <select name="type">
    <option value="">All Types</option>
    <option value="pdf"   <?= $typeFilter==='pdf'   ? 'selected' : '' ?>>PDF</option>
    <option value="docx"  <?= $typeFilter==='docx'  ? 'selected' : '' ?>>Word (.docx)</option>
    <option value="image" <?= $typeFilter==='image' ? 'selected' : '' ?>>Image</option>
  </select>
  <button type="submit" class="btn btn-primary">Search</button>
  <?php if ($search || $typeFilter): ?>
    <a href="/cdas/modules/user/documents.php" class="btn btn-outline">Clear</a>
  <?php endif; ?>
</form>

<div class="card">
  <div class="card-title">📂 <?= htmlspecialchars($user['unit_name']) ?> — Documents (<?= $docs->num_rows ?>)</div>
  <?php if ($docs->num_rows === 0): ?>
    <p style="color:var(--gray-400);font-size:14px;">No documents found. Try a different search or upload a new file.</p>
  <?php else: ?>
  <table>
    <thead>
      <tr><th>#</th><th>Title</th><th>Category</th><th>Type</th><th>Size</th><th>Uploaded By</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php $i=1; while ($doc = $docs->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td>
          <div style="font-weight:500;color:#1A2540;"><?= htmlspecialchars($doc['doc_title']) ?></div>
          <?php if ($doc['description']): ?>
            <div style="font-size:12px;color:var(--gray-400);margin-top:2px;"><?= htmlspecialchars(substr($doc['description'],0,60)) ?>...</div>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($doc['category_name'] ?? '—') ?></td>
        <td><span class="badge badge-<?= $doc['file_type'] ?>"><?= strtoupper($doc['file_type']) ?></span></td>
        <td><?= formatBytes((int)$doc['file_size']) ?></td>
        <td><?= htmlspecialchars($doc['uploader']) ?></td>
        <td style="font-size:12px;"><?= date('M d, Y', strtotime($doc['created_at'])) ?></td>
        <td style="white-space:nowrap;">
          <!-- Print request (PDF) -->
          <?php if ($doc['file_type'] === 'pdf'): ?>
            <a href="/cdas/modules/user/request_print.php?doc_id=<?= $doc['doc_id'] ?>" class="btn btn-sm btn-outline" title="Request Print">🖨️</a>
          <?php endif; ?>
          <!-- Download/Edit (docx) -->
          <?php if ($doc['file_type'] === 'docx'): ?>
            <a href="/cdas/uploads/<?= htmlspecialchars($doc['file_path']) ?>" class="btn btn-sm btn-sky" download title="Download for editing">⬇️</a>
          <?php endif; ?>
          <!-- Edit metadata -->
          <button onclick="openEdit(<?= $doc['doc_id'] ?>, `<?= addslashes($doc['doc_title']) ?>`, `<?= addslashes($doc['description']) ?>`, <?= $doc['category_id'] ?? 'null' ?>)"
                  class="btn btn-sm btn-outline" title="Edit metadata">✏️</button>
          <!-- Delete -->
          <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this document?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="doc_id"  value="<?= $doc['doc_id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger" title="Delete">🗑</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- EDIT MODAL -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:none;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:16px;padding:32px;width:480px;max-width:95vw;">
    <h3 style="font-family:'Playfair Display',serif;font-size:20px;color:var(--navy);margin-bottom:20px;">✏️ Edit Document</h3>
    <form method="POST" id="editForm">
      <input type="hidden" name="action"  value="edit">
      <input type="hidden" name="doc_id"  id="editDocId">
      <div style="margin-bottom:14px;">
        <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:6px;">Title</label>
        <input type="text" name="doc_title" id="editTitle" required
               style="width:100%;padding:10px 14px;border:1.5px solid var(--gray-200);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;">
      </div>
      <div style="margin-bottom:14px;">
        <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:6px;">Description</label>
        <textarea name="description" id="editDesc" rows="3"
                  style="width:100%;padding:10px 14px;border:1.5px solid var(--gray-200);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;resize:vertical;"></textarea>
      </div>
      <div style="margin-bottom:20px;">
        <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:6px;">Category</label>
        <select name="category_id" id="editCat"
                style="width:100%;padding:10px 14px;border:1.5px solid var(--gray-200);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;background:white;">
          <option value="">— No Category —</option>
          <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
            <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" onclick="closeEdit()" class="btn btn-outline">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
const modal = document.getElementById('editModal');
function openEdit(id, title, desc, catId) {
  document.getElementById('editDocId').value  = id;
  document.getElementById('editTitle').value  = title;
  document.getElementById('editDesc').value   = desc;
  const sel = document.getElementById('editCat');
  for (let o of sel.options) o.selected = (o.value == catId);
  modal.style.display = 'flex';
}
function closeEdit() { modal.style.display = 'none'; }
modal.addEventListener('click', e => { if (e.target === modal) closeEdit(); });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
