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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);
            $table->string('description', 255);
            $table->timestamp('expected_start_date');
            $table->timestamp('expected_completion_date');
            $table->enum('status',['IN_PROGRESS', 'DONE', 'CANCELED']);
            $table->foreignId('user_id')->constrained();
            $table->foreignId('user_id_assigned_to')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefa');
    }
};
