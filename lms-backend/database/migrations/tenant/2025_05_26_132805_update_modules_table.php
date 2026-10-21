<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('validity')->nullable()->default('anytime');
            $table->date('available_from')->nullable()->after('description')->default(null);
            $table->date('available_to')->nullable()->after('available_from')->default(null);
        });

        //update validity to anytime or null
        $modules = DB::table('modules')
            ->whereIn('valid_until', ['anytime', 'lock'])
            ->orWhereNull('valid_until')
            ->get();

        foreach ($modules as $module) {
            DB::table('modules')
                ->where('id', $module->id)
                ->update([
                    'validity' => $module->valid_until
                ]);
        }

        $modules = DB::table('modules')
            ->whereNotNull('valid_until')
            ->whereNotIn('valid_until', ['anytime', 'lock'])
            ->get();

        
        foreach ($modules as $module) {
            DB::table('modules')
                ->where('id', $module->id)
                ->update([
                    'validity' => 'date',
                    'available_to' => $module->valid_until,
                ]);
        }

        Schema::table('modules', function (Blueprint $table) {
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
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['available_from', 'available_to']);
        });
    }
}
