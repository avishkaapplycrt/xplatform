<?php

namespace App\Http\Controllers;

use App\Models\ChatBot;
use App\Models\ChatBotUser;
use App\Models\Industry;
use App\Services\Llm\AnthropicException;
use App\Services\Llm\AnthropicRefusedException;
use App\Services\Llm\MarketingChatBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Backend for the public "Chat with AI" widget on the marketing site.
 * Unauthenticated by design — visitors haven't signed up yet — so the route
 * is rate-limited (see routes/web.php) rather than gated behind a login.
 *
 * History lives in the session under its own key, kept separate from the
 * signed-in client chatbot's history so the two personas never mix if the
 * same browser session later logs in.
 *
 * A visitor can still be signed in as a client while browsing the public
 * site (e.g. a second tab). When that's the case, every exchange is also
 * persisted to chat_bots — this is the only place that decision is made;
 * MarketingChatBotService only uses the login state to word its reply.
 *
 * The chat UI itself is embedded directly on the homepage (welcome.blade.php,
 * #ask-mira section) rather than served from a route here — this controller
 * is just the send/reset API the JS on that page calls.
 */
class PublicChatController extends Controller
{
    private const SESSION_KEY = 'public_chat_history';

    public function send(Request $request, MarketingChatBotService $bot): JsonResponse
    {
        $validated = $request->validate([
            'message'     => 'required|string|max:4000',
            'industry_id' => 'nullable|integer|exists:industries,id',
        ]);

        $history  = $request->session()->get(self::SESSION_KEY, []);
        $client   = auth('client')->user();
        $industry = isset($validated['industry_id'])
            ? Industry::find($validated['industry_id'])?->name
            : null;

        try {
            $result = $bot->reply($validated['message'], $history, $client, $industry);
        } catch (AnthropicRefusedException $e) {
            Log::info('Public chat request declined', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'I declined to answer that one. Try rephrasing it.',
            ], 422);
        } catch (AnthropicException $e) {
            Log::error('Public chat request failed', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'The assistant is unavailable right now. Please try again in a moment.',
            ], 502);
        }

        $reply  = $result['reply'];
        $report = $result['report'];

        if ($client !== null) {
            ChatBot::create([
                'question' => $validated['message'],
                'answer'   => $reply,
                'user_id'  => $client->id,
            ]);
        }

        $history[] = ['role' => 'user', 'content' => $validated['message']];
        $history[] = ['role' => 'assistant', 'content' => $reply];

        $request->session()->put(self::SESSION_KEY, $bot->trim($history));

        return response()->json(['reply' => $reply, 'report' => $report]);
    }

    public function analyzeWithLead(Request $request, MarketingChatBotService $bot): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'url'         => 'required|url|max:2048',
            'industry_id' => 'nullable|integer|exists:industries,id',
        ]);

        ChatBotUser::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'website_url' => $validated['url'],
        ]);

        $history  = $request->session()->get(self::SESSION_KEY, []);
        $client   = auth('client')->user();
        $industry = isset($validated['industry_id'])
            ? Industry::find($validated['industry_id'])?->name
            : null;

        try {
            $result = $bot->reply($validated['url'], $history, $client, $industry);
        } catch (AnthropicRefusedException $e) {
            Log::info('Public chat request declined', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'I declined to answer that one. Try rephrasing it.',
            ], 422);
        } catch (AnthropicException $e) {
            Log::error('Public chat request failed', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'The assistant is unavailable right now. Please try again in a moment.',
            ], 502);
        }

        $reply  = $result['reply'];
        $report = $result['report'];

        if ($client !== null) {
            ChatBot::create([
                'question' => $validated['url'],
                'answer'   => $reply,
                'user_id'  => $client->id,
            ]);
        }

        $history[] = ['role' => 'user', 'content' => $validated['url']];
        $history[] = ['role' => 'assistant', 'content' => $reply];

        $request->session()->put(self::SESSION_KEY, $bot->trim($history));

        return response()->json(['reply' => $reply, 'report' => $report]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['status' => 'cleared']);
    }
}
