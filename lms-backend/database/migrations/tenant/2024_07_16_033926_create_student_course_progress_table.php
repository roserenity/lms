<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentCourseProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_course_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->index();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('total_done_topics');
            $table->unsignedBigInteger('total_done_sub_topics');
            $table->unsignedBigInteger('total_done_assessments');
            $table->unsignedBigInteger('total_done_course_evaluations');
            $table->unsignedBigInteger('total_done_module_evaluations');
            $table->unsignedBigInteger('total_done_group_activities');
            $table->unsignedBigInteger('total_attended_zoom_meetings');
            $table->unsignedBigInteger('total_time_spent');
            $table->unsignedBigInteger('progress')->comment('in percent');
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
        Schema::dropIfExists('student_course_progress');
    }
}
