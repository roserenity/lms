<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCourseEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_evaluations', function (Blueprint $table) {
            $table->string('validity')->nullable()->default('anytime');
            $table->date('available_from')->nullable()->after('description')->default(null);
            $table->date('available_to')->nullable()->after('available_from')->default(null);
        });

        //update validity to anytime or null
        $course_evaluations = DB::table('course_evaluations')
            ->whereIn('valid_until', ['anytime', 'lock'])
            ->orWhereNull('valid_until')
            ->get();

        foreach ($course_evaluations as $course_evaluation) {
            DB::table('course_evaluations')
                ->where('id', $course_evaluation->id)
                ->update([
                    'validity' => $course_evaluation->valid_until
                ]);
        }

        $course_evaluations = DB::table('course_evaluations')
            ->whereNotNull('valid_until')
            ->whereNotIn('valid_until', ['anytime', 'lock'])
            ->get();

        
        foreach ($course_evaluations as $course_evaluation) {
            DB::table('course_evaluations')
                ->where('id', $course_evaluation->id)
                ->update([
                    'validity' => 'date',
                    'available_to' => $course_evaluation->valid_until,
                ]);
        }

        Schema::table('course_evaluations', function (Blueprint $table) {
            $table->dropColumn('valid_until');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_evaluations', function (Blueprint $table) {
            $table->dropColumn(['available_from', 'available_to']);
        });
    }
}
