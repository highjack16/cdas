<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['superadmin']);

$pageTitle  = 'Team Members';
$activePage = 'users';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name   = clean($_POST['full_name']);
        $email  = clean($_POST['email']);
        $role   = clean($_POST['role']);
        $unitId = (int)$_POST['unit_id'] ?: null;
        $hash   = hashPassword($_POST['password']);
        $stmt   = $conn->prepare("INSERT INTO users (unit_id, full_name, email, password, role) VALUES (?,?,?,?,?)");
        $stmt->bind_param('issss', $unitId, $name, $email, $hash, $role);
        if ($stmt->execute()) {
            logActivity($conn, currentUser()['user_id'], 'CREATE', 'user', $stmt->insert_id, "Created user: $name");
            $msg = 'success';
        }
        $stmt->close();
    }
    if ($action === 'toggle_status') {
        $userId    = (int)$_POST['user_id'];
        $newStatus = clean($_POST['new_status']);
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE user_id=?");
        $stmt->bind_param('si', $newStatus, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

$users       = $conn->query("SELECT u.*, un.unit_name FROM users u LEFT JOIN units un ON u.unit_id = un.unit_id ORDER BY u.role, u.full_name");
$units       = $conn->query("SELECT * FROM units ORDER BY unit_name");
$totalUsers  = $users->num_rows;
$activeUsers = (int)$conn->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetch_row()[0];

include __DIR__ . '/../../includes/header.php';
?>

<style>
/* ── Team Members page styles ── */

.tm-title { font-family:'Playfair Display',serif; font-size:30px; font-weight:700; color:var(--text); margin-bottom:4px; }
.tm-sub   { font-size:13px; color:var(--text3); }

/* Capacity banner */
.cap-banner {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 24px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  margin-bottom: 24px;
}
.cap-left { flex: 1; }
.cap-name-row { display:flex; align-items:center; gap:10px; margin-bottom:4px; }
.cap-name { font-size:15px; font-weight:700; color:var(--text); }
.cap-active-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  color: var(--green);
  background: rgba(61,191,130,.1);
  border: 1px solid rgba(61,191,130,.25);
  border-radius: 20px;
  padding: 2px 10px;
}
.cap-active-pill::before {
  content:'';
  width:6px; height:6px;
  border-radius:50%;
  background:var(--green);
}
.cap-desc { font-size:12px; color:var(--text3); margin-bottom:18px; }

.prog-track {
  height: 8px;
  background: var(--bg4);
  border-radius: 20px;
  overflow: hidden;
  margin-bottom: 8px;
}
.prog-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--sky3), var(--sky2));
  border-radius: 20px;
  transition: width .5s ease;
}
.prog-lbl { font-size:12px; color:var(--text3); }

.cap-right { text-align:right; flex-shrink:0; }
.cap-big {
  font-family: 'JetBrains Mono', monospace;
  font-size: 48px;
  font-weight: 700;
  color: var(--text);
  line-height: 1;
}
.cap-big .sym { font-size:22px; color:var(--text3); vertical-align:top; margin-top:6px; display:inline-block; }
.cap-big-lbl { font-size:10px; text-transform:uppercase; letter-spacing:1.2px; color:var(--text3); margin-top:6px; }

/* Members panel */
.members-panel {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.mp-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 22px;
  border-bottom: 1px solid var(--border);
  gap: 16px;
}
.mp-section-title { font-size:14px; font-weight:600; color:var(--text); }
.mp-controls { display:flex; align-items:center; gap:10px; }

.mp-searchbox {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg4);
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 7px 12px;
}
.mp-searchbox svg { color:var(--text3); flex-shrink:0; }
.mp-searchbox input {
  background:none; border:none; outline:none;
  font-size:12px; color:var(--text);
  font-family:'DM Sans',sans-serif;
  width:180px;
}
.mp-searchbox input::placeholder { color:var(--text3); }

/* Table */
.mt { width:100%; border-collapse:collapse; }
.mt thead tr { background:var(--bg4); border-bottom:1px solid var(--border); }
.mt thead th {
  text-align:left;
  font-size:11px;
  font-weight:600;
  text-transform:uppercase;
  letter-spacing:.8px;
  color:var(--text3);
  padding:11px 18px;
}
.mt thead th:first-child { width:48px; text-align:center; }

.mt tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
.mt tbody tr:hover { background:var(--bg4); }
.mt tbody tr:last-child { border-bottom:none; }
.mt tbody td { padding:14px 18px; vertical-align:middle; }
.mt tbody td:first-child { text-align:center; }

input[type="checkbox"].tbl-cb {
  width:16px; height:16px;
  accent-color: var(--sky3);
  cursor:pointer;
}

