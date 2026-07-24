<?php
namespace App\Console\Commands;

use App\Models\TableRestaurant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenererQrCodes extends Command
{
    protected $signature   = 'tables:qrcodes';
    protected $description = 'Générer les UUID manquants pour les tables';

    public function handle(): void
    {
        $tables = TableRestaurant::whereNull('uuid')->get();
        foreach ($tables as $t) {
            $t->update(['uuid' => Str::uuid()]);
            $this->info("Table {$t->numero} — UUID généré : {$t->uuid}");
        }
        $this->info("✅ Terminé — {$tables->count()} table(s) mise(s) à jour.");
    }
}