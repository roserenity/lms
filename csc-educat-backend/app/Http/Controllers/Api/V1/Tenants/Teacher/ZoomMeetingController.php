<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Exports\Tenants\Teacher\ZoomMeetingParticipantsSummary;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Jobs\Tenants\ProcessInstructorZoomMeetingParticipantsExport;
use App\Models\Enrollment;
use App\Models\Exports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\Models\ZoomMeeting;
use App\Models\ZoomMeetingAttendee;

class ZoomMeetingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function requestAccessToken()
    {
        $zoom_account_id = config('zoom.account_id');

        $basic_auth = base64_encode(config('zoom.basic_auth'));

        $response = Http::withHeaders([
            'Authorization' => 'Basic' . $basic_auth
        ])->post('https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $zoom_account_id);

        return $response;
    }

    public function index()
    {
        $zoom_meetings = ZoomMeeting::where('course_id', request('course_id'))->get();

        return $this->sendResponse($zoom_meetings, 'List of all meetings.', 200);
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'course_id' => 'required|exists:courses,id',
    //         'topic' => 'required|max:255',
    //         'agenda' => 'required',
    //         'default_password' => 'required|boolean',
    //         'password' => 'sometimes|exclude_if:default_password,FALSE|min:6',
    //         'start_time' => 'required'
    //     ]);

    //     if($request->default_password) {
    //         $response = Http::withToken(self::requestAccessToken()['access_token'])
    //             ->post('https://api.zoom.us/v2/users/me/meetings', [
    //                 'topic' => $request->topic,
    //                 'agenda' => $request->agenda,
    //                 'default_password' => $request->default_password,
    //                 'password' => $request->password,
    //                 'start_time' => $request->start_time,
    //                 'timezone' => $request->timezone
    //             ]);
    //     } else {
    //         $response = Http::withToken(self::requestAccessToken()['access_token'])
    //         ->post('https://api.zoom.us/v2/users/me/meetings', [
    //             'topic' => $request->topic,
    //             'agenda' => $request->agenda,
    //             'default_password' => $request->default_password,
    //             'start_time' => $request->start_time,
    //             'timezone' => $request->timezone
    //         ]);
    //     }



    //     if ($response->successful()) {
    //         ZoomMeeting::create([
    //             'course_id' => $request->course_id,
    //             'user_id' => $this->authUser()->id,
    //             'data' => json_decode($response)
    //         ]);
    //     }

    //     return $this->sendResponse([], 'You have successfully added a new zoom meeting.', 201);
    // }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'topic' => 'required|max:255',
            'agenda' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'zoom_url' => 'required|url'
        ]);

        $data = [
            'topic' => $request->topic,
            'agenda' => $request->agenda,
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'join_url' => $request->zoom_url
        ];

        ZoomMeeting::create([
            'course_id' => $request->course_id,
            'user_id' => $this->authUser()->id,
            'data' => $data
        ]);

        return $this->sendResponse([], 'You have successfully added a new zoom meeting.', 201);
    }

    // public function update($id, Request $request)
    // {
    //     $zoom_meeting = ZoomMeeting::find($id);

    //     $response = Http::withToken(self::requestAccessToken()['access_token'])
    //         ->patch('https://api.zoom.us/v2/meetings/' . $request->meeting_id, [
    //             'topic' => $request->topic,
    //             'agenda' => $request->agenda,
    //             // 'default_password' => $request->default_password,
    //             'password' => $request->password,
    //             'start_time' => $request->start_time
    //         ]);

    //     if ($response->successful()) {

    //         $response = Http::withToken(self::requestAccessToken()['access_token'])
    //             ->get('https://api.zoom.us/v2/meetings/' . $request->meeting_id);

    //         $zoom_meeting->update([
    //             'course_id' => $request->course_id,
    //             'user_id' => $this->authUser()->id,
    //             'data' => json_decode($response)
    //         ]);
    //     }

    //     return $this->sendResponse([], 'Zoom meeting has been updated.', 201);
    // }

    public function update($id, Request $request)
    {
        $zoom_meeting = ZoomMeeting::find($id);

        $request->validate([
            'topic' => 'required|max:255',
            'agenda' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'zoom_url' => 'required|url'
        ]);

        $data = [
            'topic' => $request->topic,
            'agenda' => $request->agenda,
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'join_url' => $request->zoom_url
        ];

        $zoom_meeting->update([
            'course_id' => $request->course_id,
            'user_id' => $this->authUser()->id,
            'data' => $data
        ]);

        return $this->sendResponse([], 'Zoom meeting has been updated.', 201);
    }

    // public function destroy($id)
    // {
    //     $zoom_meeting = ZoomMeeting::find($id);

    //     $response = Http::withToken(self::requestAccessToken()['access_token'])
    //         ->delete('https://api.zoom.us/v2/users/me/meetings/' . $zoom_meeting->data['id']);

    //     if ($response->successful()) {
    //         $zoom_meeting->delete();
    //     }
    //     return $this->sendResponse([], 'Zoom meeting has been deleted.', 201);
    // }

    public function destroy($id)
    {
        $zoom_meeting = ZoomMeeting::find($id);

        $zoom_meeting->delete();

        return $this->sendResponse([], 'Zoom meeting has been deleted.', 201);
    }

    public function zoomMeetingParticipants(Request $request, $course_id)
    {
        $zoom_meeting = ZoomMeeting::where('id', $request->zoom_meeting_id)->where('course_id', $course_id)->first();

        if($zoom_meeting) {

            $searchParts = explode(' ', request('search'));

            $enrollees = Enrollment::where('course_id', $course_id)
                ->has('user')
                ->select('enrollments.*', 'zoom_meeting_attendees.present')
                ->when(request('search'), function($query) use ($searchParts) {
                    $query->whereHas('user', function ($subQuery)  use ($searchParts)  {
                        Helper::queryNameSearch($searchParts, $subQuery);
                    });
                })
                ->when(request('status'), function($query) {
                    return $query->where('zoom_meeting_attendees.present', 
                        request('status') == 'present' ? 1 : 
                        (request('status') == 'absent' ? 0 : null)
                    );
                })
                ->leftJoin('zoom_meeting_attendees', function($join) use ($zoom_meeting) {
                    $join->on('enrollments.user_id', '=', 'zoom_meeting_attendees.user_id')
                        ->where('zoom_meeting_attendees.zoom_meeting_id', '=', $zoom_meeting->id);
                })
                ->with(['user' => fn($query) => $query->select(['id', 'first_name', 'last_name', 'suffix', 'email'])])
                ->paginate(request('paginate'));

            return $this->sendResponse($enrollees, 'Participant List', 201);
        } else {
            return $this->sendResponse([], 'No zoom meeting found.', 404);
        }
        
        return $this->sendResponse([], 'Participant List', 201);
        
    }

    public function zoomMeetingParticipantsAttendanceUpdate(Request $request, $course_id)
    {
        $zoom_meeting = ZoomMeeting::where('id', $request->zoom_meeting_id)->where('course_id', $course_id)->first();

        if($zoom_meeting) {
            forEach($request->user_ids as $id) {
                ZoomMeetingAttendee::updateOrCreate(
                    [
                        'zoom_meeting_id' => $zoom_meeting->id,
                        'user_id' => $id
                    ],
                    [
                        'present' => $request->status
                    ]
                );
            }
            
            return $this->sendResponse([], 'Updated!', 201);
        } else {
            return $this->sendResponse([], 'No zoom meeting found.', 404);
        }
    }

    public function zoomMeetingParticipantsExport(Request $request)
    {
        $request->validate([
            'zoom_meeting_id' => 'required|exists:zoom_meetings,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $zoom_meeting = ZoomMeeting::where('id', $request->zoom_meeting_id)->where('course_id', $request->course_id)->first();

        if (!$zoom_meeting) {
            return $this->sendResponse([], 'No zoom meeting found.', 404);
        }

        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'course',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessInstructorZoomMeetingParticipantsExport::dispatch(
            $export->id,
            $zoom_meeting->data["topic"], 
            $request->course_id, 
            $request->zoom_meeting_id, 
            $request->search, 
            $request->status,
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);

    }

    public function getZoomMeetingParticipant($zoom_meeting_id, $user_id)
    {
        $zoom_meeting_participant = ZoomMeetingAttendee::where('zoom_meeting_id', $zoom_meeting_id)->where('user_id', $user_id)->with('zoom_meeting_activity')->first();
        
        return $this->sendResponse($zoom_meeting_participant, 'Participant Info', 200);
    }

    public function postZoomMeetingParticipant(Request $request, $zoom_meeting_id, $user_id)
    {
        $zoom_meeting_participant = ZoomMeetingAttendee::where('zoom_meeting_id', $zoom_meeting_id)->where('user_id', $user_id)->first();

        if (!$zoom_meeting_participant) {
            $zoom_meeting_participant = ZoomMeetingAttendee::create([
                'zoom_meeting_id' => $request->zoom_meeting_id,
                'user_id' => $request->user_id
            ]);
        }

        $zoom_meeting_participant->zoom_meeting_activity()->updateOrCreate(
            ['zoom_meeting_attendee_id' => $zoom_meeting_participant->id],
            [
                'score' => $request->score,
                'comment' => $request->comment,
            ]);

        $zoom_meeting_participant->save();

        return $this->sendResponse($zoom_meeting_participant, 'Update Participant Info', 200);

    }
}
