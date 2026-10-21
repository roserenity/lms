<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\Tenants\SendCertificateOfCompletionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Mail\Tenants\Certificate\CertificateOfCompletion;
use App\Models\Course;
use App\Models\User;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Image;
use App\Models\StudentCertificateAssignment;
use Illuminate\Support\Str;
use App\Helpers\StandaloneConfig;

use Log;
use NumberFormatter;
use PDF;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function generateCertificate(Request $request)
    {

        $request->validate([
            'course_uuid' => 'required|exists:courses,uuid',
            'user_id' => 'required|exists:users,id',
        ]);

        try {

            $course = Course::with(['class_categories', 'training_credit'])->where('uuid', $request->course_uuid)->first();
    
            if (!$course) {
                return $this->sendResponse([], 'Course not found.', 404);
            }
    
            $student_certificate = StudentCertificateAssignment::where('user_id', $request->user_id)->where('course_id', $request->course_id)->pluck('certificate_category_id')->toArray();

            if (empty($student_certificate)) {
                $certificate_templates = CertificateTemplate::where('certificate_category_id', 1)->where('is_default', 1)->get();
            } else {
                $certificate_templates = CertificateTemplate::whereIn('certificate_category_id' , $student_certificate)->with(['logo', 'attestor', 'signatory'])->where('is_default', 1)->get();
            }

            $data_collection = [];
            
            $certificate = Certificate::where('user_id',  $request->user_id)
                ->where('course_id', $course->id)
                ->with('image')
                ->first();
 
            if (!$certificate) {
                $certificate = Certificate::create([
                    'user_id' => $request->user_id,
                    'course_id' => $course->id,
                ]);
                $qrCode = QrCode::size(90)->generate(StandaloneConfig::domainName() . '/validate-certificate/' . $certificate->uuid);
            } else {
                $qrCode = QrCode::size(90)->generate(StandaloneConfig::domainName() . '/validate-certificate/' . $certificate->uuid);
            }
            
            $html = '<img src="data:image/png;base64,' . base64_encode($qrCode) . '">';

            foreach($certificate_templates as $certificate_template) {
                $issue_date = $certificate->created_at;

                $training_hours = (int) $course->credit_training_hours; // Ensure it's an integer

                $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
                $training_hours_words = $formatter->format($training_hours); // Capitalize first letter

                $date_conducted_str = '';

                if (isset($course->available_until['start_date'], $course->available_until['end_date'])) {
                    $startDate = strtotime($course->available_until['start_date']);
                    $endDate = strtotime($course->available_until['end_date']);

                    if (date('Y', $startDate) == date('Y', $endDate)) {
                        if ($course->available_until['start_date'] == $course->available_until['end_date']) {
                            $date_conducted_str = 'on ' . date('F d, Y', $endDate);
                        } else {
                            $date_conducted_str = 'on ' . date('F d', $startDate) . ' - ' . date('F d, Y', $endDate);
                        }
                    } else {
                        $date_conducted_str = 'on ' . date('F d, Y', $startDate) . ' - ' . date('F d, Y', $endDate);
                    }
                }

                $user = User::find($request->user_id);

                $data = [
                    'name' => (string) $user->full_name,
                    'course_title' => (string) $course->title,
                    'credit_training_hours' => (string) $training_hours_words .' ['.$course->credit_training_hours.']',
                    'date_received' => (string) date('jS \d\a\y \o\f F Y', strtotime($issue_date)),
                    'training_credits' => $course->training_credit ? $course->training_credit->name : '',
                    'course_conducted' => (string) $date_conducted_str,
                ];

                $certificate_content = json_decode($certificate_template->content, true);

                if (!is_array($certificate_content)) {
                    throw new \Exception('Certificate content could not be decoded into an array');
                }

                foreach ($certificate_content as &$item) {
                    if (isset($item['text']) && is_string($item['text'])) {
                        $item['text'] = preg_replace_callback('/{{\s*([\w\.]+)\s*}}/', function ($matches) use ($data) {
                            return $data[$matches[1]] ?? '';
                        }, $item['text']);
                        Log::info('After Replacement:', [$item['text']]);
                    }
                }

                // Log::info($certificate_content);
                $content = json_encode($certificate_content, JSON_PRETTY_PRINT);


                $data_collection[] = [
                    'content' => $content, 
                    'tenant_id' => StandaloneConfig::tenantId(),
                    'qrcode' => $qrCode,
                    'qrcodePDF' => $html,
                    'program' => $course->training_credit ? $course->training_credit->name : null,
                    'type_of_learning'=>$course->type_of_learning,
                    'email' => $user->email,
                    'logo' => $certificate_template->logo ? $certificate_template->logo->image->url : null,
                    'attestor_name' => $certificate_template->attestor->first() ? $certificate_template->attestor->first()->name : null,
                    'attestor_designation' => $certificate_template->attestor->first() ? $certificate_template->attestor->first()->designation : null,
                    'attestor_info' => $certificate_template->attestor->first() ? $certificate_template->attestor->first()->info : null,
                    'attestor_esign' => $certificate_template->attestor->first() ? $certificate_template->attestor->first()->image->url : null,
                    'signatory_name' => $certificate_template->signatory->first() ? $certificate_template->signatory->first()->name : null,
                    'signatory_designation' => $certificate_template->signatory->first() ? $certificate_template->signatory->first()->designation : null,
                    'signatory_info' => $certificate_template->signatory->first() ? $certificate_template->signatory->first()->info : null,
                    'signatory_esign' => $certificate_template->signatory->first() ? $certificate_template->signatory->first()->image->url : null,
                ];
            }

            $html = '';
            foreach ($data_collection as $data) {
                $view = 'certificates.tenants.' . $data['tenant_id'] . '.certificate';
            
                if (!View::exists($view)) {
                    $view = 'certificates.tenants.default.certificate';
                }
            
                $html .= view($view, $data)->render() . '<div style="page-break-after: always;"></div>';
            }
            $pdf = Pdf::loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $content = $pdf->download()->getOriginalContent();
            $path = 'certificates/certificate_of_completion/' . $certificate->uuid . '.pdf';
            if (config('app.env') == 'prod') {
                Storage::disk('s3')->put($path, $content);
                $url = Storage::disk('s3')->url($path);
                //$url = 'https://your-bucket.s3.amazonaws.com/tenant_' . StandaloneConfig::tenantId() . '/certificates/certificate_of_completion/' . $cert_uuid . '.pdf';
            } else {
                Storage::disk('public')->put($path, $content);
                $url = asset($path);
            }
            
            Image::updateOrCreate(
                ['imageable_id' => $certificate->id],
                [
                    'imageable_type' => Certificate::class,
                    'url' => $url,
                ]
            );
            
            $certificate->refresh();
    
            if (!$certificate->image) {
                return $this->sendResponse([], 'Failed to create/update imageable to the certificate.', 500);
            }
            
            return $this->sendResponse($certificate->image->url, 'Certificate generated.', 201);
        } catch (\Exception $e) {
            Log::error("Certificate error: " . $e->getMessage());
            return $this->sendResponse([], 'Something went wrong.', 500);
        }
    }
}
