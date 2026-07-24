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
        Schema::create('commande_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->decimal('quantite', 10, 2);
            $table->decimal('prix_unitaire', 10, 2); // prix au moment de la commande
            $table->decimal('sous_total', 10, 2);
            $table->boolean('verrouille')->default(false); // true = déjà envoyé/préparé en cuisine, non modifiable
            $table->text('notes')->nullable(); // ex: sans oignons
            $table->enum('statut', ['en_attente', 'en_cuisson', 'prete', 'servi'])->default('en_attente');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('commande_items'); }
};
