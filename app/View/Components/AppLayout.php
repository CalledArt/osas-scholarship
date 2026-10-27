<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  string|null  $title  The screen's own name. It sets the document
     *                              title and prints in the narrow-screen strip,
     *                              so every page is distinguishable on load
     *                              instead of all announcing the product name.
     * @param  bool  $sheet  Render the content area as one continuous white
     *                       sheet, for surfaces that are themselves the
     *                       document. Everything else keeps the Linen desk its
     *                       white sheets are laid on.
     */
    public function __construct(
        public ?string $title = null,
        public bool $sheet = false,
    ) {}

    /**
     * The document title: the screen's name ahead of the product's, so a
     * browser tab, a history entry and a screen reader all name the page.
     */
    public function documentTitle(): string
    {
        $application = config('app.name', 'OSAS Scholarships');

        return $this->title === null
            ? $application
            : $this->title.' · '.$application;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
