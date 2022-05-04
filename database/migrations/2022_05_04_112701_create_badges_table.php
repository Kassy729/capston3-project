<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('first_exercise');
            $table->boolean('bike_distance');
            $table->boolean('bike_distance2');
            $table->boolean('bike_distance3');
            $table->boolean('bike_altitude');
            $table->boolean('run_distance');
            $table->boolean('run_distance2');
            $table->boolean('run_distance3');
            $table->boolean('run_altitude');
            $table->boolean('make_track');
            $table->boolean('rank');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('badges');
    }
}
