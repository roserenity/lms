<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Jobs\Tenants\ProcessCourseEvaluationSummaryExport;
use App\Jobs\Tenants\ProcessModuleEvaluationSummaryExport;
use App\Jobs\Tenants\ProcessCourseEvaluationExport;
use App\Jobs\Tenants\ProcessModuleEvaluationExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleEvaluation;
use App\Models\CourseEvaluation;
use App\Models\CustomModuleField;
use App\Models\Exports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;

use Aws\S3\S3Client;
use Str;
use Log;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function index()
    {
        $exports = Exports::when(request('type'), function($query){
                $query->whereIn('type', request('type'));
            })->where('user_id', $this->authUser()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(request('paginate'));

        return $this->sendResponse($exports, 'List of all exports', 200);
    }

    public function destroy($id)
    {
        $export = Exports::findOrFail($id);

        try {
            if (Storage::disk('s3')->exists($export->path)) {
                Storage::disk('s3')->delete($export->path);
            }
            
            $export->delete();
            return $this->sendResponse([], 'Exported file successfully deleted', 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete export file: ' . $e->getMessage());
    
            return $this->sendError('Failed to delete exported file.', 500);
        }

    }

    public function courseEvaluationListExport(Request $request)
    {

        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'course_evaluation',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessCourseEvaluationExport::dispatch(
            $export->id,
            $request->course_evaluation_id,
            $request->submitted_from,
            $request->submitted_to,
            $request->search
        );
    
        return response()->json([
            'message' => 'Export has been queued.',
            'status' => 'processing',
        ]);
        
    }

    public function moduleEvaluationListExport(Request $request)
    {

        $module_evaluation = ModuleEvaluation::select(['module_id', 'id'])->find($request->module_evaluation_id);

        if (!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        $module = Module::select(['id', 'course_id'])->find($module_evaluation->module_id);

        if (!$module) {
            return $this->sendResponse([], 'No module found.', 404);
        }
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'module_evaluation',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessModuleEvaluationExport::dispatch(
            $export->id,
            $request->module_evaluation_id,
            $request->submitted_from,
            $request->submitted_to,
            $request->search,
            $module->course_id
        );
    
        return response()->json([
            'message' => 'Export has been queued.',
            'status' => 'processing',
        ]);

    }

    public function courseEvaluationSummaryExport(Request $request)
    {
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'course_evaluation',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessCourseEvaluationSummaryExport::dispatch(
            $export->id,
            $request->course_evaluation_id,
            $request->submitted_from,
            $request->submitted_to
        );
    
        return response()->json([
            'message' => 'Export has been queued.',
            'status' => 'processing',
        ]);
    }

    public function moduleEvaluationSummaryExport(Request $request)
    {
        $module_evaluation = ModuleEvaluation::select(['module_id', 'id'])->find($request->module_evaluation_id);
        
        if (!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        $module = Module::select(['id', 'course_id'])->find($module_evaluation->module_id);

        if (!$module) {
            return $this->sendResponse([], 'No module found.', 404);
        }
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'module_evaluation',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        // Dispatch the job
        ProcessModuleEvaluationSummaryExport::dispatch(
            $export->id,
            $request->module_evaluation_id,
            $request->submitted_from,
            $request->submitted_to,
            $module->course_id
        );
    
        return response()->json([
            'message' => 'Export has been queued.',
            'status' => 'processing',
        ]);
    }

    public function courseExport($course_id)
    {
        $course = Course::with(['modules' => fn($query) => $query->with(['topics' => fn($query) => $query->select(['id', 'title', 'module_id', 'course_id'])->with(['sub_topics' => fn($query) => $query->select(['id', 'title', 'topic_id', 'course_id'])])])])->find($course_id);

        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }

        $custom_module_fields = CustomModuleField::select(['input_field_name', 'input_field_label', 'input_field_type'])->get();

        Settings::loadConfig();
        Settings::setZipClass(Settings::PCLZIP);
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $headerStyle = ['size' => 14, 'bold' => true];
        $subHeaderStyle = ['size' => 13, 'bold' => true];

        $section = $phpWord->addSection();
        $section->addText("Title: " . $course->title, $headerStyle);
        $section->addText("Course Code: " . $course->course_code);
        $section->addText("Training hours: " . $course->training_hours);
        $section->addText("Facilitator: " . $course->facilitator);
        $date_conducted = '';

        if (isset($course->available_until['start_date'], $course->available_until['end_date'])) {
            $startDate = strtotime($course->available_until['start_date']);
            $endDate = strtotime($course->available_until['end_date']);

            if (date('Y', $startDate) == date('Y', $endDate)) {
                if ($course->available_until['start_date'] == $course->available_until['end_date']) {
                    $date_conducted = date('F d, Y', $endDate);
                } else {
                    $date_conducted = date('F d', $startDate) . ' - ' . date('F d, Y', $endDate);
                }
            } else {
                $date_conducted = date('F d, Y', $startDate) . ' - ' . date('F d, Y', $endDate);
            }
        }
        $section->addText("Conduct dates: " . $date_conducted);
        $section->addText("Training Credits: " . $course->training_credits);
        $section->addText("Type of Learning: " . $course->type_of_learning);

        if ($course->description) {
            $html = $course->description;
            $html = str_replace('text-align: justify;', 'text-align: left;', $html);
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, true);
        }

        $section->addPageBreak();
        $section->addText("Modules", $headerStyle);
        $section->addTextBreak();

        foreach ($course->modules as $module) {
            $section->addText("Title: $module->title", $subHeaderStyle);
            $section->addText("Description: " . $module->description);
            $section->addText("Contact Person: " . ($module->contact_person ?: 'N/A'));
            $section->addText("Version: " . ($module->version ?: 'N/A'));
            $section->addText("Language: " . (!empty($module->language) && is_array($module->language)
                ? implode(', ', $module->language) : 'N/A'));

            if($module->data) {
                foreach($custom_module_fields as $custom_module_field) {
                    if($module->data[$custom_module_field->input_field_name]){
                        if($custom_module_field->input_field_type == CustomModuleField::TYPE_DROPDOWN) {
                            $section->addText($custom_module_field->input_field_label .": " . (!empty($module->data[$custom_module_field->input_field_name]) && is_array($module->data[$custom_module_field->input_field_name]) ? implode(', ', $module->data[$custom_module_field->input_field_name]) : 'N/A'));
                        } else {
                            $section->addText($custom_module_field->input_field_label .": " . ($module->data[$custom_module_field->input_field_name] ?: 'N/A'));
                        }
                    }
                }
            }

            if (!empty($module->topics)) {
                $section->addText("Topics: ");
                foreach ($module->topics as $topicIndex => $topic) {
                    $topicNumber = $topicIndex + 1;
                    $section->addText("$topicNumber. " . $topic->title);
                    $section->addTextBreak(0,10);

                    if (!empty($topic->sub_topics)) {
                        foreach ($topic->sub_topics as $subTopicIndex => $sub_topic) {
                            $subTopicNumber = $topicNumber . '.' . ($subTopicIndex + 1);
                            $section->addListItem("$subTopicNumber. " . $sub_topic->title, 1);
                        }
                    }
                }
            }

            $section->addTextBreak();
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        $fileName = 'course-' . Str::limit(Str::slug($course->title, '_'), 50, '') . '.docx';
        Storage::disk('public')->put($fileName, file_get_contents($tempFile));

        unlink($tempFile);

        return response()->download(Storage::disk('public')->path($fileName))->deleteFileAfterSend(true);
    }

    public function downloadExportFile(Request $request)
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
