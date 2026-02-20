<?php
// ============================================================
// CDAS v3 — Shared Header
// Layout: 3-column (sidebar | main | right panel)
// Colors: Original navy/sky palette
// ============================================================
$user      = currentUser();
$role      = $user['role'];
$pageTitle = $pageTitle ?? 'CDAS';

if ($role === 'superadmin') {
    $navEssentials = [
        ['icon'=>'home',     'label'=>'Home',        'href'=>'/cdas/modules/superadmin/dashboard.php', 'key'=>'dashboard'],
        ['icon'=>'users',    'label'=>'Users',       'href'=>'/cdas/modules/superadmin/users.php',     'key'=>'users'],
        ['icon'=>'calendar', 'label'=>'Calendar',    'href'=>'#',                                       'key'=>'calendar'],
        ['icon'=>'building', 'label'=>'Units',       'href'=>'/cdas/modules/superadmin/units.php',     'key'=>'units'],
        ['icon'=>'folder',   'label'=>'Documents',   'href'=>'/cdas/modules/superadmin/documents.php', 'key'=>'documents'],
        ['icon'=>'zap',      'label'=>'Automations', 'href'=>'#',                                       'key'=>'automations'],
        ['icon'=>'chart',    'label'=>'Reports',     'href'=>'/cdas/modules/superadmin/reports.php',   'key'=>'reports'],
    ];
} elseif ($role === 'admin') {
    $navEssentials = [
        ['icon'=>'home',   'label'=>'Home',          'href'=>'/cdas/modules/admin/dashboard.php',      'key'=>'dashboard'],
        ['icon'=>'bell',   'label'=>'Requests',      'href'=>'/cdas/modules/admin/print_requests.php', 'key'=>'requests'],
        ['icon'=>'folder', 'label'=>'Documents',     'href'=>'/cdas/modules/admin/documents.php',      'key'=>'documents'],
        ['icon'=>'users',  'label'=>'Staff',         'href'=>'/cdas/modules/admin/staff.php',          'key'=>'staff'],
        ['icon'=>'chart',  'label'=>'Reporting',     'href'=>'#',                                       'key'=>'reporting'],
    ];
} else {
    $navEssentials = [
        ['icon'=>'home',   'label'=>'Home',          'href'=>'/cdas/modules/user/dashboard.php',       'key'=>'dashboard'],
        ['icon'=>'folder', 'label'=>'Documents',     'href'=>'/cdas/modules/user/documents.php',       'key'=>'documents'],
        ['icon'=>'upload', 'label'=>'Upload',        'href'=>'/cdas/modules/user/upload.php',          'key'=>'upload'],
        ['icon'=>'print',  'label'=>'Print Requests','href'=>'/cdas/modules/user/print_requests.php',  'key'=>'requests'],
        ['icon'=>'chart',  'label'=>'Reporting',     'href'=>'#',                                       'key'=>'reporting'],
    ];
}

$notifCount = 0;
if (!empty($user['user_id'])) {
    $nRes = $conn->query("SELECT COUNT(*) FROM notifications WHERE user_id={$user['user_id']} AND is_read=0");
    if ($nRes) $notifCount = (int)$nRes->fetch_row()[0];
}

$recentFiles = null;
if ($role === 'superadmin') {
    $recentFiles = $conn->query("SELECT d.*, u.full_name AS uploader FROM documents d JOIN users u ON d.uploaded_by=u.user_id WHERE d.status='active' ORDER BY d.created_at DESC LIMIT 6");
} elseif (!empty($user['unit_id'])) {
    $uid = (int)$user['unit_id'];
    $recentFiles = $conn->query("SELECT d.*, u.full_name AS uploader FROM documents d JOIN users u ON d.uploaded_by=u.user_id WHERE d.unit_id=$uid AND d.status='active' ORDER BY d.created_at DESC LIMIT 6");
}

