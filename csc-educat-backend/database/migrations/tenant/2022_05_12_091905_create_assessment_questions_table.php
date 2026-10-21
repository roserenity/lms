<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('assessment_id');
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->longText('question')->nullable();
            $table->string('type_of_question')->default('multiple_choice');
            $table->decimal('score', 5, 2)->unsigned()->default(1);
            $table->boolean('require_file_attachment')->default(0);
            $table->boolean('require_support_answer')->nullable();
            $table->unsignedInteger('rubric_id')->nullable();
            $table->unsignedInteger('sort_no')->default(0);
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
        Schema::dropIfExists('assessment_questions');
    }
}
