<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExcludeExecutable;

class UpdateTopicRequest extends FormRequest
{
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
    if ($this->has('is_downloadable')) {
      return [
        'module_id' => 'required',
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
        'is_downloadable' => 'sometimes|boolean'
      ];
    } else {
      return [
        'module_id' => 'required',
        'title' => 'required|max:255',
      ];
    }
  }
}
