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
            $table->date('due_at')->nullable()->after('notes');
        });

        DB::table('task_categories')->where('name', 'Urgent')->update(['name' => 'Immediate']);
        DB::table('task_categories')->where('name', 'Later')->update(['name' => 'Long-term']);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });

        DB::table('task_categories')->where('name', 'Immediate')->update(['name' => 'Urgent']);
        DB::table('task_categories')->where('name', 'Long-term')->update(['name' => 'Later']);
    }
};
