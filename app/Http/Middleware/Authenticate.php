<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        Log::info('Authenticate Middleware triggered for: ' . $request->fullUrl());
        if (!$request->expectsJson()) {
            Log::info('Redirecting to: ' . route('login_page'));
            return route('login_page');
        }
        Log::info('Not redirecting (expects JSON)');
        return null;
    }
}
