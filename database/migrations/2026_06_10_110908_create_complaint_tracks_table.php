<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_tracks', function (Blueprint $table) {
            $table->id();

    $table->string('complaint_no')->unique();

    $table->foreignId('citizen_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->foreignId('category_id')
        ->nullable()
        ->constrained('complaint_categories')
        ->nullOnDelete();

    $table->foreignId('assigned_to')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->string('title');

    $table->longText('description');

    $table->string('location')->nullable();

    $table->decimal('latitude',10,7)->nullable();

    $table->decimal('longitude',10,7)->nullable();

    $table->enum('priority',[
        'low',
        'medium',
        'high',
        'emergency'
    ])->default('medium');

    $table->enum('current_status',[
        'submitted',
        'under_review',
        'assigned',
        'in_progress',
        'resolved',
        'rejected',
        'closed'
    ])->default('submitted');

    $table->text('ai_summary')->nullable();

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->foreignId('updated_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->timestamps();

    $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_tracks');
    }
};
