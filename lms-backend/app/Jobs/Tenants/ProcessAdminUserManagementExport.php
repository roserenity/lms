<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Admin\UserManagementExport;
use App\Models\Exports;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use Log;

class ProcessAdminUserManagementExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $export_record_id;
    private $role;
    private $status;
    private $search;
    private $created_to;
    private $created_from;
    private $other_filters;
    private $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $role, $status, $search, $created_to, $created_from, $other_filters, $request)
    {
        $this->export_record_id = $export_record_id;
        $this->role = $role;
        $this->status = $status;
        $this->search = $search;
        $this->created_to = $created_to;
        $this->created_from = $created_from;
        $this->other_filters = $other_filters;
        $this->request = $request->all();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        ini_set('memory_limit', -1); 

        $exportRecord = Exports::find($this->export_record_id);

        try {

            $export = new UserManagementExport(
                $this->role,
                $this->status,
                $this->search,
                $this->created_to,
                $this->created_from,
                $this->other_filters,
                $this->request,
            );

            $filename = 'exports/user/user_management_' . now()->format('Ymd_His') . '.csv';

            if (config('app.env') == 'prod') {
                Excel::store($export, $filename, 's3', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('s3')->url($filename);
            } else {
                Excel::store($export, $filename, 'public', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('public')->url($filename);
            }

            $exportRecord->update([
                'filename' => basename($filename),
                'path' => $filename,
                'url' => $url,
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            $exportRecord->update(['status' => 'failed']);
        }
    }
}
