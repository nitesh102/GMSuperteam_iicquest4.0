<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('category_id')
                ->constrained('departments')->nullOnDelete();
        });

        DB::statement('UPDATE complaints c JOIN complaint_categories cc ON c.category_id = cc.id SET c.department_id = cc.department_id WHERE c.department_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
