<?php

namespace App\Livewire\Tarifas\Clientes;

use App\Models\AtributoHora;
use App\Models\Cliente;
use App\Models\TarifaCliente;
use App\Models\TarifaHistorial;
use App\Models\TiposProyecto;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tarifas → Clientes.
 *
 * Genera TODAS las combinaciones (cliente activo × tipo_proyecto activo)
 * automáticamente. Cada fila parte en modo lectura: para editar hay que
 * pulsar el botón ✏️ — entonces los inputs se activan y aparecen los
 * botones 💾 Guardar / ❌ Cancelar (patrón estilo "materiales en albaranes").
 */
#[Layout('components.layouts.web', ['active' => 'tarifas_clientes'])]
#[Title('Tarifas — Clientes')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'cliente')]
    public ?int $filtroCliente = null;

    #[Url(as: 'tipo')]
    public ?int $filtroTipoProyecto = null;

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    #[Url(as: 'pagina')]
    public int $paginaActual = 1;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'cliente';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    /** Filas en modo edición: ['cliente_id_tipo_id' => true] */
    public array $editando = [];

    /** Ediciones pendientes en memoria: [key => [atributo_id => valor]] */
    public array $ediciones = [];

    /** ['cliente_id' => ..., 'tipo_proyecto_id' => ...] o null si cerrado. */
    public ?array $historialCombinacion = null;

    public function mount(): void
    {
        Gate::authorize('tarifas.ver');
    }

    public function updatedBuscar(): void
    {
        $this->paginaActual = 1;
    }

    public function updatedFiltroCliente(): void
    {
        $this->paginaActual = 1;
    }

    public function updatedFiltroTipoProyecto(): void
    {
        $this->paginaActual = 1;
    }

    public function updatedPorPagina(): void
    {
        $this->paginaActual = 1;
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->filtroCliente = null;
        $this->filtroTipoProyecto = null;
        $this->paginaActual = 1;
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['codigo_cliente', 'cliente', 'tipo_proyecto'];
        if (! \in_array($columna, $permitidas, true)) {
            return;
        }

        if ($this->ordenColumna === $columna) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenColumna = $columna;
            $this->ordenDireccion = 'asc';
        }
    }

    /* ── Edición por fila ────────────────────────────────────── */

    /**
     * Entra una fila en modo edición. Precarga los importes actuales de TODAS
     * las tarifas de la combinación para que los inputs aparezcan rellenos.
     */
    public function editar(int $clienteId, int $tipoProyectoId): void
    {
        Gate::authorize('tarifas.editar_clientes');

        $key = $this->keyCombinacion($clienteId, $tipoProyectoId);
        $this->editando[$key] = true;

        // Cargar importes actuales de los 11 atributos. Si no existe fila en
        // BD para alguno, queda en 0 (lo que el usuario verá en el input).
        $tarifas = TarifaCliente::where('cliente_id', $clienteId)
            ->where('tipo_proyecto_id', $tipoProyectoId)
            ->pluck('importe', 'atributo_id');

        $this->ediciones[$key] = [];
        foreach (AtributoHora::pluck('id') as $atributoId) {
            $this->ediciones[$key][$atributoId] = (float) ($tarifas[$atributoId] ?? 0);
        }
    }

    /** Cancela la edición de una fila (descarta cambios pendientes). */
    public function cancelarEdicion(int $clienteId, int $tipoProyectoId): void
    {
        $key = $this->keyCombinacion($clienteId, $tipoProyectoId);
        unset($this->editando[$key], $this->ediciones[$key]);
        $this->resetErrorBag();
    }

    /**
     * Persiste cambios y sale del modo edición. Solo toca las celdas que
     * cambiaron de valor (las que están en `ediciones[key]`).
     */
    public function guardar(int $clienteId, int $tipoProyectoId): void
    {
        Gate::authorize('tarifas.editar_clientes');

        $key = $this->keyCombinacion($clienteId, $tipoProyectoId);

        if (isset($this->ediciones[$key])) {
            $reglas = [];
            foreach ($this->ediciones[$key] as $atributoId => $valor) {
                $reglas["ediciones.$key.$atributoId"] = 'required|numeric|min:0|max:9999.999';
            }

            try {
                $this->validate($reglas);
            } catch (ValidationException $e) {
                throw $e;
            }

            DB::transaction(function () use ($clienteId, $tipoProyectoId, $key): void {
                foreach ($this->ediciones[$key] as $atributoId => $valor) {
                    $valorNumerico = $valor === '' || $valor === null ? 0 : (float) $valor;

                    TarifaCliente::updateOrCreate(
                        [
                            'cliente_id' => $clienteId,
                            'tipo_proyecto_id' => $tipoProyectoId,
                            'atributo_id' => (int) $atributoId,
                        ],
                        [
                            'importe' => $valorNumerico,
                        ],
                    );
                }
            });

            session()->flash('status', 'Tarifas actualizadas correctamente.');
        }

        unset($this->editando[$key], $this->ediciones[$key]);
    }

    /* ── Modal historial contextual ──────────────────────────── */

    public function abrirHistorial(int $clienteId, int $tipoProyectoId): void
    {
        Gate::authorize('tarifas.historial_ver');
        $this->historialCombinacion = [
            'cliente_id' => $clienteId,
            'tipo_proyecto_id' => $tipoProyectoId,
        ];
    }

    public function cerrarHistorial(): void
    {
        $this->historialCombinacion = null;
    }

    /* ── Helpers ──────────────────────────────────────────────── */

    public function keyCombinacion(int $clienteId, int $tipoProyectoId): string
    {
        return "{$clienteId}_{$tipoProyectoId}";
    }

    public function estaEditando(int $clienteId, int $tipoProyectoId): bool
    {
        return isset($this->editando[$this->keyCombinacion($clienteId, $tipoProyectoId)]);
    }

    /* ── Computeds ────────────────────────────────────────────── */

    /** @return Collection<int, AtributoHora> */
    #[Computed]
    public function atributos(): Collection
    {
        return AtributoHora::query()->orderBy('orden')->get();
    }

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientes(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /** @return Collection<int, TiposProyecto> */
    #[Computed]
    public function tiposProyecto(): Collection
    {
        return TiposProyecto::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /** @return Collection<int, TarifaHistorial> */
    #[Computed]
    public function historialDeCombinacion(): Collection
    {
        if ($this->historialCombinacion === null) {
            return collect();
        }

        $ids = TarifaCliente::where('cliente_id', $this->historialCombinacion['cliente_id'])
            ->where('tipo_proyecto_id', $this->historialCombinacion['tipo_proyecto_id'])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return collect();
        }

        return TarifaHistorial::query()
            ->clientes()
            ->whereIn('referencia_id', $ids)
            ->with(['atributo:id,codigo,nombre_corto', 'cambiadoPor:id,nombre,apellidos'])
            ->latest()
            ->limit(100)
            ->get();
    }

    public function render(): View
    {
        // CROSS JOIN clientes activos × tipos_proyecto activos.
        $query = DB::table('clientes as c')
            ->crossJoin('tipos_proyectos as tp')
            ->whereNull('c.deleted_at')
            ->whereNull('tp.deleted_at')
            ->where('c.activo', true)
            ->where('tp.activo', true)
            ->select([
                'c.id as cliente_id',
                'c.codigo_cliente as codigo_cliente',
                'c.nombre as cliente_nombre',
                'tp.id as tipo_proyecto_id',
                'tp.nombre as tipo_proyecto_nombre',
            ]);

        if ($this->filtroCliente) {
            $query->where('c.id', $this->filtroCliente);
        }
        if ($this->filtroTipoProyecto) {
            $query->where('tp.id', $this->filtroTipoProyecto);
        }
        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function ($q) use ($termino): void {
                $q->where('c.codigo_cliente', 'like', $termino)
                    ->orWhere('c.nombre', 'like', $termino)
                    ->orWhere('c.nombre_comercial', 'like', $termino)
                    ->orWhere('tp.nombre', 'like', $termino);
            });
        }

        // Orden: primario por columna pedida, secundario por la otra (estabilidad).
        if ($this->ordenColumna === 'tipo_proyecto') {
            $query->orderBy('tp.nombre', $this->ordenDireccion)->orderBy('c.nombre');
        } elseif ($this->ordenColumna === 'codigo_cliente') {
            $query->orderBy('c.codigo_cliente', $this->ordenDireccion)->orderBy('tp.nombre');
        } else {
            $query->orderBy('c.nombre', $this->ordenDireccion)->orderBy('tp.nombre');
        }

        $totalCombinaciones = (clone $query)->count();
        $paginaActual = max(1, (int) $this->paginaActual);
        $offset = ($paginaActual - 1) * $this->porPagina;

        $combinaciones = $query
            ->limit($this->porPagina)
            ->offset($offset)
            ->get();

        $paginador = new LengthAwarePaginator(
            items: $combinaciones,
            total: $totalCombinaciones,
            perPage: $this->porPagina,
            currentPage: $paginaActual,
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'pagina',
            ],
        );

        // Cargar tarifas reales solo para combinaciones visibles.
        $clienteIds = $combinaciones->pluck('cliente_id')->unique();
        $tipoIds = $combinaciones->pluck('tipo_proyecto_id')->unique();

        $tarifas = TarifaCliente::query()
            ->whereIn('cliente_id', $clienteIds)
            ->whereIn('tipo_proyecto_id', $tipoIds)
            ->get();

        $matriz = [];
        foreach ($tarifas as $t) {
            $matriz[$t->cliente_id][$t->tipo_proyecto_id][$t->atributo_id] = $t->importe;
        }

        return view('livewire.tarifas.clientes.index', [
            'paginador' => $paginador,
            'matriz' => $matriz,
        ]);
    }
}
