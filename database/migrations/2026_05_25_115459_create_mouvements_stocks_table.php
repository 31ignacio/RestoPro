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
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('type', ['entree', 'sortie', 'ajustement']);
            $table->decimal('quantite', 10, 3);
            $table->decimal('quantite_avant', 10, 3);
            $table->decimal('quantite_apres', 10, 3);
            $table->string('motif')->nullable();
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mouvements_stock'); }
};
