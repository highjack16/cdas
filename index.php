<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CDAS — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:     #EAF4F8;
    --bg2:    #FFFFFF;
    --bg3:    #D6ECF3;
    --bg4:    #BEE3F0;
    --border: rgba(27,42,107,0.1);
    --border2:rgba(27,42,107,0.18);
    --navy:   #1B2A6B;
    --navy2:  #243480;
    --sky:    #BEE3F0;
    --sky2:   #9DD5E8;
    --sky3:   #7ECAE8;
    --text:   #1B2A6B;
    --text2:  rgba(27,42,107,0.7);
    --text3:  rgba(27,42,107,0.5);
    --red:    #d95b5b;
    --green:  #3dbf82;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    overflow: hidden;
  }

  /* Subtle grid overlay */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(27,42,107,.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(27,42,107,.04) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
  }

  /* Navy glow orbs */
  .orb1 {
    position: fixed;
    width: 700px; height: 700px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(190,227,240,.5) 0%, transparent 65%);
    top: -250px; left: -150px;
    pointer-events: none;
  }
  .orb2 {
    position: fixed;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(27,42,107,.08) 0%, transparent 70%);
    bottom: -100px; right: 300px;
    pointer-events: none;
  }

  /* LEFT panel */
  .left {
    flex: 1.1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 52px 64px;
    position: relative;
  }

  .brand { display: flex; align-items: center; gap: 14px; }
  .brand-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--navy), var(--navy2));
    border: 1px solid rgba(27,42,107,.2);
    display: grid;
    place-items: center;
    font-size: 20px;
  }
  .brand-name {
    font-family: 'JetBrains Mono', monospace;
    font-size: 18px;
    font-weight: 700;
    color: var(--navy);
    letter-spacing: 3px;
  }
  .brand-sub {
    font-size: 10px;
    color: var(--text3);
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-top: 3px;
  }

  .hero { max-width: 440px; }
  .hero-pill {
    display: inline-block;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--navy);
    background: rgba(27,42,107,.1);
    border: 1px solid rgba(27,42,107,.2);
    border-radius: 20px;
    padding: 4px 14px;
    margin-bottom: 22px;
  }
  .hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 46px;
    font-weight: 700;
    line-height: 1.1;
    color: var(--navy);
    margin-bottom: 18px;
  }
  .hero h1 span { color: var(--navy2); }
  .hero p {
    font-size: 14px;
    color: var(--text2);
    line-height: 1.7;
    font-weight: 300;
  }

  .hero-stats { display: flex; gap: 36px; margin-top: 40px; }
  .hstat .num {
    font-family: 'JetBrains Mono', monospace;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy);
    line-height: 1;
  }
  .hstat .lbl {
    font-size: 10px;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 4px;
  }

  .left-footer { font-size: 11px; color: var(--text3); }

  /* RIGHT panel */
  .right {
    width: 440px;
    flex-shrink: 0;
    background: var(--bg2);
    border-left: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 44px;
    box-shadow: -10px 0 40px rgba(27,42,107,.05);
  }

  .login-card { width: 100%; }
  .login-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 4px;
  }
  .login-card .sub { font-size: 13px; color: var(--text3); margin-bottom: 28px; }

  /* Role tabs */
  .role-tabs {
    display: flex;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 4px;
    gap: 3px;
    margin-bottom: 20px;
  }
  .role-tab {
    flex: 1;
    padding: 8px 4px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 500;
    color: var(--text3);
    text-align: center;
    cursor: pointer;
    transition: all .15s;
    background: none;
    border: none;
    font-family: 'DM Sans', sans-serif;
  }
  .role-tab.active {
    background: var(--navy);
    color: #FFFFFF;
    border: 1px solid var(--navy);
  }

  /* Cred hint */
  .cred-hint {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 18px;
  }
  .cred-title { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--text3); margin-bottom: 6px; }
  .cred-row { display: flex; justify-content: space-between; font-size: 11px; color: var(--text2); font-family: 'JetBrains Mono', monospace; margin-bottom: 3px; }
  .cred-val { color: var(--navy); font-weight: 600; }

  /* Form */
  .form-group { margin-bottom: 14px; }
  .form-label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text3);
    margin-bottom: 7px;
  }
  .form-input {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border2);
    border-radius: 8px;
    padding: 11px 14px;
    font-size: 13px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .form-input::placeholder { color: var(--text3); }
  .form-input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(27,42,107,.1); }
  .pw-wrap { position: relative; }
  .pw-toggle {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text3);
    cursor: pointer;
    font-size: 14px;
  }

  .error-msg {
    display: none;
    background: rgba(217,91,91,.1);
    border: 1px solid rgba(217,91,91,.25);
    color: var(--red);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    margin-bottom: 14px;
  }
  .error-msg.show { display: block; }

  .btn-login {
    width: 100%;
    padding: 13px;
    background: var(--navy);
    color: #FFFFFF;
    border: none;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
    margin-top: 6px;
  }
  .btn-login:hover { opacity: .9; transform: translateY(-1px); }

  .login-footer {
    text-align: center;
    font-size: 11px;
    color: var(--text3);
    margin-top: 22px;
    line-height: 1.6;
  }
