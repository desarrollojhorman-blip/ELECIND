<?php

namespace App\Livewire\Borradores;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\Borrador;
use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Parte;
use App\Models\Proyecto;
use App\Models\Role;
use App\Models\User;
use App\Rules\PasswordPolicy;
use App\Services\NumeracionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'borradores'])]
#[Title('Convertir borrador a albarán')]
class Convertir extends Component
{
    public Borrador $borrador;

    /**
     * Paso actual del asistente.
     *   1 Cliente · 2 Proyecto · 3 Concepto · 4 Responsable · 5 Confirmar
     *
     * Materiales y trabajadores NO se gestionan aquí: se copian tal cual del
     * borrador (las líneas con FK real) y lo que el trabajador escribió en
     * texto libre se vuelca al campo observaciones del albarán para que el
     * admin lo gestione después desde "Albaranes".
     */
    public int $paso = 1;
    public int $pasoFinal = 5;

    // ── Paso 1 · Cliente ────────────────────────────────────────────────
    /** 'elegir' | 'crear' */
    public string $clienteModo = 'elegir';
    public ?int   $clienteIdElegido    = null;
    public string $clienteNombreNuevo  = '';

    // ── Paso 2 · Proyecto ───────────────────────────────────────────────
    public string $proyectoModo = 'elegir';
    public ?int   $proyectoIdElegido    = null;
    public string $proyectoNombreNuevo  = '';

    // ── Paso 3 · Concepto ───────────────────────────────────────────────
    public string $conceptoModo = 'elegir';
    public ?int   $conceptoIdElegido    = null;
    public string $conceptoNombreNuevo  = '';

    // ── Paso 4 · Responsable (opcional) ─────────────────────────────────
    /** 'ninguno' | 'elegir' | 'crear' */
    public string $responsableModo         = 'ninguno';
    public ?int   $responsableIdElegido    = null;
    public string $responsableUsuarioNuevo  = '';
    public string $responsablePasswordNuevo = '';
    public string $responsableNombreNuevo   = '';
    public string $responsableEmailNuevo    = '';

    public function mount(Borrador $borrador): void
    {
        Gate::authorize('convertir', $borrador);

        if ($borrador->estado === 'convertido') {
            session()->flash('error', 'Este borrador ya fue convertido a albarán.');
            $this->redirectRoute('borradores.ver', ['borrador' => $borrador->getKey()]);
            return;
        }

        $this->borrador = $borrador->load([
            'cliente', 'proyecto', 'concepto', 'responsable',
            'lineasPersonal', 'lineasMaterial', 'creador',
        ]);

        // Pre-rellenar si el borrador ya trae FK reales
        if ($this->borrador->cliente_id) {
            $this->clienteIdElegido = $this->borrador->cliente_id;
        }
        if ($this->borrador->proyecto_id) {
            $this->proyectoIdElegido = $this->borrador->proyecto_id;
        }
        if ($this->borrador->concepto_id) {
            $this->conceptoIdElegido = $this->borrador->concepto_id;
        }
        if ($this->borrador->responsable_id) {
            $this->responsableIdElegido = $this->borrador->responsable_id;
            $this->responsableModo      = 'elegir';
        }
    }

    // ── Catálogos ───────────────────────────────────────────────────────

    /** @return Collection<int, Cliente> */
    #[Computed]
    public function clientesDisponibles(): Collection
    {
        return Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo_cliente']);
    }

    /** @return Collection<int, Concepto> */
    #[Computed]
    public function conceptosDisponibles(): Collection
    {
        return Concepto::query()->orderBy('nombre')->get(['id', 'nombre']);
    }

