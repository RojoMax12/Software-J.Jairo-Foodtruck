<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Admin', 'Cliente', 'Trabajador'];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre_rol' => $rol]);
        }
    }
}
