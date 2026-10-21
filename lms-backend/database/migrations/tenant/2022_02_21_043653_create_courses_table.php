<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreateCoursesTable extends Migration
{
  use SoftDeletes;
  /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
  {
    Schema::create('courses', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->index();
      $table->string('course_code')->nullable();
      $table->string('title');
      $table->longText('description')->nullable();
      $table->string('status')->comment('UNPUBLISH, PUBLISH, DRAFT')->default('UNPUBLISH');
      $table->unsignedInteger('price');
      $table->boolean('is_free')->default(0);
      $table->json('available_until')->nullable();
      $table->unsignedInteger('training_hours');
      $table->string('facilitator')->nullable();
      $table->unsignedBigInteger('training_credits')->default(1)->comment('certificate setting');
      $table->enum('type_of_learning',['E-LEARNING', 'BLENDED', 'MICROLEARNING'])->default('E-LEARNING')->comment('certificate setting');
      $table->unsignedInteger('credit_training_hours')->comment('certificate setting');
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
        Schema::dropIfExists('courses');
    }
}
