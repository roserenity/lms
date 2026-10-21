<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShareAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('share_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id')->index();
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->unsignedBigInteger('shared_by_user_id')->index();
            $table->foreign('shared_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('shared_to_user_id')->index();
            $table->foreign('shared_to_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('status')->default('pending')->comment('pending, accepted');
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
        Schema::dropIfExists('share_assessments');
    }
}
