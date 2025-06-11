<?php

namespace App\View\Components\List;

use Illuminate\View\Component;

class UserList extends Component
{
    public $users;

    public function __construct($users = [])
    {
        $this->users = $users;
    }


    public function render()
    {
        return view('Components.List.user-list');
    }
}
