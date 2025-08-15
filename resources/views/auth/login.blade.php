<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root{
      --bg:#0f172a; --card:#0b1224; --muted:#94a3b8; --text:#e2e8f0;
      --primary:#06b6d4; --primary-600:#0891b2; --ring:#22d3ee;
      --error:#ef4444; --ok:#22c55e;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif;
      background:
        radial-gradient(1200px 600px at -10% -10%, #1f2954 0%, transparent 60%),
        radial-gradient(800px 500px at 110% 0%, #0c6775 0%, transparent 55%),
        linear-gradient(180deg, #0a0f1e 0%, #0c1327 100%);
      color:var(--text);
      display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .shell{
      width:100%; max-width:480px;
      background:linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
      border:1px solid rgba(255,255,255,0.08);
      border-radius:20px; box-shadow:0 30px 60px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.05);
      overflow:hidden;
    }
    .pane{padding:34px 28px 28px;background:rgba(7,12,26,.6)}
    .brand{
      display:flex; gap:10px; align-items:center; justify-content:center; margin-bottom:6px;
      color:#a5f3fc; font-weight:700; letter-spacing:.3px;
    }
    .title{margin:6px 0 2px; text-align:center; font-size:26px; font-weight:800; letter-spacing:.2px}
    .subtitle{color:var(--muted); text-align:center; font-size:14px; margin-bottom:18px}

    .alert{
      padding:10px 12px; border-radius:10px; font-size:14px; margin-bottom:12px;
      border:1px solid rgba(255,255,255,.12); background:rgba(34,197,94,.12); color:#bbf7d0;
    }
    .alert.error{background:rgba(239,68,68,.12); color:#fecaca}

    .field{display:flex; flex-direction:column; gap:8px; margin:10px 0}
    .label{font-size:12px; color:#cbd5e1}
    .control{
      position:relative; background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.10); border-radius:10px;
      display:flex; align-items:center; padding:10px 12px; gap:10px;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .control:focus-within{ border-color:var(--ring); box-shadow:0 0 0 4px rgba(34,211,238,.15); background:rgba(255,255,255,.06) }
    .control input{ background:transparent; border:none; outline:none; color:var(--text); width:100%; font-size:14px; letter-spacing:.2px }
    .control svg{opacity:.65}
    .toggle{
      position:absolute; right:10px; top:50%; translate:0 -50%;
      background:transparent; border:none; color:#a7b4c7; cursor:pointer; font-size:12px
    }
    .error{color:var(--error); font-size:12px; margin-top:-4px}

    .row{display:flex; align-items:center; justify-content:space-between; gap:10px; margin:6px 0 14px}
    .remember{display:flex; align-items:center; gap:8px; color:#cbd5e1; font-size:13px}
    .row a{color:#a5f3fc; text-decoration:none}
    .row a:hover{color:#67e8f9}

    .btn{
      appearance:none; width:100%; border:none; cursor:pointer;
      background:linear-gradient(180deg, var(--primary), var(--primary-600));
      color:#001019; font-weight:800; padding:12px 16px; border-radius:12px;
      letter-spacing:.2px; box-shadow:0 10px 25px rgba(6,182,212,.25);
      transition:transform .04s ease, filter .2s ease; margin-top:4px;
    }
    .btn:active{transform:translateY(1px)}

    .footer{display:flex; justify-content:center; gap:6px; margin-top:14px; font-size:14px; color:#cbd5e1}
    .footer a{color:#a5f3fc; text-decoration:none}
    .footer a:hover{color:#67e8f9}
  </style>
</head>
<body>
  <div class="shell">
    <section class="pane">
      <div class="brand">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 10v7a2 2 0 0 0 2 2h3m11-9v7a2 2 0 0 1-2 2h-3M7 19v-6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6M8 7h8M10 4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        <span>loodgieter.app</span>
      </div>
      <h1 class="title">Welcome back</h1>
      <p class="subtitle">Sign in to your account</p>

      {{-- Session status --}}
      @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
      @endif

      {{-- Validation summary --}}
      @if ($errors->any())
        <div class="alert error">Please check your email and password.</div>
      @endif

      <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="field">
          <label class="label" for="email">Email</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 6l8 6 8-6M4 6h16v12H4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="username">
          </div>
          @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
          <label class="label" for="password">Password</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 10V8a5 5 0 1 1 10 0v2M6 10h12v9H6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="toggle" onclick="togglePass()">Show</button>
          </div>
          @error('password') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="row">
          <label class="remember">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Remember me</span>
          </label>

          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">Forgot password?</a>
          @endif
        </div>

        <button class="btn" type="submit">Log in</button>

        <div class="footer">
          <span>Don’t have an account?</span>
          <a href="{{ route('register') }}">Create one</a>
        </div>
      </form>
    </section>
  </div>

  <script>
    function togglePass(){
      const inp = document.getElementById('password');
      if(!inp) return;
      inp.type = inp.type === 'password' ? 'text' : 'password';
      event.currentTarget.textContent = inp.type === 'password' ? 'Show' : 'Hide';
    }
  </script>
</body>
</html>