</style>
</head>
<body>
<div class="orb1"></div>
<div class="orb2"></div>

<!-- LEFT -->
<div class="left">
  <div class="brand">
    <div class="brand-icon">🗂️</div>
    <div>
      <div class="brand-name">CDAS</div>
      <div class="brand-sub">MARINA Document System</div>
    </div>
  </div>

  <div class="hero">
    <div class="hero-pill">⚡ Paperless Initiative</div>
    <h1>Centralized<br>Document<br><span>Archive</span></h1>
    <p>A secure, role-based document management platform for MARINA — organize, digitize, and retrieve any file instantly.</p>
    <div class="hero-stats">
      <div class="hstat"><div class="num">3</div><div class="lbl">Access Roles</div></div>
      <div class="hstat"><div class="num">6</div><div class="lbl">Units</div></div>
      <div class="hstat"><div class="num">100%</div><div class="lbl">Paperless</div></div>
    </div>
  </div>

  <div class="left-footer">MARINA Office &nbsp;·&nbsp; CDAS v1.0 &nbsp;·&nbsp; <?= date('Y') ?></div>
</div>

<!-- RIGHT -->
<div class="right">
  <div class="login-card">
    <h2>Welcome back</h2>
    <p class="sub">Sign in to access your archive</p>

    <div id="errorMsg" class="error-msg">Invalid credentials. Please try again.</div>

    <div class="role-tabs">
      <button class="role-tab active" data-role="superadmin" onclick="setRole(this)">⭐ Super Admin</button>
      <button class="role-tab" data-role="admin" onclick="setRole(this)">🛡 Head of Unit</button>
      <button class="role-tab" data-role="user" onclick="setRole(this)">👤 Employee</button>
    </div>

    <div class="cred-hint">
      <div class="cred-title">Sample Credentials</div>
      <div id="credBox">
        <div class="cred-row"><span>Email</span><span class="cred-val">superadmin@marina.gov.ph</span></div>
        <div class="cred-row"><span>Password</span><span class="cred-val">Password@123</span></div>
      </div>
    </div>

    <form method="POST" action="/cdas/includes/login_process.php">
      <input type="hidden" name="role" id="roleInput" value="superadmin">

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input class="form-input" type="email" name="email" placeholder="your.name@marina.gov.ph" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="pw-wrap">
          <input class="form-input" type="password" name="password" id="pwInput" placeholder="Enter your password" required>
          <button type="button" class="pw-toggle" onclick="togglePw()" id="pwEye">👁</button>
        </div>
      </div>

      <button type="submit" class="btn-login">Sign In →</button>
    </form>

    <p class="login-footer">MARINA Office · Centralized Document Archiving System</p>
  </div>
</div>

<script>
const creds = {
  superadmin: { email:'superadmin@marina.gov.ph',   pw:'Password@123' },
  admin:      { email:'hod.manpower@marina.gov.ph', pw:'Password@123' },
  user:       { email:'lorna.dc@marina.gov.ph',     pw:'Password@123' },
};
function setRole(btn) {
  document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const r = btn.dataset.role;
  document.getElementById('roleInput').value = r;
  const c = creds[r];
  document.getElementById('credBox').innerHTML = `
    <div class="cred-row"><span>Email</span><span class="cred-val">${c.email}</span></div>
    <div class="cred-row"><span>Password</span><span class="cred-val">${c.pw}</span></div>`;
}
function togglePw() {
  const i = document.getElementById('pwInput');
  const e = document.getElementById('pwEye');
  i.type = i.type==='password'?'text':'password';
  e.textContent = i.type==='password'?'👁':'🙈';
}
if (new URLSearchParams(location.search).get('error'))
  document.getElementById('errorMsg').classList.add('show');
</script>
</body>
</html>