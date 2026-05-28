<?php

namespace App\Enums;

enum EstadoAusencia: string
{
    case PENDIENTE = 'pendiente';
    case APROBADA  = 'aprobada';
    case RECHAZADA = 'rechazada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::APROBADA  => 'Aprobada',
            self::RECHAZADA => 'Rechazada',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::APROBADA  => 'success',
            self::RECHAZADA => 'danger',
        };
    }
}
