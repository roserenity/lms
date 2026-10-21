<?php

namespace App\Services\Tenants;

use App\Models\Attachment;
use Storage;

class AttachmentService
{

  public static function uploadPrivateFileAttachment($data)
  {

      if (config('app.env') == 'local') {

        $path = "/inbox/private/{$data['private_chat_uuid']}/{$data['private_message_uuid']}/attachments";

        $file_path = Storage::disk('public')->put($path, $data['file']);

        $url = asset($file_path);
      } else {

        $path = "inbox/private/{$data['private_chat_uuid']}/{$data['private_message_uuid']}/attachments";

        $file_path = Storage::disk('s3')->put($path, $data['file']);

        $url = Storage::disk('s3')->url($file_path);
      }

      Attachment::create(
        [
          'attachable_id' => $data['model_id'], 
          'attachable_type' => $data['model_type'],
          'file_path' => $url,
          'file_type' => $data['file']->extension(),
          'file_name' => $data['file']->getClientOriginalName()
        ]
    );

    return $url;
  }

  public static function uploadGroupFileAttachment($data)
  {

      if (config('app.env') == 'local') {

        $path = "/inbox/group/{$data['group_chat_uuid']}/{$data['group_message_uuid']}/attachments";

        $file_path = Storage::disk('public')->put($path, $data['file']);

        $url = asset($file_path);
      } else {

        $path = "inbox/group/{$data['group_chat_uuid']}/{$data['group_message_uuid']}/attachments";

        $file_path = Storage::disk('s3')->put($path, $data['file']);

        $url = Storage::disk('s3')->url($file_path);
      }

      Attachment::create(
        [
          'attachable_id' => $data['model_id'], 
          'attachable_type' => $data['model_type'],
          'file_path' => $url,
          'file_type' => $data['file']->extension(),
          'file_name' => $data['file']->getClientOriginalName()
        ]
    );

    return $url;
  }
  
  public static function uploadForumPostFileAttachment($data)
  {

      if (config('app.env') == 'local') {

        $path = "/inbox/forum_post/{$data['forum_post_uuid']}/attachments";

        $file_path = Storage::disk('public')->put($path, $data['file']);

        $url = asset($file_path);
      } else {

        $path = "inbox/forum_post/{$data['forum_post_uuid']}/attachments";

        $file_path = Storage::disk('s3')->put($path, $data['file']);

        $url = Storage::disk('s3')->url($file_path);
      }

      Attachment::create(
        [
          'attachable_id' => $data['model_id'], 
          'attachable_type' => $data['model_type'],
          'file_path' => $url,
          'file_type' => $data['file']->extension(),
          'file_name' => $data['file']->getClientOriginalName()
        ]
    );

    return $url;
  }

  public static function uploadForumPostCommentFileAttachment($data)
  {

      if (config('app.env') == 'local') {

        $path = "/inbox/forum_post_comment/{$data['forum_post_comment_uuid']}/attachments";

        $file_path = Storage::disk('public')->put($path, $data['file']);

        $url = asset($file_path);
      } else {

        $path = "inbox/forum_post_comment/{$data['forum_post_comment_uuid']}/attachments";

        $file_path = Storage::disk('s3')->put($path, $data['file']);

        $url = Storage::disk('s3')->url($file_path);
      }

      Attachment::create(
        [
          'attachable_id' => $data['model_id'], 
          'attachable_type' => $data['model_type'],
          'file_path' => $url,
          'file_type' => $data['file']->extension(),
          'file_name' => $data['file']->getClientOriginalName()
        ]
    );

    return $url;
  }
  
}
