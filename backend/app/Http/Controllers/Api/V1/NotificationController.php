<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $notifications = $query->paginate($request->get('per_page', 15));

        return self::paginated($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return self::success(null, 'Notifikasi telah dibaca');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return self::success(null, 'Semua notifikasi telah dibaca');
    }
}
