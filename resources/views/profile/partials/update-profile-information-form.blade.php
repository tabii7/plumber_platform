<section>
    {{-- Resend verification form (unchanged) --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- Profile update --}}
    <form method="post" action="{{ route('profile.update') }}" novalidate>
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="full_name" type="text" class="form-control"
                   value="{{ old('full_name', $user->full_name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control"
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-muted">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="btn btn-link p-0 text-decoration-none">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- WhatsApp number --}}
        <div class="mb-3">
            <label for="whatsapp_number" class="form-label">WhatsApp number</label>
            <input id="whatsapp_number" name="whatsapp_number" type="text" class="form-control"
                   placeholder="324xxxxxxxx"
                   value="{{ old('whatsapp_number', $user->whatsapp_number) }}" required>
            @error('whatsapp_number')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <hr class="my-4">

        {{-- ADDRESS (same fields/behavior as your register page) --}}
        <div class="mb-3">
            <label for="address" class="form-label">Street address</label>
            <div class="position-relative">
                <input id="address" name="address" type="text" class="form-control"
                       value="{{ old('address', $user->address) }}"
                       placeholder="Start typing street, number, city…" autocomplete="off" required>
                <input type="hidden" id="address_json" name="address_json"
                       value='{{ old('address_json', $user->address_json ?? "") }}'>
                {{-- suggestions dropdown --}}
                <div id="suggest"
                     class="border rounded-3 bg-white shadow-sm"
                     style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:240px; overflow:auto"></div>
            </div>
            <div class="form-text">Start typing and pick an address to auto-fill the fields below.</div>
            @error('address')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label for="number" class="form-label">House number</label>
                <input id="number" name="number" type="text" class="form-control"
                       value="{{ old('number', $user->number) }}" placeholder="12A">
                @error('number')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="postal_code" class="form-label">Postal code</label>
                <input id="postal_code" name="postal_code" type="text" class="form-control"
                       value="{{ old('postal_code', $user->postal_code) }}" placeholder="1000">
                @error('postal_code')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="city" class="form-label">City</label>
                <input id="city" name="city" type="text" class="form-control"
                       value="{{ old('city', $user->city) }}" placeholder="Brussels">
                @error('city')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success">{{ __('Saved.') }}</span>
            @endif
        </div>
    </form>
</section>

