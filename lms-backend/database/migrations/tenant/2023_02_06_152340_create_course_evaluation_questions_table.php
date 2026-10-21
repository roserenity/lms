<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseEvaluationQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->unsignedBigInteger('course_evaluation_id')->index();
            $table->foreign('course_evaluation_id')->references('id')->on('course_evaluations')->onDelete('cascade');
            $table->longText('question')->nullable();
            $table->string('type_of_question')->default('multiple_choice');
            $table->boolean('is_require_to_answer')->default(0);
            $table->unsignedInteger('sort_no');
            $table->json('data')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('course_evaluation_questions');
    }
}
