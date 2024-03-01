<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Image extends Component
{
    public ?int $id;
    public string $url;
    public string $title;
    /**
     * Create a new component instance.
     */
    public function __construct(string $url, string $title, int $id = null)
    {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.image');
    }
}
