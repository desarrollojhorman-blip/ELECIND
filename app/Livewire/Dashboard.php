<?php

namespace App\Livewire;

use App\Enums\EstadoAlbaran;
use App\Models\Albaran;
use App\Models\Borrador;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'dashboard'])]
#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function kpis(): array
    {
        $user = Auth::user();

        return [
            'pendientes_firma' => $user->can('albaranes.ver_todos')
                ? Albaran::where('estado', EstadoAlbaran::PENDIENTE_FIRMA)->count()
                : null,

            'firmados' => $user->can('albaranes.ver_todos')
                ? Albaran::where('estado', EstadoAlbaran::FIRMADO)->count()
                : null,

            'borradores_abiertos' => $user->can('borradores.ver_todos')
                ? Borrador::whereNull('convertido_a_albaran_id')->whereNull('deleted_at')->count()
                : null,

            'albaranes_mes' => $user->can('albaranes.ver_todos')
                ? Albaran::whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year)
                         ->count()
                : null,

            'clientes_activos' => $user->can('clientes.ver')
                ? Cliente::where('activo', true)->count()
                : null,

            'usuarios_activos' => $user->can('usuarios.ver_todos')
                ? User::where('activo', true)->count()
                : null,
        ];
    }

    #[Computed]
    public function ultimosAlbaranes(): \Illuminate\Database\Eloquent\Collection
    {
        if (! Auth::user()->can('albaranes.ver_todos')) {
            return collect();
        }

        return Albaran::query()
            ->with(['cliente'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'numero', 'fecha', 'estado', 'cliente_id', 'cliente_texto', 'created_at']);
    }

    #[Computed]
    public function ultimosBorradores(): \Illuminate\Database\Eloquent\Collection
    {
        if (! Auth::user()->can('borradores.ver_todos')) {
            return collect();
        }

        return Borrador::query()
            ->whereNull('convertido_a_albaran_id')
            ->whereNull('deleted_at')
            ->with(['cliente', 'proyecto'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'numero_borrador', 'fecha', 'cliente_id', 'cliente_texto', 'proyecto_id', 'proyecto_texto', 'created_at']);
    }

    public function render(): View
    {
        return view('livewire.dashboard', [
            'kpis'              => $this->kpis,
            'ultimosAlbaranes'  => $this->ultimosAlbaranes,
            'ultimosBorradores' => $this->ultimosBorradores,
        ]);
    }
}
