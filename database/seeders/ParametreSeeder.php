<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Parametre;


class ParametreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void {
        $params = [
            'restaurant_nom'     => 'RestoPro',
            'restaurant_adresse' => 'Cotonou, Bénin',
            'restaurant_tel'     => '+229 XX XX XX XX',
            'monnaie'            => 'FCFA',
            'monnaie_symbole'    => 'F',
            'ticket_message'     => 'Merci de votre visite !',
            'tva_active'         => '0',
            'tva_taux'           => '18',
        ];
        foreach ($params as $cle => $valeur) {
            Parametre::firstOrCreate(['cle' => $cle], ['valeur' => $valeur]);


        }
    }
}
