<?php

namespace App\Livewire\Configuracion;

use App\Models\Albaran;
use App\Models\ApiToken;
use App\Models\Borrador;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.web', ['active' => 'logs'])]
#[Title('Logs de auditoría')]
class Logs extends Component
{
    use WithPagination;

    #[Url] public string $busqueda    = '';
    #[Url] public string $filtroUsuario = '';
    #[Url] public string $filtroModelo  = '';
    #[Url] public string $filtroEvento  = '';
    #[Url] public string $fechaDesde    = '';
    #[Url] public string $fechaHasta    = '';

    /** Fuerza el re-render de los searchable-select al limpiar filtros. */
    public int $filtrosVersion = 0;

    public const MODELOS = [
        Albaran::class  => 'Albarán',
        Borrador::class => 'Borrador',
        Cliente::class  => 'Cliente',
        Proyecto::class => 'Proyecto',
        User::class     => 'Usuario',
        Material::class => 'Material',
        Empresa::class  => 'Empresa',
        ApiToken::class => 'Token API',
    ];

    public const EVENTOS = [
        'created'  => 'Creado',
        'updated'  => 'Editado',
        'deleted'  => 'Eliminado',
        'restored' => 'Restaurado',
        'login'    => 'Login',
        'logout'   => 'Logout',
    ];

    public function mount(): void
    {
        Gate::authorize('logs.ver');
    }

    public function updatingBusqueda(): void    { $this->resetPage(); }
    public function updatingFiltroUsuario(): void { $this->resetPage(); }
    public function updatingFiltroModelo(): void  { $this->resetPage(); }
    public function updatingFiltroEvento(): void  { $this->resetPage(); }
    public function updatingFechaDesde(): void    { $this->resetPage(); }
    public function updatingFechaHasta(): void    { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'filtroUsuario', 'filtroModelo', 'filtroEvento', 'fechaDesde', 'fechaHasta']);
        $this->filtrosVersion++;
        $this->resetPage();
    }

    #[Computed]
    public function logs(): LengthAwarePaginator
    {
        return Activity::query()
            ->with('causer')
            ->when($this->filtroUsuario, fn ($q) => $q->where('causer_id', $this->filtroUsuario)
                ->where('causer_type', User::class))
            ->when($this->filtroModelo, fn ($q) => $q->where('subject_type', $this->filtroModelo))
            ->when($this->filtroEvento, function ($q): void {
                // login/logout están en description, no en event
                if (in_array($this->filtroEvento, ['login', 'logout'], true)) {
                    $q->whereNull('event')->where('description', $this->filtroEvento);
                } else {
                    $q->where('event', $this->filtroEvento);
                }
            })
            ->when($this->busqueda, fn ($q) => $q->where('description', 'like', "%{$this->busqueda}%"))
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('created_at', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('created_at', '<=', $this->fechaHasta))
            ->orderByDesc('created_at')
            ->paginate(50);
    }

    #[Computed]
    public function usuarios(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->select('id', 'username', 'nombre', 'apellidos')
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get();
    }

    /** Etiqueta legible del tipo de evento. */
    public function etiquetaEvento(Activity $log): string
    {
        if ($log->event === null) {
            return self::EVENTOS[$log->description] ?? $log->description;
        }

        return self::EVENTOS[$log->event] ?? $log->event;
    }

    /** Clase CSS del badge de evento. */
    public function claseEvento(Activity $log): string
    {
        $evento = $log->event ?? $log->description;

        return match ($evento) {
            'created'  => 'bg-green-100 text-green-800 border border-green-200',
            'updated'  => 'bg-amber-100 text-amber-800 border border-amber-200',
            'deleted'  => 'bg-red-100 text-red-800 border border-red-200',
            'restored' => 'bg-blue-100 text-blue-800 border border-blue-200',
            default    => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    /** IP registrada en la actividad (o null si no se guardó). */
    public function ip(Activity $log): ?string
    {
        return $log->properties->get('ip');
    }

    /** Navegador + SO legible a partir del user agent registrado. */
    public function navegador(Activity $log): ?string
    {
        $ua = $log->properties->get('user_agent');
        if (! $ua) {
            return null;
        }

        $navegador = match (true) {
            str_contains($ua, 'Edg')                                  => 'Edge',
            str_contains($ua, 'OPR') || str_contains($ua, 'Opera')    => 'Opera',
            str_contains($ua, 'Chrome')                               => 'Chrome',
            str_contains($ua, 'Firefox')                              => 'Firefox',
            str_contains($ua, 'Safari')                               => 'Safari',
            default                                                   => 'Otro',
        };

        $so = match (true) {
            str_contains($ua, 'Windows')                              => 'Windows',
            str_contains($ua, 'Android')                              => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')  => 'iOS',
            str_contains($ua, 'Mac OS')                               => 'macOS',
            str_contains($ua, 'Linux')                                => 'Linux',
            default                                                   => '',
        };

        return trim($navegador.($so !== '' ? ' · '.$so : ''));
    }

    /** Etiqueta legible del tipo de entidad. */
    public function etiquetaModelo(?string $class): string
    {
        if ($class === null) {
            return '—';
        }

        return self::MODELOS[$class] ?? class_basename($class);
    }

    /** Cambios formateados para mostrar en la fila (solo para 'updated'). */
    public function cambios(Activity $log): array
    {
        if ($log->event !== 'updated') {
            return [];
        }

        $old        = $log->properties->get('old', []);
        $attributes = $log->properties->get('attributes', []);
        $resultado  = [];

        foreach ($attributes as $campo => $nuevo) {
            $anterior = $old[$campo] ?? null;
            if ($anterior !== $nuevo) {
                $resultado[] = [
                    'campo'    => $campo,
                    'anterior' => $this->formatearValor($anterior),
                    'nuevo'    => $this->formatearValor($nuevo),
                ];
            }
        }

        return $resultado;
    }

    private function formatearValor(mixed $valor): string
    {
        if ($valor === null) {
            return '—';
        }
        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        return (string) $valor;
    }

    public function render(): View
    {
        return view('livewire.configuracion.logs', [
            'modelos' => self::MODELOS,
            'eventos' => self::EVENTOS,
        ]);
    }
}
