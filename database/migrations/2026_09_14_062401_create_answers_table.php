<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('question_id');

            $table->unsignedBigInteger('selected_option_id')
                ->nullable();

            $table->text('answer_text')->nullable();

            $table->unsignedSmallInteger('points_awarded')
                ->nullable();

            $table->text('feedback')->nullable();

            $table->foreignId('graded_by')
                ->nullable()
                ->constrained('users');

            $table->dateTime('graded_at')->nullable();

            $table->dateTime('submitted_at')->useCurrent();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->unique(['participant_id', 'question_id']);

            $table->foreign(
                ['game_id', 'participant_id'],
                'answers_participant_fk'
            )
                ->references(['game_id', 'id'])
                ->on('game_participants');

            $table->foreign(
                ['game_id', 'question_id'],
                'answers_question_fk'
            )
                ->references(['game_id', 'id'])
                ->on('questions');

            $table->foreign(
                ['question_id', 'selected_option_id'],
                'answers_option_fk'
            )
                ->references(['question_id', 'id'])
                ->on('question_options');
        });

        DB::statement("
            ALTER TABLE answers
            ADD CONSTRAINT chk_answers_content
                CHECK (
                    (
                        selected_option_id IS NOT NULL
                        AND answer_text IS NULL
                    )
                    OR
                    (
                        selected_option_id IS NULL
                        AND answer_text IS NOT NULL
                        AND CHAR_LENGTH(TRIM(answer_text)) > 0
                    )
                ),
            ADD CONSTRAINT chk_answers_grading
                CHECK (
                    (
                        points_awarded IS NULL
                        AND graded_at IS NULL
                        AND graded_by IS NULL
                    )
                    OR
                    (
                        points_awarded IS NOT NULL
                        AND graded_at IS NOT NULL
                    )
                )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
