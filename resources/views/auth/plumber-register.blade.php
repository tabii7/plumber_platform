<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Plumber Account - loodgieter.app</title>
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

    .field-grid{ display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:18px; margin-top:12px; }
    @media (max-width: 720px){ .field-grid{ grid-template-columns:1fr; } }
    .span-2{ grid-column: 1 / -1; }

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

    .input-wrap{position:relative}
    .suggest{ 
      position:absolute;
      top:100%;
      left:0;
      right:0; 
      background:rgba(255,255,255,.98);
      border:1px solid rgba(2,6,23,.1); 
      border-radius:12px;
      margin-top:6px;
      box-shadow:0 10px 24px rgba(0,0,0,.15); 
      z-index:50;
      max-height:240px;
      overflow:auto;
      display:none; 
      backdrop-filter:blur(10px); 
      min-height:50px; 
    }
    .s-item{ 
      padding:10px 12px;
      border-bottom:1px solid rgba(2,6,23,.06); 
      cursor:pointer;
      display:flex;
      gap:8px;
      align-items:flex-start; 
      transition:background .2s; 
    }
    .s-item:last-child{border-bottom:0}
    .s-item:hover,.s-item.active{background:rgba(6,182,212,.08)}
    .s-badge{ 
      font-size:10px;
      padding:2px 6px;
      border-radius:6px; 
      border:1px solid rgba(6,182,212,.3);
      background:rgba(6,182,212,.1); 
      color:#0891b2;
      white-space:nowrap;
      font-weight:700; 
    }
    .s-label{flex:1;color:#0f172a;font-size:13px;line-height:1.4}

    .actions-card{ 
      border:1px solid var(--border); 
      border-radius:var(--radius); 
      background:var(--card); 
      padding:18px 22px; 
      display:flex; 
      flex-wrap:wrap; 
      gap:12px; 
      align-items:center; 
      justify-content:flex-start; 
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
      font-weight:700 
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

    .toggle{ 
      position:absolute; 
      right:10px; 
      top:50%; 
      transform:translateY(-50%); 
      display:inline-flex; 
      align-items:center; 
      gap:6px; 
      padding:6px 10px; 
      border-radius:999px; 
      border:1px solid rgba(2,6,23,.14); 
      background:#fff; 
      color:#0f172a; 
      cursor:pointer; 
      font-size:12px; 
      font-weight:700; 
      box-shadow:inset 0 1px 0 rgba(255,255,255,.7); 
    }
    .toggle:hover{ background:#f8fafc }
    .toggle:active{ transform:translateY(-50%) scale(.98) }
    .toggle svg{ width:16px; height:16px; opacity:.85 }
    .toggle[aria-pressed="true"]{ 
      background:linear-gradient(180deg, rgba(6,182,212,.16), rgba(6,182,212,.08)); 
      border-color:rgba(6,182,212,.35); 
      color:#0e7490; 
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
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 10v7a2 2 0 0 0 2 2h3m11-9v7a2 2 0 0 1-2 2h-3M7 19v-6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6M8 7h8M10 4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        loodgieter.app
      </span>
      <h1 class="title">Join our plumber network.</h1>
      <p class="subtitle">Create your plumber account to start receiving service requests from clients in your area. Grow your business with our platform.</p>
      <ul class="points">
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Receive qualified leads in your area
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          WhatsApp-based communication with clients
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Set your own rates and service areas
        </li>
      </ul>
    </section>

    <!-- Form area -->
    <section class="form-wrap">


      @if (session('success'))
        <div class="alert" role="status">{{ session('success') }}</div>
      @endif
      @if (isset($errors) && $errors && $errors->any())
        <div class="alert error" role="alert">Please fix the fields highlighted below.</div>
      @endif

      <form method="POST" action="{{ route('plumber.register.store') }}" novalidate>
        @csrf

        <!-- YOUR DETAILS -->
        <fieldset class="section-card">
          <legend>YOUR DETAILS</legend>
          <div class="field-grid">
           

            <div class="field span-2">
              <label class="label" for="full_name">Full name <span class="required">*</span></label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="John Doe" required>
              </div>
              @error('full_name')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field span-2">
              <label class="label" for="whatsapp_number">WhatsApp number <span class="required">*</span></label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M16.5 13.4c-.3-.2-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.1.1-.3.1-.6 0-1.6-.6-2.9-1.8-3.8-3.4-.1-.3 0-.5.1-.6.1-.1.2-.3.3-.5.1-.1.1-.3.2-.5 0-.2 0-.4-.1-.5-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.5-.6-.5h-.5c-.2 0-.5.1-.8.4-.3.3-1.1 1.1-1.1 2.7s1.1 3.1 1.2 3.3c.1.2 2.1 3.3 5.1 4.6.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.6-.7 1.8-1.4.2-.7.2-1.3.1-1.4-.1-.2-.2-.2-.5-.4zM12 2a10 10 0 0 0-8.7 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                <input id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="324xxxxxxxx" required>
              </div>
              @error('whatsapp_number')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field span-2">
              <label class="label" for="company_name">Company name <span class="required">*</span></label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 21h18M3 7h18M3 3h18M7 21V7M17 21V7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Your company name" required>
              </div>
              <div class="hint">Required for business registration and invoicing.</div>
              @error('company_name')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
              <label class="label" for="email">Email <span class="required">*</span></label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 6l8 6 8-6M4 6h16v12H4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
              </div>
              @error('email')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
              <label class="label" for="password">Password <span class="required">*</span></label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 10V8a5 5 0 1 1 10 0v2M6 10h12v9H6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input id="password" type="password" name="password" placeholder="••••••••" required>
                <button type="button" class="toggle" aria-pressed="false" aria-label="Show password" onclick="togglePass(event)">
                  <svg class="eye" viewBox="0 0 24 24" fill="none"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                  <svg class="eye-off" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M3 3l18 18M10.6 10.6a3 3 0 104.24 4.24M6.4 6.6C4.4 7.9 3 10 3 12c0 0 4 7 9 7 2 0 3.7-.7 5.1-1.6M14.8 7.2A8.6 8.6 0 0012 5C7 5 3 12 3 12c.4.7 1 1.7 1.9 2.7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                  <span class="tlabel">Show</span>
                </button>
              </div>
              @error('password')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
              <label class="label" for="password_confirmation">Confirm password <span class="required">*</span></label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 10V8a5 5 0 1 1 10 0v2M6 10h12v9H6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required>
              </div>
              @error('password_confirmation')<div class="error">{{ $message }}</div>@enderror
            </div>
          </div>
        </fieldset>

        <!-- ADDRESS -->
        <fieldset class="section-card">
          <legend>ADDRESS</legend>
          <div class="field-grid">
            <div class="field span-2 input-wrap">
              <label class="label" for="address">Street address <span class="required">*</span></label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 10l9-7 9 7v8a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input id="address" name="address" value="{{ old('address') }}" placeholder="Start typing street, number, city…" autocomplete="off" required>
                <input type="hidden" id="address_json" name="address_json" value="{{ old('address_json') }}">
              </div>
              <div id="suggest" class="suggest"></div>
              <div class="hint">Start typing and pick an address to auto‑fill fields.</div>
            </div>

            <div class="field">
              <label class="label" for="number">House number</label>
              <div class="control"><input id="number" name="number" value="{{ old('number') }}" placeholder="12A"></div>
              @error('number')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
              <label class="label" for="postal_code">Postal code</label>
              <div class="control"><input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="1000"></div>
              @error('postal_code')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
              <label class="label" for="city">City</label>
              <div class="control"><input id="city" name="city" value="{{ old('city') }}" placeholder="Brussels"></div>
              @error('city')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field"></div> <!-- spacer to keep symmetrical grid -->
          </div>
        </fieldset>

        <!-- BUSINESS DETAILS -->
        <fieldset class="section-card">
          <legend>BUSINESS DETAILS</legend>
          <div class="field-grid">
            <div class="field">
              <label class="label" for="btw_number">VAT number</label>
              <div class="control">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 12h16M4 6h16M4 18h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input id="btw_number" name="btw_number" value="{{ old('btw_number') }}" placeholder="BE 0123.456.789">
              </div>
              <div class="hint">Required for invoicing clients. Format: BE 0123.456.789</div>
              @error('btw_number')<div class="error">{{ $message }}</div>@enderror
            </div>
          </div>
        </fieldset>

        <!-- Actions -->
        <div class="actions-card">
          <button class="btn" type="submit">Create plumber account</button>
          <a class="btn ghost" href="{{ route('login') }}">I already have an account</a>
        </div>
      </form>
    </section>
  </div>

  <script>
    // Password chip toggle with eye icons + ARIA state
    function togglePass(ev){
      const btn = ev.currentTarget;
      const inp = document.getElementById('password');
      if(!inp) return;
      const toShow = inp.type === 'password';
      inp.type = toShow ? 'text' : 'password';
      btn.setAttribute('aria-pressed', toShow ? 'true' : 'false');
      btn.setAttribute('aria-label', toShow ? 'Hide password' : 'Show password');
      const eye = btn.querySelector('.eye');
      const off = btn.querySelector('.eye-off');
      const lab = btn.querySelector('.tlabel');
      if(eye && off){ eye.style.display = toShow ? 'none':'inline'; off.style.display = toShow ? 'inline':'none'; }
      if(lab){ lab.textContent = toShow ? 'Hide' : 'Show'; }
    }

    // Enhanced address search with Vlaanderen API and OSM fallback
    document.addEventListener('DOMContentLoaded', function() {
      const input = document.querySelector('#address');
      const sugg = document.querySelector('#suggest');
      const number = document.querySelector('#number');
      const zip = document.querySelector('#postal_code');
      const city = document.querySelector('#city');
      const hidden = document.querySelector('#address_json');
      
      if (!input || !sugg || !number || !zip || !city || !hidden) { 
        console.error('Address search elements not found'); 
        return; 
      }
      
      let items = []; 
      let activeIndex = -1; 
      let debounceId = null;

      // Heuristic normalization for Vlaanderen API (from adres-test.php)
      function normalizeForVL(qRaw) {
        let q = qRaw.trim().replace(/\s+/g, ' ');
        // Zet "26 karel de stoutelaan brugge" om naar "karel de stoutelaan 26, brugge"
        // 1) getal vooraan -> verplaats achter eerste straatsegment
        const m = q.match(/^(\d+)\s+(.+)/i);
        if (m) q = m[2] + ' ' + m[1];

        // 2) Als gebruiker spaties zonder komma's geeft, voeg lichtgewicht komma in vóór gemeente-achtige woorden
        // lijstje van veelvoorkomende steden/gemeenten (mini-heuristiek; niet volledig, maar helpt)
        const cities = ['brugge','brussel','antwerpen','gent','leuven','mechelen','kortrijk','hasselt','oostende','roeselare','aalst','genk','turnhout','lier','waregem','dilbeek','asse','zaventem','knokke','deinze','oudenaarde','eeklo','blankenberge','tienen','wetteren','dendermonde'];
        for (const c of cities) {
          const idx = q.toLowerCase().lastIndexOf(' ' + c);
          if (idx > 0 && !q.includes(',')) {
            q = q.slice(0, idx) + ', ' + q.slice(idx + 1);
            break;
          }
        }

        // Capitalisatie lichtjes herstellen (niet strikt nodig voor API)
        q = q.replace(/\b([a-zà-ÿ])/g, (m)=>m.toUpperCase());
        return q;
      }

      function formatVLLabelFromLocation(L){
        if (!L) return '';
        const parts = [];
        const line1 = [L.Thoroughfarename, L.Housenumber].filter(Boolean).join(' ');
        const line2 = [L.Postalcode, L.Municipality].filter(Boolean).join(' ');
        if (line1) parts.push(line1);
        if (line2) parts.push(line2);
        return parts.join(', ');
      }

      function formatOSMLabel(addr) {
        if (!addr) return '';
        
        // Create clean two-line format like in adres-test.php
        const line1 = [addr.road || addr.pedestrian || addr.path || '', addr.house_number || ''].filter(Boolean).join(' ');
        const line2 = [addr.postcode || '', addr.city || addr.town || addr.village || addr.municipality || ''].filter(Boolean).join(' ');
        
        const parts = [];
        if (line1) parts.push(line1);
        if (line2) parts.push(line2);
        
        return parts.join(', ');
      }

      async function fetchJSON(url) {
        const r = await fetch(url);
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      }

      async function searchSmart(qUser) {
        console.log('searchSmart called with:', qUser); // Debug log
        
        // Use our unified address API
        try {
          const results = await fetchJSON(`{{ route('address.suggest') }}?q=${encodeURIComponent(qUser)}&c=10`);
          console.log('Address API result:', results); // Debug log
          
          if (Array.isArray(results) && results.length > 0) {
            // Check if results are from VL or OSM based on structure
            const hasVLStructure = results.some(item => item.Suggestion || item._vlLoc);
            console.log('Using results from:', hasVLStructure ? 'VL' : 'OSM'); // Debug log
            return {data: results, src: hasVLStructure ? 'vl' : 'osm'};
          }
          
          console.log('No results found'); // Debug log
          return {data: [], src: 'vl'};
        } catch(e) {
          console.error('Address search error:', e);
          return {data: [], src: 'vl'};
        }
      }



      function escapeHtml(s) { 
        return (s || '').replace(/[&<>"]/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); 
      }

      function renderList(list, src) {
        if (!list || !list.length) {
          sugg.innerHTML = `<div class="s-item"><span class="s-badge">Ø</span><span class="s-label">Geen resultaten</span></div>`;
          sugg.style.display = 'block';
          return;
        }
        
        sugg.innerHTML = list.map((it, i) => {
          let label;
          if (src === 'vl') {
            label = it?.Suggestion?.Label || '';
          } else {
            // Use our clean formatting for OSM results (like adres-test.php)
            label = formatOSMLabel(it?.address) || '';
          }
          const b = (src === 'vl') ? 'VL' : 'OSM';
          return `<div class="s-item${i === activeIndex ? ' active' : ''}" data-i="${i}" data-src="${b}">
            <span class="s-badge">[${b}]</span>
            <span class="s-label">${escapeHtml(label)}</span>
          </div>`;
        }).join('');
        
        sugg.style.display = 'block';
        sugg.querySelectorAll('.s-item').forEach(el => {
          el.addEventListener('mousedown', () => choose(parseInt(el.dataset.i, 10), el.dataset.src));
        });
      }

      async function choose(i, srcBadge){
        if (i<0 || i>=items.length) return;
        const src = (srcBadge==='VL') ? 'vl' : 'osm';

        let label;
        if (src==='vl') {
          label = items[i]?.Suggestion?.Label || '';
        } else {
          // Use our clean formatting for OSM results (like adres-test.php)
          label = formatOSMLabel(items[i]?.address) || '';
        }

        input.value = label;
        sugg.style.display = 'none';

        try{
          if (src==='vl'){
            // Check if we have enhanced OSM data
            if (items[i] && items[i]._osmData) {
              showOSM(items[i]._osmData);
            } else if (items[i] && items[i]._vlLoc){
              showVL(items[i]._vlLoc.Location, items[i]._vlLoc);
            } else {
              // For VL suggestions, we need to fetch detailed location data
              const detailedResults = await fetchJSON(`{{ route('address.suggest') }}?q=${encodeURIComponent(label)}&detailed=1`);
              if (detailedResults && detailedResults.length > 0 && detailedResults[0]._vlLoc) {
                showVL(detailedResults[0]._vlLoc.Location, detailedResults[0]._vlLoc);
              } else {
                // Try OSM for better city information
                const osmResults = await fetchJSON(`{{ route('address.suggest') }}?q=${encodeURIComponent(label)}&osm=1`);
                if (osmResults && osmResults.length > 0) {
                  showOSM(osmResults[0]);
                } else {
                  // Fallback: try to parse the label manually
                  showVLFromLabel(label);
                }
              }
            }
          } else {
            // OSM direct tonen + compacte samenvatting
            showOSM(items[i]);
          }
        }catch(e){
          console.error('Fout bij ophalen:', e.message);
        }
      }

      function showVL(L, rawArr) {
        if (L) {
          // Extract address components
          const streetName = L.Thoroughfarename || '';
          const houseNumber = L.Housenumber || '';
          const postalCode = L.Postalcode || '';
          const municipality = L.Municipality || '';
          
          // Fill form fields
          input.value = [streetName, houseNumber].filter(Boolean).join(' ');
          number.value = houseNumber;
          zip.value = postalCode;
          city.value = municipality;
          
          // Store full data
          hidden.value = JSON.stringify({
            source: 'vl',
            location: L,
            raw: rawArr
          });
        }
      }

      function showOSM(it) {
        const addr = it?.address || {};
        const streetName = addr.road || addr.pedestrian || addr.path || '';
        const houseNumber = addr.house_number || '';
        const postalCode = addr.postcode || '';
        // Prioritize village over town over city over municipality for correct city name
        const cityName = addr.village || addr.town || addr.city || addr.municipality || '';
        
        // Fill form fields with properly formatted address
        input.value = formatOSMLabel(addr);
        number.value = houseNumber;
        zip.value = postalCode;
        city.value = cityName;
        
        // Store full data
        hidden.value = JSON.stringify({
          source: 'osm',
          address: it
        });
      }

      function showVLFromLabel(label) {
        // Parse the label manually: "Polderhoek 8, 8300 Knokke-Heist"
        const parts = label.split(', ');
        if (parts.length >= 2) {
          const streetPart = parts[0].trim(); // "Polderhoek 8"
          const cityPart = parts[1].trim(); // "8300 Knokke-Heist"
          
          // Extract house number from street part
          const streetMatch = streetPart.match(/^(.+?)\s+(\d+.*)$/);
          const streetName = streetMatch ? streetMatch[1] : streetPart;
          const houseNumber = streetMatch ? streetMatch[2] : '';
          
          // Extract postal code and city from city part
          const cityMatch = cityPart.match(/^(\d+)\s+(.+)$/);
          const postalCode = cityMatch ? cityMatch[1] : '';
          const cityName = cityMatch ? cityMatch[2] : cityPart;
          
          // Fill form fields
          input.value = [streetName, houseNumber].filter(Boolean).join(' ');
          number.value = houseNumber;
          zip.value = postalCode;
          city.value = cityName;
          
          // Store parsed data
          hidden.value = JSON.stringify({
            source: 'vl_parsed',
            label: label,
            parsed: {
              streetName,
              houseNumber,
              postalCode,
              cityName
            }
          });
        }
      }

      // Input event with debouncing
      input.addEventListener('input', () => {
        const q = input.value.trim();
        activeIndex = -1;
        if (debounceId) clearTimeout(debounceId);
        if (q.length < 2) { 
          sugg.style.display = 'none'; 
          return; 
        }

        // Show loading state
        sugg.innerHTML = `<div class="s-item"><span class="s-badge">...</span><span class="s-label">Searching...</span></div>`;
        sugg.style.display = 'block';

        debounceId = setTimeout(async () => {
          try {
            console.log('Searching for:', q); // Debug log
            const {data, src} = await searchSmart(q);
            console.log('Search results:', data, src); // Debug log
            items = data || [];
            renderList(items, src);
          } catch(e) {
            console.error('Address search error:', e); // Debug log
            items = [];
            sugg.innerHTML = `<div class="s-item"><span class="s-badge">ERR</span><span class="s-label">Error: ${escapeHtml(e.message)}</span></div>`;
            sugg.style.display = 'block';
          }
        }, 350); // Slightly longer to avoid rate limits
      });

      // Keyboard navigation
      input.addEventListener('keydown', e => {
        if (sugg.style.display === 'none') return;
        const max = items.length - 1;
        
        if (e.key === 'ArrowDown') { 
          e.preventDefault(); 
          activeIndex = Math.min(activeIndex + 1, max); 
          rerenderActive(); 
        }
        if (e.key === 'ArrowUp') { 
          e.preventDefault(); 
          activeIndex = Math.max(activeIndex - 1, 0); 
          rerenderActive(); 
        }
        if (e.key === 'Enter') {
          e.preventDefault();
          const firstBadge = sugg.querySelector('.s-item')?.dataset.src || 'VL';
          choose(activeIndex >= 0 ? activeIndex : 0, firstBadge);
        }
        if (e.key === 'Escape') { 
          sugg.style.display = 'none'; 
        }
      });

      function rerenderActive() {
        const nodes = [...sugg.querySelectorAll('.s-item')];
        nodes.forEach((n, i) => n.classList.toggle('active', i === activeIndex));
      }

      // Close suggestions when clicking outside
      document.addEventListener('click', e => {
        if (!sugg.contains(e.target) && e.target !== input) {
          sugg.style.display = 'none';
        }
      });
    });
  </script>
</body>
</html>
