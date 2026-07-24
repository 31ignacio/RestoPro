<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // caissier
            $table->enum('mode', ['especes', 'mobile_money', 'carte', 'mixte'])->default('especes');
            $table->decimal('montant_recu', 10, 2);
            $table->decimal('montant_du', 10, 2);
            $table->decimal('monnaie_rendue', 10, 2)->default(0);
            $table->string('reference')->nullable(); // pour mobile money / carte
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('paiements'); }
};
