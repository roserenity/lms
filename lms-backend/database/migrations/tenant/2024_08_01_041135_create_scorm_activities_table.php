<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScormActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
     public function up()
     {
        Schema::create('scorm_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->index();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->unsignedBigInteger('model_id'); // topic id or subtopic id depends sa binded
            $table->string('model_type');
            $table->string('title');
            $table->string('activity_id');
            $table->unsignedBigInteger('passing_score')->nullable();
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
        Schema::dropIfExists('scorm_activities');
     }
}
