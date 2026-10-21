<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssessmentScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
     public function up()
     {
        Schema::table('assessment_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_scores', 'status')) {
                $table->string('status')->comment('PASSED', 'FAILED');
            }
            $table->softDeletes();
        });
     }
 
     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::table('assessment_scores', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_scores', 'status')) {
                $table->dropColumn('status');
            }
            $table->dropSoftDeletes();
         });
     }
}
