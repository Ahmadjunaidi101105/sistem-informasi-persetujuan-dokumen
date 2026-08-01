<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function pemohon(Request $request)
    {
        return self::success(null, 'Not implemented');
    }

    public function penilai(Request $request)
    {
        return self::success(null, 'Not implemented');
    }
}
