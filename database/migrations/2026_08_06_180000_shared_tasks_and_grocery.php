<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('linked_task_id')->nullable()->after('assigned_to_user_id')->constrained('tasks')->nullOnDelete();
        });

        Schema::create('grocery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('recurrence')->default('none');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grocery_items');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('linked_task_id');
            $table->dropConstrainedForeignId('assigned_to_user_id');
        });
    }
};
