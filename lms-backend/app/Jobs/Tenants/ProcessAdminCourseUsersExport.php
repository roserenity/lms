<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Admin\DashboardCourseUsersExport;
use App\Models\Course;
use App\Models\Exports;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

use Log;

class ProcessAdminCourseUsersExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $export_record_id;
    private $filter;
    private $value;
    private $field;
    private $search;
    private $date_from;
    private $date_to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $filter, $value, $field, $search, $date_from, $date_to)
    {

        $this->export_record_id = $export_record_id;
        $this->filter = $filter;
        $this->value = $value;
        $this->field = $field;
        $this->search = $search;
        $this->date_from = $date_from;
        $this->date_to = $date_to;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $exportrecord = Exports::find($this->export_record_id);

        $course = Course::where('id', $this->value)->first();
        
        ini_set('memory_limit', -1); 

        try {

            $export = new DashboardCourseUsersExport(
                $this->export_record_id,
                $this->filter,
                $this->value,
                $this->field,
                $this->search,
                $this->date_from,
                $this->date_to
            );
            
            $filename = 'exports/course/'. Str::limit(Str::slug($course->title, '_'), 50, '') .'_enrollees_' . now()->format('Ymd_His') . '.csv';

            if (config('app.env') == 'prod') {
                Excel::store($export, $filename, 's3', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('s3')->url($filename);
            } else {
                Excel::store($export, $filename, 'public', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('public')->url($filename);
            }

            $exportrecord->update([
                'filename' => basename($filename),
                'path' => $filename,
                'url' => $url,
                'status' => 'completed',
            ]);

        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            $exportrecord->update(['status' => 'failed']);
        }
    }
}
