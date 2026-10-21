<?php

namespace App\Jobs\Tenants;

use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Log;

class SetActiveInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tenantId)
    {
        //
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $deleted_invoices = Invoice::onlyTrashed()->get();

        $deleted_invoices->update(['active' => 0]);

        $deleted_invoices->save();

        $invoices = Invoice::orderBy('created_at', 'desc')->get()->groupBy(['user_id', 'course_id']);

        foreach ($invoices as $userInvoices) {
            foreach ($userInvoices as $courseInvoices) {

                // Reset all invoices to inactive first
                foreach ($courseInvoices as $invoice) {
                    $invoice->active = 0;
                    $invoice->save();
                }

                // Mark approved or free invoices as active
                foreach ($courseInvoices->whereIn('status', ['approved', 'free']) as $invoice) {
                    $invoice->active = 1;
                    $invoice->save();
                }

                // Mark the most recent invoice as active
                $latestInvoice = $courseInvoices->sortByDesc('created_at')->first();
                if ($latestInvoice) {
                    $latestInvoice->active = 1;
                    $latestInvoice->save();
                }
            }
        }
    }
}
