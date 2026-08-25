@push('styles')
<style>
:root{--bg:#F5F7FA;--bg2:#FFFFFF;--bg3:#EEF1F6;--border:#DDE2EC;--text:#1C2B3A;--muted:#6B7A90;--dim:#C8D0DC}
#dcs *{box-sizing:border-box;font-family:'DM Mono',monospace}
.dc-sc{padding:12px 14px;cursor:pointer;border-bottom:1px solid var(--border);transition:background .12s}
.dc-sc:hover,.dc-sc.on{background:var(--bg3)}
.dc-kpi{background:var(--bg2);border:1px solid var(--dim);border-radius:8px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.dc-bar{height:3px;background:var(--dim);border-radius:2px;margin-top:6px}
.dc-dec{display:flex;align-items:flex-start;gap:8px;padding:8px 0;border-top:1px solid var(--dim)}
.dc-dec:first-child{border-top:none}
.dc-out{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-top:1px solid var(--dim);font-size:11px}
.dc-out:first-child{border-top:none}
.dc-cta{width:100%;padding:10px 12px;border-radius:8px;border:none;cursor:pointer;text-align:left;margin-bottom:6px;transition:opacity .12s}
.dc-cta:hover{opacity:.82}
.dc-uc{padding:12px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s}
.dc-uc:hover,.dc-uc.on{background:var(--bg3)}
</style>
@endpush

@php $cn = auth('client')->user()?->company_name ?? 'BEHAVR'; @endphp

<div id="dcs" style="display:flex;flex-direction:column;height:100%;overflow:hidden;background:var(--bg);color:var(--text)">

  {{-- Header --}}
  <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
      <h1 class="text-[16px] font-semibold text-gray-900">{{ $layerTitle }}</h1>
      <p class="text-[11px] text-gray-500 mt-0.5">
        Tenant: <span class="text-teal-600 font-medium">{{ $cn }}</span>
        <span class="ml-2 inline-flex items-center gap-1 text-green-600 font-medium text-[10px]">
          <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Live · ML Scoring Active
        </span>
      </p>
    </div>
    <div class="flex items-center gap-4 text-[11px] text-gray-500">
      {{-- Home button --}}
      <a href="{{ route('client.dashboard') }}"
         class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
         title="Home">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </a>

      <span class="flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span class="font-medium text-gray-600">8.7M profiles</span>
      </span>
      <span class="flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="font-medium text-gray-600">94.1% accuracy</span>
      </span>

      {{-- Avatar with dropdown --}}
      @php
        $dcAv = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $cn), 0, 2))));
      @endphp
      <div class="relative" data-dd-wrap>
        <button onclick="xpDd('ddDc')"
                class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-bold focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-cyan-400">
          {{ $dcAv }}
        </button>
        <div id="ddDc" data-dd-menu
             class="absolute right-0 top-10 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
             style="display:none">
          <div class="px-4 py-2 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-800 truncate">{{ $cn }}</p>
            <p class="text-[10px] text-gray-400 truncate">Client Account</p>
          </div>
          <a href="{{ route('client.dashboard') }}"
             class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile Settings
          </a>
          <hr class="my-1 border-gray-100">
          <form method="POST" action="{{ route('client.logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Log Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  {{-- 3-panel --}}
  <div style="flex:1;display:flex;overflow:hidden">
    <div id="dc-left" style="width:240px;flex-shrink:0;border-right:1px solid var(--border);overflow-y:auto;background:var(--bg2)"></div>
    <div id="dc-center" style="width:360px;flex-shrink:0;border-right:1px solid var(--border);overflow-y:auto;background:var(--bg)"></div>
    <div id="dc-right" style="flex:1;min-width:0;overflow-y:auto;background:var(--bg)"></div>
  </div>
</div>

<script>
const SC=@json($scenarios),UG=@json($userGroups);
let cSc=0,cCta=null,cUsr=null;
function h(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}

