<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Tenants\AnnouncementRequest;
use App\Helpers\FilterHelper;

use App\Models\Announcement;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function index(Request $request)
    {
        //$announcements = Announcement::with('course')->where('user_id', $this->authUser()->id)->get();

        $posted_from = $request->posted_from ? Carbon::parse(request('posted_from'))->startOfMonth() : '';
        $posted_to = $request->posted_to ? Carbon::parse(request('posted_to'))->endOfMonth() : '';

        $announcements = Announcement::with([
              'course' => function($course) {
                $course->select(['id', 'title']);
              },
              'user' => function($user) {
                $user->select(['id', 'first_name', 'last_name', 'suffix']);
              }
            ])
            ->withCount('comments')
            ->when(($posted_from || $posted_to), function ($query) use ($posted_from, $posted_to) {
                FilterHelper::queryCreatedAt($query, $posted_from, $posted_to);
            })
            ->when(!empty(request('course_id')), function($query, $request) {
              $query->whereIn('course_id', request('course_id'));
            })
            ->latest()
            ->paginate($request->paginate);

        return $this->sendResponse($announcements, 'List of instructor announcement', 200);
    }

    public function show($id) {
        $announcements = Announcement::whereRelation('course', 'id', $id)
            ->with([
                'course',
                'comments' => fn ($comments) => $comments->with('replies')
            ])
            ->get();
            
        return $this->sendResponse($announcements, 'List of all announcement', 200);
    }

    public function store(AnnouncementRequest $request)
    {
        Announcement::create($request->validated());
        return $this->sendResponse([], 'You have successfully created a new announcement.', 201);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return $this->sendResponse([], 'No announcement found.', 404);
        }

        $announcement->update(['content' => $request->content, 'allow_comments' => $request->allow_comments]);

        return $this->sendResponse([], 'You have successfully updated an announcement.', 200);
    }

    public function destroy($id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return $this->sendResponse([], 'No announcement found.', 404);
        }

        $announcement->delete();

        $announcement->comments()->delete();

        return $this->sendResponse([], 'You have successfully deleted an announcement.', 200);
    }

    public function getAnnouncement($id) {
      $announcement = Announcement::with([
          'course',
          'comments' => fn ($comments) => $comments->with('replies')
      ])->find($id);

      return $this->sendResponse($announcement, 'Show Announcement', 200);
    }
}
