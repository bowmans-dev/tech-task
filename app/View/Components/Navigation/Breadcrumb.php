<?php

namespace App\View\Components\Navigation;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    public $groups;
    public $breadcrumbs;

    public function __construct($groups = [], $breadcrumbs = [])
    {
        $this->groups = $groups;
        $this->breadcrumbs = $breadcrumbs;
    }


    public function render(): View|Closure|string
    {
        return view('Components.Navigation.breadcrumb');
    }
}
