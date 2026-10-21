<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('private_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('private_chat_id')->index();
            $table->foreign('private_chat_id')->references('id')->on('private_chats')->onDelete('cascade');
            $table->longText('message_body')->nullable();
            $table->unsignedBigInteger('to_user_id')->index();
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('from_user_id')->index();
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('private_messages');
    }
}
