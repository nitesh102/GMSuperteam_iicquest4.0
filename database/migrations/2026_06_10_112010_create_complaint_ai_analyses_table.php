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
        Schema::create('complaint_ai_analyses', function (Blueprint $table) {
            $table->id();

    $table->foreignId('complaint_id')
        ->unique()
        ->constrained()
        ->cascadeOnDelete();

    $table->string('detected_category');

    $table->enum('detected_priority',[
        'low',
        'medium',
        'high',
        'emergency'
    ]);

    $table->decimal('confidence_score',5,2);

    $table->longText('ai_summary')->nullable();

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
        Schema::dropIfExists('complaint_ai_analyses');
    }
};
