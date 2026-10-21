<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessments', function (Blueprint $table) {
          $table->id();
          $table->uuid('uuid');
          $table->unsignedBigInteger('course_id');
          $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
          $table->unsignedBigInteger('module_id')->nullable();
          $table->unsignedBigInteger('uploader_id');
          $table->foreign('uploader_id')->references('id')->on('users')->onDelete('cascade');
          $table->string('title')->nullable();
          $table->string('type_of_assessment')->comment('graded_assessment, practice_assessment, graded_survey, ungraded_survey')->nullable();
          $table->longText('description')->nullable();
          $table->boolean('require_checking')->default(0);
          $table->boolean('shuffle_questions')->nullable();
          $table->boolean('shuffle_answers')->nullable();
          $table->unsignedInteger('time_limit')->nullable();
          $table->unsignedInteger('max_attempt')->nullable();
          $table->string('accepted_score')->default('latest')->comment('highest, latest')->nullable();
          $table->unsignedBigInteger('passing_score')->nullable();
          $table->string('view_results_by')->comment('summary, each_attempt, all_correct_answers')->nullable();
          $table->string('view_results_until')->nullable();
          $table->boolean('show_each_questions')->nullable();
          $table->boolean('lock_question_after_answering')->nullable();
          $table->json('due_date')->nullable();
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
        Schema::dropIfExists('assessments');
    }
}
