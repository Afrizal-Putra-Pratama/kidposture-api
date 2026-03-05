<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    protected $allowedOrigins = [
        'https://posturely-app.vercel.app',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://kidposture-api.test',
    ];

    public function handle(Request $request, Closure $next)
    {
        $origin = $request->headers->get('Origin');

        // Handle OPTIONS preflight
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 200);
            if (in_array($origin, $this->allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            }
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '3600');
            return $response;
        }

        $response = $next($request);

        if (in_array($origin, $this->allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}