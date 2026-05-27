<?php

namespace Tests\Unit;

use App\Enums\EstadoAlbaran;
use PHPUnit\Framework\TestCase;

class EstadoAlbaranTest extends TestCase
{
    public function test_pendiente_firma_solo_avanza_a_firmado(): void
    {
        $estado = EstadoAlbaran::PENDIENTE_FIRMA;

        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::FIRMADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::FACTURADO));
    }

    public function test_firmado_solo_avanza_a_facturado(): void
    {
        $estado = EstadoAlbaran::FIRMADO;

        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::FACTURADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::PENDIENTE_FIRMA));
    }

    public function test_facturado_es_terminal(): void
    {
        $estado = EstadoAlbaran::FACTURADO;

        $this->assertSame([], $estado->transicionesPermitidas());
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::PENDIENTE_FIRMA));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::FIRMADO));
    }

    public function test_solo_pendiente_firma_es_editable_libremente(): void
    {
        $this->assertTrue(EstadoAlbaran::PENDIENTE_FIRMA->esEditable());
        $this->assertFalse(EstadoAlbaran::FIRMADO->esEditable());
        $this->assertFalse(EstadoAlbaran::FACTURADO->esEditable());
    }

    public function test_firmado_y_facturado_bloquean_edicion(): void
    {
        $this->assertTrue(EstadoAlbaran::FIRMADO->bloqueaEdicion());
        $this->assertTrue(EstadoAlbaran::FACTURADO->bloqueaEdicion());
        $this->assertFalse(EstadoAlbaran::PENDIENTE_FIRMA->bloqueaEdicion());
    }
}
