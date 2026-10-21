<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
          $table->id();
          $table->uuid('uuid')->index();
          $table->string('title');
          $table->unsignedBigInteger('course_id');
          $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
          $table->unsignedBigInteger('module_id');
          $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
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
        Schema::dropIfExists('topics');
    }
}
