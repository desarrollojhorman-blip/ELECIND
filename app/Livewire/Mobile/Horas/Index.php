<?php

namespace App\Livewire\Mobile\Horas;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    public function mount(): void
    {
        if ($this->fechaDesde === '') {
            $this->fechaDesde = now()->startOfMonth()->format('Y-m-d');
        }
        if ($this->fechaHasta === '') {
            $this->fechaHasta = now()->endOfMonth()->format('Y-m-d');
        }
    }

    public function render(): View
    {
        $userId = (int) Auth::id();

        $lineas = DB::table('albaran_lineas_personal as alp')
            ->join('albaranes as a', 'a.id', '=', 'alp.albaran_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->leftJoin('proyectos as p', 'p.id', '=', 'a.proyecto_id')
            ->leftJoin('conceptos as con', 'con.id', '=', 'a.concepto_id')
            ->whereNull('a.deleted_at')
            ->where('alp.trabajador_id', $userId)
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('a.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('a.fecha', '<=', $this->fechaHasta))
            ->select([
                'a.id as albaran_id',
                'a.numero as albaran_numero',
                'a.fecha',
                'a.tipo_hora',
                'alp.horas',
                'alp.horas_extra',
                DB::raw("COALESCE(c.nombre, a.cliente_nombre_snapshot, a.cliente_texto, '') as cliente_nombre"),
                DB::raw("COALESCE(p.nombre, a.proyecto_nombre_snapshot, a.proyecto_texto, '') as proyecto_nombre"),
                DB::raw("COALESCE(con.nombre, a.concepto_nombre_snapshot, a.concepto_texto, '') as concepto_nombre"),
            ])
            ->orderBy('a.fecha')
            ->orderBy('a.id')
            ->get();

        $totales = [
            'laboral'             => (float) $lineas->where('tipo_hora', 'laboral')->sum('horas'),
            'laboral_noche'       => (float) $lineas->where('tipo_hora', 'laboral_noche')->sum('horas'),
            'festivo'             => (float) $lineas->where('tipo_hora', 'festivo')->sum('horas'),
            'festivo_noche'       => (float) $lineas->where('tipo_hora', 'festivo_noche')->sum('horas'),
            'laboral_extra'       => (float) $lineas->where('tipo_hora', 'laboral')->sum('horas_extra'),
            'laboral_noche_extra' => (float) $lineas->where('tipo_hora', 'laboral_noche')->sum('horas_extra'),
            'festivo_extra'       => (float) $lineas->where('tipo_hora', 'festivo')->sum('horas_extra'),
            'festivo_noche_extra' => (float) $lineas->where('tipo_hora', 'festivo_noche')->sum('horas_extra'),
        ];
        $totales['total'] = (float) array_sum($totales);

        $diasSemana = ['', 'Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        return view('livewire.mobile.horas.index', compact('lineas', 'totales', 'diasSemana'))
            ->layout('components.layouts.mobile', [
                'title'     => 'Mis Horas',
                'showBack'  => true,
                'backRoute' => route('mobile.dashboard'),
            ]);
    }
}
