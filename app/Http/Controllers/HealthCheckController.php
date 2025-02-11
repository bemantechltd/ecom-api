<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    /**
     * Check the health status of the API
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'service' => config('app.name'),
            'environment' => config('app.env'),
            'check' => 123
        ]);
    }
} 