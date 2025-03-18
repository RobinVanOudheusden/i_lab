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
        Schema::create('player_answers', function (Blueprint $table) {
            $table->id();
            $table->string('quizcode');
            $table->string('player_name');
            $table->unsignedBigInteger('question_id');
            $table->text('answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
            
            // Create compound index for efficiently finding answers
            $table->index(['quizcode', 'question_id']);
            $table->index(['quizcode', 'player_name']);
            
            // Foreign keys would be ideal, but we'll skip for now since
            // player records might be temporary
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_answers');
    }
};
