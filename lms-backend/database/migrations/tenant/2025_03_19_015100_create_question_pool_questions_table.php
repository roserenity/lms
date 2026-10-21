<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionPoolQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_pool_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('question_pool_id');
            $table->foreign('question_pool_id')->references('id')->on('question_pools');
            $table->longText('question')->nullable();
            $table->string('type_of_question')->default('multiple_choice');
            $table->decimal('score', 5, 2)->unsigned()->default(1);
            $table->boolean('require_file_attachment')->default(0);
            $table->boolean('require_support_answer')->nullable();
            $table->boolean('is_require_to_answer')->default(0);
            $table->unsignedInteger('rubric_id')->nullable();
            $table->unsignedInteger('sort_no');
            $table->json('data')->nullable();
            $table->unsignedBigInteger('uploader_id');
            $table->foreign('uploader_id')->references('id')->on('users');
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
        Schema::dropIfExists('question_pool_questions');
    }
}
