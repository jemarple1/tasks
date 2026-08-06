<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('recurrence')->default('none')->after('expires_at');
            $table->date('recurrence_until')->nullable()->after('recurrence');
            $table->foreignId('recurrence_parent_id')->nullable()->after('recurrence_until')->constrained('tasks')->nullOnDelete();
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->string('recurrence')->default('none')->after('ends_at');
            $table->date('recurrence_until')->nullable()->after('recurrence');
        });

        Schema::create('calendar_event_user', function (Blueprint $table) {
            $table->foreignId('calendar_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['calendar_event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_user');

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['recurrence', 'recurrence_until']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurrence_parent_id');
            $table->dropColumn(['recurrence', 'recurrence_until']);
        });
    }
};