function navIcon(string $name): string {
    $icons = [
      'home'    => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
      'users'   => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
      'folder'  => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
      'calendar'=> '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
      'building'=> '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>',
      'zap'     => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
      'chart'   => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
      'bell'    => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
      'upload'  => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>',
      'print'   => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>',
      'logout'  => '<svg class="ni" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — CDAS</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ── CSS Variables — Original Navy/Sky Palette ── */
:root {
  /* Backgrounds — navy-based darks */
  --bg:          #0d1526;   /* deepest navy background */
  --bg2:         #111d3a;   /* sidebar & right panel */
  --bg3:         #172244;   /* cards, panels */
  --bg4:         #1e2d55;   /* hover states */
  --bg5:         #243466;   /* active/selected */

  /* Borders */
  --border:      rgba(190,227,240,0.07);
  --border2:     rgba(190,227,240,0.14);
  --border3:     rgba(190,227,240,0.22);

  /* Brand colors */
  --navy:        #1B2A6B;
  --navy2:       #2D3E8A;
  --sky:         #BEE3F0;
  --sky2:        #8DCDE0;
  --sky3:        #5BAFD6;
  --sky-pale:    rgba(190,227,240,0.08);

  /* Text */
  --text:        #e4eef5;
  --text2:       #7a9ab5;
  --text3:       #3d5a78;

  /* Accent = sky blue */
  --accent:      #5BAFD6;
  --accent2:     #BEE3F0;

  /* Status */
  --green:       #3dbf82;
  --orange:      #e8a44a;
  --red:         #d95b5b;

  --sidebar-w:   230px;
  --right-w:     260px;
  --radius:      10px;
  --radius-lg:   14px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  display: flex;
  height: 100vh;
  overflow: hidden;
}
a { text-decoration: none; color: inherit; }
button { font-family: 'DM Sans', sans-serif; }

::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 4px; }

/* ════════ LEFT SIDEBAR ════════ */
.sidebar {
  width: var(--sidebar-w);
  background: var(--bg2);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  overflow-y: auto;
  z-index: 10;
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 16px 14px;
  border-bottom: 1px solid var(--border);
}

.user-avatar {
  width: 34px; height: 34px;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--navy2), var(--sky3));
  display: grid;
  place-items: center;
  font-size: 13px;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.user-meta .uname  { font-size: 12px; font-weight: 600; color: var(--text); line-height: 1.2; }
.user-meta .utag   { font-size: 10px; color: var(--text3); margin-top: 2px; }

.sidebar-collapse {
  margin-left: auto;
  background: none;
  border: none;
  color: var(--text3);
  cursor: pointer;
  font-size: 14px;
  padding: 4px;
  line-height: 1;
}

.sidebar-search {
  padding: 12px 12px 8px;
}
.search-row {
  display: flex;
  align-items: center;
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0 10px;
  gap: 8px;
}
.search-row svg { flex-shrink: 0; opacity: .5; }
.search-row input {
  flex: 1;
  background: none;
  border: none;
  outline: none;
  font-size: 12px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  padding: 8px 0;
}
.search-row input::placeholder { color: var(--text3); }
.kbd {
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px;
  color: var(--text3);
  background: var(--bg4);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 1px 5px;
  white-space: nowrap;
}

.nav-section { padding: 4px 8px; }
.nav-section-label {
  font-size: 10px;
  font-weight: 600;
  color: var(--text3);
  text-transform: uppercase;
  letter-spacing: 1.2px;
  padding: 10px 8px 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 8px;
  color: var(--text2);
  font-size: 13px;
  font-weight: 400;
  cursor: pointer;
  transition: background .15s, color .15s;
  margin-bottom: 1px;
}
.nav-item:hover  { background: var(--bg3); color: var(--text); }
.nav-item.active {
  background: var(--bg5);
  color: var(--sky);
  font-weight: 600;
  border-left: 2px solid var(--sky3);
  padding-left: 8px;
}
.nav-item .ni {
  width: 15px; height: 15px;
  flex-shrink: 0;
  opacity: .6;
}
.nav-item.active .ni { opacity: 1; color: var(--sky2); }

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 10px 4px;
}
.section-header .slabel {
  font-size: 10px;
  font-weight: 600;
  color: var(--text3);
  text-transform: uppercase;
  letter-spacing: 1.2px;
}
.section-header .sadd {
  background: none;
  border: none;
  color: var(--text3);
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
  padding: 2px 4px;
  border-radius: 4px;
}
.section-header .sadd:hover { background: var(--bg3); color: var(--text); }

.project-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 10px;
  border-radius: 8px;
  color: var(--text2);
  font-size: 12px;
  cursor: pointer;
  transition: background .15s;
  margin-bottom: 1px;
}
.project-item:hover { background: var(--bg3); color: var(--text); }

.proj-dot {
  width: 22px; height: 22px;
  border-radius: 6px;
  display: grid;
  place-items: center;
  font-size: 10px;
  font-weight: 700;
  flex-shrink: 0;
}

.sidebar-bottom {
  margin-top: auto;
  padding: 10px 8px;
  border-top: 1px solid var(--border);
}

/* ════════ MAIN WRAP ════════ */
.main-wrap { flex: 1; display: flex; overflow: hidden; }
.main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* Topbar */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 24px;
  border-bottom: 1px solid var(--border);
  background: var(--bg2);
  flex-shrink: 0;
}
.topbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--text2);
}
.topbar-left .sep     { color: var(--text3); }
.topbar-left .current { color: var(--text); font-weight: 600; }

