<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * @param  string|null  $title  The screen's own name, so the sign-in, reset
     *                              and verification pages are told apart on load
     *                              instead of all announcing the product name.
     */
    public function __construct(public ?string $title = null) {}

    /**
     * The document title: the screen's name ahead of the product's.
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
        return view('layouts.guest');
    }
}
