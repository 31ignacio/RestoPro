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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('libelle');
            $table->string('categorie')->default('general'); // loyer, salaire, fournisseur...
            $table->decimal('montant', 10, 2);
            $table->date('date_depense');
            $table->text('notes')->nullable();
            $table->string('justificatif')->nullable(); // photo reçu
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('depenses'); }
};
