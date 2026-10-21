<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomModuleFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_module_fields', function (Blueprint $table) {
            $table->id();
            $table->string('input_field_label');
            $table->string('input_field_name');
            $table->string('input_field_type');
            $table->boolean('is_multiple');
            $table->integer('sort_no')->default(0);
            $table->json('data')->nullable();
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
        Schema::dropIfExists('custom_module_fields');
    }
}