function renderLeft(){
  document.getElementById('dc-left').innerHTML=SC.map((s,i)=>`
    <div class="dc-sc${i===cSc?' on':''}" onclick="setSc(${i})">
      <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:5px">
        <span style="width:3px;min-height:28px;border-radius:2px;background:${h(s.lc)};flex-shrink:0;margin-top:2px;display:block"></span>
        <div><div style="font-size:11px;font-weight:600;color:var(--text);line-height:1.35">${h(s.name)}</div>
        <div style="font-size:10px;color:var(--muted);margin-top:2px">${h(s.users)}</div></div>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--muted);padding-left:11px">
        <span>${h(s.rev)}</span><span style="color:${h(s.urgDot)};font-weight:600">${h(s.ul)}</span>
      </div>
    </div>`).join('');
}

function renderCenter(){
  const el=document.getElementById('dc-center');
  if(!SC.length){el.innerHTML='';return;}
  const s=SC[cSc];
  const kpis=s.kpis.map(k=>`<div class="dc-kpi"><div style="font-size:20px;font-weight:700;color:${h(k.c)}">${h(k.v)}</div><div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;line-height:1.4">${h(k.l)}</div><div class="dc-bar"><div style="height:100%;width:${Math.min(100,Number(k.p))}%;background:${h(k.c)};border-radius:2px"></div></div></div>`).join('');
  const dec=s.dec.map(d=>`<div class="dc-dec"><span style="width:6px;height:6px;border-radius:50%;background:${d.g?'#10B981':'#F43F5E'};flex-shrink:0;margin-top:4px"></span><div style="flex:1"><div style="font-size:11px;font-weight:600;color:var(--text)">${h(d.l)}</div><div style="font-size:10px;color:var(--muted);margin-top:1px">${h(d.s)}</div></div><div style="font-size:14px;font-weight:700;color:${d.g?'#10B981':'#F43F5E'};white-space:nowrap;margin-left:8px">${h(d.v)}</div></div>`).join('');
  const out=s.out.map(o=>`<div class="dc-out"><span style="color:var(--muted)">${h(o.l)}</span><span style="font-weight:600">${h(o.v)}</span></div>`).join('');
  const ctas=s.ctas.map(c=>`<button class="dc-cta" onclick="setCta('${h(c.view)}')" style="background:${cCta===c.view?c.c+'28':c.c+'0F'};border:1px solid ${h(c.c)}66;color:var(--text)"><div style="display:flex;align-items:flex-start;gap:8px"><span style="font-size:16px;flex-shrink:0">${c.ico}</span><div style="flex:1;min-width:0"><div style="font-size:11px;font-weight:600">${h(c.l)}</div><div style="font-size:10px;color:var(--muted);margin-top:2px;line-height:1.4">${h(c.d)}</div></div><div style="font-size:10px;color:${h(c.c)};font-weight:700;white-space:nowrap;margin-left:4px">${h(c.b)}</div></div></button>`).join('');
  el.innerHTML=`
    <div style="padding:14px 14px 10px;border-bottom:1px solid var(--border)">
      <div style="font-size:12px;font-weight:600">${h(s.name)}</div>
      <div style="font-size:10px;color:var(--muted);margin-top:2px">${h(s.users)} · ${h(s.rev)}</div>
    </div>
    <div style="background:${h(s.banner.c)}18;border-left:3px solid ${h(s.banner.c)};margin:12px 14px;padding:10px 12px;border-radius:0 6px 6px 0;font-size:11px;line-height:1.55;color:var(--text)">${h(s.banner.text)}</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:0 14px 12px">${kpis}</div>
    <div style="padding:0 14px 12px"><div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Decision</div>${dec}</div>
    <div style="padding:0 14px 12px"><div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Outcome</div>${out}</div>
    <div style="padding:0 14px 14px"><div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Action</div>${ctas}</div>`;
}

