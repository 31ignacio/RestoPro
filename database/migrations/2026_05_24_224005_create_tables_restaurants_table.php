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
        Schema::create('tables_restaurant', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('nom')->nullable();
            $table->string('motif_reservation')->nullable();
            $table->integer('capacite')->default(4);
            $table->enum('statut', ['libre', 'occupee', 'reservee'])->default('libre');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tables_restaurant'); }
};
