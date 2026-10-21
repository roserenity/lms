<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScormActivityAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
     public function up()
     {
        Schema::create('scorm_activity_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scorm_activity_id')->index();
            $table->foreign('scorm_activity_id')->references('id')->on('scorm_activities')->onDelete('cascade');
            $table->unsignedBigInteger('question_id')->index();
            $table->foreign('question_id')->references('id')->on('scorm_questions')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->json('answer')->nullable();
            $table->boolean('is_correct')->default(0); //default false
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
        Schema::dropIfExists('scorm_activity_answers');
     }
}