/* User cell */
.uc { display:flex; align-items:center; gap:12px; }
.uc-av {
  width:38px; height:38px;
  border-radius:50%;
  background: linear-gradient(135deg, var(--navy2), var(--sky3));
  display:grid;
  place-items:center;
  font-size:14px;
  font-weight:700;
  color:white;
  flex-shrink:0;
  border:2px solid rgba(190,227,240,.15);
}
.uc-nm  { font-size:13px; font-weight:600; color:var(--text); line-height:1.3; }
.uc-em  { font-size:11px; color:var(--text3); margin-top:1px; }

/* Date */
.td-date { font-size:13px; color:var(--text2); }

/* Tag pills */
.tags { display:flex; gap:6px; flex-wrap:wrap; }
.tag {
  display:inline-block;
  padding:3px 10px;
  border-radius:20px;
  font-size:11px;
  font-weight:500;
  background:var(--bg);
  border:1px solid var(--border2);
  color:var(--text2);
  white-space:nowrap;
}
.tag.t-super { color:var(--sky);  background:rgba(190,227,240,.06); border-color:rgba(190,227,240,.25); }
.tag.t-admin { color:var(--sky2); background:rgba(141,205,224,.06); border-color:rgba(141,205,224,.2); }

/* Status */
.st-pill {
  display:inline-flex;
  align-items:center;
  gap:5px;
  font-size:11px;
  font-weight:600;
  padding:3px 10px;
  border-radius:20px;
}
.st-pill.active   { color:var(--green); background:rgba(61,191,130,.1);   border:1px solid rgba(61,191,130,.25); }
.st-pill.inactive { color:var(--text3); background:rgba(122,154,181,.08); border:1px solid var(--border); }
.st-pill.active::before, .st-pill.inactive::before {
  content:''; width:5px; height:5px; border-radius:50%;
}
.st-pill.active::before   { background:var(--green); }
.st-pill.inactive::before { background:var(--text3); }

/* Action btns */
.ac-wrap { display:flex; gap:6px; }
.ac-btn {
  font-size:11px;
  font-weight:500;
  padding:5px 12px;
  border-radius:6px;
  cursor:pointer;
  border:1px solid var(--border2);
  background:var(--bg4);
  color:var(--text2);
  font-family:'DM Sans',sans-serif;
  transition:all .15s;
}
.ac-btn:hover            { color:var(--text); border-color:var(--border3); background:var(--bg5); }
.ac-btn.ac-deact:hover   { color:var(--red);   border-color:rgba(217,91,91,.35); }
.ac-btn.ac-act:hover     { color:var(--green); border-color:rgba(61,191,130,.35); }

/* Pagination */
.pg-row {
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:14px 22px;
  border-top:1px solid var(--border);
}
.pg-info { font-size:12px; color:var(--text3); }
.pg-btns { display:flex; gap:6px; }
.pg-b {
  font-size:12px;
  padding:5px 14px;
  border-radius:6px;
  border:1px solid var(--border2);
  background:var(--bg4);
  color:var(--text2);
  cursor:pointer;
  font-family:'DM Sans',sans-serif;
  transition:all .15s;
}
.pg-b:hover { color:var(--sky); border-color:var(--border3); }

/* Toast */
.toast {
  display:none;
  position:fixed;
  bottom:28px; right:28px;
  background:var(--bg3);
  border:1px solid rgba(61,191,130,.35);
  color:var(--green);
  border-radius:10px;
  padding:12px 20px;
  font-size:13px;
  font-weight:500;
  z-index:9999;
  box-shadow:0 8px 24px rgba(0,0,0,.4);
  animation: fadeUp .3s ease;
}
.toast.show { display:block; }
</style>

<!-- Page title row -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;">
  <div>
    <div class="tm-title">Team members</div>
    <div class="tm-sub">Manage your team members and their account permissions here.</div>
  </div>
  <button onclick="document.getElementById('createModal').classList.add('open')" class="btn btn-primary" style="padding:10px 20px;margin-top:4px;">
    + Add Member
  </button>
</div>

<!-- Capacity banner -->
<div class="cap-banner">
  <div class="cap-left">
    <div class="cap-name-row">
      <span class="cap-name">System capacity</span>
      <span class="cap-active-pill">Active</span>
    </div>
    <div class="cap-desc">Current active users in the system repository. Up to 50 active users.</div>
    <div class="prog-track">
      <div class="prog-fill" style="width:<?= min(100, ($activeUsers/50)*100) ?>%;"></div>
    </div>
    <div class="prog-lbl"><?= $activeUsers ?> of 50 users</div>
  </div>
  <div class="cap-right">
    <div class="cap-big">
      <span class="sym">#</span><?= $activeUsers ?>
    </div>
    <div class="cap-big-lbl">Total Active</div>
  </div>
