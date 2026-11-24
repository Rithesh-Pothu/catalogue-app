<?php

use App\Catalogue\CategoryNotFound;
use App\Catalogue\ProductNotFound;
use App\Catalogue\ProductSourceUnavailable;
use App\Enquiries\MailDeliveryFailed;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn (ProductSourceUnavailable $exception) => response()->json(
            ['message' => 'Products are unavailable right now.'],
            503,
        ));

        $exceptions->render(fn (ProductNotFound $exception) => response()->json(
            ['message' => 'Product not found.'],
            404,
        ));

        $exceptions->render(fn (CategoryNotFound $exception) => response()->json(
            ['message' => 'Category not found.'],
            404,
        ));

        $exceptions->render(fn (MailDeliveryFailed $exception) => response()->json(
            ['message' => "We couldn't send your enquiry. Please try again."],
            502,
        ));

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
