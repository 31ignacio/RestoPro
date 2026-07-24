<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\TableRestaurant;

return new class extends Migration {
    public function up(): void {
        Schema::table('tables_restaurant', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        // Générer un UUID pour chaque table existante
        TableRestaurant::all()->each(function ($t) {
            $t->update(['uuid' => Str::uuid()]);
        });
    }

    public function down(): void {
        Schema::table('tables_restaurant', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};