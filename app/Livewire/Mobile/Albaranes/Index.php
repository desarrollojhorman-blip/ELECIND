<?php

namespace App\Livewire\Mobile\Albaranes;

use App\Models\Albaran;
use App\Models\Borrador;
use App\Models\Parte;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /** Filtros: todos | borrador | parte | pendiente_firma | firmado | facturado */
    #[Url(as: 'estado')]
    public string $filtroEstado = 'todos';

    #[Url(as: 'q')]
    public string $buscar = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Albaran::class);
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function limpiarBuscar(): void
    {
        $this->buscar = '';
        $this->resetPage();
    }

    /**
     * Lista combinada de albaranes + borradores, normalizada y paginada.
     *
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function items(): LengthAwarePaginator
    {
        $userId           = (int) Auth::id();
        $puedeVerTodosAlb = Auth::user()?->can('albaranes.ver_todos') ?? false;
        $puedeVerTodosBor = Auth::user()?->can('borradores.ver_todos') ?? false;

        $incluirAlbaranes  = in_array($this->filtroEstado, ['todos', 'albaran', 'pendiente_firma', 'firmado', 'facturado'], true);
        $incluirBorradores = in_array($this->filtroEstado, ['todos', 'borrador'], true);
        $incluirPartes     = in_array($this->filtroEstado, ['todos', 'parte'], true);

        $items = collect();

        if ($incluirAlbaranes) {
            $albaranes = Albaran::query()
                ->with(['cliente:id,nombre', 'proyecto:id,nombre', 'parte:id,numero,albaran_id'])
                ->when(! $puedeVerTodosAlb, fn (Builder $q) => $q->where(function (Builder $qq) use ($userId): void {
                    $qq->where('creado_por', $userId)
                        ->orWhereHas('lineasPersonal', fn (Builder $qp) => $qp->where('trabajador_id', $userId));
                }))
                ->when(
                    in_array($this->filtroEstado, ['pendiente_firma', 'firmado', 'facturado'], true),
                    fn (Builder $q) => $q->where('estado', $this->filtroEstado),
                )
                ->get()
                ->map(fn (Albaran $a): array => [
                    'tipo'        => 'albaran',
                    'numero'      => $a->numero,
                    'cliente'     => $a->cliente?->nombre,
                    'proyecto'    => $a->proyecto?->nombre,
                    'estadoLabel' => $a->estado->etiqueta(),
                    'estadoTone'  => $a->estado->tono(),
                    'fecha'       => $a->fecha,
                    'origen'      => $a->parte?->numero ? 'Origen: '.$a->parte->numero : null,
                    'url'         => route('mobile.albaranes.firmar', ['albaran' => $a->id]),
                ]);

            $items = $items->concat($albaranes);
        }

        if ($incluirBorradores) {
            $borradores = Borrador::query()
                ->with(['cliente:id,nombre', 'proyecto:id,nombre'])
                ->where('estado', '!=', 'convertido')
                ->when(! $puedeVerTodosBor, fn (Builder $q) => $q->where(function (Builder $qq) use ($userId): void {
                    $qq->where('creado_por', $userId)
                        ->orWhereHas('lineasPersonal', fn (Builder $qp) => $qp->where('trabajador_id', $userId));
                }))
                ->get()
                ->map(function (Borrador $b): array {
                    $convertido = $b->estado === 'convertido';

                    return [
                        'tipo'        => 'borrador',
                        'numero'      => $b->numero_borrador,
                        'cliente'     => ($n = $b->clienteNombre()) === '—' ? null : $n,
                        'proyecto'    => ($p = $b->proyectoNombre()) === '—' ? null : $p,
                        'estadoLabel' => $convertido ? 'Convertido' : 'Borrador',
                        'estadoTone'  => $convertido ? 'success' : 'neutral',
                        'fecha'       => $b->fecha,
                        'origen'      => null,
                        'url'         => route('mobile.borradores.ver', ['borrador' => $b->id]),
                    ];
                });

            $items = $items->concat($borradores);
        }

        if ($incluirPartes) {
            $partes = Parte::query()
                ->with(['cliente:id,nombre', 'proyecto:id,nombre', 'borradorOrigen:id,numero_borrador,convertido_a_parte_id'])
                ->whereNull('albaran_id')
                ->where(function (Builder $q) use ($userId): void {
                    $q->where('creado_por', $userId)
                        ->orWhereHas('lineasPersonal', fn (Builder $qp) => $qp->where('trabajador_id', $userId));
                })
                ->get()
                ->map(fn (Parte $p): array => [
                    'tipo'        => 'parte',
                    'numero'      => $p->numero,
                    'cliente'     => $p->cliente?->nombre,
                    'proyecto'    => $p->proyecto?->nombre,
                    'estadoLabel' => $p->estado === Parte::ESTADO_CERRADO ? 'Cerrado' : 'Abierto',
                    'estadoTone'  => $p->estado === Parte::ESTADO_CERRADO ? 'neutral' : 'info',
                    'fecha'       => $p->fecha,
                    'origen'      => $p->borradorOrigen?->numero_borrador ? 'Origen: '.$p->borradorOrigen->numero_borrador : null,
                    'url'         => route('mobile.partes.ver', ['parte' => $p->id]),
                ]);

            $items = $items->concat($partes);
        }

        if ($this->buscar !== '') {
            $termino = mb_strtolower(trim($this->buscar));
            $items = $items->filter(fn (array $i): bool =>
                str_contains(mb_strtolower((string) $i['numero']), $termino)
                || str_contains(mb_strtolower((string) ($i['cliente'] ?? '')), $termino)
                || str_contains(mb_strtolower((string) ($i['proyecto'] ?? '')), $termino)
                || str_contains(mb_strtolower((string) ($i['estadoLabel'] ?? '')), $termino)
                || str_contains(mb_strtolower((string) ($i['origen'] ?? '')), $termino));
        }

        $ordenados = $items
            ->sortByDesc(fn (array $i) => $i['fecha'])
            ->values();

        $page    = $this->getPage();
        $perPage = 20;

        return new LengthAwarePaginator(
            $ordenados->forPage($page, $perPage)->values(),
            $ordenados->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page'],
        );
    }

    public function render(): View
    {
        return view('livewire.mobile.albaranes.index')
            ->layout('components.layouts.mobile', [
                'title'     => 'Gestión de partes',
                'showBack'  => true,
                'backRoute' => route('mobile.dashboard'),
            ]);
    }
}
