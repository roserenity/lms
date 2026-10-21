<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_topics', function (Blueprint $table) {
          $table->id();
          $table->uuid('uuid')->index();
          $table->string('title');
          $table->unsignedBigInteger('course_id');
          $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
          $table->unsignedBigInteger('topic_id');
          $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
          $table->integer('sort_no')->default(0);
          $table->unsignedBigInteger('uploader_id');
          $table->foreign('uploader_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('sub_topics');
    }
}
