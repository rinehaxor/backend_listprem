<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Set X-API-Key header.',
            ], 401);
        }

        $key = ApiKey::where('key', $apiKey)->where('is_active', true)->first();

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API key.',
            ], 401);
        }

        // Update last used
        $key->update(['last_used_at' => now()]);

        // Store api key info in request for controllers to use
        $request->merge([
            'api_key_id' => $key->id,
            'api_key_name' => $key->name,
            'api_key_platform' => $key->platform,
        ]);

        return $next($request);
    }
}
