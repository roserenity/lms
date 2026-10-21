<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\ForumPostCommentRequest;
use App\Models\ForumPostComment;
use App\Services\Tenants\AttachmentService;
use Illuminate\Http\Request;

class ForumPostCommentController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function store(ForumPostCommentRequest $request) 
    {

      $post_comment = ForumPostComment::create([
        'user_id' => $this->authUser()->id,
        'parent_id' => $request->has('parent_id') ? $request->parent_id : null,
        'comment' => $request->comment,
        'commentable_id' => $request->commentable_id,
        'commentable_type' => 'App\\Models\\ForumPost'
      ]);

      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file_attachment) {
            AttachmentService::uploadForumPostCommentFileAttachment([
                'model_id' => $post_comment->id,
                'model_type' => 'App\\Models\\ForumPostComment',
                'forum_post_comment_uuid' => $post_comment->uuid,
                'file' => $file_attachment
            ]);
        }
      }

      return $this->sendResponse($post_comment, 'You have successfully added a new course.', 201);

    }

    
    public function update(ForumPostCommentRequest $request, $id)
    {
      $post_comment = ForumPostComment::find($id);

      if (!$post_comment) {
          return $this->sendResponse([], 'No forum post comment found.', 404);
      }

      $post_comment->update($request->all());

      return $this->sendResponse([], 'You have successfully updated a forum post.', 200);
    }

    public function destroy($id)
    {
        $post_comment = ForumPostComment::find($id);

        if (!$post_comment) {
            return $this->sendResponse([], 'No forum post comment found.', 404);
        }

        $post_comment->delete();

        return $this->sendResponse([], 'You have successfully deleted a forum post.', 200);
    }
}
