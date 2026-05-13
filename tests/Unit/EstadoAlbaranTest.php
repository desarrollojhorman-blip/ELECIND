<?php

namespace Tests\Unit;

use App\Enums\EstadoAlbaran;
use PHPUnit\Framework\TestCase;

class EstadoAlbaranTest extends TestCase
{
    public function test_borrador_puede_pasar_a_pendiente_firma_y_archivado(): void
    {
        $estado = EstadoAlbaran::BORRADOR;

        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::PENDIENTE_FIRMA));
        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::ARCHIVADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::FIRMADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::FACTURADO));
    }

    public function test_pendiente_firma_puede_volver_a_borrador_o_avanzar_a_firmado(): void
    {
        $estado = EstadoAlbaran::PENDIENTE_FIRMA;

        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::BORRADOR));
        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::FIRMADO));
        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::ARCHIVADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::FACTURADO));
    }

    public function test_firmado_solo_avanza_a_facturado_o_archivado(): void
    {
        $estado = EstadoAlbaran::FIRMADO;

        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::FACTURADO));
        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::ARCHIVADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::BORRADOR));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::PENDIENTE_FIRMA));
    }

    public function test_facturado_solo_puede_archivarse(): void
    {
        $estado = EstadoAlbaran::FACTURADO;

        $this->assertTrue($estado->puedeTransicionarA(EstadoAlbaran::ARCHIVADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::FIRMADO));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::BORRADOR));
    }

    public function test_archivado_es_terminal(): void
    {
        $estado = EstadoAlbaran::ARCHIVADO;

        $this->assertSame([], $estado->transicionesPermitidas());
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::BORRADOR));
        $this->assertFalse($estado->puedeTransicionarA(EstadoAlbaran::FIRMADO));
    }

    public function test_solo_borrador_es_editable_libremente(): void
    {
        $this->assertTrue(EstadoAlbaran::BORRADOR->esEditable());
        $this->assertFalse(EstadoAlbaran::PENDIENTE_FIRMA->esEditable());
        $this->assertFalse(EstadoAlbaran::FIRMADO->esEditable());
        $this->assertFalse(EstadoAlbaran::FACTURADO->esEditable());
        $this->assertFalse(EstadoAlbaran::ARCHIVADO->esEditable());
    }

    public function test_firmado_facturado_y_archivado_bloquean_edicion(): void
    {
        $this->assertTrue(EstadoAlbaran::FIRMADO->bloqueaEdicion());
        $this->assertTrue(EstadoAlbaran::FACTURADO->bloqueaEdicion());
        $this->assertTrue(EstadoAlbaran::ARCHIVADO->bloqueaEdicion());
        $this->assertFalse(EstadoAlbaran::BORRADOR->bloqueaEdicion());
        $this->assertFalse(EstadoAlbaran::PENDIENTE_FIRMA->bloqueaEdicion());
    }
}
