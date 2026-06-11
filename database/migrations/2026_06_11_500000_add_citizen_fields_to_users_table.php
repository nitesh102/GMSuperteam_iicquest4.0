<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('citizenship_number')->unique()->after('email');
            $table->string('phone_number')->after('citizenship_number');
            $table->string('address')->nullable()->after('phone_number');
            $table->string('citizenship_front')->nullable()->after('address');
            $table->string('citizenship_back')->nullable()->after('citizenship_front');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'citizenship_number',
                'phone_number',
                'address',
                'citizenship_front',
                'citizenship_back',
            ]);
        });
    }
};
