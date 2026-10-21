<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScormRegistrationInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scorm_registration_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scorm_registration_id')->index();
            $table->foreign('scorm_registration_id')->references('id')->on('scorm_registrations')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('type');
            $table->string('interaction_id');
            $table->string('interaction_type');
            $table->dateTime('interaction_timestamp')->nullable();
            $table->json('correct_responses')->nullable();
            $table->integer('point_value')->nullable();
            $table->longText('learner_response')->nullable();
            $table->string('result')->nullable();
            $table->string('latency')->nullable();
            $table->json('objectives')->nullable();
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
        Schema::dropIfExists('scorm_registration_interactions');
    }
}
