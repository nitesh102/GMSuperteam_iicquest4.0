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
        Schema::create('complaint_attachments', function (Blueprint $table) {
             $table->id();

    $table->foreignId('complaint_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('file_path');

    $table->string('file_name')->nullable();

    $table->string('file_type')->nullable();

    $table->foreignId('uploaded_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

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
        Schema::dropIfExists('complaint_attachments');
    }
};
