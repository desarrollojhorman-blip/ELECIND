<?php

namespace App\Livewire\Tarifas\Trabajadores;

use App\Models\AtributoHora;
use App\Models\Role;
use App\Models\TarifaHistorial;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tarifas → Trabajadores.
 *
 * Mismo patrón que Tarifas → Clientes:
 *   - Filas autogeneradas (todos los users no eliminados).
 *   - Modo lectura por defecto en cada fila. Botón ✏️ Editar activa los
 *     inputs; ✓ Guardar persiste y sale de edición; ❌ Cancelar descarta.
 *   - Formato de importes sin ceros redundantes.
 *
 * Los importes se persisten en las columnas tasa_* de `users`. El
 * UserTasasObserver registra automáticamente cada cambio en
 * `tarifas_historial` con tipo='trabajador'.
 */
#[Layout('components.layouts.web', ['active' => 'tarifas_trabajadores'])]
#[Title('Tarifas — Trabajadores')]
class Index extends Component
{
    use WithPagination;

    /** Las 8 tasas del trabajador en orden de presentación. */
    public const CAMPOS_TASA = [
        'tasa_hora',
        'tasa_lab_noche',
        'tasa_festivo',
        'tasa_fest_noche',
        'tasa_extra',
        'tasa_ex_lab_noc',
        'tasa_ex_fes',
        'tasa_ex_fes_noct',
    ];

    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'rol')]
    public string $filtroRol = '';

    /**
     * Filtro por ID de usuario (vía query string ?usuario=N). Cuando viene
     * desde la ficha de un usuario, queremos enfocar a ese trabajador y
     * abrirlo directamente en modo edición.
     */
    #[Url(as: 'usuario')]
    public ?int $filtroUsuarioId = null;

    #[Url(as: 'orden')]
    public string $ordenColumna = 'apellidos';

    #[Url(as: 'dir')]
    public string $ordenDireccion = 'asc';

    #[Url(as: 'pp')]
    public int $porPagina = 25;

    /** Filas en modo edición: [userId => true] */
    public array $editando = [];

    /** Ediciones pendientes: [userId => [campo => valor, ...]] */
    public array $ediciones = [];

    /** Modal de historial: id de usuario abierto, null si cerrado. */
    public ?int $historialUserId = null;

    public function mount(): void
    {
        Gate::authorize('tarifas.ver');

        // Si la URL trae ?usuario={id} y el usuario existe + es visible para
        // el actor (interno + nivel <=), entra automáticamente en modo edición
        // sobre esa fila. Se usa al pulsar "Editar tarifas" desde la ficha
        // de un usuario.
        if ($this->filtroUsuarioId !== null && auth()->user()?->can('tarifas.editar_trabajadores')) {
            $existeYVisible = $this->queryUsuariosVisibles()
                ->where('users.id', $this->filtroUsuarioId)
                ->exists();

            if ($existeYVisible) {
                $this->editar($this->filtroUsuarioId);
            }
        }
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroRol(): void
    {
        $this->resetPage();
    }

    public function updatedPorPagina(): void
    {
        $this->resetPage();
    }

    public function ordenarPor(string $columna): void
    {
        $permitidas = ['apellidos', 'numero_empleado', ...self::CAMPOS_TASA];
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

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->filtroRol = '';
        $this->resetPage();
    }

    /* ── Edición por fila ────────────────────────────────────── */

    /** Entra una fila en modo edición. Precarga las 8 tasas actuales. */
    public function editar(int $userId): void
    {
        Gate::authorize('tarifas.editar_trabajadores');

        $this->editando[$userId] = true;

        $user = User::select(['id', ...self::CAMPOS_TASA])->findOrFail($userId);

        $this->ediciones[$userId] = [];
        foreach (self::CAMPOS_TASA as $campo) {
            $this->ediciones[$userId][$campo] = (float) $user->{$campo};
        }
    }

    public function cancelarEdicion(int $userId): void
    {
        unset($this->editando[$userId], $this->ediciones[$userId]);
        $this->resetErrorBag();
    }

    public function guardar(int $userId): void
    {
        Gate::authorize('tarifas.editar_trabajadores');

        if (isset($this->ediciones[$userId])) {
            $reglas = [];
            foreach (self::CAMPOS_TASA as $campo) {
                if (! array_key_exists($campo, $this->ediciones[$userId])) {
                    continue;
                }
                $reglas["ediciones.$userId.$campo"] = 'required|numeric|min:0|max:9999.999';
            }

            try {
                $this->validate($reglas);
            } catch (ValidationException $e) {
                throw $e;
            }

            $user = User::findOrFail($userId);
            foreach (self::CAMPOS_TASA as $campo) {
                if (! array_key_exists($campo, $this->ediciones[$userId])) {
                    continue;
                }
                $valor = $this->ediciones[$userId][$campo];
                $user->{$campo} = $valor === '' || $valor === null ? 0 : (float) $valor;
            }
            $user->save();

            session()->flash('status', "Tasas de {$user->nombre} {$user->apellidos} actualizadas.");
        }

        unset($this->editando[$userId], $this->ediciones[$userId]);
    }

    /* ── Modal de historial ───────────────────────────────────── */

    public function abrirHistorial(int $userId): void
    {
        Gate::authorize('tarifas.historial_ver');
        $this->historialUserId = $userId;
    }

    public function cerrarHistorial(): void
    {
        $this->historialUserId = null;
    }

    /* ── Computeds ────────────────────────────────────────────── */

    /** @return \Illuminate\Support\Collection<int, AtributoHora> */
    #[Computed]
    public function atributosHora(): \Illuminate\Support\Collection
    {
        return AtributoHora::query()
            ->horas()
            ->orderBy('orden')
            ->get();
    }

    /**
     * Roles disponibles para el filtro:
     *   - solo INTERNOS (no externos),
     *   - solo los que el usuario actual puede ver (nivel <= propio).
     *
     * @return Collection<int, Role>
     */
    #[Computed]
    public function rolesDisponibles(): Collection
    {
        $nivelActual = auth()->user()?->nivelMaximo() ?? 0;

        return Role::query()
            ->where('es_externo', false)
            ->where('nivel', '<=', $nivelActual)
            ->orderByDesc('nivel')
            ->get(['id', 'name', 'etiqueta', 'nivel']);
    }

    /** @return \Illuminate\Support\Collection<int, TarifaHistorial> */
    #[Computed]
    public function historialDelUsuario(): \Illuminate\Support\Collection
    {
        if ($this->historialUserId === null) {
            return collect();
        }

        return TarifaHistorial::query()
            ->trabajadores()
            ->where('referencia_id', $this->historialUserId)
            ->with(['atributo:id,codigo,nombre_corto', 'cambiadoPor:id,nombre,apellidos'])
            ->latest()
            ->limit(50)
            ->get();
    }

    /** @return User|null */
    #[Computed]
    public function usuarioHistorial(): ?User
    {
        if ($this->historialUserId === null) {
            return null;
        }

        return User::find($this->historialUserId);
    }

    /**
     * Query base de usuarios visibles para el actor actual:
     *   - no eliminados,
     *   - activos,
     *   - internos (ningún rol con es_externo=true),
     *   - de nivel <= al propio (jerarquía).
     *
     * Sin filtros de búsqueda ni de rol — esos se aplican en render().
     */
    private function queryUsuariosVisibles(): Builder
    {
        $nivelActual = auth()->user()?->nivelMaximo() ?? 0;

        return User::query()
            ->select(['users.id', 'username', 'nombre', 'apellidos', 'numero_empleado', ...self::CAMPOS_TASA])
            ->whereNull('deleted_at')
            ->where('activo', true)
            ->whereDoesntHave('roles', function ($q): void {
                $q->where('es_externo', true);
            })
            ->whereDoesntHave('roles', function ($q) use ($nivelActual): void {
                $q->where('nivel', '>', $nivelActual);
            });
    }

    public function render(): View
    {
        $query = $this->queryUsuariosVisibles();

        // Si la URL trae ?usuario={id}, restringe SOLO a ese usuario (la
        // fila se abrió en mount() en modo edición).
        if ($this->filtroUsuarioId !== null) {
            $query->where('users.id', $this->filtroUsuarioId);
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('apellidos', 'like', $termino)
                    ->orWhere('username', 'like', $termino)
                    ->orWhere('numero_empleado', 'like', $termino);
            });
        }

        if ($this->filtroRol !== '') {
            $query->whereHas('roles', function ($q): void {
                $q->where('name', $this->filtroRol);
            });
        }

        $query->orderBy($this->ordenColumna, $this->ordenDireccion);
        if ($this->ordenColumna !== 'apellidos') {
            $query->orderBy('apellidos', 'asc');
        }

        return view('livewire.tarifas.trabajadores.index', [
            'usuarios' => $query->paginate($this->porPagina)->onEachSide(2),
        ]);
    }
}
