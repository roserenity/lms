<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tenants\ImageService;
use App\Models\Invoice;
use App\Models\Course;
use App\Models\ProofOfPayment;

use Illuminate\Support\Facades\Http;

use App\Helpers\FilterHelper;
use App\Models\CommitmentLetter;
use App\Models\EndorsementLetter;
use App\Models\Enrollment;
use App\Models\PrimeResolution;
use App\Services\Tenants\CheckoutService;
use Illuminate\Validation\Rule;
use Storage;
use Log;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function getTransactionHistory(Request $request)
    {
        $invoices = Invoice::
            with(['image', 
                'course' => fn($course) => $course->select(['id', 'uuid', 'title', 'price']),
                'nomination_form', 
                'enroll_by', 
                'proof_of_payment',
                'commitment_letter',
                'prime_resolution',
                'endorsement_letter',
                'enrollee' => fn($enrollee) => $enrollee->select(['id', 'email'])])
            ->where('user_id', $this->authUser()->id)
            ->orderBy('id', 'DESC')
            ->where('is_free', $request->is_free)
            ->when($request->invoice_status, function ($query) use ($request) {
                $query->where('status', $request->invoice_status);
            })
            ->when($request->mode_of_payment, function ($query) use ($request) {
                $query->where('mode_of_payment', $request->mode_of_payment);
            })
            ->when($request->type_of_payment, function ($query) use ($request) {
                $query->where('type_of_payment', $request->type_of_payment);
            })
            ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
            })
            ->paginate($request->paginate);

        $invoices->getCollection()->each(function ($invoice) {
            $enrollments = Enrollment::where('user_id', $invoice->user_id)
                ->where('course_id', $invoice->course_id)
                ->first();
            $invoice->setRelation('enrollment', $enrollments);
        });

        return $this->sendResponse($invoices, 'Learning Transaction History', 200);
    }

    public function uploadNominationForm(Request $request, $invoice_id)
    {
        $request->validate([
            'image' => 'required|mimes:jpg,jpeg,png,pdf'
        ]);

        $invoice = Invoice::where('id', $invoice_id)->first();

        $invoice->update([
            'user_id' => $this->authUser()->id,
            'course_id' => $request->course_id,
            'official_receipt_no' => $request->official_receipt_no,
            'mode_of_payment' => Invoice::OFFLINE
        ]);
        ImageService::uploadOfficialReceipt([
            'model_id' => $invoice->id,
            'model_type' => 'App\\Models\\Invoice',
            'image' => $request->file('image')
        ]);

        return $this->sendResponse([], 'Success!', 201);
    }

    public function uploadProofOfPayment(Request $request) {

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'proof_of_payment' => 'required|mimes:pdf,docx,doc,png,jpg,jpeg|max:10240',
            'reference_number' => 'required|string'
        ], [
            'proof_of_payment.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();

        $proof_of_payment = ProofOfPayment::create([
            'invoice_id' => $invoice->id,
            'reference_number' => $request->reference_number
        ]);

        $path = "proof_of_payment/{$proof_of_payment->id}";

        if (config('app.env') == 'prod') {

            $file_path = Storage::disk('s3')->put($path, $request['proof_of_payment']);

            $url = Storage::disk('s3')->url($file_path);
        } else {

            $file_path = Storage::disk('public')->put($path, $request['proof_of_payment']);

            $url = asset($file_path);
        }

        $proof_of_payment->update([
            'url' => $url,
            'reference_number' => $request->reference_number
        ]);
        
        $invoice->update([
            'for_verification' => true
        ]);

        if($invoice->type_of_payment ==  Invoice::PT_COMMITMENT_LETTER)
        {
            $invoice->update([
                'status' => Invoice::PAYMENT_COMMITMENT_LETTER
            ]);

        } else {

            $invoice->update([
                'status' => Invoice::PAYMENT_PENDING
            ]);
            
        }

        return $this->sendResponse([], 'Success!', 201);
    }

    public function cancelTransaction($invoice_id) {
        
        $invoice = Invoice::where('id', $invoice_id)->first();

        if (!$invoice) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        $invoice->update(['status' => 4]);

        //1 - approved
        //2 - pending
        //3 - failed
        //4 - cancelled

        return $this->sendResponse([], 'Success!', 201);
    }

    public function uploadCommitmentLetter(Request $request) {

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'commitment_letter' => 'required|mimes:pdf,docx,doc|max:10240'
        ], [
            'commitment_letter.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();

        $commitment_letter = CommitmentLetter::create([
            'invoice_id' => $invoice->id,
        ]);

        $path = "commitment_letter/{$commitment_letter->id}";

        if (config('app.env') == 'prod') {

            $file_path = Storage::disk('s3')->put($path, $request['commitment_letter']);

            $url = Storage::disk('s3')->url($file_path);
        } else {

            $file_path = Storage::disk('public')->put($path, $request['commitment_letter']);

            $url = asset($file_path);
        }

        $commitment_letter->update([
            'url' => $url
        ]);

        return $this->sendResponse([], 'Success!', 201);
    }

    public function uploadPrimeRequirements(Request $request) {

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'prime_resolution' => 'required|mimes:pdf,doc,docx|max:10240',
            'endorsement_letter' => 'required|mimes:pdf,doc,docx|max:10240',
        ], [
            'prime_resolution.max' => 'The file must not be larger than 10MB.',
            'endorsement_letter.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();
                
        $files = [
            'endorsement_letter' => EndorsementLetter::class,
            'prime_resolution' => PrimeResolution::class,
        ];
        
        foreach ($files as $fileKey => $model) {
            if ($request[$fileKey]) {
                try {
                    $fileRecord = $model::create(['invoice_id' => $invoice->id, 'url' => '']);
                    $path = "{$fileKey}/{$fileRecord->id}";
                    $url = CheckoutService::uploadRequirement($path, $request[$fileKey], str_replace('_', ' ', $fileKey));
                    $fileRecord->update(['url' => $url]);
                } catch (\Exception $e) {
                    CheckoutService::rollbackFiles($invoice, $files);
                    Log::error("Upload requirement error: " . $e->getMessage());
                    return $this->sendResponse([], 'An error occurred while uploading a requirement.', 500);
                }
            }
        }

        return $this->sendResponse([], 'Success!', 201);
    }

    public function updateDeliveryReceipt(Request $request, $invoice_id)
    {
        $request->validate([
            'delivery_option' => 'required|in:pick_up,courier,email',
            'delivery_address' => [
                'required',
                'string',
                'max:255',
                Rule::when($request->delivery_option === 'email', ['email']),
            ],
        ], [
            'delivery_address.email' => 'The email address provided is not valid.',
        ]);

        $invoice = Invoice::where('id', $invoice_id)->first();

        if (!$invoice) {
            return $this->sendResponse([], 'Invoice not found.', 404);
        }

        $invoice->update([
            'receipt_pick_up_option' => $request->delivery_option,
            'receipt_pick_up_address' => $request->delivery_address,
        ]);

        return $this->sendResponse([], 'Delivery option updated successfully.', 200);
    }

}
