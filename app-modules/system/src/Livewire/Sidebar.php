<?php

namespace Tequia\System\Livewire;

use Filament\Livewire\Sidebar as BaseSidebar;
use Illuminate\Contracts\View\View;

class Sidebar extends BaseSidebar
{
    public function render(): View
    {
        return view('system::livewire.sidebar');
    }
}
