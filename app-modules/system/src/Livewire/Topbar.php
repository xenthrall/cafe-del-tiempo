<?php

namespace Tequia\System\Livewire;

use Filament\Livewire\Topbar as LivewireTopbar;
use Illuminate\Contracts\View\View;

class Topbar extends LivewireTopbar
{
    public function render(): View
    {
        return view('system::livewire.topbar');
    }
}
