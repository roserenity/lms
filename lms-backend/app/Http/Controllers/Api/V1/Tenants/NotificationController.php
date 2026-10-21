<?php

namespace App\Http\Controllers\Api\V1\Tenants;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    const PAGINATION_COUNT = 10;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('verified')->only('user');
    }

    public function allNotifications()
    {
        $unread_count = $this->authUser()->notifications()->whereNull('read_at')->count();

        return $this->sendResponse([
            'unread_count' => $unread_count, 
            'notifications' =>$this->authUser()->notifications()->select(['id', 'type', 'data', 'read_at', 'created_at'])->paginate(self::PAGINATION_COUNT)
        ], 'List of notifications.', 200);
    }

    public function markNotification(Request $request)
    {
        $request->validate([
            'notification_id' => 'nullable|uuid'
        ]);

        $this->authUser()
            ->unreadNotifications
            ->when($request->notification_id, function ($query) use ($request) {
                return $query->where('id', $request->notification_id);
            })
            ->markAsRead();

        return $this->sendResponse([], 'Notification was marked as read.', 201);
    }

    public function markAllNotifications()
    {
        $this->authUser()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return $this->sendResponse([], 'All notifications were marked as read.', 201);
    }
}
