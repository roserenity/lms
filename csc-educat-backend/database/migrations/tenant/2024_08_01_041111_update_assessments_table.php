<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
     public function up()
     {
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'total_points')) {
                $table->decimal('total_points', 5, 2)->unsigned()->default(0);
            }
        });
     }
 
    /**
     * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'total_points')) {
                $table->dropColumn('total_points');
            }
        });
    }
}
