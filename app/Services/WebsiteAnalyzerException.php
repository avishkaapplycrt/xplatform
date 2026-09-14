<?php

namespace App\Services;

/** A PageSpeed Insights request failed — no key configured, quota exhausted, or the URL wasn't reachable. */
class WebsiteAnalyzerException extends \RuntimeException {}
