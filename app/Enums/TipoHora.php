<?php

namespace App\Enums;

enum TipoHora: string
{
    case LABORABLE_NORMAL = 'laborable_normal';
    case LABORABLE_EXTRA = 'laborable_extra';
    case FESTIVO_NORMAL = 'festivo_normal';
    case FESTIVO_EXTRA = 'festivo_extra';

    public function etiqueta(): string
    {
        return match ($this) {
            self::LABORABLE_NORMAL => 'Laborable',
            self::LABORABLE_EXTRA => 'Laborable extra',
            self::FESTIVO_NORMAL => 'Festivo',
            self::FESTIVO_EXTRA => 'Festivo extra',
        };
    }

    public function esExtra(): bool
    {
        return $this === self::LABORABLE_EXTRA || $this === self::FESTIVO_EXTRA;
    }

    public function esFestivo(): bool
    {
        return $this === self::FESTIVO_NORMAL || $this === self::FESTIVO_EXTRA;
    }
}
