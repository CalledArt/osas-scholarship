<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  bool  $sheet  Render the content area as one continuous white sheet,
     *                       for surfaces that are themselves the document (the
     *                       register). Everything else keeps the Bond Paper desk
     *                       its white cards are drawn against.
     */
    public function __construct(public bool $sheet = false) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
