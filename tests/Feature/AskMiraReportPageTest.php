<?php

namespace Tests\Feature;

use App\Services\AskMiraReportStore;
use Tests\TestCase;

class AskMiraReportPageTest extends TestCase
{
    private function report(): array
    {
        return [
            'url' => 'https://example.com/', 'title' => 'Example', 'overall' => 70, 'grade' => 'C',
            'categories' => [], 'stages' => [], 'needs_connected_data' => [], 'recommendations_source' => 'none',
        ];
    }

    public function test_a_stored_report_opens_on_its_own_page(): void
    {
        $url = app(AskMiraReportStore::class)->store('analysis', $this->report(), false);

        $this->assertStringContainsString('/mira/report/', $url);
        $this->get($url)->assertOk()->assertSee('Website report')->assertSee('https://example.com/');
    }

    public function test_a_premium_comparison_uses_the_premium_look(): void
    {
        $url = app(AskMiraReportStore::class)->store('comparison', [
            'you' => ['url' => 'https://a.test/'], 'competitor' => ['url' => 'https://b.test/'],
        ], true);

        $this->get($url)->assertOk()->assertSee('Competitor comparison')->assertSee('Ask Mira Premium');
    }

    public function test_unknown_or_malformed_ids_are_not_found(): void
    {
        $this->get('/mira/report/00000000-0000-0000-0000-000000000000')->assertNotFound();
        $this->get('/mira/report/not-a-uuid')->assertNotFound();
    }

    public function test_the_waiting_page_loads(): void
    {
        $this->get('/mira/report/pending?premium=1')->assertOk()->assertSee('Mira is preparing your report');
    }
}
