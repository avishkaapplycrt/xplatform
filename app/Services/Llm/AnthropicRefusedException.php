<?php

namespace App\Services\Llm;

/**
 * The model's safety classifiers declined the request (stop_reason: "refusal").
 * This is a normal HTTP 200, not a transport failure — callers should treat it
 * as "no insight generated this cycle", not retry it, and not surface it as an
 * error to the client.
 */
class AnthropicRefusedException extends \RuntimeException {}