.topbar-right { display: flex; align-items: center; gap: 8px; }

.topbar-search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 7px 14px;
  min-width: 220px;
}
.topbar-search input {
  background: none;
  border: none;
  outline: none;
  font-size: 12px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  width: 100%;
}
.topbar-search input::placeholder { color: var(--text3); }

.icon-btn {
  width: 34px; height: 34px;
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: 8px;
  display: grid;
  place-items: center;
  cursor: pointer;
  color: var(--text2);
  transition: all .15s;
  position: relative;
}
.icon-btn:hover { background: var(--bg4); color: var(--sky); border-color: var(--border2); }

.notif-dot {
  position: absolute;
  top: 6px; right: 6px;
  width: 7px; height: 7px;
  background: var(--red);
  border-radius: 50%;
  border: 1.5px solid var(--bg2);
}

/* Page scroll */
.page-scroll { flex: 1; overflow-y: auto; padding: 24px; }

/* Page title row */
.page-title-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 24px;
}
.page-h1 {
  font-family: 'Playfair Display', serif;
  font-size: 28px;
  font-weight: 700;
  color: var(--text);
  line-height: 1;
}
.page-actions { display: flex; gap: 8px; }

/* ════════ RIGHT PANEL ════════ */
.right-panel {
  width: var(--right-w);
  background: var(--bg2);
  border-left: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  flex-shrink: 0;
}

.rp-header {
  padding: 16px 16px 10px;
  border-bottom: 1px solid var(--border);
}
.rp-title { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 4px; line-height: 1.4; }
.rp-sub   { font-size: 11px; color: var(--text3); }

.rp-action-row {
  display: flex;
  gap: 6px;
  padding: 10px 16px;
  border-bottom: 1px solid var(--border);
}
.rp-action-btn {
  width: 30px; height: 30px;
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: 7px;
  display: grid;
  place-items: center;
  cursor: pointer;
  color: var(--text2);
  font-size: 12px;
  transition: background .15s, color .15s;
}
.rp-action-btn:hover { background: var(--bg4); color: var(--sky); }

.rp-file-card {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  transition: background .15s;
}
.rp-file-card:hover { background: var(--bg3); }

.rp-file-name {
  font-size: 12px;
  font-weight: 500;
  color: var(--text);
  margin-bottom: 5px;
  line-height: 1.4;
  word-break: break-word;
}
.rp-file-meta {
  font-size: 11px;
  color: var(--text3);
  line-height: 1.7;
}
.rp-detail-btn {
  display: inline-block;
  margin-top: 8px;
  font-size: 10px;
  font-weight: 600;
  color: var(--sky3);
  background: rgba(91,175,214,.12);
  border: 1px solid rgba(91,175,214,.22);
  border-radius: 5px;
  padding: 3px 9px;
  cursor: pointer;
  transition: background .15s;
}
.rp-detail-btn:hover { background: rgba(91,175,214,.22); }

/* ════════ CARDS & GRID ════════ */
.doc-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
  gap: 14px;
  margin-bottom: 28px;
}

.doc-card {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 20px;
  cursor: pointer;
  transition: border-color .2s, background .2s, transform .15s;
}
.doc-card:hover {
  border-color: var(--border3);
  background: var(--bg4);
  transform: translateY(-1px);
}

.doc-card-icon {
  width: 38px; height: 38px;
  border-radius: 10px;
  background: rgba(190,227,240,.08);
  border: 1px solid var(--border2);
  display: grid;
  place-items: center;
  font-size: 18px;
  margin-bottom: 14px;
}

.doc-card-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 6px;
  line-height: 1.3;
}
.doc-card-desc {
  font-size: 11px;
  color: var(--text3);
  line-height: 1.6;
  margin-bottom: 14px;
}
.doc-card-footer { display: flex; align-items: center; }

.connect-btn {
  font-size: 11px;
  font-weight: 500;
  color: var(--text2);
  background: var(--bg);
  border: 1px solid var(--border2);
  border-radius: 6px;
  padding: 5px 12px;
  cursor: pointer;
  transition: all .15s;
  text-decoration: none;
  display: inline-block;
}
.connect-btn:hover { background: var(--sky3); color: var(--bg); border-color: var(--sky3); }

/* Section label */
.section-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.section-title .count { font-size: 11px; color: var(--text3); font-weight: 400; }

