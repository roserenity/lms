<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AnnouncementComment;

class AnnouncementCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable',
            'comment' => 'required',
            'commentable_id' => 'required',
        ]);

        AnnouncementComment::create([
            'user_id' => $this->authUser()->id,
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
            'commentable_id' => $request->commentable_id,
            'commentable_type' => 'App\\Models\\Announcement'
        ]);

        return $this->sendResponse([], 'You have successfully post a comment.', 201);
    }
}
