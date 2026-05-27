<?php

namespace App\Enums;

enum EstadoAlbaran: string
{
    case PENDIENTE_FIRMA = 'pendiente_firma';
    case FIRMADO = 'firmado';
    case FACTURADO = 'facturado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::PENDIENTE_FIRMA => 'Pendiente de firma',
            self::FIRMADO => 'Firmado',
            self::FACTURADO => 'Facturado',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::PENDIENTE_FIRMA => 'warning',
            self::FIRMADO => 'success',
            self::FACTURADO => 'info',
        };
    }

    /**
     * Estados a los que se puede transicionar desde el actual.
     *
     * @return array<int, self>
     */
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            self::PENDIENTE_FIRMA => [self::FIRMADO],
            self::FIRMADO => [self::FACTURADO],
            self::FACTURADO => [],
        };
    }

    public function puedeTransicionarA(self $destino): bool
    {
        return in_array($destino, $this->transicionesPermitidas(), true);
    }

    /**
     * ¿El albarán es editable libremente en este estado?
     */
    public function esEditable(): bool
    {
        return $this === self::PENDIENTE_FIRMA;
    }

    /**
     * ¿El albarán bloquea la edición de líneas (requiere permiso especial para modificar)?
     */
    public function bloqueaEdicion(): bool
    {
        return in_array($this, [self::FIRMADO, self::FACTURADO], true);
    }
}
