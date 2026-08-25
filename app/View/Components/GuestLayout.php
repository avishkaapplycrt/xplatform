<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public function __construct(
        public string $backUrl = '/',
        public ?string $previousUrl = null,
        public int $currentStep = 1,
        public int $totalSteps = 2,
    ) {}

    public function render(): View
    {
        return view('layouts.guest');
    }
}
