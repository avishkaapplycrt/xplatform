<?php

namespace App\Services\MockMaster;

use App\Services\Llm\OpenAiClient;
use App\Services\Llm\OpenAiException;

/**
 * Answers free-typed questions in Mock Master Helper's "Ask Mira" chat box,
 * grounded in a real snapshot of the mm_* tables (see
 * MockMasterDataService::chatSnapshot()) — never this client's own CRM/email
 * data. Same real-data-first, narrate-second pattern as SalesChatService:
 * the data is computed first, OpenAI is only ever asked to phrase an answer
 * from it, never to invent or estimate a number itself.
 */
class MockMasterChatService
{
    public function __construct(
        private readonly MockMasterDataService $data,
        private readonly OpenAiClient $client,
    ) {
    }

    /**
     * @return array{answer: string, ai_used: bool}
     */
    public function answer(string $agent, string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return [
                'answer' => 'Ask me something about your Mock Master students — for example, who is most at risk of churning, or how mock test scores are trending.',
                'ai_used' => false,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'answer' => "The AI assistant isn't configured yet — try one of the suggested prompts above.",
                'ai_used' => false,
            ];
        }

        try {
            return ['answer' => $this->ask($agent, $question), 'ai_used' => true];
        } catch (OpenAiException $e) {
            report($e);
            // A broken AI call should never take down a chat that has real
            // data behind it — surface a plain retry message instead.
            return [
                'answer' => "I couldn't reach the AI just now — try again in a moment, or use one of the suggested prompts above.",
                'ai_used' => false,
            ];
        }
    }

    private function ask(string $agent, string $question): string
    {
        $snapshot = $this->data->chatSnapshot();

        // The aggregated snapshot above deliberately excludes the full 58k+
        // student directory — too large to send on every question. If the
        // question looks like it names a specific person, run a real,
        // targeted search instead of leaving the AI with nothing to match.
        $matchedStudents = $this->data->searchStudents($this->extractNameTerms($question));
        if (!empty($matchedStudents)) {
            $snapshot['matched_students_for_this_question'] = $matchedStudents;
        }

        $persona = match ($agent) {
            'sales' => 'You are the Mock Master Sales copilot — help the rep decide who to call, nurture, or close.',
            'retention' => 'You are the Mock Master Retention copilot — help the team decide who to save first and why.',
            default => 'You are the Mock Master Marketing copilot — help the team decide who to target and what is working.',
        };

        $system = $persona . ' '
            . 'Answer using ONLY the data provided as JSON below — never invent a student name, number, or fact that isn\'t in it. '
            . 'The data covers real Mock Master platform tables: students, purchases, payments, packages, coupon usage, '
            . 'mock test results and activity logs, login activity, meetings, feedback, notifications, and scheduled emails. '
            . 'If the question names a specific person, check "matched_students_for_this_question" first — it is a real, '
            . 'targeted lookup for that name/email, separate from the aggregated stats elsewhere in the data. '
            . 'If that key is absent or empty and the question needs an individual student\'s details, say the search found '
            . 'no matching student rather than guessing. For everything else, if the data doesn\'t contain what\'s needed to '
            . 'answer precisely, say so plainly. Be brief and concrete — name specific students/numbers where relevant, '
            . 'in plain English, under 130 words.';

        $prompt = "Question: {$question}\n\ndata:\n" . json_encode($snapshot, JSON_PRETTY_PRINT);

        return $this->client->chat($system, $prompt, ['max_tokens' => 350]);
    }

    /**
     * Pulls out likely name/email tokens from a free-typed question — e.g.
     * "tell me the last name of Bharati" -> ["Bharati"], "Samina's email" ->
     * ["Samina"]. Deliberately simple (capitalised words, plus anything
     * containing "@"), not NLP — good enough to catch the common "who is
     * X" / "X's email" phrasing this chat actually receives.
     */
    private function extractNameTerms(string $question): array
    {
        $terms = [];

        if (preg_match_all('/[\w.+-]+@[\w-]+\.[\w.-]+/', $question, $emailMatches)) {
            $terms = array_merge($terms, $emailMatches[0]);
        }

        $words = preg_split('/[^\p{L}\']+/u', $question, -1, PREG_SPLIT_NO_EMPTY);
        $stopwords = ['I', 'The', 'What', 'Who', 'Which', 'Give', 'Tell', 'Send', 'Me', 'Is', 'Are', 'Do', 'Does'];

        foreach ($words as $word) {
            $bare = rtrim($word, "'s");
            if (mb_strlen($bare) >= 3 && ctype_upper(mb_substr($bare, 0, 1)) && !in_array($bare, $stopwords, true)) {
                $terms[] = $bare;
            }
        }

        return array_values(array_unique($terms));
    }
}
