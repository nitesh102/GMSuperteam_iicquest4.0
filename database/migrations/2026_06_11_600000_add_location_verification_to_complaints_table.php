<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->decimal('reporter_latitude', 10, 7)->nullable()->after('longitude');
            $table->decimal('reporter_longitude', 10, 7)->nullable()->after('reporter_latitude');
            $table->decimal('reporter_accuracy', 6, 1)->nullable()->after('reporter_longitude');
            $table->decimal('location_distance', 8, 1)->nullable()->after('reporter_accuracy');
            $table->boolean('location_verified')->default(false)->after('location_distance');
            $table->string('location_verification_method', 10)->nullable()->after('location_verified');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'reporter_latitude',
                'reporter_longitude',
                'reporter_accuracy',
                'location_distance',
                'location_verified',
                'location_verification_method',
            ]);
        });
    }
};
