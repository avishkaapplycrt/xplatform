<?php

namespace App\Http\Controllers;

use App\Services\Llm\AnthropicException;
use App\Services\Llm\AnthropicRefusedException;
use App\Services\Llm\ChatBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The Chat Bot page.
 *
 * Conversations live in the session rather than the database — they are
 * scratch context for one sitting, and keeping them out of MySQL means this
 * feature ships without a migration. Signing out clears them.
 */
class ChatBotController extends Controller
{
    private const SESSION_KEY = 'chatbot_history';

    public function index(Request $request, ChatBotService $bot)
    {
        return view('client.chatbot', [
            'history'    => $request->session()->get(self::SESSION_KEY, []),
            'configured' => $bot->isConfigured(),
        ]);
    }

    /**
     * Answer one message. Returns JSON so the page can append the reply
     * without a reload.
     */
    public function send(Request $request, ChatBotService $bot): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $history = $request->session()->get(self::SESSION_KEY, []);

        try {
            $reply = $bot->reply($validated['message'], $history, auth('client')->user());
        } catch (AnthropicRefusedException $e) {
            Log::info('Chat bot request declined', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'The assistant declined to answer that one. Try rephrasing it.',
            ], 422);
        } catch (AnthropicException $e) {
            Log::error('Chat bot request failed', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'The assistant is unavailable right now. Please try again in a moment.',
            ], 502);
        }

        /* Only persist once the call succeeded, so a failed turn doesn't leave
           a dangling user message that unbalances the next request. */
        $history[] = ['role' => 'user', 'content' => $validated['message']];
        $history[] = ['role' => 'assistant', 'content' => $reply];

        $request->session()->put(self::SESSION_KEY, $bot->trim($history));

        return response()->json(['reply' => $reply]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['status' => 'cleared']);
    }
}
