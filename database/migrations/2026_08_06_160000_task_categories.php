<?php

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#1d4ed8');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_category_id')->nullable()->after('category')->constrained()->nullOnDelete();
        });

        foreach (User::query()->cursor() as $user) {
            $defaults = TaskCategory::seedDefaultsFor($user);

            Task::query()
                ->where('user_id', $user->id)
                ->where('category', 'immediate')
                ->update(['task_category_id' => $defaults['urgent']->id]);

            Task::query()
                ->where('user_id', $user->id)
                ->where('category', 'longterm')
                ->update(['task_category_id' => $defaults['later']->id]);

            Task::query()
                ->where('user_id', $user->id)
                ->whereNull('task_category_id')
                ->update(['task_category_id' => $defaults['urgent']->id]);
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_category_id');
        });

        Schema::dropIfExists('task_categories');
    }
};
