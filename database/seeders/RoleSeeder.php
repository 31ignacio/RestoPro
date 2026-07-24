<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void {
        $roles = [
            ['nom' => 'admin',      'label' => 'Administrateur'],
            ['nom' => 'caissier',   'label' => 'Caissier'],
            ['nom' => 'serveur',    'label' => 'Serveur'],
            ['nom' => 'cuisinier',  'label' => 'Cuisinier'],
        ];
        foreach ($roles as $role) Role::firstOrCreate(['nom' => $role['nom']], $role);
    }
}
