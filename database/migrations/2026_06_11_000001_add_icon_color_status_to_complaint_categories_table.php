<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaint_categories', function (Blueprint $table) {
            $table->string('icon')->default('fa-tag')->after('description');
            $table->string('color')->default('#2563EB')->after('icon');
            $table->string('status')->default('active')->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('complaint_categories', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color', 'status']);
        });
    }
};
