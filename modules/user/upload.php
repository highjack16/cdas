<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['user']);

$pageTitle  = 'Upload Document';
$activePage = 'upload';
$user       = currentUser();
$msg = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = clean($_POST['doc_title']);
    $desc    = clean($_POST['description']);
    $catId   = (int)$_POST['category_id'] ?: null;
    $file    = $_FILES['file_upload'] ?? null;

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $msg = '❌ File upload error. Please try again.';
    } else {
        $origName  = basename($file['name']);
        $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $fileSize  = $file['size'];

        // Map extension to file_type enum
        $typeMap = ['pdf' => 'pdf', 'docx' => 'docx', 'doc' => 'docx',
                    'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image'];

        if (!array_key_exists($ext, $typeMap)) {
            $msg = '❌ Invalid file type. Allowed: PDF, DOCX, JPG, PNG.';
        } elseif ($fileSize > MAX_FILE_SIZE) {
            $msg = '❌ File exceeds 20MB limit.';
        } else {
            $fileType  = $typeMap[$ext];
            $unitCode  = strtolower($user['unit_id']);
            $saveName  = uniqid('doc_', true) . '.' . $ext;
            $unitDir   = UPLOAD_DIR . $user['unit_id'] . '/';
            if (!is_dir($unitDir)) mkdir($unitDir, 0755, true);
            $savePath  = $unitDir . $saveName;
            $relPath   = $user['unit_id'] . '/' . $saveName;

            if (move_uploaded_file($file['tmp_name'], $savePath)) {
                $stmt = $conn->prepare(
                    "INSERT INTO documents (unit_id, uploaded_by, category_id, doc_title, description, file_name, file_path, file_type, file_size)
                     VALUES (?,?,?,?,?,?,?,?,?)"
                );
                $stmt->bind_param('iiisssssi',
                    $user['unit_id'], $user['user_id'], $catId,
                    $title, $desc, $origName, $relPath, $fileType, $fileSize
                );
                $stmt->execute();
                $newId = $stmt->insert_id;
                $stmt->close();
                logActivity($conn, $user['user_id'], 'UPLOAD', 'document', $newId, "Uploaded: $title");
                $msg = '✅ Document uploaded successfully!';
            } else {
                $msg = '❌ Could not save file. Check server upload permissions.';
            }
        }
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($msg): ?>
<div style="background:<?= str_starts_with($msg,'✅') ? 'rgba(26,122,74,.1)' : 'rgba(192,57,43,.1)' ?>;
            border:1px solid <?= str_starts_with($msg,'✅') ? '#1A7A4A' : '#C0392B' ?>;
            color:<?= str_starts_with($msg,'✅') ? '#1A7A4A' : '#C0392B' ?>;
            border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:14px;"><?= $msg ?></div>
<?php endif; ?>

<div class="card" style="max-width:640px;">
  <div class="card-title">⬆️ Upload New Document</div>

  <form method="POST" enctype="multipart/form-data">
    <div style="margin-bottom:18px;">
      <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:8px;">Document Title *</label>
      <input type="text" name="doc_title" required placeholder="e.g. Monthly Report January 2025"
             style="width:100%;padding:11px 16px;border:1.5px solid var(--gray-200);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;">
    </div>

    <div style="margin-bottom:18px;">
      <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:8px;">Category</label>
      <select name="category_id"
              style="width:100%;padding:11px 16px;border:1.5px solid var(--gray-200);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;background:white;color:#1A2540;">
        <option value="">— Select Category (optional) —</option>
        <?php while ($cat = $categories->fetch_assoc()): ?>
          <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div style="margin-bottom:18px;">
      <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:8px;">Description</label>
      <textarea name="description" rows="3" placeholder="Brief description of this document..."
                style="width:100%;padding:11px 16px;border:1.5px solid var(--gray-200);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;resize:vertical;"></textarea>
    </div>

    <!-- Drop Zone -->
    <div style="margin-bottom:24px;">
      <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-600);display:block;margin-bottom:8px;">File *</label>
      <div id="dropZone"
           style="border:2px dashed var(--sky-mid);border-radius:12px;padding:40px;text-align:center;background:var(--sky-pale);cursor:pointer;transition:background .2s;"
           onclick="document.getElementById('fileInput').click()"
           ondragover="event.preventDefault();this.style.background='var(--sky)'"
           ondragleave="this.style.background='var(--sky-pale)'"
           ondrop="handleDrop(event)">
        <div style="font-size:36px;margin-bottom:10px;">📎</div>
        <div style="font-weight:600;color:var(--navy);font-size:15px;">Click to browse or drag & drop</div>
        <div style="font-size:12px;color:var(--gray-400);margin-top:6px;">PDF, DOCX, JPG, PNG — max 20MB</div>
        <div id="fileName" style="margin-top:12px;font-size:13px;color:var(--navy);font-weight:600;"></div>
      </div>
      <input type="file" id="fileInput" name="file_upload" required accept=".pdf,.docx,.doc,.jpg,.jpeg,.png" style="display:none;"
             onchange="showFileName(this)">
    </div>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn btn-primary">Upload Document</button>
      <a href="/cdas/modules/user/documents.php" class="btn btn-outline">Cancel</a>
    </div>
  </form>
</div>

<script>
function showFileName(input) {
  if (input.files && input.files[0]) {
    document.getElementById('fileName').textContent = '📄 ' + input.files[0].name;
  }
}
function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropZone').style.background = 'var(--sky-pale)';
  const input = document.getElementById('fileInput');
  input.files = e.dataTransfer.files;
  showFileName(input);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
