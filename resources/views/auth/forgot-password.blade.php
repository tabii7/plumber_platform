<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reset Password - loodgieter.app</title>
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
      width:100%;max-width:600px;margin:0 auto;
      background:linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,255,255,0.94));
      border:1px solid var(--border);
      border-radius:20px;
      box-shadow: var(--shadow), inset 0 1px 0 rgba(255,255,255,.7);
      overflow:hidden;
    }

    .hero{ 
      position:relative; 
      padding:36px 28px 44px; 
      background:
        radial-gradient(600px 300px at 0% 0%, rgba(6,182,212,.10), transparent 60%),
        radial-gradient(500px 500px at 100% 100%, rgba(34,211,238,.10), transparent 60%),
        linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
      text-align:center;
    }
    
    .badge{ 
      display:inline-flex;align-items:center;gap:8px; 
      background:rgba(6,182,212,.10); 
      border:1px solid rgba(6,182,212,.30); 
      color:#0891b2;font-weight:700; 
      padding:6px 12px;border-radius:999px;font-size:12px; 
    }
    .title{margin:14px 0 6px;font-size:32px;font-weight:800;letter-spacing:.1px}
    .subtitle{color:var(--muted);font-size:14px;line-height:1.7;max-width:52ch;margin:0 auto}

    .form-wrap{ padding:40px 36px; display:flex; flex-direction:column; gap:24px; }

    fieldset.section-card{ 
      border:1px solid var(--border); 
      border-radius:var(--radius); 
      background:linear-gradient(180deg, rgba(248,250,252,.88), #fff); 
      padding:24px; 
      margin:0; 
    }
    legend{ 
      font-size:12px; 
      font-weight:800; 
      letter-spacing:.12em; 
      text-transform:uppercase; 
      color:#0f172a; 
      padding:0 8px; 
    }

    .field{display:flex;flex-direction:column;gap:8px}
    .label{font-size:12px;color:#475569}
    .control{ 
      position:relative;
      background:rgba(2,6,23,.02); 
      border:1px solid rgba(2,6,23,.12); 
      border-radius:12px; 
      display:flex;
      align-items:center;
      padding:14px 14px;
      gap:10px; 
      transition:border .2s, box-shadow .2s, background .2s; 
    }
    .control:focus-within{ 
      border-color:var(--ring); 
      box-shadow:0 0 0 4px rgba(6,182,212,.15); 
      background:rgba(255,255,255,.98); 
    }
    .control input{ 
      background:transparent;
      border:none;
      outline:none;
      color:var(--text); 
      width:100%;
      font-size:15px;
      letter-spacing:.2px; 
    }
    .control svg{opacity:.75}
    .error{color:var(--error);font-size:12px;margin-top:-2px}
    .hint{font-size:12px;color:#64748b;margin-top:2px}

    .actions-card{ 
      border:1px solid var(--border); 
      border-radius:var(--radius); 
      background:var(--card); 
      padding:18px 22px; 
      display:flex; 
      flex-wrap:wrap; 
      gap:12px; 
      align-items:center; 
      justify-content:space-between; 
    }
    .btn{ 
      appearance:none;
      border:none;
      cursor:pointer; 
      background:linear-gradient(180deg, var(--primary), var(--primary-600)); 
      color:#001019;
      font-weight:800;
      padding:12px 16px;
      border-radius:12px; 
      letter-spacing:.2px;
      box-shadow:0 10px 25px rgba(6,182,212,.25); 
      transition:transform .04s ease, filter .2s ease; 
    }
    .btn:active{transform:translateY(1px)}
    .ghost{ 
      background:transparent;
      color:#0f172a;
      border:1px solid rgba(2,6,23,.14); 
      box-shadow:none; 
      font-weight:700;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:8px;
    }

    .alert{ 
      padding:10px 12px;
      border-radius:10px;
      font-size:14px; 
      border:1px solid rgba(34,197,94,.2);
      background:rgba(34,197,94,.1);
      color:#166534; 
    }
    .alert.error{
      background:rgba(239,68,68,.1);
      color:#dc2626;
      border-color:rgba(239,68,68,.2)
    }

    .required {
      color: var(--error);
      font-weight: 600;
    }

  </style>
</head>
<body>
  <div class="shell">
    <!-- Branding / value prop -->
    <section class="hero">
      <span class="badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 15l-3-3h6l-3 3zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        Password Reset
      </span>
      <h1 class="title">Forgot Password?</h1>
      <p class="subtitle">No problem! Enter your email address and we'll send you a password reset link.</p>
    </section>

    <!-- Form area -->
    <section class="form-wrap">
      @if (session('status'))
        <div class="alert" role="status">{{ session('status') }}</div>
      @endif
      @if (isset($errors) && $errors && $errors->any())
        <div class="alert error" role="alert">Please fix the fields highlighted below.</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <!-- Email Address -->
        <fieldset class="section-card">
          <legend>EMAIL ADDRESS</legend>
          <div class="field">
            <label class="label" for="email">Email address <span class="required">*</span></label>
            <div class="control">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 6l8 6 8-6M4 6h16v12H4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required autofocus>
            </div>
            @error('email')<div class="error">{{ $message }}</div>@enderror
          </div>
        </fieldset>

        <!-- Actions -->
        <div class="actions-card">
          <button class="btn" type="submit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 8px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            Send Reset Link
          </button>
          <a class="btn ghost" href="{{ route('welcome') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            Back to Home
          </a>
        </div>
      </form>
    </section>
  </div>
</body>
</html>
