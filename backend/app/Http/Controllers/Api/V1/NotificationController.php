<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function index(Request $request)
    {
        return self::success(null, 'Not implemented');
    }

    public function markAsRead(Request $request, $id)
    {
        return self::success(null, 'Not implemented');
    }

    public function markAllAsRead(Request $request)
    {
        return self::success(null, 'Not implemented');
    }
}