function uDetail(u){
  const flds=u.fields.map(f=>`<div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px solid var(--border);font-size:11px"><span style="color:var(--muted)">${h(f[0])}</span><span style="font-weight:500">${h(f[1])}</span></div>`).join('');
  const tl=u.timeline.map(t=>`<div style="display:flex;gap:8px;padding:4px 0"><span style="width:6px;height:6px;border-radius:50%;background:${h(t.c)};flex-shrink:0;margin-top:4px"></span><div><div style="font-size:10px">${h(t.e)}</div><div style="font-size:9px;color:var(--muted)">${h(t.t)}</div></div></div>`).join('');
  const acts=u.actions.map(a=>`<button onclick="event.stopPropagation()" style="flex:1;padding:6px 8px;border-radius:6px;border:1px solid ${h(a[1])}80;background:${a[2]==='bg'?h(a[1])+'28':'transparent'};color:${h(a[1])};cursor:pointer;font-size:10px;font-family:inherit;font-weight:600">${h(a[0])}</button>`).join('');
  return`<div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">${flds}<div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin:10px 0 4px">Timeline</div>${tl}<div style="display:flex;gap:6px;margin-top:10px">${acts}</div></div>`;
}

function renderRight(){
  const el=document.getElementById('dc-right');
  if(!cCta){el.innerHTML='<div style="padding:40px 20px;text-align:center;color:var(--muted);font-size:12px">← Select an action to view records</div>';return;}
  const s=SC[cSc];
  const ctaObj=s.ctas.find(c=>c.view===cCta)||{};
  const users=UG[cCta]||[];
  const filters=(ctaObj.vf||['All']).map(f=>`<button style="font-size:10px;padding:4px 10px;border-radius:20px;border:1px solid var(--dim);background:transparent;color:var(--muted);cursor:pointer">${h(f)}</button>`).join('');
  const cards=users.length?users.map(u=>`
    <div class="dc-uc${cUsr===u.id?' on':''}" onclick="setUsr(${u.id})">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:50%;background:${h(u.avatarColor)}30;border:1px solid ${h(u.avatarColor)}80;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:${h(u.avatarColor)};flex-shrink:0">${h(u.initials)}</div>
        <div style="flex:1;min-width:0"><div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${h(u.name)}</div>
        <div style="font-size:10px;color:var(--muted)">${h(u.company)} · ${h(u.industry)}</div></div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-size:9px;padding:2px 7px;border-radius:4px;background:${h(u.tierColor)}18;color:${h(u.tierColor)};border:1px solid ${h(u.tierColor)}66;font-weight:700">${h(u.tier)}</div>
          <div style="font-size:10px;color:var(--muted);margin-top:3px">${h(u.amountLabel)}</div>
        </div>
      </div>
      ${u.tags.length?`<div style="display:flex;gap:4px;margin-top:6px;flex-wrap:wrap">${u.tags.map(t=>`<span style="font-size:9px;padding:2px 6px;border-radius:3px;background:var(--bg3);color:var(--muted)">${h(t)}</span>`).join('')}</div>`:''}
      ${cUsr===u.id?uDetail(u):''}
    </div>`).join(''):'<div style="padding:20px;text-align:center;color:var(--muted);font-size:12px">No records in this group</div>';
  el.innerHTML=`
    <div style="padding:12px 14px 8px;border-bottom:1px solid var(--border)">
      <div style="font-size:11px;font-weight:600">${h(ctaObj.vt||'Records')}</div>
      <div style="font-size:10px;color:var(--muted);margin-top:2px">${h(ctaObj.vs||'')} · ${users.length} records</div>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap;padding:10px 14px;border-bottom:1px solid var(--border)">${filters}</div>
    ${cards}`;
}

function setSc(i){cSc=i;cCta=null;cUsr=null;renderLeft();renderCenter();renderRight();}
function setCta(v){cCta=v;cUsr=null;renderCenter();renderRight();}
function setUsr(id){cUsr=cUsr===id?null:id;renderRight();}

renderLeft();renderCenter();renderRight();

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('dcpAvatarWrap');
  var drop = document.getElementById('dcpDropdown');
  if (wrap && drop && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>
