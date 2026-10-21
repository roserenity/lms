<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\ZoomMeeting;

class ZoomMeetingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
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

    public function getMeetingInfo($id) {

        $zoom_meeting = ZoomMeeting::find($id);

        $response = Http::withToken(self::requestAccessToken()['access_token'])
            ->get('https://api.zoom.us/v2/meetings/' . $zoom_meeting->data['id']);

        return $this->sendResponse(json_decode($response), 'Here are the meeting details.', 201);
    }

}
