<?php

namespace App\Http\Controllers;

use App\Services\Llm\OpenAiException;
use App\Services\Llm\PremiumMiraService;
use App\Services\WebsiteAnalyzerException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Send/reset API for the Premium Mira page (mira-premium.blade.php).
 *
 * Flow: the first message must be the visitor's website URL — that loads
 * the site into the session. Every later message is a question answered
 * about that site (AEO-focused, via OpenAI). Pasting another URL switches
 * to that site and starts a fresh conversation.
 *
 * Public and unauthenticated like the free chat, so the routes are
 * rate-limited (see routes/web.php). Session keys are separate from
 * PublicChatController's so the two chats never mix.
 */
class PremiumMiraController extends Controller
{
    private const SITE_KEY    = 'premium_mira_site';
    private const HISTORY_KEY = 'premium_mira_history';

    public function send(Request $request, PremiumMiraService $mira): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);
        $message = $validated['message'];

        $url = $mira->extractUrl($message);

        if ($url !== null) {
            try {
                $site = $mira->loadWebsite($url);
            } catch (WebsiteAnalyzerException $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            $reply = $mira->welcomeFor($site);

            $request->session()->put(self::SITE_KEY, $site);
            $request->session()->put(self::HISTORY_KEY, [
                ['role' => 'user', 'content' => $message],
                ['role' => 'assistant', 'content' => $reply],
            ]);

            return response()->json([
                'reply'       => $reply,
                'site'        => $this->siteSummary($site),
                'suggestions' => $mira->suggestionsFor($site),
            ]);
        }

        $site = $request->session()->get(self::SITE_KEY);

        if ($site === null) {
            return response()->json([
                'reply' => "Paste your website URL first (for example https://yourwebsite.com) and I'll read it. Then ask me anything about it.",
                'site'  => null,
            ]);
        }

        if (!$mira->isConfigured()) {
            return response()->json([
                'error' => 'Premium Mira is not available right now. Please try again later.',
            ], 503);
        }

        $history = $request->session()->get(self::HISTORY_KEY, []);

        try {
            $reply = $mira->reply($message, $site, $history);
        } catch (OpenAiException $e) {
            Log::error('Premium Mira request failed', ['message' => $e->getMessage()]);

            return response()->json([
                'error' => 'The assistant is unavailable right now. Please try again in a moment.',
            ], 502);
        }

        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        $request->session()->put(self::HISTORY_KEY, $mira->trim($history));

        return response()->json(['reply' => $reply, 'site' => $this->siteSummary($site)]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget([self::SITE_KEY, self::HISTORY_KEY]);

        return response()->json(['status' => 'cleared']);
    }

    /** What the page shows in its "Your website" box. */
    private function siteSummary(array $site): array
    {
        return ['url' => $site['url'], 'title' => $site['title']];
    }
}
