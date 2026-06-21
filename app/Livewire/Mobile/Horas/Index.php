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

        $fromAlbaranes = DB::table('albaran_lineas_personal as alp')
            ->join('albaranes as a', 'a.id', '=', 'alp.albaran_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->leftJoin('proyectos as p', 'p.id', '=', 'a.proyecto_id')
            ->leftJoin('conceptos as con', 'con.id', '=', 'a.concepto_id')
            ->whereNull('a.deleted_at')
            ->where('alp.trabajador_id', $userId)
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('a.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('a.fecha', '<=', $this->fechaHasta))
            ->select([
                'a.id as doc_id',
                'a.numero as doc_numero',
                DB::raw("'albaran' as tipo_doc"),
                'a.fecha',
                'a.tipo_hora',
                'alp.horas',
                'alp.horas_extra',
                DB::raw("COALESCE(c.nombre, a.cliente_nombre_snapshot, a.cliente_texto, '') as cliente_nombre"),
                DB::raw("COALESCE(p.nombre, a.proyecto_nombre_snapshot, a.proyecto_texto, '') as proyecto_nombre"),
                DB::raw("COALESCE(con.nombre, a.concepto_nombre_snapshot, a.concepto_texto, '') as concepto_nombre"),
            ]);

        $fromPartes = DB::table('partes_lineas_personal as plp')
            ->join('partes as pt', 'pt.id', '=', 'plp.parte_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'pt.cliente_id')
            ->leftJoin('proyectos as p', 'p.id', '=', 'pt.proyecto_id')
            ->leftJoin('conceptos as con', 'con.id', '=', 'pt.concepto_id')
            ->whereNull('pt.deleted_at')
            ->whereNull('pt.albaran_id')
            ->where('plp.trabajador_id', $userId)
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('pt.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('pt.fecha', '<=', $this->fechaHasta))
            ->select([
                'pt.id as doc_id',
                'pt.numero as doc_numero',
                DB::raw("'parte' as tipo_doc"),
                'pt.fecha',
                'pt.tipo_hora',
                'plp.horas',
                'plp.horas_extra',
                DB::raw("COALESCE(c.nombre, pt.cliente_nombre_snapshot, '') as cliente_nombre"),
                DB::raw("COALESCE(p.nombre, pt.proyecto_nombre_snapshot, '') as proyecto_nombre"),
                DB::raw("COALESCE(con.nombre, pt.concepto_nombre_snapshot, '') as concepto_nombre"),
            ]);

        $fromAlbaranes->unionAll($fromPartes);

        $lineas = DB::table(DB::raw("({$fromAlbaranes->toSql()}) as unified"))
            ->mergeBindings($fromAlbaranes)
            ->select('*')
            ->orderBy('fecha')
            ->orderBy('doc_id')
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
