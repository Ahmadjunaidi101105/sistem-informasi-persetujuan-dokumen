<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        return self::success(null, 'Not implemented');
    }

    public function login(Request $request)
    {
        return self::success(null, 'Not implemented');
    }

    public function logout(Request $request)
    {
        return self::success(null, 'Not implemented');
    }

    public function user(Request $request)
    {
        return self::success(null, 'Not implemented');
    }
}
