<?php

namespace App\Enums;

enum TipoHora: string
{
    case LABORAL = 'laboral';
    case LABORAL_NOCHE = 'laboral_noche';
    case FESTIVO = 'festivo';
    case FESTIVO_NOCHE = 'festivo_noche';

    public function etiqueta(): string
    {
        return match ($this) {
            self::LABORAL => 'Laboral',
            self::LABORAL_NOCHE => 'Laboral (noche)',
            self::FESTIVO => 'Festivo',
            self::FESTIVO_NOCHE => 'Festivo (noche)',
        };
    }

    public function esNoche(): bool
    {
        return $this === self::LABORAL_NOCHE || $this === self::FESTIVO_NOCHE;
    }

    public function esFestivo(): bool
    {
        return $this === self::FESTIVO || $this === self::FESTIVO_NOCHE;
    }
}
