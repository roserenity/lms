<?php

namespace App\Jobs\Tenants;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

use Log;

class SetTransactionData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public static function refId()
    {
        $format = 'ABCDEF';
        $randStr = str_replace(
            ['A', 'B', 'C', 'D', 'E', 'F'],
            [
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
            ],
            $format
        );

        return date('ymhis') . $randStr;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

            $enrollments = Enrollment::doesntHave('transactions')->get();
    
            foreach($enrollments as $enrollment) {
    
                $invoice = Invoice::where('course_id', $enrollment->course_id)->where('user_id', $enrollment->user_id)->exists();
    
                if(!$invoice) {
    
                    $ref_id = self::refId();
    
                    $course = Course::find($enrollment->course_id);

                    if($course) {

                        if ($course->price > 0) {
                            Invoice::create([
                                'reference_id' => $ref_id,
                                'user_id' => $enrollment->user_id,
                                'course_id' => $enrollment->course_id,
                                'mode_of_payment' => Invoice::OFFLINE,
                                'amount' => $course->price,
                                'is_free' => $course->is_free,
                                'status' => Invoice::PAYMENT_PENDING,
                                'enroll_by_id' => 1
                            ]);
                        } else {
                            Invoice::create([
                                'reference_id' => $ref_id,
                                'user_id' => $enrollment->user_id,
                                'course_id' => $enrollment->course_id,
                                'mode_of_payment' => Invoice::OFFLINE,
                                'amount' => $course->price,
                                'is_free' => $course->is_free,
                                'status' => Invoice::PAYMENT_FREE,
                                'enroll_by_id' => 1
                            ]);
                        }

                    }
    
                }
    
            }
        }  catch (\Exception $e) {
            
            Log::info($e);
        }

    }
}
