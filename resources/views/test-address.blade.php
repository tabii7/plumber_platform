<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address Search Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 600px; margin: 0 auto; }
        .input-wrap { position: relative; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; font-size: 16px; border: 1px solid #ddd; border-radius: 8px; }
        .suggest { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 1000; max-height: 300px; overflow: auto; display: none; }
        .s-item { padding: 10px 12px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; gap: 8px; align-items: flex-start; }
        .s-item:hover { background: #f5f5f5; }
        .s-badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; background: #e3f2fd; color: #1976d2; font-weight: bold; }
        .s-label { flex: 1; }
        .result { margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-weight: bold; margin-bottom: 5px; }
        .field input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Address Search Test</h1>
        <p>Test the enhanced address search functionality with Vlaanderen API and OSM fallback.</p>
        
        <div class="input-wrap">
            <label for="address">Start typing an address:</label>
            <input id="address" placeholder="e.g., Minnewater 22 Brugge, Karel de Stoutelaan 26, etc." autocomplete="off">
            <div id="suggest" class="suggest"></div>
        </div>

        <div class="result">
            <h3>Form Fields (will be auto-filled):</h3>
            <div class="field">
                <label for="street">Street Address:</label>
                <input id="street" readonly>
            </div>
            <div class="field">
                <label for="number">House Number:</label>
                <input id="number" readonly>
            </div>
            <div class="field">
                <label for="postal_code">Postal Code:</label>
                <input id="postal_code" readonly>
            </div>
            <div class="field">
                <label for="city">City:</label>
                <input id="city" readonly>
            </div>
            <div class="field">
                <label for="json_data">JSON Data:</label>
                <textarea id="json_data" rows="5" style="width: 100%; font-family: monospace; font-size: 12px;" readonly></textarea>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.querySelector('#address');
            const sugg = document.querySelector('#suggest');
            const street = document.querySelector('#street');
            const number = document.querySelector('#number');
            const postalCode = document.querySelector('#postal_code');
            const city = document.querySelector('#city');
            const jsonData = document.querySelector('#json_data');
            
            let items = []; 
            let activeIndex = -1; 
            let debounceId = null;

            // Heuristic normalization for Vlaanderen API
            function normalizeForVL(qRaw) {
                let q = qRaw.trim().replace(/\s+/g, ' ');
                
                // Move number to end of street name
                const m = q.match(/^(\d+)\s+(.+)/i);
                if (m) q = m[2] + ' ' + m[1];

                // Add comma before city names
                const cities = ['brugge','brussel','antwerpen','gent','leuven','mechelen','kortrijk','hasselt','oostende','roeselare','aalst','genk','turnhout','lier','waregem','dilbeek','asse','zaventem','knokke','deinze','oudenaarde','eeklo','blankenberge','tienen','wetteren','dendermonde'];
                for (const c of cities) {
                    const idx = q.toLowerCase().lastIndexOf(' ' + c);
                    if (idx > 0 && !q.includes(',')) {
                        q = q.slice(0, idx) + ', ' + q.slice(idx + 1);
                        break;
                    }
                }

                // Capitalize first letter of each word
                q = q.replace(/\b([a-zà-ÿ])/g, (m) => m.toUpperCase());
                return q;
            }

            async function fetchJSON(url) {
                const r = await fetch(url);
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            }

            async function searchSmart(qUser) {
                // 1) Try Vlaanderen Suggestion with normalized query
                const qVL = normalizeForVL(qUser);
                let vlS = [];
                try {
                    vlS = await fetchJSON(`/api/address/search-vlaanderen?q=${encodeURIComponent(qVL)}&c=10`);
                } catch(e) {}

                if (Array.isArray(vlS) && vlS.length) return {data: vlS, src: 'vl'};

                // 2) Try Vlaanderen Location as fallback
                let vlL = [];
                try {
                    vlL = await fetchJSON(`/api/address/search-vlaanderen-location?q=${encodeURIComponent(qVL)}`);
                } catch(e) {}
                
                if (Array.isArray(vlL) && vlL.length) {
                    // Convert to suggestion format for display
                    const mapped = vlL.map(x => {
                        const location = x.Location || {};
                        const line1 = [location.Thoroughfarename, location.Housenumber].filter(Boolean).join(' ');
                        const line2 = [location.Postalcode, location.Municipality].filter(Boolean).join(' ');
                        const parts = [];
                        if (line1) parts.push(line1);
                        if (line2) parts.push(line2);
                        const label = parts.join(', ');
                        return {
                            Suggestion: {Label: label}, 
                            _vlLoc: x
                        };
                    });
                    return {data: mapped, src: 'vl'};
                }

                // 3) Fallback to OSM
                const osm = await fetchJSON(`/api/address/search-osm?q=${encodeURIComponent(qUser)}`);
                return {data: osm, src: 'osm'};
            }

            function escapeHtml(s) { 
                return (s || '').replace(/[&<>"]/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); 
            }

            function renderList(list, src) {
                if (!list || !list.length) {
                    sugg.innerHTML = `<div class="s-item"><span class="s-badge">Ø</span><span class="s-label">No results found</span></div>`;
                    sugg.style.display = 'block';
                    return;
                }
                
                sugg.innerHTML = list.map((it, i) => {
                    const label = (src === 'vl') ? (it?.Suggestion?.Label || '') : (it?.display_name || '');
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

            async function choose(i, srcBadge) {
                if (i < 0 || i >= items.length) return;
                const src = (srcBadge === 'VL') ? 'vl' : 'osm';

                const label = (src === 'vl')
                    ? (items[i]?.Suggestion?.Label || '')
                    : (items[i]?.display_name || '');

                input.value = label;
                sugg.style.display = 'none';

                try {
                    if (src === 'vl') {
                        // Get detailed Location data
                        if (items[i] && items[i]._vlLoc) {
                            showVL(items[i]._vlLoc);
                        } else {
                            const loc = await fetchJSON(`/api/address/search-vlaanderen-location?q=${encodeURIComponent(label)}`);
                            showVL(loc[0]?.Location || null, loc);
                        }
                    } else {
                        // OSM data
                        showOSM(items[i]);
                    }
                } catch(e) {
                    console.error('Error fetching details:', e);
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
                    street.value = [streetName, houseNumber].filter(Boolean).join(' ');
                    number.value = houseNumber;
                    postalCode.value = postalCode;
                    city.value = municipality;
                    
                    // Store full data
                    const data = {
                        source: 'vl',
                        location: L,
                        raw: rawArr
                    };
                    jsonData.value = JSON.stringify(data, null, 2);
                }
            }

            function showOSM(it) {
                const addr = it?.address || {};
                const streetName = addr.road || addr.pedestrian || addr.path || '';
                const houseNumber = addr.house_number || '';
                const postalCode = addr.postcode || '';
                const cityName = addr.city || addr.town || addr.village || addr.municipality || '';
                
                // Fill form fields
                street.value = [streetName, houseNumber].filter(Boolean).join(' ');
                number.value = houseNumber;
                postalCode.value = postalCode;
                city.value = cityName;
                
                // Store full data
                const data = {
                    source: 'osm',
                    address: it
                };
                jsonData.value = JSON.stringify(data, null, 2);
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

                debounceId = setTimeout(async () => {
                    try {
                        const {data, src} = await searchSmart(q);
                        items = data || [];
                        renderList(items, src);
                    } catch(e) {
                        items = [];
                        sugg.innerHTML = `<div class="s-item"><span class="s-badge">ERR</span><span class="s-label">Error: ${escapeHtml(e.message)}</span></div>`;
                        sugg.style.display = 'block';
                    }
                }, 350);
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
