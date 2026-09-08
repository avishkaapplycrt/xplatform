<?php

namespace App\Services\Llm;

/** OpenAI API call failed to produce a usable result — retries exhausted, non-retryable HTTP error, or empty output. */
class OpenAiException extends \RuntimeException {}