{{-- Lightweight address-suggest JS (reuses your /api/address/* endpoints) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('address');
    const sugg  = document.getElementById('suggest');
    const number = document.getElementById('number');
    const zip   = document.getElementById('postal_code');
    const city  = document.getElementById('city');
    const hidden= document.getElementById('address_json');
    if (!input || !sugg || !number || !zip || !city || !hidden) return;

    let items = [], active = -1, t = null;

    function escapeHtml(s){ return (s||'').replace(/[&<>"]/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); }
    async function j(url){ const r=await fetch(url); if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }

    function normalizeForVL(q){
        let s=q.trim().replace(/\s+/g,' ');
        const m=s.match(/^(\d+)\s+(.+)/i); if(m) s=m[2]+' '+m[1];
        return s.replace(/\b([a-zà-ÿ])/g, m=>m.toUpperCase());
    }

    async function searchSmart(qUser){
        const qVL=normalizeForVL(qUser);
        let vlS=[]; try{ vlS=await j(`/api/address/search-vlaanderen?q=${encodeURIComponent(qVL)}&c=10`);}catch(e){}
        if (Array.isArray(vlS)&&vlS.length) return {data:vlS,src:'vl'};
        let vlL=[]; try{ vlL=await j(`/api/address/search-vlaanderen-location?q=${encodeURIComponent(qVL)}`);}catch(e){}
        if (Array.isArray(vlL)&&vlL.length){
            const mapped=vlL.map(x=>{
                const L=x.Location||{};
                const l1=[L.Thoroughfarename,L.Housenumber].filter(Boolean).join(' ');
                const l2=[L.Postalcode,L.Municipality].filter(Boolean).join(' ');
                return { Suggestion:{ Label:[l1,l2].filter(Boolean).join(', ') }, _vlLoc:x };
            });
            return {data:mapped,src:'vl'};
        }
        const osm = await j(`/api/address/search-osm?q=${encodeURIComponent(qUser)}`);
        return {data:osm,src:'osm'};
    }

    function render(list,src){
        if(!list||!list.length){
            sugg.innerHTML='<div class="p-2 small text-muted">No results</div>';
            sugg.style.display='block';
            return;
        }
        sugg.innerHTML = list.map((it,i)=>{
            const label = (src==='vl') ? (it?.Suggestion?.Label||'') : (it?.display_name||'');
            return `<div class="p-2 border-bottom s-item ${i===active?'bg-light':''}" data-i="${i}" data-src="${src}">
                <span class="badge text-bg-info me-2">${src.toUpperCase()}</span>
                <span>${escapeHtml(label)}</span>
            </div>`;
        }).join('');
        sugg.style.display='block';
        [...sugg.querySelectorAll('.s-item')].forEach(el=>{
            el.addEventListener('mousedown',()=>choose(parseInt(el.dataset.i,10),el.dataset.src));
        });
    }

    async function choose(i,src){
        if (i<0 || i>=items.length) return;
        const label = (src==='vl') ? (items[i]?.Suggestion?.Label||'') : (items[i]?.display_name||'');
        input.value = label; sugg.style.display='none';
        try{
            if (src==='vl'){
                const L = items[i]?._vlLoc?.Location;
                if (L){
                    fillFromVL(L, items[i]._vlLoc);
                }else{
                    const loc = await j(`/api/address/search-vlaanderen-location?q=${encodeURIComponent(label)}`);
                    fillFromVL(loc?.[0]?.Location || null, loc);
                }
            }else{
                fillFromOSM(items[i]);
            }
        }catch(e){ console.error(e); }
    }

    function fillFromVL(L, raw){
        if(!L) return;
        const street=L.Thoroughfarename||'', hn=L.Housenumber||'', pc=L.Postalcode||'', muni=L.Municipality||'';
        input.value=[street,hn].filter(Boolean).join(' ');
        number.value=hn; zip.value=pc; city.value=muni;
        hidden.value=JSON.stringify({source:'vl',location:L,raw});
    }
    function fillFromOSM(it){
        const a=it?.address||{};
        const street=a.road||a.pedestrian||a.path||'', hn=a.house_number||'', pc=a.postcode||'', c=a.city||a.town||a.village||a.municipality||'';
        input.value=[street,hn].filter(Boolean).join(' ');
        number.value=hn; zip.value=pc; city.value=c;
        hidden.value=JSON.stringify({source:'osm',address:it});
    }

    input.addEventListener('input', ()=>{
        const q=input.value.trim(); active=-1;
        if (t) clearTimeout(t);
        if (q.length<2){ sugg.style.display='none'; return; }
        t=setTimeout(async ()=>{
            try{ const {data,src}=await searchSmart(q); items=data||[]; render(items,src); }
            catch(e){ sugg.innerHTML=`<div class="p-2 small text-danger">Error: ${escapeHtml(e.message)}</div>`; sugg.style.display='block'; }
        }, 300);
    });

    input.addEventListener('keydown', e=>{
        if (sugg.style.display==='none') return;
        const max=items.length-1;
        if (e.key==='ArrowDown'){ e.preventDefault(); active=Math.min(active+1,max); render(items, sugg.querySelector('.s-item')?.dataset.src || 'vl'); }
        if (e.key==='ArrowUp'){ e.preventDefault(); active=Math.max(active-1,0); render(items, sugg.querySelector('.s-item')?.dataset.src || 'vl'); }
        if (e.key==='Enter'){ e.preventDefault(); const src=sugg.querySelector('.s-item')?.dataset.src || 'vl'; choose(active>=0?active:0, src); }
        if (e.key==='Escape'){ sugg.style.display='none'; }
    });

    document.addEventListener('click', (e)=>{ if(!sugg.contains(e.target) && e.target!==input) sugg.style.display='none'; });
});
</script>
