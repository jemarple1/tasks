<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('archived_at');
        });

        DB::table('tasks')->whereNull('expires_at')->update([
            'expires_at' => now()->addDays(7),
        ]);

        Schema::create('garden_flowers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('emoji', 8);
            $table->unsignedTinyInteger('position_x');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garden_flowers');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
