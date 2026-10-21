<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScormRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scorm_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scorm_id')->index();
            $table->foreign('scorm_id')->references('id')->on('scorms')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('scorm_cloud_course_id');
            $table->string('registration_id');
            $table->string('launch_link')->nullable();
            $table->unsignedBigInteger('scorm_registration_progress_id')->nullable()->index();
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
        Schema::dropIfExists('scorm_registrations');
    }
}
