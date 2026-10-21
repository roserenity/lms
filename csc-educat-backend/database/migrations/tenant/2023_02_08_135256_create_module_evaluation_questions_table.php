<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModuleEvaluationQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->unsignedBigInteger('module_evaluation_id')->index();
            $table->foreign('module_evaluation_id')->references('id')->on('module_evaluations')->onDelete('cascade');
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
        Schema::dropIfExists('module_evaluation_questions');
    }
}
