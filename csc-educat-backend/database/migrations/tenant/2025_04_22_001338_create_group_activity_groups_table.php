<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupActivityGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_activity_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_activity_id');
            $table->foreign('group_activity_id')->references('id')->on('group_activities')->onDelete('cascade');
            $table->string('name');
            $table->string('file_id');
            $table->string('file_url')->nullable();
            $table->boolean('checked')->default(0);
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
        Schema::dropIfExists('group_activity_groups');
    }
}
