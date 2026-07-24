<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sur la table des articles de commande : identifie à quelle "vague"
        // d'envoi en cuisine appartient chaque ligne.
        // vague = 1 -> commande initiale
        // vague >= 2 -> complément ajouté après le premier envoi en cuisine
        Schema::table('commande_items', function (Blueprint $table) {
            if (!Schema::hasColumn('commande_items', 'vague')) {
                $table->unsignedTinyInteger('vague')->default(1)->after('quantite');
            }
        });

        // Sur la commande : badge affiché en cuisine tant que le complément
        // n'a pas été traité (repassé à false quand la commande repasse "prete").
        Schema::table('commandes', function (Blueprint $table) {
            if (!Schema::hasColumn('commandes', 'a_nouveaux_articles')) {
                $table->boolean('a_nouveaux_articles')->default(false)->after('statut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commande_items', function (Blueprint $table) {
            $table->dropColumn('vague');
        });

        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn('a_nouveaux_articles');
        });
    }
};