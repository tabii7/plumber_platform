<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root{
      --bg:#ffffff;            /* white */
      --card:#f8fafc;          /* slate-50 */
      --muted:#64748b;         /* slate-500 */
      --text:#1e293b;          /* slate-800 */
      --primary:#06b6d4;       /* cyan-500 */
      --primary-600:#0891b2;   /* cyan-600 */
      --ring:#22d3ee;          /* cyan-400 */
      --error:#ef4444;
      --ok:#22c55e;
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
      display:flex;align-items:center;justify-content:center;
      padding:24px;
    }
    .shell{
      width:100%;max-width:980px;
      background:linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.9));
      border:1px solid rgba(0,0,0,0.08);
      border-radius:20px;
      box-shadow:
        0 30px 60px rgba(0,0,0,.1),
        inset 0 1px 0 rgba(255,255,255,.8);
      overflow:hidden;
      display:grid;
      grid-template-columns: 1.1fr 1fr;
    }
    @media (max-width: 860px){ .shell{grid-template-columns: 1fr} }

    /* Left side / branding */
    .hero{
      position:relative;
      padding:40px 36px 48px;
      background:
        radial-gradient(600px 300px at 0% 0%, rgba(6,182,212,.1), transparent 60%),
        radial-gradient(500px 500px at 100% 100%, rgba(34,211,238,.1), transparent 60%),
        linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
      border-right:1px solid rgba(0,0,0,0.06);
    }
    @media (max-width: 860px){ .hero{border-right:none;border-bottom:1px solid rgba(0,0,0,0.06)} }
    .badge{
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(6,182,212,.1);
      border:1px solid rgba(6,182,212,.3);
      color:#0891b2;font-weight:600;
      padding:6px 12px;border-radius:999px;font-size:12px;
    }
    .title{margin:14px 0 6px;font-size:28px;font-weight:700;letter-spacing:.2px}
    .subtitle{color:var(--muted);font-size:14px;line-height:1.6;max-width:44ch}
    .points{margin:22px 0 0;padding:0;list-style:none;color:#475569;font-size:14px}
    .points li{display:flex;gap:10px;align-items:flex-start;margin:10px 0}
    .points svg{flex:0 0 18px;margin-top:2px}
    .watermark{
      position:absolute;inset:auto -40px -40px auto;opacity:.08;pointer-events:none;
      font-size:140px;font-weight:800;letter-spacing:2px;rotate:-8deg;
      color:#06b6d4;
    }

    /* Right side / form */
    .pane{padding:34px 30px 30px;background:rgba(255,255,255,.8)}
    .tabs{
      display:flex;gap:8px;margin-bottom:18px;
      background:rgba(0,0,0,.04);border:1px solid rgba(0,0,0,.08);
      padding:6px;border-radius:12px;
    }
    .tab{
      flex:1;text-align:center;padding:10px 12px;border-radius:10px;
      font-weight:600;font-size:14px;color:#64748b;cursor:pointer;
      border:1px solid transparent;
    }
    .tab.active{
      background:linear-gradient(180deg, rgba(6,182,212,.16), rgba(6,182,212,.08));
      color:#0891b2;border-color:rgba(6,182,212,.35);
      box-shadow:inset 0 1px 0 rgba(255,255,255,.8);
    }

    form{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    @media (max-width:600px){ form{grid-template-columns:1fr} }

    .field{display:flex;flex-direction:column;gap:8px}
    .label{font-size:12px;color:#475569}
    .control{
      position:relative;background:rgba(0,0,0,.02);
      border:1px solid rgba(0,0,0,.1);border-radius:10px;
      display:flex;align-items:center;padding:10px 12px;gap:10px;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .control:focus-within{
      border-color:var(--ring);
      box-shadow:0 0 0 4px rgba(6,182,212,.15);
      background:rgba(255,255,255,.9);
    }
    .control input{
      background:transparent;border:none;outline:none;color:var(--text);
      width:100%;font-size:14px;letter-spacing:.2px;
    }
    .control svg{opacity:.65}
    .error{color:var(--error);font-size:12px;margin-top:-4px}
    .row-span-2{grid-column:1 / -1}

    .hint{font-size:12px;color:#64748b;margin-top:2px}
    .actions{grid-column:1 / -1;display:flex;align-items:center;gap:12px;margin-top:6px}
    .btn{
      appearance:none;border:none;cursor:pointer;
      background:linear-gradient(180deg, var(--primary), var(--primary-600));
      color:#001019;font-weight:700;padding:12px 16px;border-radius:12px;
      letter-spacing:.2px;box-shadow:0 10px 25px rgba(6,182,212,.25);
      transition:transform .04s ease, filter .2s ease;
    }
    .btn:active{transform:translateY(1px)}
    .ghost{
      background:transparent;color:#64748b;border:1px solid rgba(0,0,0,.14);
      box-shadow:none;
    }

    .alert{
      grid-column:1/-1;padding:10px 12px;border-radius:10px;font-size:14px;
      border:1px solid rgba(34,197,94,.2);background:rgba(34,197,94,.1);color:#166534;
    }
    .alert.error{background:rgba(239,68,68,.1);color:#dc2626;border-color:rgba(239,68,68,.2)}
    .toggle{
      position:absolute;right:10px;top:50%;translate:0 -50%;
      background:transparent;border:none;color:#64748b;cursor:pointer;font-size:12px
    }

    /* Address search styles */
    .input-wrap{position:relative}
    .suggest{
      position:absolute;top:100%;left:0;right:0;
      background:rgba(255,255,255,.98);border:1px solid rgba(255,255,255,.2);
      border-radius:10px;margin-top:6px;box-shadow:0 10px 24px rgba(0,0,0,.15);
      z-index:9999;max-height:200px;overflow:auto;display:none;
      backdrop-filter:blur(10px);
      min-height:50px;
    }
    .s-item{
      padding:10px 12px;border-bottom:1px solid rgba(0,0,0,.06);
      cursor:pointer;display:flex;gap:8px;align-items:flex-start;
      transition:background .2s;
    }
    .s-item:last-child{border-bottom:0}
    .s-item:hover,.s-item.active{background:rgba(6,182,212,.08)}
    .s-badge{
      font-size:10px;padding:2px 6px;border-radius:6px;
      border:1px solid rgba(6,182,212,.3);background:rgba(6,182,212,.1);
      color:#0891b2;white-space:nowrap;font-weight:600;
    }
    .s-label{flex:1;color:#1e293b;font-size:13px;line-height:1.4}
  </style>



<script>
// Address search functionality
document.addEventListener('DOMContentLoaded', function() {
  const input = document.querySelector('#address');
  const sugg = document.querySelector('#suggest');
  const number = document.querySelector('#number');
  const zip = document.querySelector('#postal_code');
  const city = document.querySelector('#city');
  const hidden = document.querySelector('#address_json');

  // Check if elements exist
  if (!input || !sugg || !number || !zip || !city || !hidden) {
    console.error('Address search elements not found');
    return;
  }

  let items = [];
  let activeIndex = -1;
  let debounceId = null;

// Normalize query for better search results
function normalizeQuery(q) {
  let normalized = q.trim().replace(/\s+/g, ' ');
  
  // Move house number to end if it's at the beginning
  const m = normalized.match(/^(\d+)\s+(.+)/i);
  if (m) {
    normalized = m[2] + ' ' + m[1];
  }
  
  // Add comma before common Belgian cities if no comma exists
  const cities = ['brugge', 'brussel', 'antwerpen', 'gent', 'leuven', 'mechelen', 'kortrijk', 'hasselt', 'oostende', 'roeselare', 'aalst', 'genk', 'turnhout', 'lier', 'waregem', 'dilbeek', 'asse', 'zaventem', 'knokke', 'deinze', 'oudenaarde', 'eeklo', 'blankenberge', 'tienen', 'wetteren', 'dendermonde'];
  for (const c of cities) {
    const idx = normalized.toLowerCase().lastIndexOf(' ' + c);
    if (idx > 0 && !normalized.includes(',')) {
      normalized = normalized.slice(0, idx) + ', ' + normalized.slice(idx + 1);
      break;
    }
  }
  
  return normalized;
}

async function fetchJSON(url) {
  const r = await fetch(url);
  if (!r.ok) throw new Error('HTTP ' + r.status);
  return r.json();
}

async function searchAddress(q) {
  const normalizedQ = normalizeQuery(q);
  
  try {
    // Try Vlaanderen API first (for Belgian addresses)
    const vlUrl = `/api/address/search-vlaanderen?q=${encodeURIComponent(normalizedQ)}`;
    const vlResults = await fetchJSON(vlUrl);
    if (vlResults && vlResults.length > 0) {
      return { data: vlResults, source: 'vl' };
    }
  } catch (e) {
    // Vlaanderen API failed, continue to OSM
  }
  
  // Fallback to OSM
  const osmUrl = `/api/address/search?q=${encodeURIComponent(q)}`;
  const osmResults = await fetchJSON(osmUrl);
  return { data: osmResults, source: 'osm' };
}

function renderList(list, source) {
  if (!list || !list.length) {
    sugg.innerHTML = `<div class="s-item"><span class="s-badge">Ø</span><span class="s-label">No results found</span></div>`;
    sugg.style.display = 'block';
    return;
  }
  
  sugg.innerHTML = list.map((it, i) => {
    let label = '';
    let badge = source === 'vl' ? 'VL' : 'OSM';
    
    if (source === 'vl') {
      label = it.Suggestion?.Label || '';
    } else {
      label = it.display_name || '';
    }
    
    return `<div class="s-item${i === activeIndex ? ' active' : ''}" data-i="${i}" data-source="${source}">
      <span class="s-badge">${badge}</span>
      <span class="s-label">${escapeHtml(label)}</span>
    </div>`;
  }).join('');
  
  sugg.style.display = 'block';
  sugg.querySelectorAll('.s-item').forEach(el => {
    el.addEventListener('mousedown', () => choose(parseInt(el.dataset.i, 10), el.dataset.source));
  });
}

function escapeHtml(s) {
  return (s || '').replace(/[&<>"]/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c]));
}

async function choose(i, source) {
  if (i < 0 || i >= items.length) return;
  
  const item = items[i];
  let label = '';
  
  if (source === 'vl') {
    label = item.Suggestion?.Label || '';
  } else {
    label = item.display_name || '';
  }
  
  input.value = label;
  sugg.style.display = 'none';
  
  // Auto-fill fields based on source
  if (source === 'vl') {
    // Vlaanderen API data structure
    if (item.Suggestion) {
      // Extract data from Vlaanderen format
      const parts = label.split(', ');
      if (parts.length >= 2) {
        const streetPart = parts[0];
        const cityPart = parts[1];
        
        // Extract house number from street part
        const streetMatch = streetPart.match(/(.+?)\s+(\d+[a-zA-Z]?)$/);
        if (streetMatch) {
          number.value = streetMatch[2];
        }
        
        // Extract postal code and city
        const cityMatch = cityPart.match(/^(\d{4})\s+(.+)$/);
        if (cityMatch) {
          zip.value = cityMatch[1];
          city.value = cityMatch[2];
        } else {
          city.value = cityPart;
        }
      }
    }
  } else {
    // OSM data structure
    if (item.address) {
      number.value = item.address.house_number || '';
      zip.value = item.address.postcode || '';
      city.value = item.address.city || item.address.town || item.address.village || item.address.municipality || '';
    }
  }
  
  // Save full JSON data
  hidden.value = JSON.stringify(item);
}

// Event listeners
  input.addEventListener('input', () => {
    const q = input.value.trim();
    activeIndex = -1;
    
    if (debounceId) clearTimeout(debounceId);
    if (q.length < 2) {
      sugg.style.display = 'none';
      return;
    }
  
  debounceId = setTimeout(async () => {
    try {
      const { data, source } = await searchAddress(q);
      items = data || [];
      renderList(items, source);
    } catch (e) {
      items = [];
      sugg.innerHTML = `<div class="s-item"><span class="s-badge">ERR</span><span class="s-label">Error: ${escapeHtml(e.message)}</span></div>`;
      sugg.style.display = 'block';
    }
  }, 350);
});

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
    const firstSource = sugg.querySelector('.s-item')?.dataset.source || 'osm';
    choose(activeIndex >= 0 ? activeIndex : 0, firstSource);
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

</head>
<body>
  <div class="shell">
    <!-- Branding / value prop -->
    <section class="hero">
      <span class="badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 10v7a2 2 0 0 0 2 2h3m11-9v7a2 2 0 0 1-2 2h-3M7 19v-6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6M8 7h8M10 4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        loodgieter.app
      </span>
      <h1 class="title">Get a plumber faster.</h1>
      <p class="subtitle">Create an account as a client or a plumber. Most of the workflow happens over WhatsApp — quick, simple, and reliable.</p>
      <ul class="points">
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Seamless WhatsApp updates
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Clients match with nearby plumbers
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Admin control over pricing & areas
        </li>
      </ul>
      <div class="watermark">PLUMBER</div>
    </section>

    <!-- Form -->
    <section class="pane">
      @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="alert error">Please fix the fields highlighted below.</div>
      @endif

      <div class="tabs">
        <button type="button" class="tab active" id="tab-client" onclick="selectRole('client')">Client</button>
        <button type="button" class="tab" id="tab-plumber" onclick="selectRole('plumber')">Plumber</button>
      </div>

      <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf
        <input type="hidden" name="role" id="role" value="{{ old('role','client') }}">

        <div class="field row-span-2">
          <label class="label" for="full_name">Full name</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="John Doe" required>
            </div>
          @error('full_name')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label class="label" for="email">Email</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 6l8 6 8-6M4 6h16v12H4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
          </div>
          @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label class="label" for="password">Password</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 10V8a5 5 0 1 1 10 0v2M6 10h12v9H6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="password" type="password" name="password" placeholder="••••••••" required>
            <button type="button" class="toggle" onclick="togglePass()">Show</button>
          </div>
          @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field row-span-2 input-wrap">
          <label class="label" for="address">Street address</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 10l9-7 9 7v8a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="address" name="address" value="{{ old('address') }}" placeholder="Start typing street, number, city…" autocomplete="off" required>
            <input type="hidden" id="address_json" name="address_json" value="{{ old('address_json') }}">
          </div>
          <div id="suggest" class="suggest"></div>
          <div class="hint">Start typing and pick an address to auto-fill fields.</div>
        </div>

        <div class="field">
          <label class="label" for="number">House number</label>
          <div class="control">
            <input id="number" name="number" value="{{ old('number') }}" placeholder="12A">
          </div>
          @error('number')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label class="label" for="postal_code">Postal code</label>
          <div class="control">
            <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="1000">
          </div>
          @error('postal_code')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label class="label" for="city">City</label>
          <div class="control">
            <input id="city" name="city" value="{{ old('city') }}" placeholder="Brussels">
          </div>
          @error('city')<div class="error">{{ $message }}</div>@enderror
        </div>


        <div class="field">
          <label class="label" for="phone">Phone number</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 3.15 9.81 19.79 19.79 0 0 1 .08 1.18 2 2 0 0 1 2.06 0h3A2 2 0 0 1 7 1.72a12.66 12.66 0 0 0 .7 2.61 2 2 0 0 1-.45 2.11L6.16 7.52a16 16 0 0 0 6.32 6.32l1.08-1.09a2 2 0 0 1 2.11-.45 12.66 12.66 0 0 0 2.61.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="324xxxxxxxx" required>
          </div>
          @error('phone')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label class="label" for="whatsapp_number">WhatsApp number</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M16.5 13.4c-.3-.2-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.1.1-.3.1-.6 0-1.6-.6-2.9-1.8-3.8-3.4-.1-.3 0-.5.1-.6.1-.1.2-.3.3-.5.1-.1.1-.3.2-.5 0-.2 0-.4-.1-.5-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.5-.6-.5h-.5c-.2 0-.5.1-.8.4-.3.3-1.1 1.1-1.1 2.7s1.1 3.1 1.2 3.3c.1.2 2.1 3.3 5.1 4.6.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.6-.7 1.8-1.4.2-.7.2-1.3.1-1.4-.1-.2-.2-.2-.5-.4zM12 2a10 10 0 0 0-8.7 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
            <input id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="324xxxxxxxx" required>
          </div>
          @error('whatsapp_number')<div class="error">{{ $message }}</div>@enderror
        </div>

        <!-- Plumber-only -->
        <div class="field row-span-2" id="btw_block" style="display:none">
          <label class="label" for="btw_number">VAT number (optional)</label>
          <div class="control">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 12h16M4 6h16M4 18h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input id="btw_number" name="btw_number" value="{{ old('btw_number') }}" placeholder="BE 0123.456.789">
          </div>
          <div class="hint">Required for plumbers who invoice clients.</div>
        </div>

        <div class="actions">
          <button class="btn" type="submit">Create account</button>
          <a class="btn ghost" href="{{ route('login') }}">I already have an account</a>
        </div>
      </form>
    </section>
  </div>

  <script>
    function selectRole(role){
      const clientTab = document.getElementById('tab-client');
      const plumberTab = document.getElementById('tab-plumber');
      const block = document.getElementById('btw_block');
      document.getElementById('role').value = role;
      clientTab.classList.toggle('active', role === 'client');
      plumberTab.classList.toggle('active', role === 'plumber');
      block.style.display = role === 'plumber' ? 'block' : 'none';
    }

    // Respect old value after validation error:
    (function(){
      const initialRole = document.getElementById('role').value || 'client';
      selectRole(initialRole);
    })();

    function togglePass(){
      const inp = document.getElementById('password');
      if(!inp) return;
      inp.type = inp.type === 'password' ? 'text' : 'password';
      event.currentTarget.textContent = inp.type === 'password' ? 'Show' : 'Hide';
    }
  </script>
</body>
</html>
