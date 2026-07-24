<?php
// database/migrations/2024_01_01_000001_create_roles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique(); // admin, caissier, serveur, cuisinier
            $table->string('label');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('roles'); }
};