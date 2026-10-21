<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use App\Jobs\Tenants\SendCertificateOfCompletionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Mail\Tenants\Certificate\CertificateOfCompletion;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\StudentCourseProgress;
use App\Models\Image;
use App\Models\StudentCertificateAssignment;
use Illuminate\Support\Facades\Log;
use NumberFormatter;
use PDF;

use App\Helpers\Helper;
use App\Helpers\StandaloneConfig;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function certificates(Request $request)
    {
        $certificates = Certificate::has('course')->with(['image', 'course' => fn($course) =>
                $course->select(['id', 'title'])
            ])
            ->where('user_id', $this->authUser()->id)
            ->paginate($request->paginate);
        return $this->sendResponse($certificates, 'List of all certificates.', 200);
    }

    public function generateCertificate(Request $request)
    {

        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $course = Course::where('id', $request->course_id)
            ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
            ->withCount([
                'my_assessment_score as total_checked_assessments' => function ($query) {
                    $query->where('checked', true);
                }
            ])
            ->first();
        
        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }

        $user_id = $this->authUser()->id;
        
        $progress = [
            'total_checked_assessments' => $course->total_checked_assessments ?? 0,
            'total_done_topics' => $course->getTotalDoneTopics($user_id),
            'total_done_sub_topics' => $course->getTotalDoneSubTopics($user_id),
            'total_done_assessments' => $course->getTotalDoneAssessments($user_id),
            'total_done_course_evaluations' => $course->getTotalDoneCourseEvaluations($user_id),
            'total_done_module_evaluations' => $course->getTotalDoneModuleEvaluations($user_id),
            'total_attended_zoom_meetings' => $course->getTotalAttendedZoomMeetings($user_id),
            'total_done_group_activities' => $course->getTotalDoneGroupActivities($user_id),
            'total_time_spent' => $course->getTotalTimeSpent($course->id, $user_id),
            'topics_count' => $course->topics_count,
            'sub_topics_count' => $course->sub_topics_count,
            'assessments_count' => $course->assessments_count,
            'course_evaluations_count' => $course->course_evaluations_count,
            'module_evaluations_count' => $course->module_evaluations_count,
            'zoom_meetings_count' => $course->zoom_meetings_count,
            'group_activities_count' => $course->group_activities_count,
        ];

        $progress_percent = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended((object)$progress) : Helper::calculateLearnerProgressPercentElearning((object)$progress);

        StudentCourseProgress::updateOrCreate([
            'user_id' => $user_id,
            'course_id' => $request->course_id
        ], [
            ... $progress,
            'progress' => $progress_percent
        ]);

        if($progress_percent != 100){
            return $this->sendResponse(['progress' => $progress_percent], 'Complete the course to receive your certificate.', 409);
        }

        try {

            $course = Course::with(['class_categories', 'training_credit'])->find($request->course_id);
    
            if (!$course) {
                return $this->sendResponse([], 'Course not found.', 404);
            }
    
            $student_certificate = StudentCertificateAssignment::where('user_id', $this->authUser()->id)->where('course_id', $request->course_id)->pluck('certificate_category_id')->toArray();

            if (empty($student_certificate)) {
                $certificate_templates = CertificateTemplate::where('certificate_category_id', 1)->where('is_default', 1)->get();
            } else {
                $certificate_templates = CertificateTemplate::whereIn('certificate_category_id' , $student_certificate)->with(['logo', 'attestor', 'signatory'])->where('is_default', 1)->get();
            }

            $data_collection = [];
            
            $certificate = Certificate::where('user_id', $this->authUser()->id)
                ->where('course_id', $course->id)
                ->with('image')
                ->first();
 
            if (!$certificate) {
                $certificate = Certificate::create([
                    'user_id' => $this->authUser()->id,
                    'course_id' => $request->course_id,
                ]);
                $qrCode = QrCode::size(90)->generate(StandaloneConfig::domainName() . '/validate-certificate/' . $certificate->uuid);
            } else {
                $qrCode = QrCode::size(90)->generate(StandaloneConfig::domainName() . '/validate-certificate/' . $certificate->uuid);
            }
            
            $html = '<img src="data:image/png;base64,' . base64_encode($qrCode) . '">';

            foreach($certificate_templates as $certificate_template) {
                Log::info(json_encode($certificate_template));
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
                

                $data = [
                    'name' => (string) $this->authUser()->full_name,
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

                // Log::info($content);
                Log::info($course);

                $data_collection[] = [
                    'content' => $content, 
                    'tenant_id' => StandaloneConfig::tenantId(),
                    'qrcode' => $qrCode,
                    'qrcodePDF' => $html,
                    'program' => $course->training_credit ? $course->training_credit->name : null,
                    'type_of_learning'=>$course->type_of_learning,
                    'email' => $this->authUser()->email,
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
                Log::info($certificate_template);
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
            
            Log::info('Certificate Details:', [
                'certificate_id' => $certificate->id,
                'certificate_uuid' => $certificate->uuid,
                'image' => $certificate->image,
                'url' => $url,
            ]);
            
            $data['file_url'] = $url;
            $data['certificate_uuid'] = $certificate->uuid;

            $apiKey = config("services.sender.key");

            if($apiKey) {
                try {
                    SendCertificateOfCompletionMail::dispatch($data);
                } catch (\Exception $e) {
                    Log::error('Email sending error: ' . $e);
                }
            } else {
                Mail::to($this->authUser()->email)->send(new CertificateOfCompletion($data));
            }
                
            return $this->sendResponse($certificate->image->url, 'Certificate generated.', 201);
        } catch (\Exception $e) {
            Log::error("Certificate error: " . $e->getMessage());
            return $this->sendResponse([], 'Something went wrong.', 500);
        }
    }

    public function downloadCertificate(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $path = ltrim(parse_url($request->url, PHP_URL_PATH), '/');
        $parts = explode('/', $path);
        $file_path = implode('/', array_slice($parts, 1));

        $expires = 60;
        // Create temporary URL
        $temporary_url = Storage::disk('s3')->temporaryUrl(
            $file_path,
            now()->addMinutes($expires),
            [
                'ResponseContentType' => 'application/octet-stream',
                'ResponseContentDisposition' => 'attachment; filename="' . basename($file_path) . '"',
            ]
        );

        return response()->json([
            'result' => 'success',
            'downloadable_url' => $temporary_url,
        ]);
        
    }
}
