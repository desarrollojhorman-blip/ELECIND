<?php

namespace App\Livewire\Configuracion;

use App\Models\Empresa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'ajustes'])]
#[Title('Ajustes')]
class Ajustes extends Component
{
    #[Validate(['required', 'string', 'max:60'])]
    public string $plantilla_numeracion_albaran = 'ALB-{YYYY}-{NNNN}';

    #[Validate(['required', 'string', 'max:60'])]
    public string $plantilla_numeracion_cliente = 'CLI-{NNNN}';

    #[Validate(['required', 'integer', 'min:1', 'max:90'])]
    public int $token_caducidad_dias = 7;

    public function mount(): void
    {
        Gate::authorize('configuracion.empresa');

        $empresa = Empresa::actual();
        $this->plantilla_numeracion_albaran = $empresa->plantilla_numeracion_albaran ?? 'ALB-{YYYY}-{NNNN}';
        $this->plantilla_numeracion_cliente = $empresa->plantilla_numeracion_cliente ?? 'CLI-{NNNN}';
        $this->token_caducidad_dias = $empresa->token_caducidad_dias ?? 7;
    }

    public function guardar(): void
    {
        Gate::authorize('configuracion.empresa');

        $this->validate();

        Empresa::actual()->update([
            'plantilla_numeracion_albaran' => $this->plantilla_numeracion_albaran,
            'plantilla_numeracion_cliente' => $this->plantilla_numeracion_cliente,
            'token_caducidad_dias' => $this->token_caducidad_dias,
        ]);

        session()->flash('status', 'Ajustes guardados correctamente.');
    }

    public function render(): View
    {
        return view('livewire.configuracion.ajustes');
    }
}
