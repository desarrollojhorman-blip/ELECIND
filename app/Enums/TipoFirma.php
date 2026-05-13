<?php

namespace App\Enums;

enum TipoFirma: string
{
    case TRABAJADOR = 'trabajador';
    case RESPONSABLE = 'responsable';

    public function etiqueta(): string
    {
        return match ($this) {
            self::TRABAJADOR => 'Trabajador',
            self::RESPONSABLE => 'Responsable',
        };
    }
}
