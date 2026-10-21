<?php

namespace App\Jobs\Tenants;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

use App\Models\Certificate;
use App\Models\User;

class SetLearnerCertificateActivityLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function processCertificate($certificate) {

        $user = User::find($certificate->user_id);

        if($user) {

            $activity = Activity::where('log_name', 'user_certificate_gen')
                ->where('causer_id', $user->id)
                ->where('properties->course_id', $certificate->course_id)
                ->latest()
                ->first();

            if ($activity) {
                $activity->update([
                    'created_at' => $certificate->created_at,
                    'updated_at' => $certificate->created_at,
                ]);
            } else {
                activity('user_certificate_gen')
                    ->causedBy($user)
                    ->withProperties([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'course_id' => $certificate->course_id,
                    ])
                    ->event('created')
                    ->performedOn($user)
                    ->log('User Certificate Generated');

                Activity::where('log_name', 'user_certificate_gen')
                    ->where('causer_id', $user->id)
                    ->where('properties->course_id', $certificate->course_id)
                    ->latest()
                    ->first()
                    ->update([
                        'created_at' => $certificate->created_at,
                        'updated_at' => $certificate->created_at,
                    ]);
            }

        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Processing data for Tenant ID:". $this->tenantId);

        Certificate::chunk(100, function ($certificates) {
            
            if ($certificates->isEmpty()) {
                Log::info("No certificates found to process.");
                return;
            }

            foreach ($certificates as $certificate) {
                $this->processCertificate($certificate);
            }
        });
        
        Log::info("Finished processing data");
    }
}
