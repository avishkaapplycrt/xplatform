<?php

namespace App\Services;

use App\Models\BehavioralProfile;
use App\Models\BusinessHealthSnapshot;
use App\Models\BusinessInsight;
use App\Models\Client;
use App\Models\EmailLog;
use App\Models\WebsiteEvent;
use App\Services\Llm\AnthropicClient;
use App\Services\Llm\AnthropicRefusedException;
use Illuminate\Support\Facades\DB;

/**
 * Turns a client's own behavioral scores, website traffic, and email
 * performance into an AI-generated health score, narrative, and prioritized
 * recommendations — the thing GrowthReportController used to fake with a
 * hardcoded `return 78`.
 *
 * The snapshot fed to the model is built entirely from client-scoped queries.
 * That scoping is load-bearing, not incidental: several other controllers in
 * this codebase (DecisionCentreController, EmailLog::deliveryStats()) query
 * BehavioralProfile/EmailLog without a client_id filter, which would leak one
 * client's user data into another client's prompt if copied here.
 */
class BusinessIntelligenceService
{
    private const LOOKBACK_DAYS = 30;

    public function __construct(private AnthropicClient $llm) {}

    /**
     * True once the client has at least one real signal to summarize.
     * Calling the model on an empty snapshot produces a confident-sounding
     * hallucination instead of an honest "nothing to show yet".
     */
    public function hasEnoughData(Client $client): bool
    {
        return $this->snapshotFor($client)['has_data'];
    }

    /**
     * Generate and persist a fresh health snapshot + insight list for a
     * client. Throws \App\Services\Llm\AnthropicException on a hard API
     * failure (retries already exhausted by the client) — callers decide
     * whether that should fail a request or just skip this client in a batch.
     */
    public function generate(Client $client): BusinessHealthSnapshot
    {
        $snapshot = $this->snapshotFor($client);

        if (!$snapshot['has_data']) {
            throw new \RuntimeException("Client {$client->id} has no data to summarize yet.");
        }

        $result = null;

        try {
            $result = $this->llm->structuredCompletion(
                $this->systemPrompt(),
                json_encode($snapshot, JSON_PRETTY_PRINT),
                $this->responseSchema(),
                ['effort' => 'medium', 'max_tokens' => 8000]
            );
        } catch (AnthropicRefusedException $e) {
            // Nothing unsafe about business metrics — a refusal here almost
            // certainly means a false positive on the safety classifier.
            // Don't retry inside the same call; let the next scheduled run try again.
            report($e);
            throw $e;
        }

        return DB::transaction(function () use ($client, $result) {
            $health = BusinessHealthSnapshot::updateOrCreate(
                ['client_id' => $client->id],
                [
                    'health_score'  => max(0, min(100, (int) $result['health_score'])),
                    'summary'       => $result['summary'],
                    'strengths'     => $result['strengths'],
                    'weaknesses'    => $result['weaknesses'],
                    'opportunities' => $result['opportunities'],
                    'generated_at'  => now(),
                ]
            );

            // Replace rather than append — each generation reflects the client's
            // current state, not a running log. History lives in generated_at.
            BusinessInsight::where('client_id', $client->id)->delete();

            foreach ($result['insights'] as $insight) {
                BusinessInsight::create([
                    'client_id'        => $client->id,
                    'category'         => $insight['category'],
                    'title'            => $insight['title'],
                    'description'      => $insight['description'],
                    'recommendation'   => $insight['recommendation'],
                    'confidence_score' => max(0, min(1, (float) $insight['confidence_score'])),
                    'impact_level'     => $insight['impact_level'],
                    'status'           => 'new',
                    'generated_at'     => now(),
                ]);
            }

            return $health;
        });
    }

    /**
     * The client-scoped data snapshot sent to the model. Every query here
     * filters by $client->id — see the class docblock for why that matters.
     */
    public function snapshotFor(Client $client): array
    {
        $since = now()->subDays(self::LOOKBACK_DAYS);

        $behavioral = $this->behavioralSummary($client);
        $website    = $this->websiteSummary($client, $since);
        $email      = $this->emailSummary($client, $since);

        return [
            'has_data'        => $behavioral['profile_count'] > 0 || $website['total_visitors'] > 0 || $email['sent'] > 0,
            'company_name'    => $client->company_name,
            'industry'        => $client->industry?->name,
            'window_days'     => self::LOOKBACK_DAYS,
            'behavioral'      => $behavioral,
            'website_traffic' => $website,
            'email_engagement'=> $email,
        ];
    }

