<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        $catalogo = [
            'Cableado' => [
                ['Cable H07V-K 1,5mm² azul', 'm'],
                ['Cable H07V-K 2,5mm² negro', 'm'],
                ['Cable RZ1-K 5G6mm²', 'm'],
                ['Cable manguera 3x1,5', 'm'],
            ],
            'Mecanismos' => [
                ['Interruptor simple blanco', 'ud'],
                ['Conmutador blanco', 'ud'],
                ['Base de enchufe schuko', 'ud'],
                ['Pulsador timbre', 'ud'],
            ],
            'Protección' => [
                ['Magnetotérmico 1P+N 10A', 'ud'],
                ['Magnetotérmico 1P+N 16A', 'ud'],
                ['Diferencial 2P 25A 30mA', 'ud'],
                ['Diferencial 4P 40A 30mA', 'ud'],
            ],
            'Iluminación' => [
                ['Downlight LED 12W', 'ud'],
                ['Panel LED 60x60', 'ud'],
                ['Tira LED 5m IP65', 'ud'],
                ['Foco proyector LED 50W', 'ud'],
            ],
            'Canalización' => [
                ['Tubo corrugado Ø20mm', 'm'],
                ['Tubo corrugado Ø25mm', 'm'],
                ['Canal 60x40 blanca', 'm'],
                ['Caja empalmes 100x100', 'ud'],
            ],
            'Cuadros' => [
                ['Cuadro 12 módulos empotrar', 'ud'],
                ['Cuadro 24 módulos superficie', 'ud'],
                ['Embarrado 4P', 'ud'],
            ],
        ];

        $grupo = fake()->randomElement(array_keys($catalogo));
        [$nombre, $unidad] = fake()->randomElement($catalogo[$grupo]);

        return [
            'codigo' => fake()->unique()->bothify('MAT-####'),
            'grupo' => $grupo,
            'nombre' => $nombre.' '.fake()->bothify('#?'),
            'descripcion' => fake()->boolean(35) ? fake()->sentence(6) : null,
            'unidad_medida' => $unidad,
            'stock_minimo' => fake()->randomElement([0, 5, 10, 20, 50]),
            'notificar_stock_bajo' => fake()->boolean(75),
            'activo' => true,
        ];
    }
}
