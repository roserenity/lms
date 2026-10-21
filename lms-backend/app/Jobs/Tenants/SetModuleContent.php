<?php

namespace App\Jobs\Tenants;

use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Log;

class SetModuleContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        try {

            $modules = Module::with(['topics'])->get();
    
            foreach($modules as $module) {
                $sort_no = 1;

                foreach ($module->topics as $topic) {
                    ModuleContent::create([
                        'module_id' => $module->id,
                        'contentable_type' => 'App\Models\Topic',
                        'contentable_id' => $topic->id,
                        'sort_no' => $sort_no
                    ]);

                    $sort_no++;
                }
            
                Log::info("processing module content {$module->id}");
    
            }
        }  catch (\Exception $e) {
            
            Log::info($e);
        }
    }
}
