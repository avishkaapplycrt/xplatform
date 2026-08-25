<style>
  .platform-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    padding: 28px 24px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    position: relative;
    overflow: hidden;
  }
  .platform-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.12);
    transform: translateY(-3px);
  }
  .platform-card.active {
    border: 2px solid #3b82f6;
    background: #eff6ff;
  }
  .platform-card .icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    transition: background .2s ease;
  }
  .platform-card:hover .icon-wrap {
    background: #eff6ff;
  }
  .platform-card .platform-name {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 6px;
  }
  .platform-card .platform-desc {
    font-size: 12px;
    color: #9ca3af;
    line-height: 1.5;
  }
  .platform-card .connect-btn {
    margin-top: 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: all .15s ease;
  }
  .platform-card:hover .connect-btn {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
  }
  .platform-card .status-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: #f3f4f6;
    color: #9ca3af;
  }
  .platform-card.connected .status-badge {
    background: #dcfce7;
    color: #16a34a;
  }
  .platform-card.connected {
    border-color: #22c55e;
  }
  .platform-card.connected .connect-btn {
    background: #22c55e;
    color: #fff;
    border-color: #22c55e;
  }
  .platform-card.connected:hover .connect-btn {
    background: #16a34a;
    border-color: #16a34a;
  }
</style>

{{-- Page Title Section --}}
<div class="mb-6">
  <h2 class="text-[18px] font-bold text-gray-900">Choose Your Website Platform</h2>
  <p class="text-[12px] text-gray-500 mt-1">Connect your website to start tracking events and analytics. Select your platform below to begin the integration.</p>
</div>

{{-- Platform Cards Grid --}}
<div class="grid grid-cols-5 gap-4">

  {{-- WordPress --}}
  <a href="{{ route('client.website-connections.wordpress') }}" class="platform-card" style="text-decoration:none; display:block;">
    <span class="status-badge">Not Connected</span>
    <div class="icon-wrap">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
        <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2z" fill="#21759b"/>
        <path d="M3.009 12c0 3.559 2.068 6.634 5.067 8.092L4.258 8.014A9.958 9.958 0 003.009 12zm18.013.335c0-2.908-1.755-5.462-4.41-6.878-.264-.15-.528-.3-.793-.45-.264-.15-.528-.3-.793-.45 1.584 2.558 2.377 5.116 2.377 7.675 0 1.889-.528 3.778-1.584 5.667l-.264.45c-.264.45-.528.9-.793 1.35 2.377-1.35 3.961-3.778 4.225-6.364h.028zm-5.934 7.227c1.056-.15 2.112-.6 2.905-1.2.793-.6 1.32-1.35 1.848-2.25.528-.9.793-1.95.793-3.008 0-1.05-.264-2.1-.793-3.008-.528-.9-1.056-1.65-1.848-2.25-.793-.6-1.849-1.05-2.905-1.2-1.056-.15-2.112-.15-3.169 0l3.697 10.916h.472zm-2.377-3.008L11.76 8.014c-.264 0-.528-.15-.793-.15-.264 0-.528.15-.793.15L6.76 19.562c.528.15 1.056.15 1.584.15.528 0 1.056 0 1.584-.15.528-.15 1.056-.3 1.584-.6.528-.3.793-.6 1.056-1.05.264-.45.528-.9.528-1.35.264-.45.264-.9.264-1.35 0-.45-.264-.9-.264-1.35-.264-.45-.528-.75-.793-1.05z" fill="#fff"/>
      </svg>
    </div>
    <p class="platform-name">WordPress</p>
    <p class="platform-desc">Connect via plugin or embed code for full event tracking on your WordPress site.</p>
    <span class="connect-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
      </svg>
      Connect
    </span>
  </a>

  {{-- Wix --}}
  <a href="{{ route('client.website-connections.wix') }}" class="platform-card" style="text-decoration:none; display:block;">
    <span class="status-badge">Not Connected</span>
    <div class="icon-wrap">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
        <rect x="2" y="4" width="20" height="16" rx="3" fill="#0c0c0c"/>
        <text x="12" y="15" text-anchor="middle" fill="#fff" font-size="10" font-weight="bold" font-family="Arial">Wix</text>
      </svg>
    </div>
    <p class="platform-name">Wix</p>
    <p class="platform-desc">Integrate with Wix using our custom app or by adding a tracking snippet to your site.</p>
    <span class="connect-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
      </svg>
      Connect
    </span>
  </a>

  {{-- Shopify --}}
  <a href="{{ route('client.website-connections.shopify') }}" class="platform-card" style="text-decoration:none; display:block;">
    <span class="status-badge">Not Connected</span>
    <div class="icon-wrap">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
        <path d="M15.337 3.415c-.066 0-.132.013-.198.026-.066.013-.132.04-.185.066l-.132.092c-.053.04-.106.092-.146.146l-.092.132-.092.132-.066.185-.026.198v.264l.026.198.066.185.092.132.092.132.146.146.132.092.185.066.198.026h.264l.198-.026.185-.066.132-.092.146-.146.092-.132.092-.132.066-.185.026-.198v-.264l-.026-.198-.066-.185-.092-.132-.092-.132-.146-.146-.132-.092-.185-.066-.198-.026h-.264z" fill="#95bf47"/>
        <path d="M12 2L2 6l2 12 8 4 8-4 2-12-10-4z" fill="#95bf47" opacity="0.9"/>
        <path d="M12 5L5 7.5l1.5 9L12 19l5.5-2.5 1.5-9L12 5z" fill="#fff"/>
        <path d="M12 8l-3 1 .5 6L12 17l2.5-2 .5-6L12 8z" fill="#95bf47"/>
      </svg>
    </div>
    <p class="platform-name">Shopify</p>
    <p class="platform-desc">Install our Shopify app from the marketplace for seamless e-commerce tracking.</p>
    <span class="connect-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
      </svg>
      Connect
    </span>
  </a>

  {{-- Webflow --}}
  <a href="{{ route('client.website-connections.webflow') }}" class="platform-card" style="text-decoration:none; display:block;">
    <span class="status-badge">Not Connected</span>
    <div class="icon-wrap">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
        <rect x="3" y="3" width="18" height="18" rx="4" fill="#4353ff"/>
        <path d="M8 8h3v8H8V8zm5 0h3v8h-3V8z" fill="#fff" opacity="0.3"/>
        <circle cx="12" cy="12" r="3" fill="#fff"/>
      </svg>
    </div>
    <p class="platform-name">Webflow</p>
    <p class="platform-desc">Add our tracking script to your Webflow custom code section for full analytics.</p>
    <span class="connect-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
      </svg>
      Connect
    </span>
  </a>

  {{-- Squarespace --}}
  <a href="{{ route('client.website-connections.squarespace') }}" class="platform-card" style="text-decoration:none; display:block;">
    <span class="status-badge">Not Connected</span>
    <div class="icon-wrap">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
        <rect x="3" y="3" width="18" height="18" rx="3" fill="#000"/>
        <rect x="7" y="7" width="10" height="10" rx="1" fill="#fff"/>
        <rect x="9" y="9" width="6" height="6" rx="0.5" fill="#000"/>
      </svg>
    </div>
    <p class="platform-name">Squarespace</p>
    <p class="platform-desc">Use our Squarespace extension or inject the tracking code via code injection.</p>
    <span class="connect-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
      </svg>
      Connect
    </span>
  </a>

