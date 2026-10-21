<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateScormRegistrationProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scorm_registrations', function (Blueprint $table) {
            $table->foreign('scorm_registration_progress_id')
                ->references('id')
                ->on('scorm_registration_progress')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scorm_registrations', function (Blueprint $table) {
            $table->dropForeign(['scorm_registration_progress_id']);
        });
    }
}
