<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];
        $httpCode = 200;

        // Check database connection (non-blocking)
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'connected';
        } catch (\Exception $e) {
            $checks['database'] = 'disconnected';
            // Don't fail health check if DB is down, let it retry
        }

        // Check cache connection (non-blocking)
        try {
            Cache::has('health_check');
            $checks['cache'] = 'connected';
        } catch (\Exception $e) {
            $checks['cache'] = 'disconnected';
        }

        // Check storage writability (non-blocking)
        try {
            $testFile = storage_path('logs/health_check.txt');
            file_put_contents($testFile, 'test');
            unlink($testFile);
            $checks['storage'] = 'writable';
        } catch (\Exception $e) {
            $checks['storage'] = 'read-only';
        }

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'environment' => config('app.env'),
            'version' => config('app.release_version', config('app.version', '1.0.0')),
            'commit' => config('app.release_commit', 'unknown'),
            'branch' => config('app.release_branch', 'unknown'),
            'deployed_at' => config('app.release_timestamp', 'unknown'),
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => (bool) config('app.debug'),
            ],
            'checks' => $checks,
        ], $httpCode);
    }
}
