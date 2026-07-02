<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SimansLayout extends Component
{
    public function __construct(
        public string $title = 'Dashboard'
    ) {}

    public function render()
    {
        return view('layouts.simans');
    }
}