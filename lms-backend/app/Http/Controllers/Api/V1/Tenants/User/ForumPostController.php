<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\Tenants\ForumPostRequest;
use App\Helpers\FilterHelper;
use App\Helpers\Helper;
use App\Models\ForumPost;
use App\Services\Tenants\AttachmentService;
use Carbon\Carbon;

class ForumPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function index(Request $request)
    {

        $posted_from = Helper::isValidDateString($request->posted_from) 
          ? Carbon::parse($request->posted_from)->startOfMonth() 
          : null;

        $posted_to = Helper::isValidDateString($request->posted_to) 
            ? Carbon::parse($request->posted_to)->endOfMonth() 
            : null;

        $forum_posts = ForumPost::with([
              'course' => function($course) {
                $course->select(['id', 'title']);
              },
              'user' => function($user) {
                $user->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
              }
            ])
            ->withCount('comments')
            ->when(($posted_from || $posted_to), function ($query) use ($posted_from, $posted_to) {
                FilterHelper::queryCreatedAt($query, $posted_from, $posted_to);
            })
            ->when(!empty(request('course_id')), function($query, $request) {
              $query->whereIn('course_id', request('course_id'));
            })
            ->where('status', ForumPost::IS_APPROVED)
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate($request->paginate);

        return $this->sendResponse($forum_posts, 'List of forum posts', 200);
    }

    public function show($id)
    {
      
      $post = ForumPost::where('id', $id)->with([
          'course' => function($course) {
            $course->select(['id', 'title']);
          },
          'user' => function($user) {
            $user->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
          },
          'comments' => fn ($comments) => $comments->with('replies')
        ])
        ->withCount('comments')
        ->first();

      if (!$post) {
          return $this->sendResponse([], 'No forum post found.', 404);
      }

      return $this->sendResponse($post, 'Show forum post', 200);

    }

    public function store(ForumPostRequest $request) 
    {

      $post = ForumPost::create($request->all());

      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file_attachment) {
            AttachmentService::uploadForumPostFileAttachment([
                'model_id' => $post->id,
                'model_type' => 'App\\Models\\ForumPost',
                'forum_post_uuid' => $post->uuid,
                'file' => $file_attachment
            ]);
        }
      }

      return $this->sendResponse($post, 'You have successfully added a new post.', 201);

    }

    
    public function update(ForumPostRequest $request, $id)
    {
      $post = ForumPost::find($id);

      if (!$post) {
          return $this->sendResponse([], 'No forum post found.', 404);
      }

      $post->update($request->all());

      return $this->sendResponse([], 'You have successfully updated a forum post.', 200);
    }

    public function destroy($id)
    {
        $post = ForumPost::find($id);

        if (!$post) {
            return $this->sendResponse([], 'No forum post found.', 404);
        }

        $post->delete();

        $post->comments()->delete();

        return $this->sendResponse([], 'You have successfully deleted a forum post.', 200);
    }

    public function getOwnedForumPost(Request $request)
    {

      $forum_posts = ForumPost::with([
            'course' => function($course) {
              $course->select(['id', 'title']);
            },
            'user' => function($user) {
              $user->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
            }
          ])
          ->withCount('comments')
          ->orderByDesc('is_pinned')
          ->where('user_id', $this->authUser()->id)
          ->when($request->status, function($query) use ($request) {
            $query->where('status', $request->status);
          })
          ->latest()
          ->paginate($request->paginate);

      return $this->sendResponse($forum_posts, 'List of owned forum posts', 200);

    }
}
