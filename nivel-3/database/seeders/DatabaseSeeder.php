<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** Carga usuarios y productos educativos en la base de datos. */
    public function run(): void
    {
        /**
         * Los seeders cargan datos de ejemplo. firstOrCreate evita duplicarlos
         * y también conserva cambios hechos por estudiantes si Docker reinicia.
         *
         * IMPORTANTE: estas claves son solamente para desarrollo educativo.
         * Hay que eliminar estas cuentas antes de publicar una aplicación real.
         */
        User::query()->firstOrCreate(
            ['email' => 'admin@utu.edu.uy'],
            [
                'nombre' => 'Ana Administradora',
                'password' => 'admin123',
                'rol' => 'admin',
                'activo' => true,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => 'alumno@utu.edu.uy'],
            [
                'nombre' => 'Bruno Alumno',
                'password' => 'alumno123',
                'rol' => 'usuario',
                'activo' => true,
            ],
        );

        $products = [
            ['nombre' => 'Teclado mecánico', 'descripcion' => 'Teclado con luces y switches azules.', 'precio' => 2450, 'stock' => 12, 'categoria' => 'perifericos'],
            ['nombre' => 'Mouse inalámbrico', 'descripcion' => 'Mouse óptico con receptor USB.', 'precio' => 890, 'stock' => 34, 'categoria' => 'perifericos'],
            ['nombre' => 'Monitor 24 pulgadas', 'descripcion' => 'Monitor Full HD con HDMI.', 'precio' => 9800, 'stock' => 5, 'categoria' => 'monitores'],
            ['nombre' => 'Notebook 15 pulgadas', 'descripcion' => 'Notebook con 8 GB de RAM y disco SSD.', 'precio' => 38500, 'stock' => 0, 'categoria' => 'computadoras'],
            ['nombre' => 'Auriculares', 'descripcion' => 'Auriculares con micrófono.', 'precio' => 3200, 'stock' => 18, 'categoria' => 'audio'],
        ];

        foreach ($products as $product) {
            $product['activo'] = true;

            Product::query()->firstOrCreate(
                ['nombre' => $product['nombre']],
                $product,
            );
        }
    }
}
