<?php

namespace App\Enums;

enum TipoDia: string
{
    case LABORABLE = 'laborable';
    case FESTIVO = 'festivo';

    public function etiqueta(): string
    {
        return match ($this) {
            self::LABORABLE => 'Laborable',
            self::FESTIVO => 'Festivo',
        };
    }

    public function esFestivo(): bool
    {
        return $this === self::FESTIVO;
    }
}
