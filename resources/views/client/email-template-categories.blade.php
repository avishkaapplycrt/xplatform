{{-- resources/views/client/email-template-categories.blade.php --}}
@extends('layouts.platform')
@section('title', 'Email Template Categories')

@push('styles')
<style>
  /* ── Root layout fix for scrolling ── */
  .etc-root {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    background: #f9fafb;
  }

  .etc-main-scroll {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 16px 20px;
  }

  /* ── Top bar (same as l5) ── */
  .etc-topbar {
    flex-shrink: 0;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 10;
  }

  .etc-topbar-title { font-size: 16px; font-weight: 600; color: #111827; }
  .etc-topbar-sub { font-size: 11px; color: #6b7280; margin-top: 2px; }
  .etc-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 999px; }
  .etc-pill.live { background: #dcfce7; color: #166534; }
  .etc-pill.demo { background: #fef3c7; color: #92400e; }

  /* ── Original card styles ── */
  .etc-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; max-width: 800px; margin: 0 auto; }
  .etc-card-hd { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
  
  .etc-list { padding: 0; margin: 0; list-style: none; }
  .etc-item { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-bottom: 1px solid #f3f4f6; }
  .etc-item:hover { background: #f9fafb; }
  .etc-item:last-child { border-bottom: none; }
  
  .etc-badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }
  .etc-badge-default { border: 1px dashed #d1d5db; }
  
  .etc-info { flex: 1; }
  .etc-name { font-size: 14px; font-weight: 600; color: #111827; }
  .etc-meta { font-size: 11px; color: #9ca3af; margin-top: 2px; }
  
  .etc-sort { display: flex; align-items: center; gap: 4px; }
  .etc-sort input { width: 50px; padding: 4px 8px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 12px; text-align: center; }
  
  .etc-actions { display: flex; gap: 6px; }
  
  .etc-btn { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; }
  .etc-btn-primary { background: #0EA5E9; color: #fff; }
  .etc-btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
  .etc-btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
  .etc-btn-sm { padding: 4px 8px; font-size: 11px; }
  
  .etc-color-picker { display: flex; align-items: center; gap: 8px; }
  .etc-color-picker input[type="color"] { width: 40px; height: 32px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; padding: 2px; }
  .etc-color-hex { width: 80px; padding: 4px 8px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 12px; font-family: monospace; }
  
  .etc-form-group { margin-bottom: 16px; }
  .etc-form-label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .etc-form-input { width: 100%; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px; outline: none; }
  
  .etc-empty { text-align: center; padding: 40px; color: #9ca3af; }
  
  .etc-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 100; align-items: center; justify-content: center; }
  .etc-modal.active { display: flex; }
  .etc-modal-box { background: #fff; border-radius: 12px; width: 480px; max-width: 92%; padding: 24px; }
</style>
@endpush

@section('content')
@php
  $cn = auth('client')->user()?->company_name ?? 'Test Company';
  $av = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$cn),0,2))));
  $isLive = $dataSourceConnected ?? false;
@endphp

<div class="etc-root">

  {{-- ── TOP BAR (same as l5) ── --}}
  <div class="etc-topbar">
    <div>
      <div class="etc-topbar-title">Email Template Categories</div>
      <div style="font-size:11px; color:#6b7280; margin-top:2px;">
        Tenant: <span style="color:#0d9488; font-weight:500;">{{ $cn }}</span>
        @if($isLive)
          <span class="etc-pill live" style="margin-left:8px;">
            <span style="width:6px; height:6px; border-radius:50%; background:#22c55e; display:inline-block;"></span>Live
          </span>
        @else
          <span class="etc-pill demo" style="margin-left:8px;">
            <span style="width:6px; height:6px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>Demo Data
          </span>
        @endif
      </div>
    </div>
    <div style="display:flex; align-items:center; gap:16px; font-size:11px; color:#6b7280;">
      {{-- Home button --}}
      <a href="{{ route('client.dashboard') }}"
         style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; color:#9ca3af; text-decoration:none;"
         onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151';"
         onmouseout="this.style.background='transparent'; this.style.color='#9ca3af';"
         title="Home">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </a>

      <span style="display:flex; align-items:center; gap:4px;">
        <svg style="width:14px; height:14px; color:#a78bfa;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-5a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">{{ number_format(($profiles ?? collect())->count()) }} profiles</span>
      </span>

      <span style="display:flex; align-items:center; gap:4px;">
        <svg style="width:14px; height:14px; color:#f472b6;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span style="font-weight:600; color:#374151;">94.1% accuracy</span>
      </span>

      {{-- Avatar --}}
      <div style="position:relative;" data-dd-wrap>
        <button onclick="xpDd('ddEtc')"
                style="width:32px; height:32px; border-radius:50%; background:#06b6d4; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:700; border:none; cursor:pointer;">
          {{ $av }}
        </button>
        <div id="ddEtc" data-dd-menu
             style="position:absolute; right:0; top:42px; width:192px; background:#fff; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,.1); border:1px solid #e5e7eb; padding:4px 0; z-index:50; display:none;">
          <div style="padding:8px 16px; border-bottom:1px solid #f3f4f6;">
            <p style="font-size:12px; font-weight:600; color:#1f2937; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cn }}</p>
            <p style="font-size:10px; color:#9ca3af; margin:2px 0 0;">Client Account</p>
          </div>
          <a href="{{ route('client.dashboard') }}"
             style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:12px; color:#374151; text-decoration:none;"
             onmouseover="this.style.background='#f9fafb';"
             onmouseout="this.style.background='transparent';">
            <svg style="width:14px; height:14px; color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile Settings
          </a>
          <hr style="margin:4px 0; border:none; border-top:1px solid #f3f4f6;">
          <form method="POST" action="{{ route('client.logout') }}" style="margin:0;">
            @csrf
            <button type="submit"
                    style="width:100%; display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:12px; color:#dc2626; background:none; border:none; cursor:pointer; text-align:left;"
                    onmouseover="this.style.background='#fef2f2';"
                    onmouseout="this.style.background='transparent';">
              <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Log Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ── SCROLLABLE BODY ── --}}
  <div class="etc-main-scroll">

    <div class="etc-header" style="max-width:800px; margin:0 auto 24px;">
      <div class="etc-title" style="font-size:20px; font-weight:700; color:#111827;">Email Template Categories</div>
      <div class="etc-subtitle" style="font-size:13px; color:#6b7280; margin-top:4px;">Organize your templates with custom categories</div>
    </div>

    @if(session('success'))
      <div style="max-width:800px; margin:0 auto 16px; padding: 12px 16px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; color: #166534; font-size: 13px;">
        ✓ {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div style="max-width:800px; margin:0 auto 16px; padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #dc2626; font-size: 13px;">
        ✗ {{ session('error') }}
      </div>
    @endif

    <div class="etc-card">
      <div class="etc-card-hd">
        <span style="font-size: 14px; font-weight: 600;">Categories</span>
        <button class="etc-btn etc-btn-primary" onclick="openModal()">
          <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          New Category
        </button>
      </div>
      
      <ul class="etc-list">
        @foreach($categories as $category)
          <li class="etc-item">
            <div class="etc-badge" style="background: {{ $category->bg_color }}; color: {{ $category->color }}; {{ $category->is_default ? 'border: 1px dashed #d1d5db;' : '' }}">
              {{ $category->name }}
            </div>
            <div class="etc-info">
              <div class="etc-name">{{ $category->name }}</div>
              <div class="etc-meta">
                Slug: {{ $category->slug }} · 
                Templates: {{ $category->templates()->count() }} · 
                {{ $category->is_default ? 'Default · ' : '' }}
                Order: {{ $category->sort_order }}
              </div>
            </div>
            <form method="POST" action="{{ route('client.email.template.categories.update', $category) }}" class="etc-sort" onchange="this.submit()">
              @csrf
              @method('PUT')
              <input type="hidden" name="name" value="{{ $category->name }}">
              <input type="hidden" name="color" value="{{ $category->color }}">
              <input type="hidden" name="bg_color" value="{{ $category->bg_color }}">
              <input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" title="Sort Order">
            </form>
            <div class="etc-actions">
              <button class="etc-btn etc-btn-secondary etc-btn-sm" onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ $category->color }}', '{{ $category->bg_color }}', {{ $category->sort_order }})">
                Edit
              </button>
              @if(!$category->is_default)
                <form method="POST" action="{{ route('client.email.template.categories.destroy', $category) }}" style="display: inline;" onsubmit="return confirm('Delete this category? Templates will be moved to General.');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="etc-btn etc-btn-danger etc-btn-sm">Delete</button>
                </form>
              @endif
            </div>
          </li>
        @endforeach
      </ul>
    </div>

    <div style="max-width:800px; margin:16px auto 0; text-align: center;">
      <a href="{{ route('client.email.templates') }}" class="etc-btn etc-btn-secondary">← Back to Templates</a>
    </div>

  </div>

</div>

<!-- Modal -->
<div id="categoryModal" class="etc-modal">
  <div class="etc-modal-box">
    <h3 id="modalTitle" style="margin: 0 0 20px; font-size: 16px; font-weight: 600;">New Category</h3>
    <form id="categoryForm" method="POST" action="{{ route('client.email.template.categories.store') }}">
      @csrf
      <input type="hidden" id="formMethod" name="_method" value="POST">
      <input type="hidden" id="categoryId" name="category_id">
      
      <div class="etc-form-group">
        <label class="etc-form-label">Category Name</label>
        <input type="text" id="catName" name="name" class="etc-form-input" required placeholder="e.g., Holiday Promotions">
      </div>
      
      <div class="etc-form-group">
        <label class="etc-form-label">Text Color</label>
        <div class="etc-color-picker">
          <input type="color" id="catColor" name="color" value="#374151" onchange="document.getElementById('colorHex').value = this.value">
          <input type="text" id="colorHex" class="etc-color-hex" value="#374151" maxlength="7" onchange="document.getElementById('catColor').value = this.value">
        </div>
      </div>
      
      <div class="etc-form-group">
        <label class="etc-form-label">Background Color</label>
        <div class="etc-color-picker">
          <input type="color" id="catBgColor" name="bg_color" value="#f3f4f6" onchange="document.getElementById('bgColorHex').value = this.value">
          <input type="text" id="bgColorHex" class="etc-color-hex" value="#f3f4f6" maxlength="7" onchange="document.getElementById('catBgColor').value = this.value">
        </div>
      </div>
      
      <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 24px;">
        <button type="button" class="etc-btn etc-btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="submit" class="etc-btn etc-btn-primary">Save Category</button>
      </div>
    </form>
  </div>
</div>

<script>
function xpDd(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('[data-dd-wrap]')) {
    document.querySelectorAll('[data-dd-menu]').forEach(m => m.style.display = 'none');
  }
});

function openModal() {
  document.getElementById('categoryForm').action = '{{ route("client.email.template.categories.store") }}';
  document.getElementById('formMethod').value = 'POST';
  document.getElementById('modalTitle').textContent = 'New Category';
  document.getElementById('catName').value = '';
  document.getElementById('catColor').value = '#374151';
  document.getElementById('colorHex').value = '#374151';
  document.getElementById('catBgColor').value = '#f3f4f6';
  document.getElementById('bgColorHex').value = '#f3f4f6';
  document.getElementById('categoryModal').classList.add('active');
}

function editCategory(id, name, color, bgColor, sortOrder) {
  document.getElementById('categoryForm').action = '{{ url("app/email-template-categories") }}/' + id;
  document.getElementById('formMethod').value = 'PUT';
  document.getElementById('modalTitle').textContent = 'Edit Category';
  document.getElementById('catName').value = name;
  document.getElementById('catColor').value = color;
  document.getElementById('colorHex').value = color;
  document.getElementById('catBgColor').value = bgColor;
  document.getElementById('bgColorHex').value = bgColor;
  document.getElementById('categoryModal').classList.add('active');
}

function closeModal() {
  document.getElementById('categoryModal').classList.remove('active');
}

document.getElementById('categoryModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>

@endsection