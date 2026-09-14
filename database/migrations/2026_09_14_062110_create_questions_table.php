<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')
                ->constrained('games');

            $table->uuid('qr_token')->unique();

            $table->string('source_code', 20)->nullable();

            $table->text('question_text');

            $table->string('type', 16);

            $table->unsignedSmallInteger('max_points')
                ->default(10);

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->unique(['game_id', 'source_code']);
            $table->unique(['game_id', 'id']);
        });

        DB::statement("
            ALTER TABLE questions
            ADD CONSTRAINT chk_questions_type
                CHECK (type IN ('multiple_choice', 'open')),
            ADD CONSTRAINT chk_questions_points
                CHECK (max_points > 0),
            ADD CONSTRAINT chk_questions_text
                CHECK (CHAR_LENGTH(TRIM(question_text)) > 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
