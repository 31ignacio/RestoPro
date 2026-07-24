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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_id')->constrained('categories')->restrictOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2);
            $table->string('photo')->nullable();
            $table->boolean('disponible')->default(true);
            $table->boolean('gerer_stock')->default(false);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('produits'); }
};
