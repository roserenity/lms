<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupActivityHasSubTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_activity_has_sub_topics', function (Blueprint $table) {
            $table->unsignedBigInteger('group_activity_id');
            $table->foreign('group_activity_id')->references('id')->on('group_activities')->onDelete('cascade');
            $table->unsignedBigInteger('sub_topic_id');
            $table->foreign('sub_topic_id')->references('id')->on('sub_topics')->onDelete('cascade');
            $table->primary(['group_activity_id', 'sub_topic_id'], 'ga_subtopic_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_activity_has_sub_topics');
    }
}
