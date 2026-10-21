<?php

namespace App\Jobs\Tenants;

use App\Models\Course;
use App\Exports\Tenants\Teacher\ZoomMeetingParticipantsSummary;
use App\Models\Exports;
use App\Models\ZoomMeeting;
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

class ProcessInstructorZoomMeetingParticipantsExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $export_record_id;
    private $topic;
    private $course_id;
    private $zoom_meeting_id;
    private $search;
    private $status;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $topic, $course_id, $zoom_meeting_id, $search, $status)
    {
        $this->export_record_id = $export_record_id;
        $this->topic = $topic;
        $this->course_id = $course_id;
        $this->zoom_meeting_id = $zoom_meeting_id;
        $this->search = $search;
        $this->status = $status;
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

        try {

            $course = Course::find($this->course_id);
            $zoom = ZoomMeeting::findOrFail($this->zoom_meeting_id);

            $export = new ZoomMeetingParticipantsSummary(
                $this->topic,
                $this->course_id,
                $this->zoom_meeting_id,
                $this->search,
                $this->status
            );

            $zoom_data = is_string($zoom->data) ? json_decode($zoom->data) : $zoom->data;
            $topic = $zoom_data['topic'] ?? 'untitled';

            $filename = 'exports/zoom_meeting/'. Str::limit(Str::slug($topic, '_'), 50, '') . '_meeting_' . now()->format('Ymd_His') . '.csv';

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
