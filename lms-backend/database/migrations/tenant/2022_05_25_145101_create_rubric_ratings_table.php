<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRubricRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rubric_ratings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('rubric_id');
            $table->foreign('rubric_id')->references('id')->on('rubrics')->onDelete('cascade');
            // $table->unsignedBigInteger('rubric_criteria_id');
            // $table->foreign('rubric_criteria_id')->references('id')->on('rubric_criterias')->onDelete('cascade');
            $table->string('rate')->nullable();
            $table->string('description')->nullable();
            // $table->softDeletes();
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
        Schema::dropIfExists('rubric_ratings');
    }
}