</div>

{{-- Connection Steps / Info Section --}}
<div class="mt-6 panel-card" style="border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:20px 24px;">
  <div class="section-header" style="margin-bottom:16px">
    <div class="section-dot" style="background:#3b82f6"></div>
    <span class="section-title" style="color:#3b82f6">How Website Connection Works</span>
    <div class="section-line" style="background:#bfdbfe"></div>
  </div>
  <div class="grid grid-cols-3 gap-4">
    <div class="flex items-start gap-3">
      <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-500 font-bold text-sm">1</div>
      <div>
        <p class="text-[13px] font-semibold text-gray-900">Select Platform</p>
        <p class="text-[11px] text-gray-500 mt-1">Choose your website builder from the cards above.</p>
      </div>
    </div>
    <div class="flex items-start gap-3">
      <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-500 font-bold text-sm">2</div>
      <div>
        <p class="text-[13px] font-semibold text-gray-900">Authenticate</p>
        <p class="text-[11px] text-gray-500 mt-1">Authorize our app to access your site data securely.</p>
      </div>
    </div>
    <div class="flex items-start gap-3">
      <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-500 font-bold text-sm">3</div>
      <div>
        <p class="text-[13px] font-semibold text-gray-900">Start Tracking</p>
        <p class="text-[11px] text-gray-500 mt-1">Events begin flowing to your dashboard automatically.</p>
      </div>
    </div>
  </div>
</div>

{{-- Connected Sites List (if any) --}}
<div class="mt-6 panel-card" style="border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:20px 24px;">
  <div class="section-header" style="margin-bottom:16px">
    <div class="section-dot" style="background:#10b981"></div>
    <span class="section-title" style="color:#10b981">Connected Websites</span>
    <div class="section-line" style="background:#a7f3d0"></div>
  </div>
  <div id="connected-sites-list" style="display:flex; flex-direction:column; gap:8px;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#f9fafb; border-radius:8px;">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
          </svg>
        </div>
        <div>
          <p class="text-[13px] font-semibold text-gray-900">No websites connected yet</p>
          <p class="text-[11px] text-gray-500">Select a platform above to connect your first site.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let selectedPlatform = null;

function selectPlatform(card, platform) {
  document.querySelectorAll('.platform-card').forEach(c => c.classList.remove('active'));
  card.classList.add('active');
  selectedPlatform = platform;
  console.log('Selected platform:', platform);
}
</script>
