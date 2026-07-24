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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // CMD-20240101-0001
            $table->foreignId('table_id')->nullable()->constrained('tables_restaurant')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // serveur
            $table->enum('type', ['sur_place', 'emporter', 'livraison'])->default('sur_place');
            $table->enum('statut', ['en_attente', 'en_cuisson', 'prete', 'servie', 'payee', 'annulee'])->default('en_attente');
            $table->decimal('sous_total', 10, 2)->default(0);
            $table->decimal('remise', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('motif_annulation')->nullable();
            $table->timestamp('prise_en_charge_at')->nullable(); // quand cuisine prend
            $table->timestamp('prete_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('commandes'); }
};
