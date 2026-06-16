<?php

namespace App\Livewire\Tarifas\Clientes;

use App\Models\AtributoHora;
use App\Models\Cliente;
use App\Models\TarifaCliente;
use App\Models\TarifaHistorial;
use App\Models\TiposProyecto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Bloque embebido de Tarifas de un cliente.
 *
 * Se usa dentro de la ficha del cliente (pestaña "Tarifas") tanto en Editar
 * como en Ver. Replica el mismo dato que muestra /tarifas/clientes pero
 * filtrado por un único cliente.
 *
 * Modo:
 *   - $soloLectura = false → modo lectura por defecto + botón ✏️ Editar por
 *     fila (igual que la pantalla global). Edita las mismas columnas de
 *     `tarifas_cliente`, dispara `TarifaClienteObserver` para historial.
 *   - $soloLectura = true → solo muestra valores, sin acciones de edición.
 */
class Bloque extends Component
{
    public int $clienteId;

    public bool $soloLectura = false;

    /** Filas en modo edición: [tipo_proyecto_id => true] */
    public array $editando = [];

    /** Ediciones pendientes: [tipo_proyecto_id => [atributo_id => valor]] */
    public array $ediciones = [];

    /** Modal historial: tipo_proyecto_id abierto, null si cerrado. */
    public ?int $historialTipoProyectoId = null;

    public function mount(int $clienteId, bool $soloLectura = false): void
    {
        Gate::authorize('tarifas.ver');

        $this->clienteId = $clienteId;
        $this->soloLectura = $soloLectura;
    }

    /* ── Edición por fila ────────────────────────────────────── */

    public function editar(int $tipoProyectoId): void
    {
        if ($this->soloLectura) {
            return;
        }
        Gate::authorize('tarifas.editar_clientes');

        $this->editando[$tipoProyectoId] = true;

        $tarifas = TarifaCliente::where('cliente_id', $this->clienteId)
            ->where('tipo_proyecto_id', $tipoProyectoId)
            ->pluck('importe', 'atributo_id');

        $this->ediciones[$tipoProyectoId] = [];
        foreach (AtributoHora::pluck('id') as $atributoId) {
            $this->ediciones[$tipoProyectoId][$atributoId] = (float) ($tarifas[$atributoId] ?? 0);
        }
    }

    public function cancelarEdicion(int $tipoProyectoId): void
    {
        unset($this->editando[$tipoProyectoId], $this->ediciones[$tipoProyectoId]);
        $this->resetErrorBag();
    }

    public function guardar(int $tipoProyectoId): void
    {
        if ($this->soloLectura) {
            return;
        }
        Gate::authorize('tarifas.editar_clientes');

        if (isset($this->ediciones[$tipoProyectoId])) {
            $reglas = [];
            foreach ($this->ediciones[$tipoProyectoId] as $atributoId => $valor) {
                $reglas["ediciones.$tipoProyectoId.$atributoId"] = 'required|numeric|min:0|max:9999.999';
            }

            try {
                $this->validate($reglas);
            } catch (ValidationException $e) {
                throw $e;
            }

            DB::transaction(function () use ($tipoProyectoId): void {
                foreach ($this->ediciones[$tipoProyectoId] as $atributoId => $valor) {
                    $valorNumerico = $valor === '' || $valor === null ? 0 : (float) $valor;

                    TarifaCliente::updateOrCreate(
                        [
                            'cliente_id' => $this->clienteId,
                            'tipo_proyecto_id' => (int) $tipoProyectoId,
                            'atributo_id' => (int) $atributoId,
                        ],
                        [
                            'importe' => $valorNumerico,
                        ],
                    );
                }
            });

            session()->flash('status', 'Tarifas actualizadas.');
        }

        unset($this->editando[$tipoProyectoId], $this->ediciones[$tipoProyectoId]);
    }

    /* ── Modal historial ──────────────────────────────────────── */

    public function abrirHistorial(int $tipoProyectoId): void
    {
        Gate::authorize('tarifas.historial_ver');
        $this->historialTipoProyectoId = $tipoProyectoId;
    }

    public function cerrarHistorial(): void
    {
        $this->historialTipoProyectoId = null;
    }

    /* ── Computeds ────────────────────────────────────────────── */

    /** @return Collection<int, AtributoHora> */
    #[Computed]
    public function atributos(): Collection
    {
        return AtributoHora::query()->orderBy('orden')->get();
    }

    /** @return Collection<int, TarifaHistorial> */
    #[Computed]
    public function historialDelTipo(): Collection
    {
        if ($this->historialTipoProyectoId === null) {
            return collect();
        }

        $ids = TarifaCliente::where('cliente_id', $this->clienteId)
            ->where('tipo_proyecto_id', $this->historialTipoProyectoId)
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
        // Todos los tipos_proyectos activos (las filas de la matriz para este
        // cliente). Si el cliente nunca ha tenido tarifa para alguno, aparece
        // con importes a 0 (placeholder).
        $tiposProyecto = TiposProyecto::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $tipoIds = $tiposProyecto->pluck('id');

        $tarifas = TarifaCliente::query()
            ->where('cliente_id', $this->clienteId)
            ->whereIn('tipo_proyecto_id', $tipoIds)
            ->get();

        // Matriz[tipo_proyecto_id][atributo_id] => importe.
        $matriz = [];
        foreach ($tarifas as $t) {
            $matriz[$t->tipo_proyecto_id][$t->atributo_id] = $t->importe;
        }

        $tipoActual = null;
        if ($this->historialTipoProyectoId !== null) {
            $tipoActual = $tiposProyecto->firstWhere('id', $this->historialTipoProyectoId);
        }

        return view('livewire.tarifas.clientes.bloque', [
            'cliente' => Cliente::find($this->clienteId),
            'tiposProyecto' => $tiposProyecto,
            'matriz' => $matriz,
            'tipoActual' => $tipoActual,
        ]);
    }
}
