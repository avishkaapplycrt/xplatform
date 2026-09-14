@extends('layouts.client')

@section('title', 'Chat Bot')

@section('content')
<div class="px-4 sm:px-0">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Chat Bot</h2>
            <p class="text-sm text-gray-500 mt-0.5">Ask about your scores, segments and connected sources.</p>
        </div>
        <button type="button" id="chatReset"
                class="inline-flex items-center gap-2 border border-gray-200 hover:border-gray-300 bg-white text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            New chat
        </button>
    </div>

    @unless($configured)
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
            <span class="font-semibold">Basic mode.</span>
            Answering from built-in knowledge of your scores, segments, layers and data sources.
            Add <code class="font-mono text-xs bg-blue-100 px-1 py-0.5 rounded">ANTHROPIC_API_KEY</code>
            to your <code class="font-mono text-xs bg-blue-100 px-1 py-0.5 rounded">.env</code> file for open-ended AI answers.
        </div>
    @endunless

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col" style="height: calc(100vh - 16rem); min-height: 24rem;">

        {{-- Transcript --}}
        <div id="chatLog" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">

            <div id="chatEmpty" class="text-center py-10 {{ $history ? 'hidden' : '' }}">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">How can I help?</p>
                <p class="text-xs text-gray-400 mt-1">Try &ldquo;What does a low trust score mean?&rdquo;</p>
            </div>

            @foreach($history as $turn)
                <div class="chat-row flex {{ $turn['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] sm:max-w-[75%] px-4 py-2.5 rounded-2xl text-sm whitespace-pre-wrap break-words
                                {{ $turn['role'] === 'user'
                                    ? 'bg-indigo-600 text-white rounded-br-sm'
                                    : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">{{ $turn['content'] }}</div>
                </div>
            @endforeach

        </div>

        {{-- Quick prompts --}}
        <div class="px-3 sm:px-4 pt-3 flex-shrink-0">
            <button type="button" class="quick-prompt inline-flex items-center gap-1.5 text-xs text-gray-600 bg-gray-50
                           hover:bg-indigo-50 hover:text-indigo-700 border border-gray-200 hover:border-indigo-200
                           rounded-full px-3 py-1.5 transition">For more information, please give your website URL</button>
        </div>

        {{-- Composer --}}
        <div class="border-t border-gray-100 p-3 sm:p-4 flex-shrink-0">
            <form id="chatForm" class="flex items-end gap-2">
                <textarea id="chatInput" rows="1" maxlength="4000"
                          placeholder="Ask a question&hellip;"
                          class="flex-1 resize-none px-3.5 py-2.5 text-sm bg-gray-50 border border-gray-200
                                 rounded-lg text-gray-700 placeholder-gray-400 max-h-40
                                 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 transition"></textarea>
                <button type="submit" id="chatSend"
                        class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700
                               disabled:opacity-40 disabled:cursor-not-allowed
                               text-white p-2.5 rounded-lg transition flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-7 7m7-7l7 7"/>
                    </svg>
                </button>
            </form>
            <p class="text-[11px] text-gray-400 mt-2">Enter to send, Shift+Enter for a new line.</p>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form  = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const send  = document.getElementById('chatSend');
    const log   = document.getElementById('chatLog');
    const empty = document.getElementById('chatEmpty');
    const reset = document.getElementById('chatReset');
    const token = document.querySelector('meta[name="csrf-token"]').content;

    const SEND_URL  = @json(route('client.chatbot.send'));
    const RESET_URL = @json(route('client.chatbot.reset'));

    let pending = false;

    function scroll() {
        log.scrollTop = log.scrollHeight;
    }

    // textContent, never innerHTML — replies are model output and must not be
    // parsed as markup.
    function bubble(role, text) {
        empty.classList.add('hidden');

        const row = document.createElement('div');
        row.className = 'chat-row flex ' + (role === 'user' ? 'justify-end' : 'justify-start');

        const body = document.createElement('div');
        body.className = 'max-w-[85%] sm:max-w-[75%] px-4 py-2.5 rounded-2xl text-sm whitespace-pre-wrap break-words ' +
            (role === 'user'
                ? 'bg-indigo-600 text-white rounded-br-sm'
                : role === 'error'
                    ? 'bg-red-50 text-red-700 border border-red-200 rounded-bl-sm'
                    : 'bg-gray-100 text-gray-800 rounded-bl-sm');
        body.textContent = text;

        row.appendChild(body);
        log.appendChild(row);
        scroll();
        return body;
    }

    function setPending(state) {
        pending = state;
        send.disabled = state;
        input.disabled = state;
        if (!state) input.focus();
    }

    // Grow the box with the text, up to the max-height set in CSS.
    input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    document.querySelectorAll('.quick-prompt').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (pending) return;
            input.value = btn.textContent.trim();
            form.requestSubmit();
        });
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const message = input.value.trim();
        if (!message || pending) return;

        bubble('user', message);
        input.value = '';
        input.style.height = 'auto';
        setPending(true);

        const thinking = bubble('assistant', 'Thinking…');
        thinking.classList.add('opacity-50');

        try {
            const res = await fetch(SEND_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ message: message }),
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                thinking.classList.remove('opacity-50');
                thinking.textContent = data.reply;
            } else {
                thinking.remove();
                bubble('error', data.error || 'Something went wrong. Please try again.');
            }
        } catch (err) {
            thinking.remove();
            bubble('error', 'Could not reach the server. Check your connection and try again.');
        } finally {
            setPending(false);
            scroll();
        }
    });

    reset.addEventListener('click', async function () {
        if (pending) return;

        await fetch(RESET_URL, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
        });

        log.querySelectorAll('.chat-row').forEach(function (row) { row.remove(); });
        empty.classList.remove('hidden');
        input.focus();
    });

    scroll();
})();
</script>
@endpush
