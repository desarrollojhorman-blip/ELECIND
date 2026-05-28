<?php

namespace App\Enums;

enum TipoIncidencia: string
{
    case ALBARAN  = 'albaran';
    case AUSENCIA = 'ausencia';
    case OTRO     = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::ALBARAN  => 'Albarán',
            self::AUSENCIA => 'Ausencia',
            self::OTRO     => 'Otro',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::ALBARAN  => 'heroicon-o-document-text',
            self::AUSENCIA => 'heroicon-o-calendar-days',
            self::OTRO     => 'heroicon-o-ellipsis-horizontal-circle',
        };
    }
}
