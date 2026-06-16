<?php

namespace App\Livewire\Tarifas\Historial;

use App\Models\AtributoHora;
use App\Models\TarifaCliente;
use App\Models\TarifaHistorial;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tarifas → Historial.
 *
 * Vista global de auditoría: lee `tarifas_historial` unificada (cliente +
 * trabajador) con filtros: buscador libre, tipo, atributo, usuario que cambió,
 * rango de fechas. Solo lectura. Las filas las generan los Observers.
 */
#[Layout('components.layouts.web', ['active' => 'tarifas_historial'])]
#[Title('Tarifas — Historial')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'tipo')]
    public string $filtroTipo = '';

    #[Url(as: 'atr')]
    public ?int $filtroAtributo = null;

    #[Url(as: 'cambiadoPor')]
    public ?int $filtroCambiadoPor = null;

    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'created_at';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'desc';

    public function mount(): void
    {
        Gate::authorize('tarifas.historial_ver');
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroAtributo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroCambiadoPor(): void
    {
        $this->resetPage();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatedFechaHasta(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['created_at', 'tipo', 'atributo_id', 'importe_anterior', 'importe_nuevo', 'cambiado_por'];
        if (! \in_array($columna, $permitidas, true)) {
            return;
        }

        if ($this->ordenColumna === $columna) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenColumna = $columna;
            $this->ordenDireccion = $columna === 'created_at' ? 'desc' : 'asc';
        }
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->filtroTipo = '';
        $this->filtroAtributo = null;
        $this->filtroCambiadoPor = null;
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->resetPage();
    }

    /* ── Computeds ────────────────────────────────────────────── */

    /** @return Collection<int, AtributoHora> */
    #[Computed]
    public function atributos(): Collection
    {
        return AtributoHora::query()->orderBy('orden')->get(['id', 'codigo', 'nombre_corto']);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function usuarios(): Collection
    {
        return User::query()
            ->select(['id', 'nombre', 'apellidos'])
            ->orderBy('apellidos')
            ->get();
    }

    public function render(): View
    {
        $query = TarifaHistorial::query()
            ->with(['atributo:id,codigo,nombre_corto', 'cambiadoPor:id,nombre,apellidos']);

        if ($this->filtroTipo !== '') {
            $query->where('tipo', $this->filtroTipo);
        }
        if ($this->filtroAtributo) {
            $query->where('atributo_id', $this->filtroAtributo);
        }
        if ($this->filtroCambiadoPor) {
            $query->where('cambiado_por', $this->filtroCambiadoPor);
        }
        if ($this->fechaDesde !== '') {
            $query->whereDate('created_at', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta !== '') {
            $query->whereDate('created_at', '<=', $this->fechaHasta);
        }

        // Búsqueda libre: encuentra por descripción de la referencia
        // (cliente + tipo proyecto si es 'cliente', nombre del usuario si es
        // 'trabajador'), por motivo y por nombre del usuario que hizo el cambio.
        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';

            $idsClientes = TarifaCliente::query()
                ->join('clientes', 'clientes.id', '=', 'tarifas_cliente.cliente_id')
                ->join('tipos_proyectos', 'tipos_proyectos.id', '=', 'tarifas_cliente.tipo_proyecto_id')
                ->where(function ($q) use ($termino): void {
                    $q->where('clientes.codigo_cliente', 'like', $termino)
                        ->orWhere('clientes.nombre', 'like', $termino)
                        ->orWhere('clientes.nombre_comercial', 'like', $termino)
                        ->orWhere('tipos_proyectos.nombre', 'like', $termino);
                })
                ->pluck('tarifas_cliente.id');

            $idsUsuarios = User::query()
                ->where(function ($q) use ($termino): void {
                    $q->where('nombre', 'like', $termino)
                        ->orWhere('apellidos', 'like', $termino)
                        ->orWhere('username', 'like', $termino)
                        ->orWhere('numero_empleado', 'like', $termino);
                })
                ->pluck('id');

            $query->where(function ($q) use ($termino, $idsClientes, $idsUsuarios): void {
                $q->where(function ($qq) use ($idsClientes): void {
                    $qq->where('tipo', TarifaHistorial::TIPO_CLIENTE)
                        ->whereIn('referencia_id', $idsClientes);
                })->orWhere(function ($qq) use ($idsUsuarios): void {
                    $qq->where('tipo', TarifaHistorial::TIPO_TRABAJADOR)
                        ->whereIn('referencia_id', $idsUsuarios);
                })->orWhere('motivo', 'like', $termino);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);
        if ($this->ordenColumna !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }

        $registros = $query->paginate($this->porPagina)->onEachSide(2);

        // Resolver descripciones de referencia en bloque (sin N+1).
        $idsCli = $registros->where('tipo', TarifaHistorial::TIPO_CLIENTE)->pluck('referencia_id')->unique();
        $idsTrab = $registros->where('tipo', TarifaHistorial::TIPO_TRABAJADOR)->pluck('referencia_id')->unique();

        $tarifasCli = TarifaCliente::query()
            ->whereIn('id', $idsCli)
            ->with(['cliente:id,nombre', 'tipoProyecto:id,nombre'])
            ->get()
            ->keyBy('id');

        $usuariosTrab = User::query()
            ->select(['id', 'nombre', 'apellidos'])
            ->whereIn('id', $idsTrab)
            ->get()
            ->keyBy('id');

        $referenciaTexto = [];
        foreach ($registros as $r) {
            if ($r->tipo === TarifaHistorial::TIPO_CLIENTE) {
                $t = $tarifasCli->get($r->referencia_id);
                $referenciaTexto[$r->id] = $t
                    ? ($t->cliente?->nombre.' / '.$t->tipoProyecto?->nombre)
                    : '— (tarifa eliminada)';
            } else {
                $u = $usuariosTrab->get($r->referencia_id);
                $referenciaTexto[$r->id] = $u
                    ? trim($u->apellidos.' '.$u->nombre)
                    : '— (usuario eliminado)';
            }
        }

        return view('livewire.tarifas.historial.index', [
            'registros' => $registros,
            'referenciaTexto' => $referenciaTexto,
        ]);
    }
}
