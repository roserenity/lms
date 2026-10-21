<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExcludeExecutable;

use App\Models\Topic;

class TopicRequest extends FormRequest
{

  protected $stopOnFirstFailure = true;

  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'course_id' => 'required|exists:courses,id',
      'module_id' => 'required|exists:modules,id',
      'title' => 'required|max:255',
      'file' => [
          'required_without_all:youtube_link,s3_link,gdrive_link,scorm_file',
          'max:1000000',
          'mimes:mp4,mov,webm,doc,docx,pdf,ppt,pptx,jpg,jpeg,png',
          new ExcludeExecutable
      ],
      'youtube_link' => 'required_without_all:file,s3_link,gdrive_link,scorm_file|url',
      's3_link' => 'required_without_all:file,youtube_link,gdrive_link,scorm_file|url',
      'gdrive_link' => 'required_without_all:file,youtube_link,s3_link,scorm_file|url',
      'scorm_file' => [
        'required_without_all:file,youtube_link,s3_link,gdrive_link',
        'file',
        'max:1000000',
        'mimes:zip',
        new ExcludeExecutable,
      ],
      'sort_no' => 'required',
      'uploader_id' => 'required',
      'is_downloadable' => 'required|boolean'
    ];
  }

  protected function prepareForValidation()
  {
    $this->merge([
      'sort_no' => Topic::count() > 0 ? Topic::max('sort_no') + 1 : 0,
      'uploader_id' => auth()->guard('api')->user()->id
    ]);
  }
}
