<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{User, Role};
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $users = [
            ['role' => 'admin',     'name' => 'Admin Système',  'email' => 'admin@restopro.com'],
            ['role' => 'caissier',  'name' => 'Caissier Test',  'email' => 'caisse@restopro.com'],
            ['role' => 'serveur',   'name' => 'Serveur Test',   'email' => 'serveur@restopro.com'],
            ['role' => 'cuisinier', 'name' => 'Chef Test',      'email' => 'cuisine@restopro.com'],
        ];
        foreach ($users as $u) {
            User::firstOrCreate(['email' => $u['email']], [
                'role_id'  => Role::where('nom', $u['role'])->value('id'),
                'name'     => $u['name'],
                'password' => Hash::make('password'),
            ]);
        }
    }
}
