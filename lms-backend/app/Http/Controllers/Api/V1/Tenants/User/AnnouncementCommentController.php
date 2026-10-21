<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Announcement;
use App\Models\AnnouncementComment;

class AnnouncementCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
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

    public function showAnnouncement($id)
    {
        $announcement = Announcement::with([
            'comments' => fn ($comments) => $comments->with('replies')->orderBy('created_at', 'DESC')
        ])
            ->where('id', $id)
            ->first();

        return $this->sendResponse($announcement, 'Show announcement details.', 200);
    }
    
}
