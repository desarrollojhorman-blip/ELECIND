<?php

namespace App\Livewire\Mobile\Perfil;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MiPerfil extends Component
{
    public function render(): View
    {
        return view('livewire.mobile.perfil.mi-perfil', [
            'user' => Auth::user(),
        ])->layout('components.layouts.mobile', [
            'title' => 'Mi perfil',
            'showBack' => true,
            'backRoute' => route('mobile.dashboard'),
        ]);
    }
}
