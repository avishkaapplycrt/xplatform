{{-- Floating "Ask Mira" widget — available from every /app page. See
     routes/web.php ("client.ask-mira") and App\Services\Llm\PlatformChatService.
     Separate from the per-agent "Ask Mira" chat panels already embedded in
     Business Helpers / Mock Master Helper — this is an always-available,
     additional way to reach the assistant from anywhere.

     Styled with its own scoped CSS below rather than Tailwind utility
     classes — the compiled CSS bundle is purged to only the classes seen in
     blade files at the last `npm run build`, so a brand-new partial's
     utility classes (bottom-5, right-5, z-[9998], etc.) can silently be
     missing until the next build. Plain CSS here always applies. --}}
<div id="amWidget">
    <div id="amPanel" class="am-panel-hidden">
        <div class="am-hd">
            <div class="am-hd-left">
                <span class="am-dot"></span>
                <span class="am-title">Ask Mira</span>
            </div>
            <button type="button" onclick="amToggle(false)" class="am-close" title="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="amChat" class="am-chat">
            <div class="am-msg am-bot">Hi, I'm Mira — ask me anything about your accounts, campaigns, or customers, from any page.</div>
        </div>
        <form id="amForm" class="am-form" onsubmit="return amSubmit(event)">
            <input id="amInput" type="text" placeholder="Type your message here" autocomplete="off" class="am-input" />
            <button type="submit" class="am-send">
                <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2v7z"/></svg>
            </button>
        </form>
    </div>

    <button type="button" onclick="amToggle()" id="amBubble" class="am-bubble" title="Ask Mira">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
    </button>
</div>

<style>
#amWidget{position:fixed;bottom:20px;right:20px;z-index:9998;display:flex;flex-direction:column;align-items:flex-end;font-family:inherit}

#amWidget .am-bubble{width:56px;height:56px;border-radius:50%;background:#4f46e5;color:#fff;border:none;box-shadow:0 10px 30px rgba(79,70,229,.4);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .15s,background .15s}
#amWidget .am-bubble:hover{background:#4338ca;transform:scale(1.06)}

#amWidget .am-panel-hidden,#amWidget .am-panel-shown{width:340px;height:440px;max-width:calc(100vw - 40px);background:#fff;border-radius:16px;box-shadow:0 20px 50px rgba(0,0,0,.25);border:1px solid #e5e7eb;display:flex;flex-direction:column;overflow:hidden;margin-bottom:12px}
#amWidget .am-panel-hidden{display:none}

#amWidget .am-hd{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#4f46e5;color:#fff;flex-shrink:0}
#amWidget .am-hd-left{display:flex;align-items:center;gap:8px}
#amWidget .am-dot{width:8px;height:8px;border-radius:50%;background:#34d399}
#amWidget .am-title{font-weight:600;font-size:13.5px}
#amWidget .am-close{background:none;border:none;color:rgba(255,255,255,.85);cursor:pointer;padding:0;display:flex}
#amWidget .am-close:hover{color:#fff}

#amWidget .am-chat{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:#f9fafb}
#amWidget .am-msg{max-width:85%;padding:8px 12px;border-radius:12px;font-size:12.5px;line-height:1.5;white-space:pre-wrap}
#amWidget .am-bot{align-self:flex-start;background:#fff;border:1px solid #e5e7eb;color:#1f2937;border-bottom-left-radius:3px}
#amWidget .am-user{align-self:flex-end;background:#4f46e5;color:#fff;border-bottom-right-radius:3px}

#amWidget .am-form{display:flex;align-items:center;gap:8px;padding:10px 12px;border-top:1px solid #f3f4f6;background:#fff;flex-shrink:0}
#amWidget .am-input{flex:1;font-size:13px;padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;outline:none}
#amWidget .am-input:focus{border-color:#818cf8}
#amWidget .am-send{width:32px;height:32px;border-radius:8px;background:#4f46e5;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}
#amWidget .am-send:hover{background:#4338ca}
</style>

<script>
function amToggle(force) {
    var panel = document.getElementById('amPanel');
    var show = typeof force === 'boolean' ? force : panel.classList.contains('am-panel-hidden');
    panel.className = show ? 'am-panel-shown' : 'am-panel-hidden';
    if (show) {
        var input = document.getElementById('amInput');
        if (input) setTimeout(function () { input.focus(); }, 50);
    }
}

function amSubmit(e) {
    e.preventDefault();
    var input = document.getElementById('amInput');
    var text = (input.value || '').trim();
    if (!text) return false;
    input.value = '';

    var chat = document.getElementById('amChat');

    var userBubble = document.createElement('div');
    userBubble.className = 'am-msg am-user';
    userBubble.textContent = text;
    chat.appendChild(userBubble);

    var botBubble = document.createElement('div');
    botBubble.className = 'am-msg am-bot';
    botBubble.innerHTML = '<span style="color:#9ca3af">Thinking…</span>';
    chat.appendChild(botBubble);
    chat.scrollTop = chat.scrollHeight;

    fetch('{{ route('client.ask-mira') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ question: text })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        botBubble.textContent = data.answer || "I couldn't get an answer just now.";
        chat.scrollTop = chat.scrollHeight;
    })
    .catch(function () {
        botBubble.textContent = "I couldn't reach the AI just now — try again in a moment.";
        chat.scrollTop = chat.scrollHeight;
    });

    return false;
}
</script>
