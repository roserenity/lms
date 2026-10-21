<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShareRubricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('share_rubrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rubric_id')->index();
            $table->foreign('rubric_id')->references('id')->on('rubrics')->onDelete('cascade');
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
        Schema::dropIfExists('share_rubrics');
    }
}