/* Shortcut folders */
.shortcut-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
  gap: 10px;
  margin-bottom: 28px;
}
.shortcut-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 10px 6px;
  border-radius: 10px;
  transition: background .15s;
}
.shortcut-item:hover { background: var(--bg3); }
.shortcut-folder {
  width: 64px; height: 50px;
  border-radius: 10px;
  background: linear-gradient(145deg, var(--bg4), var(--bg3));
  border: 1px solid var(--border2);
  display: grid;
  place-items: center;
  font-size: 22px;
  transition: border-color .15s;
}
.shortcut-item:hover .shortcut-folder { border-color: var(--sky3); }
.shortcut-label { font-size: 10px; color: var(--text2); text-align: center; line-height: 1.3; }

/* Stats */
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}
.stat-box {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 16px 18px;
}
.stat-box .val {
  font-size: 26px;
  font-weight: 700;
  color: var(--sky);
  font-family: 'JetBrains Mono', monospace;
  line-height: 1;
  margin-bottom: 4px;
}
.stat-box .lbl { font-size: 11px; color: var(--text3); }
.stat-box .chg { font-size: 11px; color: var(--green); margin-top: 6px; font-weight: 500; }

/* Charts */
.chart-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-bottom: 24px;
}
.chart-box {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 18px;
  min-height: 200px;
}
.chart-box .ctitle { font-size: 12px; font-weight: 600; color: var(--text); margin-bottom: 14px; }

/* Panel */
.panel { background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 16px; overflow: hidden; }
.panel-header {
  padding: 13px 18px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.panel-title { font-size: 13px; font-weight: 600; color: var(--text); }
.panel-body  { padding: 18px; }

/* Table */
.data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.data-table thead tr { border-bottom: 1px solid var(--border); }
.data-table thead th {
  text-align: left;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text3);
  padding: 8px 12px;
}
.data-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
.data-table tbody tr:hover { background: var(--bg4); }
.data-table tbody td { padding: 11px 12px; color: var(--text2); }
.data-table tbody td:first-child { color: var(--text); font-weight: 500; }

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 600;
}
.badge-pdf      { background: rgba(217,91,91,.15);  color: #e07070; border: 1px solid rgba(217,91,91,.25); }
.badge-docx     { background: rgba(91,175,214,.15); color: var(--sky2); border: 1px solid rgba(91,175,214,.25); }
.badge-image    { background: rgba(61,191,130,.15); color: var(--green); border: 1px solid rgba(61,191,130,.25); }
.badge-pending  { background: rgba(232,164,74,.15); color: var(--orange); border: 1px solid rgba(232,164,74,.25); }
.badge-approved { background: rgba(61,191,130,.15); color: var(--green); border: 1px solid rgba(61,191,130,.25); }
.badge-denied   { background: rgba(217,91,91,.15);  color: var(--red); border: 1px solid rgba(217,91,91,.25); }
.badge-active   { background: rgba(61,191,130,.15); color: var(--green); border: 1px solid rgba(61,191,130,.25); }
.badge-inactive { background: rgba(122,154,181,.12); color: var(--text2); border: 1px solid rgba(122,154,181,.2); }
.badge-superadmin { background: rgba(190,227,240,.1); color: var(--sky); border: 1px solid rgba(190,227,240,.2); }
.badge-admin    { background: rgba(91,175,214,.1);  color: var(--sky2); border: 1px solid rgba(91,175,214,.2); }
.badge-user     { background: rgba(122,154,181,.1); color: var(--text2); border: 1px solid rgba(122,154,181,.2); }

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  text-decoration: none;
  transition: opacity .15s, transform .1s;
}
.btn:hover { opacity: .88; transform: translateY(-1px); }
.btn-primary { background: var(--sky3); color: var(--bg); }
.btn-ghost   { background: var(--bg3); color: var(--text2); border: 1px solid var(--border2); }
.btn-ghost:hover { color: var(--sky); }
.btn-danger  { background: rgba(217,91,91,.15); color: var(--red); border: 1px solid rgba(217,91,91,.3); }
.btn-success { background: rgba(61,191,130,.15); color: var(--green); border: 1px solid rgba(61,191,130,.3); }
.btn-sm      { padding: 5px 10px; font-size: 11px; }

/* Filter bar */
.filter-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.filter-input {
  flex: 1;
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 9px 14px;
  font-size: 12px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  outline: none;
  transition: border-color .2s;
}
.filter-input::placeholder { color: var(--text3); }
.filter-input:focus { border-color: var(--sky3); }
.filter-select {
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 9px 12px;
  font-size: 12px;
  color: var(--text2);
  font-family: 'DM Sans', sans-serif;
  outline: none;
}

