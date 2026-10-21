<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScormRegistrationProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scorm_registration_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scorm_registration_id')->index();
            $table->foreign('scorm_registration_id')->references('id')->on('scorm_registrations')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('completion_status');
            $table->string('success_status');
            $table->dateTime('completion_date')->nullable();
            $table->integer('total_seconds_tracked')->unsigned();
            $table->dateTime('first_accessed_date')->nullable();
            $table->dateTime('last_accessed_date')->nullable();
            $table->longText('activity_details')->nullable();
            $table->longText('data')->nullable();
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
        Schema::dropIfExists('scorm_registration_progress');
    }
}
