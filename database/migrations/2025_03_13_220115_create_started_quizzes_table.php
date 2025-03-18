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
        Schema::create('started_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes');
            $table->string('title');
            $table->string('description');
            $table->string('image');
            $table->string('tags');
            $table->integer('quizcode');
            $table->integer('current_question')->default(0);
            $table->boolean('started')->default(false);
            $table->boolean('show_answers')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('started_quizzes');
    }
};
