<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupActivityHasGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_activity_has_group', function (Blueprint $table) {
            $table->unsignedBigInteger('group_activity_group_id');
            $table->foreign('group_activity_group_id')->references('id')->on('group_activity_groups')->onDelete('cascade');
            $table->unsignedBigInteger('group_activity_id');
            $table->foreign('group_activity_id')->references('id')->on('group_activities')->onDelete('cascade');
            $table->primary(['group_activity_group_id', 'group_activity_id'], 'gag_groupactivity_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_activity_has_group');
    }
}
