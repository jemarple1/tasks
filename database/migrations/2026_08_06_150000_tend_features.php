<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('tree_emoji', 8)->default('🌳')->after('password');
        });

        foreach (DB::table('users')->orderBy('id')->get() as $user) {
            $base = Str::slug(Str::before($user->email, '@'), '_') ?: 'user';
            $username = $base;
            $i = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base.'_'.$i++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('user_id')->constrained('users')->cascadeOnDelete();
        });

        DB::table('tasks')->update(['created_by_user_id' => DB::raw('user_id')]);

        Schema::create('user_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('connected_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'connected_user_id']);
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('garden_flowers');
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('user_connections');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'tree_emoji']);
        });

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
};
