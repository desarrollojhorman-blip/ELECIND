<?php

namespace App\Enums;

enum TipoAusencia: string
{
    case VACACIONES          = 'vacaciones';
    case PERMISO_PERSONAL    = 'permiso_personal';
    case BAJA_MEDICA         = 'baja_medica';
    case ACCIDENTE_LABORAL   = 'accidente_laboral';
    case MATERNIDAD          = 'maternidad';
    case OTROS               = 'otros';

    public function etiqueta(): string
    {
        return match ($this) {
            self::VACACIONES        => 'Vacaciones',
            self::PERMISO_PERSONAL  => 'Permiso personal',
            self::BAJA_MEDICA       => 'Baja médica',
            self::ACCIDENTE_LABORAL => 'Accidente laboral',
            self::MATERNIDAD        => 'Maternidad / Paternidad',
            self::OTROS             => 'Otros',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::VACACIONES        => 'success',
            self::PERMISO_PERSONAL  => 'info',
            self::BAJA_MEDICA       => 'warning',
            self::ACCIDENTE_LABORAL => 'danger',
            self::MATERNIDAD        => 'info',
            self::OTROS             => 'neutral',
        };
    }
}
