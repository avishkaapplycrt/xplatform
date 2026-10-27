{{-- resources/views/client/email-sender.blade.php --}}
@extends('layouts.platform')
@section('title', 'Sending Account')

@push('styles')
<style>
  .es-root { display: flex; flex-direction: column; height: 100vh; overflow: hidden; background: #f9fafb; }
  .es-topbar { flex-shrink: 0; background: #fff; border-bottom: 1px solid #e5e7eb; padding: 12px 24px; }
  .es-topbar-title { font-size: 16px; font-weight: 600; color: #111827; }
  .es-topbar-sub { font-size: 11px; color: #6b7280; margin-top: 2px; }
  .es-scroll { flex: 1; min-height: 0; overflow-y: auto; padding: 20px; }
  .es-wrap { max-width: 760px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px; }

  .es-flash { padding: 10px 14px; border-radius: 8px; font-size: 13px; border: 1px solid; }
  .es-flash.ok { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
  .es-flash.err { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

  .es-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
  .es-card-hd { padding: 14px 20px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
  .es-card-hd h2 { margin: 0; font-size: 14px; font-weight: 600; color: #111827; }
  .es-card-bd { padding: 18px 20px; }

  .es-status { display: flex; align-items: center; gap: 14px; }
  .es-ic { flex: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
  .es-ic.on { background: #dcfce7; color: #16a34a; }
  .es-ic.off { background: #fef3c7; color: #b45309; }
  .es-status b { display: block; font-size: 14px; color: #111827; }
  .es-status span { font-size: 12.5px; color: #6b7280; }
  .es-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 999px; }
  .es-pill.on { background: #dcfce7; color: #166534; }
  .es-pill.off { background: #fef3c7; color: #92400e; }

  .es-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 16px; }
  .es-grid .full { grid-column: 1 / -1; }
  @media (max-width: 640px) { .es-grid { grid-template-columns: 1fr; } }
  .es-lbl { display: block; font-size: 11.5px; font-weight: 600; color: #374151; margin-bottom: 5px; }
  .es-in { width: 100%; padding: 8px 11px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-family: inherit; background: #fff; color: #111827; }
  .es-in:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
  .es-in[readonly] { background: #f9fafb; color: #6b7280; }
  .es-help { font-size: 11.5px; color: #6b7280; margin-top: 4px; }
  .es-err { font-size: 11.5px; color: #b91c1c; margin-top: 4px; }

  .es-btns { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
  .es-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid transparent; font-family: inherit; }
  .es-btn.primary { background: #0284c7; color: #fff; }
  .es-btn.primary:hover { background: #0369a1; }
  .es-btn.ghost { background: #fff; color: #374151; border-color: #e5e7eb; }
  .es-btn.ghost:hover { background: #f9fafb; }
  .es-btn.danger { background: #fff; color: #b91c1c; border-color: #fecaca; }
  .es-btn.danger:hover { background: #fef2f2; }
  .es-btn.sm { padding: 5px 11px; font-size: 12px; }

  .es-steps { margin: 0; padding-left: 18px; font-size: 13px; color: #374151; display: flex; flex-direction: column; gap: 6px; }
  .es-steps a { color: #0284c7; }
  code.es-c { font-size: 12px; background: #f3f4f6; padding: 1px 5px; border-radius: 4px; }
</style>
@endpush

@section('content')
@php
  $connected = (bool) $settings;
  $isGmail = str_ends_with(strtolower((string) $client->email), '@gmail.com');
@endphp
<div class="es-root">
  <div class="es-topbar">
    <div class="es-topbar-title">Sending Account</div>
    <div class="es-topbar-sub">Emails you send from XPlatform go out from your own address</div>
  </div>

  <div class="es-scroll">
    <div class="es-wrap">
      @if (session('success'))<div class="es-flash ok">{{ session('success') }}</div>@endif
      @if (session('error'))<div class="es-flash err">{{ session('error') }}</div>@endif

      {{-- Current status --}}
      <div class="es-card">
        <div class="es-card-bd es-status">
          <div class="es-ic {{ $connected ? 'on' : 'off' }}">
            @if ($connected)
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            @else
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            @endif
          </div>
          <div style="flex:1;min-width:0">
            <b>{{ $client->email }}</b>
            @if ($connected)
              <span>Connected via {{ $settings->host }} · checked {{ $settings->verified_at?->diffForHumans() }}</span>
            @else
              <span>Not connected. Emails currently go out from the shared XPlatform address ({{ config('mail.from.address') }}).</span>
            @endif
          </div>
          <span class="es-pill {{ $connected ? 'on' : 'off' }}">{{ $connected ? 'Connected' : 'Not connected' }}</span>
        </div>
        @if ($connected)
          <div class="es-card-bd es-btns" style="border-top:1px solid #f3f4f6;padding-top:12px;padding-bottom:12px">
            <form method="POST" action="{{ route('client.email.sender.test') }}">@csrf
              <button type="submit" class="es-btn ghost sm">Send a test email to {{ $client->email }}</button>
            </form>
            <form method="POST" action="{{ route('client.email.sender.destroy') }}" onsubmit="return confirm('Remove this sending account? Emails will go out from the shared XPlatform address.')">@csrf @method('DELETE')
              <button type="submit" class="es-btn danger sm">Remove</button>
            </form>
          </div>
        @endif
      </div>

      {{-- Connection form --}}
      <div class="es-card">
        <div class="es-card-hd">
          <h2>{{ $connected ? 'Update mail login' : 'Connect your mail account' }}</h2>
          <button type="button" class="es-btn ghost sm" onclick="esGmail()">Use Gmail settings</button>
        </div>
        <form method="POST" action="{{ route('client.email.sender.save') }}" class="es-card-bd" autocomplete="off">
          @csrf
          <div class="es-grid">
            <div class="full">
              <label class="es-lbl">Send from</label>
              <input class="es-in" value="{{ $client->email }}" readonly>
              <div class="es-help">Your account email. Every email you send uses this address.</div>
            </div>
            <div>
              <label class="es-lbl" for="esHost">SMTP server</label>
              <input class="es-in" id="esHost" name="host" value="{{ old('host', $settings->host ?? ($isGmail ? 'smtp.gmail.com' : '')) }}" placeholder="smtp.gmail.com" required>
              @error('host')<div class="es-err">{{ $message }}</div>@enderror
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <div>
                <label class="es-lbl" for="esPort">Port</label>
                <input class="es-in" id="esPort" name="port" type="number" value="{{ old('port', $settings->port ?? 587) }}" required>
                @error('port')<div class="es-err">{{ $message }}</div>@enderror
              </div>
              <div>
                <label class="es-lbl" for="esEnc">Security</label>
                @php $enc = old('encryption', $settings->encryption ?? 'tls'); @endphp
                <select class="es-in" id="esEnc" name="encryption">
                  <option value="tls" @selected($enc === 'tls')>TLS</option>
                  <option value="ssl" @selected($enc === 'ssl')>SSL</option>
                  <option value="none" @selected($enc === 'none')>None</option>
                </select>
              </div>
            </div>
            <div>
              <label class="es-lbl" for="esUser">Username</label>
              <input class="es-in" id="esUser" name="username" value="{{ old('username', $settings->username ?? $client->email) }}" required>
              @error('username')<div class="es-err">{{ $message }}</div>@enderror
            </div>
            <div>
              <label class="es-lbl" for="esPass">{{ $isGmail ? 'App Password' : 'Password' }}</label>
              <input class="es-in" id="esPass" name="password" type="password" autocomplete="new-password"
                     placeholder="{{ $connected ? 'Leave blank to keep the saved password' : ($isGmail ? '16-character App Password' : '') }}" {{ $connected ? '' : 'required' }}>
              @error('password')<div class="es-err">{{ $message }}</div>@enderror
            </div>
            <div class="full">
              <label class="es-lbl" for="esName">Sender name</label>
              <input class="es-in" id="esName" name="from_name" value="{{ old('from_name', $settings->from_name ?? $client->company_name) }}" placeholder="{{ $client->company_name }}">
              <div class="es-help">Shown next to your address in the recipient's inbox.</div>
            </div>
          </div>
          <div class="es-btns" style="margin-top:18px">
            <button type="submit" class="es-btn primary">{{ $connected ? 'Check and save' : 'Check and connect' }}</button>
            <span class="es-help" style="margin:0">We sign in once to check the login before saving it. The password is stored encrypted.</span>
          </div>
        </form>
      </div>

      @if ($isGmail)
      <div class="es-card">
        <div class="es-card-hd"><h2>Getting a Gmail App Password</h2></div>
        <div class="es-card-bd">
          <ol class="es-steps">
            <li>Sign in to {{ $client->email }} and turn on <b>2-Step Verification</b> at <a href="https://myaccount.google.com/security" target="_blank" rel="noopener">myaccount.google.com/security</a>.</li>
            <li>Open <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">myaccount.google.com/apppasswords</a>, name it "XPlatform" and create it.</li>
            <li>Copy the 16-character password into <b>App Password</b> above. Spaces are fine.</li>
          </ol>
          <p class="es-help" style="margin-top:10px">Gmail sends up to about 500 emails a day from a personal account. For larger campaigns, use a provider such as Brevo SMTP.</p>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<script>
function esGmail() {
  document.getElementById('esHost').value = 'smtp.gmail.com';
  document.getElementById('esPort').value = 587;
  document.getElementById('esEnc').value = 'tls';
  document.getElementById('esUser').value = @json($client->email);
  document.getElementById('esPass').focus();
}
</script>
@endsection
