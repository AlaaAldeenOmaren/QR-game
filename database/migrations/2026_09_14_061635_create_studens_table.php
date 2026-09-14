<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('student_number', 20)->unique();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        DB::statement("
            ALTER TABLE students
            ADD CONSTRAINT chk_students_number
            CHECK (
                CHAR_LENGTH(TRIM(student_number)) > 0
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