</div>

<!-- Members table panel -->
<div class="members-panel">
  <div class="mp-topbar">
    <span class="mp-section-title">All members</span>
    <div class="mp-controls">
      <div class="mp-searchbox">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="memberSearch" placeholder="Search members..." oninput="filterMembers()">
      </div>
      <button class="btn btn-ghost" style="border:1px solid var(--border);font-size:12px;padding:7px 14px;">
        ⬇ Download CSV
      </button>
    </div>
  </div>

  <table class="mt" id="memberTable">
    <thead>
      <tr>
        <th><input type="checkbox" class="tbl-cb" id="selectAll" onchange="toggleAll(this)"></th>
        <th>Name</th>
        <th>Date added</th>
        <th>Role / Unit</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php $users->data_seek(0); while ($u = $users->fetch_assoc()):
      $tagClass  = $u['role']==='superadmin' ? 't-super' : ($u['role']==='admin' ? 't-admin' : '');
      $roleLabel = $u['role']==='superadmin' ? 'Super Admin' : ($u['role']==='admin' ? 'Head of Unit' : 'Employee');
    ?>
    <tr>
      <td><input type="checkbox" class="tbl-cb row-cb"></td>

      <td>
        <div class="uc">
          <div class="uc-av"><?= strtoupper(substr($u['full_name'],0,1)) ?></div>
          <div>
            <div class="uc-nm"><?= htmlspecialchars($u['full_name']) ?></div>
            <div class="uc-em"><?= htmlspecialchars($u['email']) ?></div>
          </div>
        </div>
      </td>

      <td><div class="td-date"><?= date('M d, Y', strtotime($u['created_at'])) ?></div></td>

      <td>
        <div class="tags">
          <span class="tag <?= $tagClass ?>"><?= $roleLabel ?></span>
          <?php if ($u['unit_name']): ?>
          <span class="tag"><?= htmlspecialchars($u['unit_name']) ?></span>
          <?php endif; ?>
        </div>
      </td>

      <td>
        <span class="st-pill <?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span>
      </td>

      <td>
        <div class="ac-wrap">
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
            <input type="hidden" name="new_status" value="<?= $u['status']==='active'?'inactive':'active' ?>">
            <button type="submit" class="ac-btn <?= $u['status']==='active'?'ac-deact':'ac-act' ?>">
              <?= $u['status']==='active' ? 'Deactivate' : 'Activate' ?>
            </button>
          </form>
          <button class="ac-btn">Edit</button>
        </div>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <div class="pg-row">
    <span class="pg-info">Showing <?= $totalUsers ?> of <?= $totalUsers ?> members</span>
    <div class="pg-btns">
      <button class="pg-b">← Previous</button>
      <button class="pg-b">Next →</button>
    </div>
  </div>
</div>

<!-- CREATE MODAL -->
<div id="createModal" class="modal-overlay">
  <div class="modal-box">
    <div class="modal-title">Add Team Member</div>
    <p style="font-size:12px;color:var(--text3);margin-top:4px;margin-bottom:20px;">Fill in the details to register a new CDAS account.</p>

    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" required placeholder="e.g. Juan dela Cruz">
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required placeholder="juan@marina.gov.ph">
      </div>
      <div class="form-group">
        <label class="form-label">Temporary Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Min. 8 characters">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
          <label class="form-label">Role</label>
          <select name="role" class="form-control" required>
            <option value="user">Employee</option>
            <option value="admin">Head of Unit</option>
            <option value="superadmin">Super Admin</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Unit / Office</label>
          <select name="unit_id" class="form-control">
            <option value="">— None —</option>
            <?php $units->data_seek(0); while ($un = $units->fetch_assoc()): ?>
            <option value="<?= $un['unit_id'] ?>"><?= htmlspecialchars($un['unit_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:24px;">
        <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;">Create Account</button>
        <button type="button" onclick="document.getElementById('createModal').classList.remove('open')" class="btn btn-ghost" style="flex:1;padding:12px;border:1px solid var(--border2);">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Toast notification -->
<?php if ($msg === 'success'): ?>
<div class="toast show" id="toast">✅ Team member added successfully!</div>
<script>setTimeout(()=>document.getElementById('toast').classList.remove('show'),3500);</script>
<?php endif; ?>

<script>
function filterMembers() {
  const q = document.getElementById('memberSearch').value.toLowerCase();
  document.querySelectorAll('#memberTable tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
function toggleAll(master) {
  document.querySelectorAll('.row-cb').forEach(cb => cb.checked = master.checked);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>