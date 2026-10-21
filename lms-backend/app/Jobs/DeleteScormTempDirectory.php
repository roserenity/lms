<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;

class DeleteScormTempDirectory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempDirectory;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Storage::disk('public')->exists($this->tempDirectory)) {
            Storage::disk('public')->deleteDirectory($this->tempDirectory);
        }
    }
}
