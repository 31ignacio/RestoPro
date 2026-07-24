<?php
// database/migrations/xxxx_create_notifications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('titre');
            $table->string('message');
            $table->string('icone')->default('bell');
            $table->string('couleur')->default('primary');
            $table->string('lien')->nullable();
            $table->json('roles')->nullable(); // quels rôles voient cette notif
            $table->boolean('lue')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notifications'); }
};