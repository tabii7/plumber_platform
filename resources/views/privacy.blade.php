{{-- resources/views/privacy.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Privacy Policy – loodgieter.app</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  {{-- Bootstrap / Icons / Fonts (same as home) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  {{-- THEME (copied from home so it looks identical) --}}
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Inter',sans-serif;line-height:1.6;color:#333}

    .navbar{background:rgba(255,255,255,.95);backdrop-filter:blur(10px);box-shadow:0 2px 20px rgba(0,0,0,.1);padding:1rem 0}
    .navbar-brand{font-weight:700;font-size:1.5rem;color:#10b981!important}
    .nav-link{font-weight:500;color:#374151!important;margin:0 1rem;transition:color .3s ease}
    .nav-link:hover,.nav-link.active{color:#10b981!important}

    .btn-primary{background:linear-gradient(135deg,#10b981 0%,#059669 100%);border:none;padding:.875rem 2rem;border-radius:8px;font-weight:600;transition:all .3s;box-shadow:0 4px 15px rgba(16,185,129,.3)}
    .btn-primary:hover{background:linear-gradient(135deg,#059669 0%,#047857 100%);transform:translateY(-2px);box-shadow:0 6px 20px rgba(16,185,129,.4)}
    .btn-outline{background:transparent;border:2px solid #e2e8f0;color:#e2e8f0;padding:.875rem 2rem;border-radius:8px;font-weight:600;transition:all .3s}
    .btn-outline:hover{background:#e2e8f0;color:#1e293b;border-color:#e2e8f0}

    .hero-section{background:linear-gradient(135deg,#1e293b 0%,#334155 50%,#475569 100%);color:#fff;padding:120px 0 80px;position:relative;overflow:hidden}
    .hero-section::before{content:'';position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');opacity:.4}
    .hero-content{position:relative;z-index:2}
    .hero-title{font-size:3rem;font-weight:700;line-height:1.2;margin-bottom:.5rem;background:linear-gradient(135deg,#fff 0%,#e2e8f0 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

    .section{padding:64px 0}
    .footer{background:#111827;color:#fff;padding:2rem 0;text-align:center}
    .footer p{margin:0;opacity:.8}

    /* Policy niceties */
    .policy h2{font-size:1.6rem;color:#1f2937;margin-top:2rem}
    .policy h3{font-size:1.1rem;color:#1f2937;margin-top:1.25rem}
    .policy p{color:#0b1220;margin:10px 0}
    .policy ul, .policy ol{margin:8px 0 16px 22px}
    .policy a{color:#0ea5e9;text-decoration:none}
    .policy a:hover{text-decoration:underline}
    .muted{color:#5f6b7a}
    .card-soft{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px}
    table.policy-table{width:100%;border-collapse:collapse}
    table.policy-table th, table.policy-table td{border:1px solid #e5e7eb;padding:10px;text-align:left;vertical-align:top}
    table.policy-table th{background:#f1f5f9}
    @media (max-width:768px){.hero-title{font-size:2.3rem}}
  </style>
</head>
<body>
  {{-- NAV (same as home, with Privacy active) --}}
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="{{ url('/') }}">
        <i class="fas fa-wrench me-2"></i>ProPlumber
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
          @auth
            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">Profile</a></li>
          @endauth
        </ul>

        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="{{ url('/#pricing') }}">Pricing</a></li>
          <li class="nav-item"><a class="nav-link active" href="{{ route('privacy') }}">Privacy & policy</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('terms') }}">Terms & Conditions</a></li>
          @guest
            <li class="nav-item ms-2">
              <a href="{{ url('/login') }}" class="btn btn-primary">Get Started</a>
            </li>
          @else
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                <i class="fas fa-user me-1"></i>{{ Auth::user()->full_name }}
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                  </form>
                </li>
              </ul>
            </li>
          @endguest
        </ul>
      </div>
    </div>
  </nav>

  {{-- HERO --}}
  <section class="hero-section">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">Privacy Policy</h1>
        <p class="mb-0" style="color:#cbd5e1">Last updated: <strong>27 August 2025</strong></p>
      </div>
    </div>
  </section>

  {{-- POLICY CONTENT --}}
  <section class="section">
    <div class="container policy">
      {{-- Summary card --}}
      <div class="card-soft p-4 mb-4">
        <h2 class="mb-3"><i class="fa-solid fa-list-check me-2 text-success"></i>Summary</h2>
        <ul class="mb-0">
          <li>We store only minimal account and usage data needed to run loodgieter.app.</li>
          <li>If your account is inactive for 90 days, it is deactivated. You can reactivate via WhatsApp.</li>
          <li>We do not sell your data. We share data only with essential service providers or when required by law.</li>
        </ul>
      </div>

      <h2>1. Who we are</h2>
      <p><strong>Controller:</strong> IMHOTION</p>
      <p>
        VAT: ES-Y1187957K<br />
        Address: TF-657, 14, 38627 Arona, Buzanada, España<br />
        Email: <a href="mailto:complains@diensten.pro">complains@diensten.pro</a> &nbsp;|&nbsp;
        <a href="mailto:hello@loodgieter.app">hello@loodgieter.app</a>
      </p>
      <p>This policy covers personal data processed for <strong>loodgieter.app</strong>.</p>

      <h2>2. What data we collect</h2>
      <div class="table-responsive mb-3">
        <table class="policy-table">
          <thead>
            <tr><th>Category</th><th>Examples</th><th>Why we collect it</th></tr>
          </thead>
          <tbody>
            <tr>
              <td>Account data</td>
              <td>Name, phone number, email, role (customer or plumber), city or postal area</td>
              <td>Create and manage your account. Match requests to available plumbers.</td>
            </tr>
            <tr>
              <td>Service data</td>
              <td>Issue type selections, preferred schedule, WhatsApp chat identifiers, status of requests</td>
              <td>Provide the service, route communications, and show request history.</td>
            </tr>
            <tr>
              <td>Technical data</td>
              <td>IP address, device and browser info, basic logs</td>
              <td>Security, fraud prevention, performance, and analytics.</td>
            </tr>
            <tr>
              <td>Communications</td>
              <td>Messages you send us by email or WhatsApp</td>
              <td>Support, reactivation, and dispute handling.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2>3. How we use your data (purposes)</h2>
      <ul>
        <li>Operate and improve loodgieter.app and its features.</li>
        <li>Create and maintain accounts and profiles.</li>
        <li>Connect customers and plumbers and manage request workflows.</li>
        <li>Provide support, prevent abuse, and ensure platform security.</li>
        <li>Comply with legal and tax obligations.</li>
        <li>Send important service messages about your account or requests.</li>
      </ul>

      <h2>4. Legal bases (GDPR)</h2>
      <div class="table-responsive mb-3">
        <table class="policy-table">
          <thead><tr><th>Purpose</th><th>Legal basis</th></tr></thead>
          <tbody>
            <tr><td>Account creation and service delivery</td><td>Performance of a contract</td></tr>
            <tr><td>Security, fraud prevention, product improvement</td><td>Legitimate interests</td></tr>
            <tr><td>Legal, tax, and compliance</td><td>Legal obligation</td></tr>
            <tr><td>Optional messages not required for the service</td><td>Consent</td></tr>
          </tbody>
        </table>
      </div>

      <h2>5. Retention and deactivation</h2>
      <p>
        We apply data minimization. We keep only what is necessary to operate the platform.
        Accounts with no activity for <strong>90 days</strong> are marked inactive and deactivated.
      </p>
      <ul>
        <li><strong>Inactive for 90 days:</strong> your account is deactivated. Minimal identifiers and audit logs may be kept to prevent misuse and to enable reactivation.</li>
        <li><strong>Reactivation:</strong> message us on WhatsApp and we will restore your account after verifying your identity.</li>
        <li><strong>Deletion:</strong> on verified request, we delete personal data unless we must retain some information to meet legal obligations.</li>
      </ul>
      <div class="card-soft p-3 mb-4 small">
        <strong>Reactivate by WhatsApp:</strong>
        <a href="https://wa.me/32490468009" rel="noopener">https://wa.me/32490468009</a>
      </div>

      <h2>6. Sharing your data</h2>
      <p>We do not sell personal data. We may share limited data with:</p>
      <ul>
        <li><strong>Service providers</strong> such as hosting, security, logging, analytics, and customer support tools, under data processing agreements.</li>
        <li><strong>Communication platforms</strong> like WhatsApp for reactivation or support. Your use of WhatsApp is subject to their terms and privacy policy.</li>
        <li><strong>Authorities</strong> when required by law or to protect rights, safety, or the platform.</li>
      </ul>

      <h2>7. International transfers</h2>
      <p>If we transfer data outside the EEA, we use lawful safeguards such as adequacy decisions or standard contractual clauses.</p>

      <h2>8. Security</h2>
      <p>
        We use technical and organizational measures to protect data, including access controls, encryption in transit, and logging.
        No system can be 100% secure, but we work to keep risks low.
      </p>

      <h2>9. Your rights</h2>
      <p>Under GDPR, you can request:</p>
      <ul>
        <li>Access to your personal data</li>
        <li>Correction of inaccurate data</li>
        <li>Deletion of your data</li>
        <li>Restriction or objection to processing</li>
        <li>Data portability</li>
        <li>Withdrawal of consent where processing is based on consent</li>
      </ul>
      <p>
        Contact: <a href="mailto:complains@diensten.pro">complains@diensten.pro</a> or
        <a href="mailto:hello@loodgieter.app">hello@loodgieter.app</a>.
        We will respond within the timelines set by law.
      </p>

      <h2>10. Cookies and similar technologies</h2>
      <p>
        We use only essential cookies needed to run the site and optional analytics that help us improve the service.
        Where required, we will ask for your consent for non-essential cookies and provide a cookie banner with choices.
      </p>

      <h2>11. Children</h2>
      <p>loodgieter.app is not intended for children under 16. If you believe a child has provided personal data, contact us and we will remove it.</p>

      <h2>12. Changes to this policy</h2>
      <p>We may update this policy. We’ll post the new version here and update the date at the top.</p>

      <hr class="my-4" />
      <p class="muted small">
        If you have questions about this policy or how we handle your data, contact us at
        <a href="mailto:complains@diensten.pro">complains@diensten.pro</a>.
      </p>
    </div>
  </section>

  {{-- FOOTER --}}
  <footer class="footer">
    <div class="container">
      <p>&copy; © 2025 Professional Plumbing Services. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
