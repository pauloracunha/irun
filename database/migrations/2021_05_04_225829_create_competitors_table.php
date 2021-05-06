<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateCompetitorsTable
 */
class CreateCompetitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('runner_id')->constrained()->onDelete('cascade');
            $table->foreignId('race_id')->constrained()->onDelete('cascade');
            $table->dateTime('started_in')->nullable();
            $table->dateTime('ended_in')->nullable();
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
        Schema::dropIfExists('competitors');
    }
}
