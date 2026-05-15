<?php

namespace App\Livewire\Perfil;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'mi_perfil'])]
#[Title('Mi perfil')]
class MiPerfil extends Component
{
    public function render(): View
    {
        return view('livewire.perfil.mi-perfil', [
            'user' => auth()->user(),
        ]);
    }
}
