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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('telephone')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->text('adresse')->nullable();
            $table->integer('points_fidelite')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('clients'); }
};
