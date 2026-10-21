<?php

namespace App\Jobs\Tenants;

use App\Services\Tenants\ResendService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

use Log;

class SendZoomMeetingUpdateMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            ResendService::send($this->data['email'], 'Course Meeting Update', $this->buildHtmlBody());
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    protected function buildHtmlBody()
    {
        return view('emails.tenants.default.course.course_zoom_meeting_update', [
            'name' => $this->data['name'],
            'course_title' => $this->data['course_title'],
            'topic' => $this->data['topic'],
            'agenda' => $this->data['agenda'],
            'start_date' => Carbon::parse($this->data['start_date'])->toDateString(),
            'start_time' => Carbon::createFromTimeString($this->data['start_time'])->format('g:i A'),
            'join_url' => $this->data['join_url'],
        ])->render();
    }
}
