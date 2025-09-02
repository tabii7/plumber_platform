<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Choose Account Type - loodgieter.app</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root{
      --bg:#ffffff;
      --card:#ffffff;
      --muted:#64748b;
      --text:#0f172a;
      --primary:#06b6d4;
      --primary-600:#0891b2;
      --ring:#22d3ee;
      --error:#ef4444;
      --ok:#22c55e;
      --border: rgba(2,6,23,.12);
      --shadow: 0 24px 60px rgba(2,6,23,.10);
      --radius: 16px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif;
      background:
        radial-gradient(1200px 600px at -10% -10%, #e0f2fe 0%, transparent 60%),
        radial-gradient(800px 500px at 110% 0%, #b3e5fc 0%, transparent 55%),
        linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
      color:var(--text);
      min-height:100vh;
      padding:40px 20px 56px;
    }

    .shell{
      width:100%;max-width:1180px;margin:0 auto;
      background:linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,255,255,0.94));
      border:1px solid var(--border);
      border-radius:20px;
      box-shadow: var(--shadow), inset 0 1px 0 rgba(255,255,255,.7);
      overflow:hidden;
      display:grid;
      grid-template-columns: 0.75fr 1.45fr;
    }
    @media (max-width: 1060px){ .shell{grid-template-columns: 1fr} }

    .hero{ 
      position:relative; 
      padding:36px 28px 44px; 
      background:
        radial-gradient(600px 300px at 0% 0%, rgba(6,182,212,.10), transparent 60%),
        radial-gradient(500px 500px at 100% 100%, rgba(34,211,238,.10), transparent 60%),
        linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
      border-right:1px solid var(--border); 
    }
    @media (max-width: 1060px){ .hero{border-right:none;border-bottom:1px solid var(--border)} }
    
    .badge{ 
      display:inline-flex;align-items:center;gap:8px; 
      background:rgba(6,182,212,.10); 
      border:1px solid rgba(6,182,212,.30); 
      color:#0891b2;font-weight:700; 
      padding:6px 12px;border-radius:999px;font-size:12px; 
    }
    .title{margin:14px 0 6px;font-size:32px;font-weight:800;letter-spacing:.1px}
    .subtitle{color:var(--muted);font-size:14px;line-height:1.7;max-width:52ch}
    .points{margin:24px 0 0;padding:0;list-style:none;color:#475569;font-size:14px}
    .points li{display:flex;gap:10px;align-items:flex-start;margin:10px 0}
    .points svg{flex:0 0 18px;margin-top:2px}

    .choice-wrap{ 
      padding:40px 36px; 
      display:flex; 
      flex-direction:column; 
      gap:24px; 
      align-items: center;
      justify-content: center;
    }

    .choice-title {
      text-align: center;
      margin-bottom: 32px;
    }
    .choice-title h2 {
      font-size: 28px;
      font-weight: 800;
      margin: 0 0 8px 0;
      color: var(--text);
    }
    .choice-title p {
      font-size: 16px;
      color: var(--muted);
      margin: 0;
    }

    .choice-cards {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 24px;
      width: 100%;
      max-width: 600px;
    }
    @media (max-width: 720px) {
      .choice-cards {
        grid-template-columns: 1fr;
      }
    }

    .choice-card {
      border: 2px solid var(--border);
      border-radius: 16px;
      padding: 32px 24px;
      text-align: center;
      background: white;
      transition: all 0.3s ease;
      text-decoration: none;
      color: var(--text);
      position: relative;
      overflow: hidden;
    }
    .choice-card:hover {
      border-color: var(--primary);
      transform: translateY(-4px);
      box-shadow: 0 20px 40px rgba(6,182,212,.15);
    }
    .choice-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--primary-600));
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    .choice-card:hover::before {
      transform: scaleX(1);
    }

    .choice-card .icon {
      font-size: 48px;
      margin-bottom: 16px;
      display: block;
    }
    .choice-card .label {
      font-weight: 800;
      font-size: 20px;
      margin-bottom: 8px;
      color: var(--text);
    }
    .choice-card .desc {
      font-size: 14px;
      color: var(--muted);
      line-height: 1.5;
      margin-bottom: 20px;
    }
    .choice-card .features {
      list-style: none;
      padding: 0;
      margin: 0 0 24px 0;
      text-align: left;
    }
    .choice-card .features li {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
      font-size: 13px;
      color: var(--text);
    }
    .choice-card .features li::before {
      content: '✓';
      color: var(--ok);
      font-weight: bold;
      font-size: 14px;
    }
    .choice-card .btn {
      display: inline-block;
      background: linear-gradient(180deg, var(--primary), var(--primary-600));
      color: white;
      font-weight: 700;
      padding: 12px 24px;
      border-radius: 12px;
      text-decoration: none;
      transition: all 0.2s ease;
      box-shadow: 0 8px 20px rgba(6,182,212,.25);
    }
    .choice-card .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(6,182,212,.35);
    }

    .login-link {
      text-align: center;
      margin-top: 24px;
    }
    .login-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="shell">
    <!-- Branding / value prop -->
    <section class="hero">
      <span class="badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 10v7a2 2 0 0 0 2 2h3m11-9v7a2 2 0 0 1-2 2h-3M7 19v-6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6M8 7h8M10 4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        loodgieter.app
      </span>
      <h1 class="title">Connect plumbers with clients.</h1>
      <p class="subtitle">Our platform bridges the gap between homeowners needing plumbing services and qualified professionals ready to help.</p>
      <ul class="points">
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          WhatsApp-based communication
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Location-based matching
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Transparent pricing & quotes
        </li>
      </ul>
    </section>

    <!-- Choice area -->
    <section class="choice-wrap">
      <div class="choice-title">
        <h2>Choose your account type</h2>
        <p>Select the type of account that best describes your needs</p>
      </div>

      <div class="choice-cards">
        <!-- Client Card -->
        <a href="{{ route('client.register') }}" class="choice-card">
          <span class="icon">👤</span>
          <div class="label">I need a plumber</div>
          <div class="desc">Create a client account to find and hire qualified plumbers in your area.</div>
          <ul class="features">
            <li>Get matched with nearby plumbers</li>
            <li>Receive quotes and estimates</li>
            <li>Track service progress via WhatsApp</li>
            <li>Rate and review completed work</li>
          </ul>
          <div class="btn">Create Client Account</div>
        </a>

        <!-- Plumber Card -->
        <a href="{{ route('plumber.register') }}" class="choice-card">
          <span class="icon">🔧</span>
          <div class="label">I provide services</div>
          <div class="desc">Create a plumber account to receive service requests and grow your business.</div>
          <ul class="features">
            <li>Receive qualified leads in your area</li>
            <li>Set your own rates and availability</li>
            <li>Manage client communications</li>
            <li>Build your professional reputation</li>
          </ul>
          <div class="btn">Create Plumber Account</div>
        </a>
      </div>

      <div class="login-link">
        Already have an account? <a href="{{ route('login') }}">Sign in here</a>
      </div>
    </section>
  </div>
</body>
</html>
