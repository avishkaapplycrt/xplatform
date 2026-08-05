<?php

namespace App\Services\Llm;

/** Anthropic API call failed to produce a usable result — retries exhausted, non-retryable HTTP error, or unparseable output. */
class AnthropicException extends \RuntimeException {}
