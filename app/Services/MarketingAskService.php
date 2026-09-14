<?php

namespace App\Services;

/**
 * Answers free-text questions typed into the Marketing "Ask anything" box by
 * matching them to the same real, data-grounded questions already answered
 * elsewhere on this page (Audience/Insights/Campaign/Performance/A-B
 * test/Lift services — all backed by crm_contacts, crm_deals, email_logs and
 * email_logs_providers). Nothing here invents an answer: a question either
 * matches one of these known intents and returns the real service's answer,
 * or it doesn't match and the caller is told plainly that no data-backed
 * answer exists yet for it.
 *
 * Matching is deliberately simple keyword overlap (score = how many of an
 * intent's keywords appear in the typed text), the same approach already
 * used by the page's PLAYBOOKS matcher — no LLM call is needed to route the
 * question, only (optionally, inside the underlying services) to phrase the
 * already-computed numbers in prose.
 */
class MarketingAskService
{
    /**
     * @return array{matched: bool, question?: string, answer?: string, ranked?: array, ai_used?: bool}
     */
    public function answer(string $text): array
    {
        $words = $this->normWords($text);

        $best = null;
        $bestScore = 0;

        foreach ($this->intents() as $intent) {
            $score = count(array_intersect($intent['keywords'], $words));
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $intent;
            }
        }

        if ($best === null || $bestScore < 2) {
            return ['matched' => false];
        }

        $result = $best['handler']();

        return [
            'matched' => true,
            'question' => $best['question'],
            'answer' => $result['answer'],
            'ranked' => $result['ranked'],
            'ai_used' => $result['ai_used'] ?? false,
        ];
    }

    /**
     * @return array<int, array{question: string, keywords: array<int, string>, handler: callable}>
     */
    private function intents(): array
    {
        return [
            [
                'question' => 'Who must be excluded from every send, and why?',
                'keywords' => ['exclude', 'excluded', 'every', 'send', 'sends', 'sending'],
                'handler' => fn () => app(MarketingAudienceService::class)->excludeFromEverySend(),
            ],
            [
                'question' => 'Who is in a live sales cycle — leave them alone?',
                'keywords' => ['live', 'sales', 'cycle', 'alone', 'leave'],
                'handler' => fn () => app(MarketingAudienceService::class)->liveSalesCycle(),
            ],
            [
                'question' => 'Is MQL → Sales a proof audience or an offer audience?',
                'keywords' => ['proof', 'offer', 'audience', 'lead'],
                'handler' => fn () => app(MarketingInsightsService::class)->proofOrOfferAudience(),
            ],
            [
                'question' => 'What is the one lever that moves MQL → Sales?',
                'keywords' => ['lever', 'moves', 'move', 'biggest', 'single'],
                'handler' => fn () => app(MarketingInsightsService::class)->oneLever(),
            ],
            [
                'question' => 'What rule put people into MQL → Sales?',
                'keywords' => ['rule', 'qualify', 'qualifies', 'criteria', 'threshold'],
                'handler' => fn () => app(MarketingInsightsService::class)->rulePutPeopleIntoMqlSales(),
            ],
            [
                'question' => 'What changed in the last 7 days?',
                'keywords' => ['changed', 'last', 'days', 'week', 'recent', 'recently'],
                'handler' => fn () => app(MarketingInsightsService::class)->changedLast7Days(),
            ],
            [
                'question' => 'Write the 3-touch email sequence for MQL → Sales',
                'keywords' => ['sequence', 'touch', 'email', 'emails', 'series', 'write'],
                'handler' => fn () => app(MarketingCampaignService::class)->emailSequence(),
            ],
            [
                'question' => 'Can MQL → Sales get a discount, or proof only?',
                'keywords' => ['discount', 'proof', 'only'],
                'handler' => fn () => app(MarketingCampaignService::class)->discountOrProof(),
            ],
            [
                'question' => 'Should I test proof vs offer on MQL → Sales?',
                'keywords' => ['test', 'proof', 'offer', 'versus'],
                'handler' => fn () => app(MarketingAbTestService::class)->proofVsOfferTest(),
            ],
            [
                'question' => 'How many per arm do I need for MQL → Sales?',
                'keywords' => ['many', 'arm', 'sample', 'size'],
                'handler' => fn () => app(MarketingAbTestService::class)->sampleSizePerArm(),
            ],
            [
                'question' => 'Is my holdout enough for MQL → Sales?',
                'keywords' => ['holdout', 'enough', 'percent'],
                'handler' => fn () => app(MarketingAbTestService::class)->holdoutEnough(),
            ],
            [
                'question' => 'Who became an MQL since the last send?',
                'keywords' => ['became', 'since', 'last', 'send', 'new'],
                'handler' => fn () => app(MarketingPerformanceService::class)->whoBecameMqlSinceLastSend(),
            ],
            [
                'question' => "Push this week's MQLs to Sales",
                'keywords' => ['push', 'week', 'sales', 'mqls'],
                'handler' => fn () => app(MarketingPerformanceService::class)->pushWeekMqlsToSales(),
            ],
            [
                'question' => 'Which subject-line test is worth running on MQL → Sales?',
                'keywords' => ['subject', 'line', 'test', 'worth', 'running'],
                'handler' => fn () => app(MarketingEmailTestService::class)->subjectLineTest(),
            ],
            [
                'question' => 'When should MQL → Sales receive touch 1?',
                'keywords' => ['touch', 'send', 'time', 'when', 'receive'],
                'handler' => fn () => app(MarketingEmailTestService::class)->touch1SendTime(),
            ],
            [
                'question' => 'All test ideas for MQL → Sales',
                'keywords' => ['test', 'ideas', 'all'],
                'handler' => fn () => app(MarketingEmailTestService::class)->allTestIdeas(),
            ],
            [
                'question' => 'What lift did MQL → Sales get vs its holdout?',
                'keywords' => ['lift', 'holdout', 'versus', 'compare'],
                'handler' => fn () => app(MarketingLiftService::class)->mqlLiftVsHoldout(),
            ],
            [
                'question' => 'Which audience has the worst unsubscribe rate?',
                'keywords' => ['unsubscribe', 'unsubscribed', 'worst', 'audience', 'rate'],
                'handler' => fn () => app(MarketingLiftService::class)->worstUnsubscribeAudience(),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function normWords(string $text): array
    {
        $lower = mb_strtolower($text);
        $stripped = preg_replace('/[^a-z0-9 ]/', ' ', $lower);
        $words = preg_split('/\s+/', trim((string) $stripped));

        return array_values(array_filter($words, fn ($w) => mb_strlen($w) > 3));
    }
}
