<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomRegistrationFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_registration_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_registration_page_id')->index();
            $table->foreign('custom_registration_page_id')->references('id')->on('custom_registration_pages')->onDelete('cascade');
            $table->string('input_field_label');
            $table->string('input_field_name');
            $table->string('input_field_type');
            $table->boolean('is_required');
            $table->boolean('is_multiple');
            $table->integer('sort_no')->default(0);
            $table->json('data')->nullable();
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
        Schema::dropIfExists('custom_registration_fields');
    }
}
