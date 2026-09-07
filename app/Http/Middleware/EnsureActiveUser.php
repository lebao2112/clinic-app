<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\Message;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_active) {
            return response()->json([
                'success' => false,
                'message' => Message::ACCOUNT_LOCKED,
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
