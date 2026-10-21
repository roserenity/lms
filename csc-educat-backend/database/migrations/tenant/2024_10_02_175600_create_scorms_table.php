<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scorms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->index();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->unsignedBigInteger('model_id'); // topic id or subtopic id depends sa binded
            $table->string('model_type');
            $table->string('scorm_cloud_course_id');
            $table->string('title');
            $table->string('status')->nullable()->comment("COMPLETED, RUNNING, ERROR");
            $table->string('job_id');
            $table->json('data')->nullable(); //job return
            $table->string('preview_link')->nullable();
            $table->longText('errors')->nullable();
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
        Schema::dropIfExists('scorms');
    }
}
