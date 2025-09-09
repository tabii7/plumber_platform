@extends('layouts.modern-dashboard')

@section('title', 'Add Plumber')

@section('page-title', 'Add Plumber')

@section('sidebar-nav')
    <div class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.whatsapp') }}" class="nav-link">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('admin.flows.index') }}" class="nav-link">
            <i class="fas fa-project-diagram"></i>
            <span>WhatsApp Flows</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('plumbers.index') }}" class="nav-link active">
            <i class="fas fa-user-tie"></i>
            <span>Plumbers</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('clients.index') }}" class="nav-link">
            <i class="fas fa-users"></i>
            <span>Clients</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('requests.index') }}" class="nav-link">
            <i class="fas fa-tools"></i>
            <span>Service Requests</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('support') }}" class="nav-link">
            <i class="fas fa-headset"></i>
            <span>Support</span>
        </a>
        </div>
    
    <div class="nav-item">
        <a href="{{ route('profile.edit') }}" class="nav-link">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Create New Plumber</h4>
                    <a href="{{ route('plumbers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('plumbers.store') }}">
        @csrf

        {{-- User details --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Account Details
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                       class="form-control @error('full_name') is-invalid @enderror">
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp number <span class="text-danger">*</span></label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                                       class="form-control @error('whatsapp_number') is-invalid @enderror">
                                @error('whatsapp_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Company name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" required
                                       class="form-control @error('company_name') is-invalid @enderror">
                                <div class="form-text">Required for business registration and invoicing.</div>
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" required
                                       class="form-control @error('password') is-invalid @enderror">
                                <div class="form-text">Minimum 8 characters.</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" required
                                       class="form-control @error('password_confirmation') is-invalid @enderror">
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
            </div>

                        {{-- Address with smart search --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>Address
                                </h5>
                            </div>
                            <div class="col-12 mb-3 position-relative">
                                <label class="form-label">Street address <span class="text-danger">*</span></label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}" 
                                       placeholder="Start typing street, number, city…" autocomplete="off" required
                                       class="form-control @error('address') is-invalid @enderror">
                                <input type="hidden" id="address_json" name="address_json" value="{{ old('address_json') }}">
                                <div id="suggest" class="position-absolute w-100 bg-white border rounded shadow-lg d-none" style="z-index: 1000; max-height: 240px; overflow-y: auto;"></div>
                                <div class="form-text">Start typing and pick an address to auto‑fill fields.</div>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">House number</label>
                                <input type="text" id="number" name="number" value="{{ old('number') }}"
                                       class="form-control @error('number') is-invalid @enderror">
                                @error('number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Postal code</label>
                                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                                       class="form-control @error('postal_code') is-invalid @enderror">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}"
                                       class="form-control @error('city') is-invalid @enderror">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" id="country" name="country" value="{{ old('country', 'Belgium') }}"
                                       class="form-control @error('country') is-invalid @enderror">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
            </div>
        </div>

                        {{-- Business details --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-briefcase me-2"></i>Business Details
                                </h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">VAT number</label>
                                <input type="text" name="btw_number" value="{{ old('btw_number') }}" placeholder="BE 0123.456.789"
                                       class="form-control @error('btw_number') is-invalid @enderror">
                                <div class="form-text">Required for invoicing clients. Format: BE 0123.456.789</div>
                                @error('btw_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hourly tariff (€)</label>
                                <input type="number" step="0.01" min="0" name="tariff" value="{{ old('tariff') }}" placeholder="45.00"
                                       class="form-control @error('tariff') is-invalid @enderror">
                                @error('tariff')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Availability</label>
                                <select name="availability_status" class="form-control @error('availability_status') is-invalid @enderror">
                        <option value="available" {{ old('availability_status') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="busy" {{ old('availability_status') === 'busy' ? 'selected' : '' }}>Busy (not working)</option>
                        <option value="holiday" {{ old('availability_status') === 'holiday' ? 'selected' : '' }}>On holiday (not working)</option>
                    </select>
                                @error('availability_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
            </div>

                        {{-- Service areas --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-map me-2"></i>Service Areas
                                </h5>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Municipalities (Hoofdgemeente) <span class="text-danger">*</span></label>
                                <div class="form-text mb-3">Selecting a municipality automatically covers all towns within it. Hold Ctrl/Cmd to select multiple municipalities.</div>
                @if(isset($municipalities) && count($municipalities))
                                    <div class="border rounded p-3 bg-light">
                                        <select name="municipalities[]" multiple class="form-select @error('municipalities') is-invalid @enderror" 
                                                style="min-height: 200px; border: none; background: transparent;" size="8">
                        @foreach($municipalities as $m)
                            <option value="{{ $m }}" @selected(collect(old('municipalities', []))->contains($m))>{{ $m }}</option>
                        @endforeach
                    </select>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Selected: <span id="selected-count">0</span> municipalities
                                    </div>
                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No municipalities list available. You can enter them manually below.
                                    </div>
                    <input type="text" name="municipalities_csv" value="{{ old('municipalities_csv') }}"
                           placeholder="Type comma-separated municipalities e.g. Brugge, Oostkamp"
                                           class="form-control @error('municipalities') is-invalid @enderror">
                                    <div class="form-text">Tip: pass $municipalities to render a multi-select.</div>
                @endif
                                @error('municipalities')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
            </div>

                        {{-- Service categories --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-tools me-2"></i>Service Categories
                                </h5>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Select all categories this plumber can handle <span class="text-danger">*</span></label>
                                <div class="form-text mb-3">Choose all service categories that this plumber can provide to clients.</div>
                                @if(isset($categories) && count($categories) > 0)
                                    <div class="border rounded p-3 bg-light">
                                        <div class="row g-2">
                                            @foreach($categories as $cat)
                                                <div class="col-lg-4 col-md-6 col-sm-12">
                                                    <div class="form-check p-3 border rounded bg-white hover-bg-light transition-all">
                                                        <input type="checkbox" name="categories[]" value="{{ $cat->id ?? '' }}" 
                                                               class="form-check-input" id="category_{{ $cat->id ?? '' }}"
                                                               @checked(in_array($cat->id ?? '', old('categories', [])))>
                                                        <label class="form-check-label fw-medium" for="category_{{ $cat->id ?? '' }}">
                                                            <i class="fas fa-wrench me-2 text-primary"></i>
                                                            {{ $cat->label ?? '' }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Selected: <span id="categories-count">0</span> categories
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No service categories found. Please seed the categories first.
                                    </div>
                                @endif
                                @error('categories')
                                    <div class="text-danger small mt-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- Form actions --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Plumber
                                    </button>
                                    <a href="{{ route('plumbers.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .transition-all {
            transition: all 0.2s ease;
        }
        .form-check:hover .form-check-input {
            transform: scale(1.1);
        }
        .form-check-input:checked + .form-check-label {
            color: #0d6efd;
        }
    </style>

    <script>
    // Enhanced form functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Update counters for municipalities and categories
      function updateCounters() {
        // Update municipalities counter
        const municipalitiesSelect = document.querySelector('select[name="municipalities[]"]');
        const selectedCount = document.getElementById('selected-count');
        if (municipalitiesSelect && selectedCount) {
          const selected = municipalitiesSelect.selectedOptions.length;
          selectedCount.textContent = selected;
        }

        // Update categories counter
        const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]');
        const categoriesCount = document.getElementById('categories-count');
        if (categoryCheckboxes.length > 0 && categoriesCount) {
          const checked = Array.from(categoryCheckboxes).filter(cb => cb.checked).length;
          categoriesCount.textContent = checked;
        }
      }

      // Add event listeners for counters
      const municipalitiesSelect = document.querySelector('select[name="municipalities[]"]');
      if (municipalitiesSelect) {
        municipalitiesSelect.addEventListener('change', updateCounters);
      }

      const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]');
      categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCounters);
      });

      // Initialize counters
      updateCounters();

      // Enhanced address search with Vlaanderen API and OSM fallback
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
        
        // 1) Probeer Vlaanderen Suggestion op genormaliseerde query
        const qVL = normalizeForVL(qUser);
        console.log('Normalized query:', qVL); // Debug log
        let vlS = [];
        try {
          const vlUrl = `https://geo.api.vlaanderen.be/geolocation/v4/Suggestion?q=${encodeURIComponent(qVL)}&c=10`;
          console.log('Trying Vlaanderen Suggestion API:', vlUrl); // Debug log
          vlS = await fetchJSON(vlUrl);
          console.log('Vlaanderen Suggestion result:', vlS); // Debug log
        } catch(e) {
          console.log('Vlaanderen Suggestion API error:', e.message); // Debug log
        }

        if (Array.isArray(vlS) && vlS.length) {
          console.log('Using Vlaanderen Suggestion results'); // Debug log
          return {data:vlS, src:'vl'};
        }

        // 2) Probeer Vlaanderen Location direct (soms vindt dit wel wat Suggestion mist)
        let vlL = [];
        try {
          const vlLocUrl = `https://geo.api.vlaanderen.be/geolocation/v4/Location?q=${encodeURIComponent(qVL)}`;
          console.log('Trying Vlaanderen Location API:', vlLocUrl); // Debug log
          vlL = await fetchJSON(vlLocUrl);
          console.log('Vlaanderen Location result:', vlL); // Debug log
        } catch(e) {
          console.log('Vlaanderen Location API error:', e.message); // Debug log
        }
        
        if (Array.isArray(vlL) && vlL.length) {
          console.log('Using Vlaanderen Location results'); // Debug log
          // giet om naar Suggestion-achtig formaat voor weergave
          const mapped = vlL.map(x=>({Suggestion:{Label: formatVLLabelFromLocation(x.Location||{})}, _vlLoc:x}));
          return {data:mapped, src:'vl'};
        }

        // 3) Fallback OSM (heel België, tolerant voor volgorde)
        try {
          const osmUrl = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=10&countrycodes=be&addressdetails=1&accept-language=nl&q=${encodeURIComponent(qUser)}`;
          console.log('Trying OSM API:', osmUrl); // Debug log
          const osm = await fetchJSON(osmUrl);
          console.log('OSM result:', osm); // Debug log
          return {data:osm, src:'osm'};
        } catch(e) {
          console.log('OSM API error:', e.message); // Debug log
          throw e;
        }
      }

      function escapeHtml(s) { 
        return (s || '').replace(/[&<>"]/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); 
      }

      function renderList(list, src) {
        if (!list || !list.length) {
          sugg.innerHTML = `<div class="p-2 text-muted"><small>No results found</small></div>`;
          sugg.classList.remove('d-none');
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
          const activeClass = i === activeIndex ? 'bg-primary text-white' : 'hover-bg-light';
          return `<div class="p-2 border-bottom cursor-pointer ${activeClass}" data-i="${i}" data-src="${b}" style="cursor: pointer;">
            <small class="badge bg-secondary me-2">[${b}]</small>
            <span>${escapeHtml(label)}</span>
          </div>`;
        }).join('');
        
        sugg.classList.remove('d-none');
        sugg.querySelectorAll('[data-i]').forEach(el => {
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
        sugg.classList.add('d-none');

        try{
          if (src==='vl'){
            // Haal echte Location data op (ook als item uit vl_loc-mapping kwam)
            if (items[i] && items[i]._vlLoc){
              showVL(items[i]._vlLoc);
            } else {
              const loc = await fetchJSON(`https://geo.api.vlaanderen.be/geolocation/v4/Location?q=${encodeURIComponent(label)}`);
              showVL(loc[0]?.Location || null, loc);
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
        // Prioritize city over town over village over municipality to avoid "municipal" text
        const cityName = addr.city || addr.town || addr.village || addr.municipality || '';
        
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

      // Input event with debouncing
      input.addEventListener('input', () => {
        const q = input.value.trim();
        activeIndex = -1;
        if (debounceId) clearTimeout(debounceId);
        if (q.length < 2) { 
          sugg.classList.add('d-none'); 
          return; 
        }

        // Show loading state
        sugg.innerHTML = `<div class="p-2 text-muted"><small>Searching...</small></div>`;
        sugg.classList.remove('d-none');

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
            sugg.innerHTML = `<div class="p-2 text-danger"><small>Error: ${escapeHtml(e.message)}</small></div>`;
            sugg.classList.remove('d-none');
          }
        }, 350); // Slightly longer to avoid rate limits
      });

      // Keyboard navigation
      input.addEventListener('keydown', e => {
        if (sugg.classList.contains('d-none')) return;
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
          const firstBadge = sugg.querySelector('[data-src]')?.dataset.src || 'VL';
          choose(activeIndex >= 0 ? activeIndex : 0, firstBadge);
        }
        if (e.key === 'Escape') { 
          sugg.classList.add('d-none'); 
        }
      });

      function rerenderActive() {
        const nodes = [...sugg.querySelectorAll('[data-i]')];
        nodes.forEach((n, i) => {
          if (i === activeIndex) {
            n.classList.remove('hover-bg-light');
            n.classList.add('bg-primary', 'text-white');
          } else {
            n.classList.remove('bg-primary', 'text-white');
            n.classList.add('hover-bg-light');
          }
        });
      }

      // Close suggestions when clicking outside
      document.addEventListener('click', e => {
        if (!sugg.contains(e.target) && e.target !== input) {
          sugg.classList.add('d-none');
        }
      });
    });
    </script>
@endsection
