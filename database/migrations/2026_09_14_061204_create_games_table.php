<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);

            $table->string('status', 16)
                ->default('not_started');

            $table->foreignId('created_by')
                ->constrained('users');

            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        DB::statement("
            ALTER TABLE games
            ADD CONSTRAINT chk_games_status
            CHECK (
                status IN (
                    'not_started',
                    'active',
                    'paused',
                    'finished'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
