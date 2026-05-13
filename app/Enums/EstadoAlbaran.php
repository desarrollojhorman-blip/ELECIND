<?php

namespace App\Enums;

enum EstadoAlbaran: string
{
    case BORRADOR = 'borrador';
    case PENDIENTE_FIRMA = 'pendiente_firma';
    case FIRMADO = 'firmado';
    case FACTURADO = 'facturado';
    case ARCHIVADO = 'archivado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::PENDIENTE_FIRMA => 'Pendiente de firma',
            self::FIRMADO => 'Firmado',
            self::FACTURADO => 'Facturado',
            self::ARCHIVADO => 'Archivado',
        };
    }

    public function tono(): string
    {
        return match ($this) {
            self::BORRADOR => 'neutral',
            self::PENDIENTE_FIRMA => 'warning',
            self::FIRMADO => 'success',
            self::FACTURADO => 'info',
            self::ARCHIVADO => 'neutral',
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
            self::BORRADOR => [self::PENDIENTE_FIRMA, self::ARCHIVADO],
            self::PENDIENTE_FIRMA => [self::BORRADOR, self::FIRMADO, self::ARCHIVADO],
            self::FIRMADO => [self::FACTURADO, self::ARCHIVADO],
            self::FACTURADO => [self::ARCHIVADO],
            self::ARCHIVADO => [],
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
        return $this === self::BORRADOR;
    }

    /**
     * ¿El albarán bloquea la edición de líneas (requiere permiso especial para modificar)?
     */
    public function bloqueaEdicion(): bool
    {
        return in_array($this, [self::FIRMADO, self::FACTURADO, self::ARCHIVADO], true);
    }
}
