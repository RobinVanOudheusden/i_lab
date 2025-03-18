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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes');
            $table->string('question');
            $table->string('answer');
            $table->string('type');
            $table->string('options')->nullable();
            $table->string('correct_option');
            $table->string('explanation')->nullable();
            $table->string('image')->nullable();
            $table->integer('time')->default(15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
