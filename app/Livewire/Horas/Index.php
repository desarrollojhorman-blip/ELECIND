<?php

namespace App\Livewire\Horas;

use App\Enums\EstadoAlbaran;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.web', ['active' => 'horas'])]
#[Title('Control de Horas')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'trabajador')]
    public ?int $filtroTrabajador = null;

    #[Url(as: 'cliente')]
    public ?int $filtroCliente = null;

    #[Url(as: 'proyecto')]
    public ?int $filtroProyecto = null;

    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    #[Url(as: 'estado')]
    public string $filtroEstado = '';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    public function mount(): void
    {
        if ($this->fechaDesde === '') {
            $this->fechaDesde = now()->startOfMonth()->format('Y-m-d');
        }
        if ($this->fechaHasta === '') {
            $this->fechaHasta = now()->endOfMonth()->format('Y-m-d');
        }
    }

    /**
     * Hook genérico: cuando cambia cualquier filtro o el tamaño de página,
     * vuelve a la página 1. La cascada cliente → proyecto sigue funcionando.
     */
    public function updated(string $name): void
    {
        if ($name === 'filtroCliente') {
            $this->filtroProyecto = null;
        }

        if (in_array($name, [
            'filtroTrabajador', 'filtroCliente', 'filtroProyecto',
            'filtroEstado', 'fechaDesde', 'fechaHasta', 'porPagina',
        ], true)) {
            $this->resetPage();
        }
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function trabajadoresDisponibles(): Collection
    {
        return User::query()
            ->role('trabajador')
            ->withTrashed()
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /** @return Collection<int, Proyecto> */
    #[Computed]
    public function proyectosDisponibles(): Collection
    {
        return Proyecto::query()
            ->when($this->filtroCliente, fn ($q) => $q->where('cliente_id', $this->filtroCliente))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);
    }

    /**
     * Query base con joins y filtros aplicados. Se usa tanto para la lista
     * paginada como para el agregado de totales — así los totales suman
     * SIEMPRE todo lo filtrado, no solo la página visible.
     */
    private function baseQuery(): QueryBuilder
    {
        $idsTrabajadores = User::role('trabajador')->withTrashed()->pluck('id');

        return DB::table('albaran_lineas_personal as alp')
            ->join('albaranes as a', 'a.id', '=', 'alp.albaran_id')
            ->leftJoin('users as u', 'u.id', '=', 'alp.trabajador_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->leftJoin('proyectos as p', 'p.id', '=', 'a.proyecto_id')
            ->leftJoin('conceptos as con', 'con.id', '=', 'a.concepto_id')
            ->whereNull('a.deleted_at')
            ->whereIn('alp.trabajador_id', $idsTrabajadores)
            ->when($this->filtroTrabajador, fn ($q) => $q->where('alp.trabajador_id', $this->filtroTrabajador))
            ->when($this->filtroCliente, fn ($q) => $q->where('a.cliente_id', $this->filtroCliente))
            ->when($this->filtroProyecto, fn ($q) => $q->where('a.proyecto_id', $this->filtroProyecto))
            ->when($this->filtroEstado, fn ($q) => $q->where('a.estado', $this->filtroEstado))
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('a.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('a.fecha', '<=', $this->fechaHasta));
    }

    public function render(): View
    {
        // Página visible
        $lineas = $this->baseQuery()
            ->select([
                'a.id as albaran_id',
                'a.numero as albaran_numero',
                'a.fecha',
                'a.tipo_hora',
                'a.estado',
                'alp.horas',
                'alp.horas_extra',
                DB::raw("COALESCE(u.nombre, alp.trabajador_nombre_snapshot, '') as trabajador_nombre"),
                DB::raw("COALESCE(u.apellidos, alp.trabajador_apellidos_snapshot, '') as trabajador_apellidos"),
                DB::raw("COALESCE(c.nombre, a.cliente_nombre_snapshot, a.cliente_texto, '') as cliente_nombre"),
                DB::raw("COALESCE(p.nombre, a.proyecto_nombre_snapshot, a.proyecto_texto, '') as proyecto_nombre"),
                DB::raw("p.codigo as proyecto_codigo"),
                DB::raw("COALESCE(con.nombre, a.concepto_nombre_snapshot, a.concepto_texto, '') as concepto_nombre"),
            ])
            ->orderBy('a.fecha')
            ->orderBy('trabajador_apellidos')
            ->orderBy('trabajador_nombre')
            ->orderBy('a.id')
            ->paginate($this->porPagina)
            ->onEachSide(2);

        // Totales: una sola query agregada sobre TODO lo filtrado
        $sumas = $this->baseQuery()
            ->groupBy('a.tipo_hora')
            ->select([
                'a.tipo_hora',
                DB::raw('SUM(alp.horas) as total_horas'),
                DB::raw('SUM(alp.horas_extra) as total_extras'),
            ])
            ->get()
            ->keyBy('tipo_hora');

        $get = fn (string $tipo, string $col) => (float) ($sumas->get($tipo)?->{$col} ?? 0);

        $totales = [
            'laboral'             => $get('laboral', 'total_horas'),
            'laboral_noche'       => $get('laboral_noche', 'total_horas'),
            'festivo'             => $get('festivo', 'total_horas'),
            'festivo_noche'       => $get('festivo_noche', 'total_horas'),
            'laboral_extra'       => $get('laboral', 'total_extras'),
            'laboral_noche_extra' => $get('laboral_noche', 'total_extras'),
            'festivo_extra'       => $get('festivo', 'total_extras'),
            'festivo_noche_extra' => $get('festivo_noche', 'total_extras'),
        ];
        $totales['total'] = (float) array_sum($totales);

        $diasSemana = ['', 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $estados    = EstadoAlbaran::cases();

        return view('livewire.horas.index', compact('lineas', 'totales', 'diasSemana', 'estados'));
    }
}
