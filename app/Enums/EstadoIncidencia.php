<?php

namespace App\Enums;

enum EstadoIncidencia: string
{
    case PENDIENTE  = 'pendiente';
    case EN_PROCESO = 'en_proceso';
    case RESUELTA   = 'resuelta';
    case CERRADA    = 'cerrada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::PENDIENTE  => 'Pendiente',
            self::EN_PROCESO => 'En proceso',
            self::RESUELTA   => 'Resuelta',
            self::CERRADA    => 'Cerrada',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::PENDIENTE  => 'warning',
            self::EN_PROCESO => 'info',
            self::RESUELTA   => 'success',
            self::CERRADA    => 'neutral',
        };
    }

    public function esActiva(): bool
    {
        return in_array($this, [self::PENDIENTE, self::EN_PROCESO], true);
    }
}
