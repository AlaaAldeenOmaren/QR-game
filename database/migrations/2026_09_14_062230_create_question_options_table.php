<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained('questions');

            $table->char('label', 1);

            $table->text('option_text');

            $table->boolean('is_correct')->default(false);

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->unique(['question_id', 'label']);
            $table->unique(['question_id', 'id']);
        });

        DB::statement("
            ALTER TABLE question_options
            ADD CONSTRAINT chk_question_options_label
                CHECK (label IN ('A', 'B', 'C')),
            ADD CONSTRAINT chk_question_options_correct
                CHECK (is_correct IN (0, 1)),
            ADD CONSTRAINT chk_question_options_text
                CHECK (CHAR_LENGTH(TRIM(option_text)) > 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
