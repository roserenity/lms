<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateModuleEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('module_evaluations', function (Blueprint $table) {
            $table->string('validity')->nullable()->default('anytime');
            $table->date('available_from')->nullable()->after('description')->default(null);
            $table->date('available_to')->nullable()->after('available_from')->default(null);
        });

        //update validity to anytime or null
        $module_evaluations = DB::table('module_evaluations')
            ->whereIn('valid_until', ['anytime', 'lock'])
            ->orWhereNull('valid_until')
            ->get();

        foreach ($module_evaluations as $module_evaluation) {
            DB::table('module_evaluations')
                ->where('id', $module_evaluation->id)
                ->update([
                    'validity' => $module_evaluation->valid_until
                ]);
        }

        $module_evaluations = DB::table('module_evaluations')
            ->whereNotNull('valid_until')
            ->whereNotIn('valid_until', ['anytime', 'lock'])
            ->get();

        
        foreach ($module_evaluations as $module_evaluation) {
            DB::table('module_evaluations')
                ->where('id', $module_evaluation->id)
                ->update([
                    'validity' => 'date',
                    'available_to' => $module_evaluation->valid_until,
                ]);
        }

        Schema::table('module_evaluations', function (Blueprint $table) {
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
        Schema::table('module_evaluations', function (Blueprint $table) {
            $table->dropColumn(['available_from', 'available_to']);
        });
    }
}
