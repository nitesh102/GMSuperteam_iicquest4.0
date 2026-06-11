<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaint_tracks', function (Blueprint $table) {
            $table->text('remarks')->after('changed_by');
        });

        DB::table('complaint_tracks')->update(['remarks' => DB::raw('COALESCE(notes, \'\')')]);

        Schema::table('complaint_tracks', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }

    public function down(): void
    {
        Schema::table('complaint_tracks', function (Blueprint $table) {
            $table->text('notes')->after('changed_by');
        });

        DB::table('complaint_tracks')->update(['notes' => DB::raw('COALESCE(remarks, \'\')')]);

        Schema::table('complaint_tracks', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
