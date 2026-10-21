<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteriaIndicatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('criteria_indicators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rubric_criteria_id');
            $table->foreign('rubric_criteria_id')->references('id')->on('rubric_criterias')->onDelete('cascade');
            $table->unsignedBigInteger('rubric_rating_id');
            $table->foreign('rubric_rating_id')->references('id')->on('rubric_ratings')->onDelete('cascade');
            $table->longText('body')->nullable();
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
        Schema::dropIfExists('criteria_indicators');
    }
}
