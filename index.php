<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CDAS — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root {
    /* ── Image-Based Palette ── */
    --img-sky:   #BEE3F0;  /* Top color from image */
    --img-navy:  #121B46;  /* Bottom color from image */
    
    /* Functional shades */
    --bg-light:  #BEE3F0;
    --navy-dark: #121B46;
    --navy-accent:#1c2b6b;
    --text-navy: #121B46;
    --text-sky:  #BEE3F0;
    --white:     #FFFFFF;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  
  body {
    font-family: 'DM Sans', sans-serif;
    background-color: var(--bg-light);
    color: var(--text-navy);
    min-height: 100vh;
    display: flex;
    overflow: hidden;
  }

  /* Texture for the sky background */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: radial-gradient(circle, rgba(18, 27, 70, 0.06) 1px, transparent 1px);
    background-size: 30px 30px;
    pointer-events: none;
  }

  /* ══ LEFT panel (Sky Background) ══ */
  .left {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 60px;
    position: relative;
  }

  .brand { display: flex; align-items: center; gap: 14px; }
  .brand-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: var(--navy-dark);
    color: var(--bg-light);
    display: grid;
    place-items: center;
    font-size: 22px;
  }
  .brand-name {
    font-family: 'JetBrains Mono', monospace;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 2px;
  }
  .brand-sub {
    font-size: 10px;
    text-transform: uppercase;
    opacity: 0.7;
    margin-top: 2px;
  }

  .hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 52px;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 20px;
  }
  .hero p {
    font-size: 15px;
    line-height: 1.7;
    max-width: 400px;
    font-weight: 400;
    opacity: 0.8;
  }

  .hero-stats { display: flex; gap: 40px; margin-top: 40px; }
  .hstat .num {
    font-family: 'JetBrains Mono', monospace;
    font-size: 28px;
    font-weight: 700;
  }
  .hstat .lbl {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.6;
  }

  /* ══ RIGHT panel (Solid Navy) ══ */
  .right {
    width: 460px;
    background: var(--navy-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px;
    position: relative;
    box-shadow: -15px 0 40px rgba(0,0,0,0.15);
  }

  .login-card { width: 100%; z-index: 2; }
  .login-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    color: var(--white);
    margin-bottom: 6px;
  }
  .login-card .sub {
    font-size: 14px;
    color: var(--text-sky);
    opacity: 0.6;
    margin-bottom: 30px;
  }

  /* Role Tabs */
  .role-tabs {
    display: flex;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(190, 227, 240, 0.15);
    border-radius: 12px;
    padding: 4px;
    gap: 4px;
    margin-bottom: 24px;
  }
  .role-tab {
    flex: 1;
    padding: 10px;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 600;
    color: rgba(190, 227, 240, 0.4);
    background: none;
    border: none;
    cursor: pointer;
    transition: 0.2s;
  }
  .role-tab.active {
    background: var(--bg-light);
    color: var(--navy-dark);
  }

  /* Form */
  .form-group { margin-bottom: 18px; }
  .form-label {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-sky);
    margin-bottom: 8px;
    opacity: 0.7;
  }
  .form-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(190, 227, 240, 0.2);
    border-radius: 10px;
    padding: 14px;
    font-size: 14px;
    color: #fff;
    outline: none;
    transition: 0.2s;
  }
  .form-input:focus {
    border-color: var(--bg-light);
    background: rgba(255, 255, 255, 0.12);
  }

  .btn-login {
    width: 100%;
    padding: 16px;
    background: var(--bg-light);
    color: var(--navy-dark);
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    margin-top: 10px;
    transition: transform 0.1s, filter 0.2s;
  }
  .btn-login:hover { transform: translateY(-2px); filter: brightness(1.1); }

  .cred-hint {
    background: rgba(190, 227, 240, 0.05);
    border: 1px dashed rgba(190, 227, 240, 0.2);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 24px;
    font-size: 11px;
    color: var(--white);
  }
  .cred-val { color: var(--bg-light); font-family: 'JetBrains Mono', monospace; }

  .login-footer {
    text-align: center;
    font-size: 11px;
    color: rgba(190, 227, 240, 0.3);
    margin-top: 30px;
  }
</style>
</head>
<body>

<div class="left">
  <div class="brand">
    <div class="brand-icon">📁</div>
    <div>
      <div class="brand-name">CDAS</div>
      <div class="brand-sub">MARINA Archive</div>
    </div>
  </div>

  <div class="hero">
    <h1>Archive<br>Management<br>Redefined</h1>
    <p>The centralized gateway for all MARINA documents. Secure, searchable, and completely paperless.</p>
    
    <div class="hero-stats">
      <div class="hstat"><div class="num">03</div><div class="lbl">Roles</div></div>
      <div class="hstat"><div class="num">06</div><div class="lbl">Units</div></div>
      <div class="hstat"><div class="num">100%</div><div class="lbl">Cloud</div></div>
    </div>
  </div>

  <div class="left-footer">MARINA Office &nbsp;·&nbsp; v1.0 &nbsp;·&nbsp; 2026</div>
</div>

<div class="right">
  <div class="login-card">
    <h2>Welcome Back</h2>
    <p class="sub">Please enter your credentials</p>

    <div class="role-tabs">
      <button class="role-tab active" data-role="superadmin" onclick="setRole(this)">SUPER ADMIN</button>
      <button class="role-tab" data-role="admin" onclick="setRole(this)">UNIT HEAD</button>
      <button class="role-tab" data-role="user" onclick="setRole(this)">EMPLOYEE</button>
    </div>

    <div class="cred-hint">
      <div id="credBox">
        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
          <span>Email</span>
          <span class="cred-val">superadmin@marina.gov.ph</span>
        </div>
        <div style="display:flex; justify-content:space-between;">
          <span>Pass</span>
          <span class="cred-val">Password@123</span>
        </div>
      </div>
    </div>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input class="form-input" type="email" placeholder="name@marina.gov.ph" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-input" type="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-login">Secure Sign In →</button>
    </form>

    <p class="login-footer">Protected by MARINA Security Systems</p>
  </div>
</div>

<script>
  function setRole(btn) {
    document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // You can add your specific credential switching logic here
  }
</script>
</body>
</html>