    /**
     * Proyectos del cliente RESUELTO (solo si está en modo "elegir"). Si el
     * cliente es nuevo no hay proyectos previos posibles — un proyecto solo
     * puede tener un cliente, así que los proyectos existentes pertenecen a
     * otros clientes y no se pueden reasignar desde aquí.
     *
     * @return Collection<int, Proyecto>
     */
    #[Computed]
    public function proyectosDelCliente(): Collection
    {
        if ($this->clienteModo !== 'elegir' || ! $this->clienteIdElegido) {
            return new Collection();
        }

        return Proyecto::query()
            ->where('cliente_id', (int) $this->clienteIdElegido)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);
    }

    /* ─────────────────── PASO 3 · Conceptos ─────────────────── */

    /** Conceptos ya asociados al proyecto resuelto (si existe). */
    #[Computed]
    public function conceptosDelProyecto(): Collection
    {
        if ($this->proyectoModo !== 'elegir' || ! $this->proyectoIdElegido) {
            return new Collection();
        }

        $proyecto = Proyecto::with('conceptos:id,nombre')->find($this->proyectoIdElegido);

        return $proyecto?->conceptos ?? new Collection();
    }

    /** Conceptos del catálogo que NO están asociados al proyecto resuelto. */
    #[Computed]
    public function otrosConceptos(): Collection
    {
        $idsAsociados = $this->conceptosDelProyecto->pluck('id')->all();

        return Concepto::query()
            ->whereNotIn('id', $idsAsociados)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /* ─────────────────── PASO 4 · Responsables ─────────────────── */

    /**
     * Responsables del cliente elegido que ya están en el proyecto resuelto.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesDelClienteEnElProyecto(): Collection
    {
        if ($this->clienteModo !== 'elegir' || ! $this->clienteIdElegido) {
            return new Collection();
        }

        if ($this->proyectoModo !== 'elegir' || ! $this->proyectoIdElegido) {
            return new Collection();
        }

        return User::query()
            ->where('cliente_id', (int) $this->clienteIdElegido)
            ->where('tipo_usuario', 'externo')
            ->where('activo', true)
            ->whereHas('proyectos', function ($q): void {
                $q->where('proyectos.id', (int) $this->proyectoIdElegido)
                    ->where('proyecto_usuario.rol_en_proyecto', 'responsable');
            })
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);
    }

    /**
     * Responsables del cliente que NO están en el proyecto (o todos los del
     * cliente si el proyecto es nuevo).
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function responsablesDelCliente(): Collection
    {
        if ($this->clienteModo !== 'elegir' || ! $this->clienteIdElegido) {
            return new Collection();
        }

        $idsEnProyecto = $this->responsablesDelClienteEnElProyecto->pluck('id')->all();

        return User::query()
            ->where('cliente_id', (int) $this->clienteIdElegido)
            ->where('tipo_usuario', 'externo')
            ->where('activo', true)
            ->whereNotIn('id', $idsEnProyecto)
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos']);
    }

    // ── Navegación ──────────────────────────────────────────────────────

    public function siguiente(): void
    {
        $this->validarPaso($this->paso);
        $this->paso = min($this->paso + 1, $this->pasoFinal);
    }

    public function atras(): void
    {
        $this->paso = max($this->paso - 1, 1);
    }

    public function irAPaso(int $paso): void
    {
        if ($paso >= 1 && $paso < $this->paso) {
            $this->paso = $paso;
        }
    }

    private function validarPaso(int $paso): void
    {
        match ($paso) {
            1 => $this->validarCliente(),
            2 => $this->validarProyecto(),
            3 => $this->validarConcepto(),
            4 => $this->validarResponsable(),
            default => null,
        };
    }

    private function validarCliente(): void
    {
        if ($this->clienteModo === 'elegir') {
            $this->validate([
                'clienteIdElegido' => ['required', 'integer', 'exists:clientes,id'],
            ], [
                'clienteIdElegido.required' => 'Elige un cliente existente o cámbiate a "Crear nuevo".',
            ]);
            return;
        }

        Gate::authorize('create', Cliente::class);
        $this->validate([
            'clienteNombreNuevo' => ['required', 'string', 'max:255'],
        ], [
            'clienteNombreNuevo.required' => 'Escribe el nombre del cliente nuevo.',
        ]);
    }

    private function validarProyecto(): void
    {
        // Si el cliente es nuevo, no hay proyectos previos posibles: siempre
        // toca crear. Forzamos el modo "crear" para evitar estados raros.
        if ($this->clienteModo === 'crear') {
            $this->proyectoModo = 'crear';
        }

        if ($this->proyectoModo === 'elegir') {
            $this->validate([
                'proyectoIdElegido' => ['required', 'integer', 'exists:proyectos,id'],
            ], [
                'proyectoIdElegido.required' => 'Elige un proyecto del cliente o cámbiate a "Crear nuevo".',
            ]);
            return;
        }

        Gate::authorize('create', Proyecto::class);
        $this->validate([
            'proyectoNombreNuevo' => ['required', 'string', 'max:255'],
        ], [
            'proyectoNombreNuevo.required' => 'Escribe el nombre del proyecto nuevo.',
        ]);
    }

    private function validarConcepto(): void
    {
        if ($this->conceptoModo === 'elegir') {
            $this->validate([
                'conceptoIdElegido' => ['required', 'integer', 'exists:conceptos,id'],
            ], [
                'conceptoIdElegido.required' => 'Elige un concepto o cámbiate a "Crear nuevo".',
            ]);
            return;
        }

        Gate::authorize('create', Concepto::class);
        $this->validate([
            'conceptoNombreNuevo' => ['required', 'string', 'max:255'],
        ], [
            'conceptoNombreNuevo.required' => 'Escribe el nombre del concepto nuevo.',
        ]);
    }

    private function validarResponsable(): void
    {
        if ($this->responsableModo === 'ninguno') {
            return;
        }

        if ($this->responsableModo === 'elegir') {
            $this->validate([
                'responsableIdElegido' => ['required', 'integer', 'exists:users,id'],
            ], [
                'responsableIdElegido.required' => 'Elige un responsable o cambia a otra opción.',
            ]);
            return;
        }

        // crear
        $this->validate([
            'responsableUsuarioNuevo'  => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'responsablePasswordNuevo' => [
                'required', 'string', 'max:100',
                new PasswordPolicy([$this->responsableUsuarioNuevo, $this->responsableNombreNuevo]),
            ],
            'responsableNombreNuevo'   => ['required', 'string', 'max:100'],
            'responsableEmailNuevo'    => ['nullable', 'email', 'max:150'],
        ], [
            'responsableUsuarioNuevo.unique' => 'Ese nombre de usuario ya existe.',
            'responsableUsuarioNuevo.required' => 'Indica el nombre de usuario.',
            'responsableUsuarioNuevo.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'responsablePasswordNuevo.required' => 'Indica una contraseña.',
            'responsableNombreNuevo.required' => 'Indica el nombre del responsable.',
        ]);
    }

    // ── Commit final ────────────────────────────────────────────────────

    public function confirmar(): void
    {
        Gate::authorize('convertir', $this->borrador);

        $this->validarCliente();
        $this->validarProyecto();
        $this->validarConcepto();
        $this->validarResponsable();

        DB::transaction(function (): void {
            // 1. Cliente
            $clienteId = $this->clienteModo === 'elegir'
                ? (int) $this->clienteIdElegido
                : Cliente::create([
                    'codigo_cliente' => app(NumeracionService::class)->siguienteNumeroCliente(),
                    'nombre'         => trim($this->clienteNombreNuevo),
                    'activo'         => true,
                ])->id;

            // 2. Proyecto
            if ($this->proyectoModo === 'elegir') {
                $proyectoId = (int) $this->proyectoIdElegido;
            } else {
                $num = app(NumeracionService::class)->siguienteProyecto();
                $proyectoId = Proyecto::create([
                    'cliente_id'        => $clienteId,
                    'nombre'            => trim($this->proyectoNombreNuevo),
                    'codigo'            => $num['codigo'],
                    'codigo_secuencial' => $num['secuencial'],
                    'estado'            => 'activo',
                ])->id;
            }

            /** @var Proyecto $proyecto */
            $proyecto = Proyecto::findOrFail($proyectoId);

            // 3. Concepto + asociar al proyecto
            $conceptoId = $this->conceptoModo === 'elegir'
                ? (int) $this->conceptoIdElegido
                : Concepto::create([
                    'nombre' => trim($this->conceptoNombreNuevo),
                    'activo' => true,
                ])->id;

            $proyecto->conceptos()->syncWithoutDetaching([$conceptoId]);

            // 4. Responsable (opcional) → crear si toca + asociar al proyecto
            $responsableId = null;
            if ($this->responsableModo === 'elegir') {
                $responsableId = (int) $this->responsableIdElegido;
            } elseif ($this->responsableModo === 'crear') {
                $user = User::create([
                    'username'     => trim($this->responsableUsuarioNuevo),
                    'password'     => Hash::make($this->responsablePasswordNuevo),
                    'nombre'       => trim($this->responsableNombreNuevo),
                    'apellidos'    => '',
                    'email'        => $this->responsableEmailNuevo !== '' ? trim($this->responsableEmailNuevo) : null,
                    'tipo_usuario' => 'externo',
                    'cliente_id'   => $clienteId,
                    'activo'       => true,
                ]);

                $rol = Role::query()->where('name', 'responsable')->first();
                if ($rol !== null) {
                    $user->assignRole($rol);
                }

                $responsableId = $user->id;
            }

            if ($responsableId !== null) {
                $proyecto->usuarios()->syncWithoutDetaching([
                    $responsableId => ['rol_en_proyecto' => 'responsable'],
                ]);
            }

            // 5. Componer observaciones (texto del borrador + avisos de texto libre).
            $observaciones = $this->componerObservaciones();

            $fecha     = Carbon::parse($this->borrador->fecha);
            $tipoHora  = $this->borrador->tipo_hora;
            $plusReten = (bool) $this->borrador->tiene_plus_retencion;

            if ($this->borrador->crear_albaran) {
                // ── Crear Albarán ──────────────────────────────────────────
                $albaran = new Albaran;
                $albaran->numero                   = app(NumeracionService::class)->siguienteNumeroAlbaran($fecha);
                $albaran->creado_por               = (int) Auth::id();
                $albaran->estado                   = EstadoAlbaran::PENDIENTE_FIRMA;
                $albaran->fecha                    = $fecha;
                $albaran->cliente_id               = $clienteId;
                $albaran->proyecto_id              = $proyectoId;
                $albaran->concepto_id              = $conceptoId;
                $albaran->responsable_id           = $responsableId;
                $albaran->firma_trabajador_user_id = $this->borrador->creado_por;
                $albaran->tipo_hora                = $tipoHora;
                $albaran->tiene_plus_retencion     = $plusReten;
                $albaran->observaciones            = $observaciones;
                $albaran->save();

                foreach ($this->borrador->lineasPersonal as $linea) {
                    if ($linea->trabajador_id !== null) {
                        $albaran->lineasPersonal()->create([
                            'trabajador_id' => $linea->trabajador_id,
                            'horas'         => $linea->horas,
                            'horas_extra'   => $linea->horas_extra,
                        ]);
                    }
                }
                foreach ($this->borrador->lineasMaterial as $linea) {
                    if ($linea->material_id !== null) {
                        $albaran->lineasMaterial()->create([
                            'material_id' => $linea->material_id,
                            'cantidad'    => $linea->cantidad,
                        ]);
                    }
                }

                $this->borrador->update([
                    'estado'                  => 'convertido',
                    'convertido_a_albaran_id' => $albaran->id,
                ]);

                session()->flash('status', "Borrador convertido. Albarán «{$albaran->numero}» creado correctamente.");
                $this->redirectRoute('albaranes.editar', ['albaran' => $albaran->getKey()], navigate: true);
            } else {
                // ── Crear Parte ────────────────────────────────────────────
                $parte = new Parte;
                $parte->creado_por            = $this->borrador->creado_por ?? (int) Auth::id();
                $parte->estado                = Parte::ESTADO_ABIERTO;
                $parte->fecha                 = $fecha;
                $parte->cliente_id            = $clienteId;
                $parte->proyecto_id           = $proyectoId;
                $parte->concepto_id           = $conceptoId;
                $parte->responsable_id        = $responsableId;
                $parte->tipo_hora             = $tipoHora instanceof TipoHora ? $tipoHora->value : (string) $tipoHora;
                $parte->tiene_plus_retencion  = $plusReten;
                $parte->observaciones         = $observaciones;
                $parte->save();

                foreach ($this->borrador->lineasPersonal as $linea) {
                    if ($linea->trabajador_id !== null) {
                        $parte->lineasPersonal()->create([
                            'trabajador_id' => $linea->trabajador_id,
                            'horas'         => $linea->horas,
                            'horas_extra'   => $linea->horas_extra,
                        ]);
                    }
                }
                foreach ($this->borrador->lineasMaterial as $linea) {
                    if ($linea->material_id !== null) {
                        $parte->lineasMaterial()->create([
                            'material_id' => $linea->material_id,
                            'cantidad'    => $linea->cantidad,
                        ]);
                    }
                }

                $this->borrador->update([
                    'estado'                 => 'convertido',
                    'convertido_a_parte_id'  => $parte->id,
                ]);

                session()->flash('status', "Borrador convertido. Parte «{$parte->numero}» creado correctamente.");
                $this->redirectRoute('partes.editar', ['parte' => $parte->getKey()], navigate: true);
            }
        });
    }

    /**
     * Compone el campo observaciones del albarán resultante a partir de:
     *   - Observaciones que ya traía el borrador (lo que escribió el trabajador).
     *   - Aviso por cada línea de material en texto libre — sin asignar.
     *   - Aviso por cada línea de personal en texto libre — sin asignar.
     * El admin podrá editar las líneas reales después desde Albaranes.
     */
    private function componerObservaciones(): ?string
    {
        $bloques = [];

        $obsBorrador = trim((string) $this->borrador->observaciones);
        if ($obsBorrador !== '') {
            $bloques[] = $obsBorrador;
        }

        $materialesTextoLibre = $this->borrador->lineasMaterial
            ->filter(fn ($l) => $l->material_id === null && trim((string) $l->material_texto) !== '')
            ->map(fn ($l): string => '⚠ Material en texto libre: «'.trim($l->material_texto).'» — sin asignar.')
            ->all();

        $trabajadoresTextoLibre = $this->borrador->lineasPersonal
            ->filter(fn ($l) => $l->trabajador_id === null && trim((string) $l->trabajador_texto) !== '')
            ->map(fn ($l): string => '⚠ Trabajador en texto libre: «'.trim($l->trabajador_texto).'» — sin asignar.')
            ->all();

        if (! empty($materialesTextoLibre)) {
            $bloques[] = implode("\n", $materialesTextoLibre);
        }
        if (! empty($trabajadoresTextoLibre)) {
            $bloques[] = implode("\n", $trabajadoresTextoLibre);
        }

        if (empty($bloques)) {
            return null;
        }

        return implode("\n\n", $bloques);
    }

    public function render(): View
    {
        return view('livewire.borradores.convertir');
    }
}
