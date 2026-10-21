<?php

namespace App\Observers;

use App\Jobs\Tenants\SendCommitmentLetterConfimationEmail;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Mail;
use App\Mail\Tenants\Payment\PaymentCommitmentLetter;
use Log;
use Str;

class InvoiceObserver
{
    public function creating(Invoice $invoice)
    {
        $invoice->uuid = Str::uuid();
    }

    /**
     * Handle the Invoice "created" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function created(Invoice $invoice)
    {
        $course = Course::find($invoice->course_id);
        $user = User::find($invoice->user_id);

        if($invoice->status === Invoice::PAYMENT_COMMITMENT_LETTER) {

            $data = [
                'name' => $user->full_name,
                'course_title' => $course->title,
                'email' => $user->email
            ];
            $apiKey = config("services.sender.key");

            if($apiKey) {
                SendCommitmentLetterConfimationEmail::dispatch($data);
            } else {
                Mail::to($user->email)->send(new PaymentCommitmentLetter($data));
            }

        }
        
        activity('user_transaction')
            ->causedBy(auth()->guard('api')->user())
            ->withProperties([
                'user_id' => $invoice->user_id,
                'email' => $user->email,
                'course_id' => $invoice->course_id,
                'status' => $invoice->status
            ])
            ->event('created')
            ->performedOn($user)
            ->log('User Transaction');

    }

    /**
     * Handle the Invoice "updated" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function updated(Invoice $invoice)
    {
        $course = Course::find($invoice->course_id);
        $user = User::find($invoice->user_id);
        
        if ($invoice->wasChanged('status')) {
            $previous_status = $invoice->getOriginal('status');
            if($invoice->status == Invoice::PAYMENT_COMMITMENT_LETTER && $previous_status != Invoice::PAYMENT_DISAPPROVED) {

                $data = [
                    'name' => $user->full_name,
                    'course_title' => $course->title,
                    'email' => $user->email
                ];

                $apiKey = config("services.sender.key");

                if($apiKey) {
                    SendCommitmentLetterConfimationEmail::dispatch($data);
                } else {
                    Mail::to($user->email)->send(new PaymentCommitmentLetter($data));
                }
            }
        }
        
        activity('user_transaction')
            ->causedBy(auth()->guard('api')->user())
            ->withProperties([
                'user_id' => $invoice->user_id,
                'email' => $user->email,
                'course_id' => $invoice->course_id,
                'status' => $invoice->status
            ])
            ->event('update')
            ->performedOn($user)
            ->log('User Transaction');

    }

    /**
     * Handle the Invoice "deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function deleted(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function restored(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function forceDeleted(Invoice $invoice)
    {
        //
    }
}
