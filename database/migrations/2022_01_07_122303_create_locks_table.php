<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')
                ->constrained('players')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('action_id')
                ->constrained('locked_actions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->text('reason')->default('');
            $table->timestamp('expired_at');
            $table->timestamps();

            $table->unique(['player_id', 'action_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locks');
    }
}
