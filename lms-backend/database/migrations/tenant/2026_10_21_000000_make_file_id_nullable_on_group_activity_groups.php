<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFileIdNullableOnGroupActivityGroups extends Migration
{
    /**
     * Groups are created before their Google Drive file is generated.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_activity_groups', function (Blueprint $table) {
            $table->string('file_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_activity_groups', function (Blueprint $table) {
            $table->string('file_id')->nullable(false)->change();
        });
    }
}