/* Form */
.form-group { margin-bottom: 14px; }
.form-label {
  display: block;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text3);
  margin-bottom: 6px;
}
.form-control {
  width: 100%;
  background: var(--bg);
  border: 1px solid var(--border2);
  border-radius: 8px;
  padding: 9px 14px;
  font-size: 13px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.form-control::placeholder { color: var(--text3); }
.form-control:focus { border-color: var(--sky3); box-shadow: 0 0 0 3px rgba(91,175,214,.12); }

/* Alerts */
.success-bar { background: rgba(61,191,130,.1); border: 1px solid rgba(61,191,130,.25); color: var(--green); border-radius: 8px; padding: 10px 14px; font-size: 12px; margin-bottom: 14px; }
.error-bar   { background: rgba(217,91,91,.1);  border: 1px solid rgba(217,91,91,.25);  color: var(--red);   border-radius: 8px; padding: 10px 14px; font-size: 12px; margin-bottom: 14px; }

/* Modal */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.65); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.modal-overlay.open { display: flex; }
.modal-box {
  background: var(--bg2);
  border: 1px solid var(--border2);
  border-radius: var(--radius-lg);
  padding: 28px;
  width: 460px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
}
.modal-title { font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 18px; }

/* Animations */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}
.page-scroll > * { animation: fadeUp .2s ease both; }
.page-scroll > *:nth-child(2) { animation-delay: .04s; }
.page-scroll > *:nth-child(3) { animation-delay: .08s; }
.page-scroll > *:nth-child(4) { animation-delay: .12s; }
.page-scroll > *:nth-child(5) { animation-delay: .16s; }
</style>
</head>
<body>

<!-- ══ LEFT SIDEBAR ══ -->
<aside class="sidebar">

  <!-- User row -->
  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
    <div class="user-meta">
      <div class="uname"><?= htmlspecialchars(explode(' ',$user['full_name'])[0]) ?></div>
      <div class="utag"><?= $role==='superadmin'?'Super Admin':($role==='admin'?'Head of Unit':'Employee') ?></div>
    </div>
    <button class="sidebar-collapse">⇤</button>
  </div>

  <!-- Search -->
  <div class="sidebar-search">
    <div class="search-row">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text3)"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" placeholder="Search...">
      <span class="kbd">⌘ F</span>
    </div>
  </div>

  <!-- Essentials nav -->
  <div class="nav-section">
    <div class="nav-section-label">Essentials</div>
    <?php foreach ($navEssentials as $item): ?>
    <a href="<?= $item['href'] ?>" class="nav-item <?= ($activePage??'')===$item['key']?'active':'' ?>">
      <?= navIcon($item['icon']) ?>
      <?= $item['label'] ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Units -->
  <div class="nav-section" style="padding-top:0;">
    <div class="section-header">
      <span class="slabel">Units</span>
      <button class="sadd">+</button>
    </div>
    <?php
    $sideUnits = $conn->query("SELECT * FROM units ORDER BY unit_name LIMIT 6");
    $dotColors = ['#5BAFD6','#3dbf82','#e8a44a','#d95b5b','#a78bfa','#fb923c'];
    $ci = 0;
    while ($u = $sideUnits->fetch_assoc()):
    ?>
    <div class="project-item">
      <div class="proj-dot" style="background:<?= $dotColors[$ci%6] ?>18;border:1px solid <?= $dotColors[$ci%6] ?>44;color:<?= $dotColors[$ci%6] ?>;">
        <?= substr($u['unit_code'],0,2) ?>
      </div>
      <?= htmlspecialchars($u['unit_name']) ?>
    </div>
    <?php $ci++; endwhile; ?>
  </div>

  <!-- Bottom -->
  <div class="sidebar-bottom">
    <a href="/cdas/includes/logout.php" class="nav-item" style="color:var(--red);">
      <?= navIcon('logout') ?>
      Sign Out
    </a>
  </div>
</aside>

<!-- ══ MAIN WRAP ══ -->
<div class="main-wrap">
<div class="main-content">

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <span>CDAS</span>
      <span class="sep">/</span>
      <span class="current"><?= htmlspecialchars($pageTitle) ?></span>
    </div>
    <div class="topbar-right">
      <div class="topbar-search">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text3)"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" placeholder="Search documents..." id="topbarSearch">
        <span class="kbd">⌘ F</span>
      </div>
      <div class="icon-btn" title="Filter">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      </div>
      <div class="icon-btn" title="Notifications" style="position:relative;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <?php if ($notifCount > 0): ?><div class="notif-dot"></div><?php endif; ?>
      </div>
      <div class="icon-btn" title="Grid view">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      </div>
    </div>
  </header>

  <!-- Page scroll -->
  <div class="page-scroll">
