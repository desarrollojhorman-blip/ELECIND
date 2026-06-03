<?php

namespace App\Livewire\Configuracion;

use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'licencias'])]
#[Title('Licencias')]
class Licencias extends Component
{
    public function mount(): void
    {
        Gate::authorize('licencias.ver');
    }

    #[Computed]
    public function empresa(): Empresa
    {
        return Empresa::actual();
    }

    #[Computed]
    public function licencia(): array
    {
        return [
            'clave'        => 'ENIA-2024-ELEC-XXXX-XXXX-XXXX',
            'plan'         => 'Profesional',
            'producto'     => 'Gestión de Procesos KD Getradi — ELECIND',
            'fabricante'   => 'Entreredes Consultoría Tecnológica SL',
            'version'      => '3.26',
            'emision'      => Carbon::parse('2025-10-09'),
            'caducidad'    => Carbon::parse('2026-10-09'),
            'max_usuarios' => 25,
        ];
    }

    #[Computed]
    public function usuariosActivos(): int
    {
        return User::where('activo', true)->count();
    }

    #[Computed]
    public function modulos(): array
    {
        return [
            ['nombre' => 'Albaranes',    'activo' => true],
            ['nombre' => 'Borradores',   'activo' => true],
            ['nombre' => 'Proyectos',    'activo' => true],
            ['nombre' => 'Clientes',     'activo' => true],
            ['nombre' => 'Materiales',   'activo' => true],
            ['nombre' => 'Conceptos',    'activo' => true],
            ['nombre' => 'Firma móvil',  'activo' => true],
            ['nombre' => 'API externa',  'activo' => true],
        ];
    }

    public function render(): View
    {
        return view('livewire.configuracion.licencias', [
            'empresa'         => $this->empresa,
            'licencia'        => $this->licencia,
            'usuariosActivos' => $this->usuariosActivos,
            'modulos'         => $this->modulos,
        ]);
    }
}
