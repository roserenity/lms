<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScormQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
     public function up()
     {
        Schema::create('scorm_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scorm_activity_id');
            $table->foreign('scorm_activity_id')->references('id')->on('scorm_activities')->onDelete('cascade');
            $table->string('question_uuid');
            $table->string('question');
            $table->string('type_of_question');
            $table->json('choices')->nullable();
            $table->json('correct_answers')->nullable();
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
        Schema::dropIfExists('scorm_questions');
     }
}
