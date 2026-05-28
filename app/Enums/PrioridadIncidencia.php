<?php

namespace App\Enums;

enum PrioridadIncidencia: string
{
    case BAJA    = 'baja';
    case MEDIA   = 'media';
    case ALTA    = 'alta';
    case URGENTE = 'urgente';

    public function etiqueta(): string
    {
        return match ($this) {
            self::BAJA    => 'Baja',
            self::MEDIA   => 'Media',
            self::ALTA    => 'Alta',
            self::URGENTE => 'Urgente',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::BAJA    => 'neutral',
            self::MEDIA   => 'info',
            self::ALTA    => 'warning',
            self::URGENTE => 'danger',
        };
    }

    public function orden(): int
    {
        return match ($this) {
            self::URGENTE => 4,
            self::ALTA    => 3,
            self::MEDIA   => 2,
            self::BAJA    => 1,
        };
    }
}
