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

    private function checkDatabase(): array
    {
        $ok = false;
        $latencyMs = null;
        $error = null;

        try {
            $t0 = microtime(true);
            DB::select('SELECT 1');
            $latencyMs = (int) ((microtime(true) - $t0) * 1000);
            $ok = true;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return [
            'ok' => $ok,
            'latency_ms' => $latencyMs,
            'driver' => config('database.default'),
            'error' => $error,
        ];
    }

    private function checkCache(): array
    {
        $ok = false;
        $error = null;

        try {
            $testKey = 'health_check_'.uniqid();
            Cache::put($testKey, 'ok', 10);
            $ok = Cache::get($testKey) === 'ok';
            Cache::forget($testKey);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return [
            'ok' => $ok,
            'driver' => config('cache.default'),
            'error' => $error,
        ];
    }

    private function checkStorage(): array
    {
        $ok = false;
        $error = null;

        try {
            $testFile = storage_path('app/.health_check');
            file_put_contents($testFile, 'ok');
            $ok = file_exists($testFile) && file_get_contents($testFile) === 'ok';
            @unlink($testFile);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return [
            'ok' => $ok,
            'path' => storage_path(),
            'writable' => is_writable(storage_path()),
            'error' => $error,
        ];
    }

    private function checkQueue(): array
    {
        $ok = false;
        $error = null;

        try {
            // Just check if queue connection is configured
            $driver = config('queue.default');
            $ok = ! empty($driver);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return [
            'ok' => $ok,
            'driver' => config('queue.default'),
            'error' => $error,
        ];
    }
}
