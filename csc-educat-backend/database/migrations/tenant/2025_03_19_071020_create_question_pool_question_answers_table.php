<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionPoolQuestionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_pool_question_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_pool_question_id');
            $table->foreign('question_pool_question_id')->references('id')->on('question_pool_questions');
            $table->string('correct_answer')->nullable();
            $table->json('choices')->nullable();
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
        Schema::dropIfExists('question_pool_question_answers');
    }
}
