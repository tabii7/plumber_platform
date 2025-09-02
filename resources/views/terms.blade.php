{{-- resources/views/cookies.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Cookie Policy – loodgieter.app</title>
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

    /* Policy text */
    .policy h2{font-size:1.6rem;color:#1f2937;margin-top:2rem}
    .policy h3{font-size:1.1rem;color:#1f2937;margin-top:1.25rem}
    .policy p{color:#0b1220;margin:10px 0}
    .policy ul, .policy ol{margin:8px 0 16px 22px}
    .policy a{color:#0ea5e9;text-decoration:none}
    .policy a:hover{text-decoration:underline}
    .muted{color:#5f6b7a}
    @media (max-width:768px){.hero-title{font-size:2.3rem}}
  </style>
</head>
<body>
  {{-- NAV (same as home, with Cookies active) --}}
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
          <li class="nav-item"><a class="nav-link" href="{{ route('privacy') }}">Privacy & policy</a></li>
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
        <h1 class="hero-title">Cookie Policy</h1>
        <p class="mb-0" style="color:#cbd5e1">Last updated: <strong>27 August 2025</strong></p>
      </div>
    </div>
  </section>

  {{-- POLICY CONTENT (your provided text, themed) --}}
  <section class="section">
    <div class="container policy">
      <h2>1. Introduction</h2>
      <p>
        This Cookie Policy explains how <strong>loodgieter.app</strong>, operated by <strong>IMHOTION</strong>
        (TF-657, 14, 38627 Arona, Buzanada, España, VAT ES-Y1187957K), uses cookies and similar technologies.
      </p>

      <h2>2. What are cookies?</h2>
      <p>
        Cookies are small text files placed on your device when you visit a website.
        They help us ensure the platform functions properly, improve performance, and remember preferences.
      </p>

      <h2>3. Types of cookies we use</h2>
      <ul>
        <li><strong>Strictly necessary cookies:</strong> Required for core functionality (e.g. login, security). Cannot be disabled.</li>
        <li><strong>Performance and analytics cookies:</strong> Help us understand how users interact with the platform. Used only with your consent.</li>
        <li><strong>Functional cookies:</strong> Remember your preferences (e.g. language, saved forms).</li>
        <li><strong>Third-party cookies:</strong> May be set by external providers like analytics or support tools.</li>
      </ul>

      <h2>4. How we use cookies</h2>
      <p>Cookies help us to:</p>
      <ul>
        <li>Provide a secure and stable platform.</li>
        <li>Store limited account settings for smoother use.</li>
        <li>Measure performance and fix errors.</li>
        <li>Offer support through integrated communication tools.</li>
      </ul>

      <h2>5. Your choices</h2>
      <ul>
        <li>You can accept or reject optional cookies through our cookie banner when visiting the site.</li>
        <li>You may also adjust your browser settings to block cookies or alert you when cookies are being set.</li>
        <li>Disabling cookies may affect the functionality of some parts of loodgieter.app.</li>
      </ul>

      <h2>6. Data retention</h2>
      <p>
        Cookies remain on your device for different periods depending on their purpose:
        some expire when you close your browser (session cookies), others remain longer until deleted manually or automatically (persistent cookies).
      </p>

      <h2>7. Updates to this policy</h2>
      <p>
        We may update this Cookie Policy to reflect changes in our practices or legal requirements.
        Updates will be published on this page with the revision date.
      </p>

      <h2>8. Contact</h2>
      <p>
        For questions about our Cookie Policy, contact us at:<br />
        Email: <a href="mailto:complains@diensten.pro">complains@diensten.pro</a> |
        <a href="mailto:hello@loodgieter.app">hello@loodgieter.app</a><br />
        Address: TF-657, 14, 38627 Arona, Buzanada, España
      </p>

      <hr class="my-4" />
      <p class="muted small">
        By continuing to use loodgieter.app, you consent to the use of cookies as described in this policy.
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
