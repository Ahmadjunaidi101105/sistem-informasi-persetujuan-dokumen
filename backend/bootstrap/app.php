<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // General API rate limit (60 req/min, see the "api" limiter in
        // AppServiceProvider). Auth and export routes tighten this to 5/min
        // individually in routes/api.php.
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan masuk kembali.'], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Preserve the specific reason supplied by the Policy (Response::deny)
                // instead of collapsing every failure into a generic message.
                $message = $e->getMessage();
                if ($message === '' || $message === 'This action is unauthorized.') {
                    $message = 'Anda tidak memiliki akses untuk melakukan tindakan ini.';
                }

                return response()->json(['success' => false, 'message' => $message], 403);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $message = $e->getMessage();
                if ($message === '' || $message === 'This action is unauthorized.') {
                    $message = 'Anda tidak memiliki akses untuk melakukan tindakan ini.';
                }

                return response()->json(['success' => false, 'message' => $message], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Data yang Anda cari tidak ditemukan.'], 404);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Data yang Anda cari tidak ditemukan.'], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Terlalu banyak permintaan. Silakan coba lagi beberapa saat lagi.'], 429);
            }
        });

        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';

                // Allow specific statuses to fallback or be handled above
                if (in_array($status, [401, 403, 404, 422, 429])) {
                    return null;
                }

                return response()->json(['success' => false, 'message' => $message], $status);
            }
        });
    })->create();
