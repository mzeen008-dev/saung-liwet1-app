<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Header keamanan dipasang pada semua respons
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua alamat /api/* selalu dijawab JSON, walau header Accept tidak dikirim
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $mauJson = fn (Request $request) => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($mauJson) {
            if ($mauJson($request)) {
                return response()->json(['message' => 'Belum login atau token tidak valid.'], 401);
            }
        });

        // Pesan bawaan "No query results for model [App\Models\...]" membocorkan nama class, jadi diganti
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($mauJson) {
            if ($mauJson($request)) {
                return response()->json(['message' => 'Data tidak ditemukan.'], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($mauJson) {
            if ($mauJson($request)) {
                return response()->json(['message' => 'Metode permintaan tidak diizinkan.'], 405);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($mauJson) {
            if ($mauJson($request)) {
                return response()->json(
                    ['message' => 'Terlalu banyak permintaan. Coba lagi sebentar lagi.'],
                    429,
                    $e->getHeaders()
                );
            }
        });
    })->create();