    private function behavioralSummary(Client $client): array
    {
        $profiles = BehavioralProfile::where('client_id', $client->id)->get();

        if ($profiles->isEmpty()) {
            return ['profile_count' => 0];
        }

        return [
            'profile_count'         => $profiles->count(),
            'segments'              => $profiles->countBy('segment'),
            'avg_overall_score'     => round($profiles->avg('overall_score'), 1),
            'avg_churn_score'       => round($profiles->avg('churn_score'), 1),
            'avg_intent_score'      => round($profiles->avg('intent_score'), 1),
            'avg_loyalty_score'     => round($profiles->avg('loyalty_score'), 1),
            'at_risk_count'         => $profiles->where('churn_score', '>=', 65)->count(),
            'high_intent_count'     => $profiles->where('intent_score', '>=', 70)->count(),
        ];
    }

    private function websiteSummary(Client $client, \Carbon\Carbon $since): array
    {
        $events = WebsiteEvent::where('client_id', $client->id)
            ->where('created_at', '>=', $since);

        $totalVisitors = (clone $events)->distinct('ip_address')->count('ip_address');

        if ($totalVisitors === 0) {
            return ['total_visitors' => 0];
        }

        $topPages = (clone $events)
            ->where('event_type', 'pageview')
            ->select('page_url', DB::raw('COUNT(*) as views'))
            ->groupBy('page_url')
            ->orderByDesc('views')
            ->limit(5)
            ->pluck('views', 'page_url');

        return [
            'total_visitors'  => $totalVisitors,
            'total_pageviews' => (clone $events)->where('event_type', 'pageview')->count(),
            'top_pages'       => $topPages,
        ];
    }

    private function emailSummary(Client $client, \Carbon\Carbon $since): array
    {
        $logs = EmailLog::forClient($client->id)->where('created_at', '>=', $since);
        $sent = (clone $logs)->count();

        if ($sent === 0) {
            return ['sent' => 0];
        }

        $delivered = (clone $logs)->whereNotNull('delivered_at')->count();
        $opened    = (clone $logs)->whereNotNull('opened_at')->count();
        $clicked   = (clone $logs)->whereNotNull('clicked_at')->count();

        return [
            'sent'          => $sent,
            'delivered'     => $delivered,
            'open_rate_pct' => $delivered > 0 ? round($opened / $delivered * 100, 1) : 0.0,
            'click_rate_pct'=> $opened > 0 ? round($clicked / $opened * 100, 1) : 0.0,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
        You are a business analyst for an AI analytics platform. You are given
        one client's own behavioral scoring, website traffic, and email
        engagement data for the last {$this->windowLabel()}, and you produce an
        honest assessment: a health score, a short narrative, strengths,
        weaknesses, opportunities, and a prioritized list of insights.

        Ground every claim in the numbers you were given. Do not invent metrics,
        channels, or activity that isn't in the data. If a section of the data
        has no signal (e.g. no email data), don't speculate about it — say so
        briefly or omit it from the narrative rather than filling the gap with
        generic advice.

        health_score reflects the data as given, not a sales pitch — a client
        with high churn and low engagement should score low, not be softened.
        confidence_score on each insight should reflect how much data supports
        it: high confidence for well-populated metrics, lower for a single
        data point.
        PROMPT;
    }

    private function windowLabel(): string
    {
        return self::LOOKBACK_DAYS . ' days';
    }

    private function responseSchema(): array
    {
        return [
            'type'                 => 'object',
            'additionalProperties' => false,
            'required'             => ['health_score', 'summary', 'strengths', 'weaknesses', 'opportunities', 'insights'],
            'properties'           => [
                'health_score' => [
                    'type'        => 'integer',
                    'description' => 'Overall business health, 0-100, grounded in the supplied metrics.',
                ],
                'summary' => [
                    'type'        => 'string',
                    'description' => 'Two to three sentence plain-language summary of where the business stands.',
                ],
                'strengths' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
                'weaknesses' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
                'opportunities' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
                'insights' => [
                    'type'  => 'array',
                    'items' => [
                        'type'                 => 'object',
                        'additionalProperties' => false,
                        'required'             => ['category', 'title', 'description', 'recommendation', 'confidence_score', 'impact_level'],
                        'properties'           => [
                            'category' => [
                                'type' => 'string',
                                'enum' => ['growth', 'risk', 'opportunity', 'anomaly', 'prediction'],
                            ],
                            'title'       => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'recommendation' => [
                                'type'        => 'string',
                                'description' => 'A single concrete, actionable next step.',
                            ],
                            'confidence_score' => [
                                'type'        => 'number',
                                'description' => '0.0 to 1.0.',
                            ],
                            'impact_level' => [
                                'type' => 'string',
                                'enum' => ['low', 'medium', 'high', 'critical'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
