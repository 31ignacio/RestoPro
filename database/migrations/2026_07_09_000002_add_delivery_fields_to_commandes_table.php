<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->foreignId('livreur_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->decimal('frais_livraison', 10, 2)->default(0)->after('livreur_id');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('livreur_id');
            $table->dropColumn('frais_livraison');
        });
    }
};
