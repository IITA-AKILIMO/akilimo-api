<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    public function index(Request $request)
    {
        // will not implement
    }

    /**
     * Health Check
     *
     * Performs comprehensive health checks on all system services including database, Redis, cache, storage, queue, mail, disk space, migrations, environment config, PHP extensions, and the external Akilimo Compute service.
     *
     * @unauthenticated
     */
    public function check(): JsonResponse
    {
        $healthChecks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkFileStorage(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMailConnection(),
            'disk-space' => $this->checkDiskSpace(),
            'migrations' => $this->checkMigrations(),
            'env-config' => $this->checkEnvironmentConfig(),
            'php-extensions' => $this->checkPHPExtensions(),
            'akilimo-compute' => $this->checkAkilimoCompute(),
        ];

        $overallStatus = true;
        foreach ($healthChecks as $check) {
            $status = Arr::get(array: $check, key: 'status', default: 'DOWN');
            if ($status === 'DOWN') {
                $overallStatus = false;
                break;
            }
        }

        return response()->json(data: [
            'status' => $overallStatus ? 'healthy' : 'unhealthy',
            'timestamp' => Carbon::now()->toIso8601String(),
            //            'server_info' => [
            //                'php_version' => PHP_VERSION,
            //                'laravel_version' => app()->version(),
            //                'environment' => app()->environment(),
            //            ],
            'checks' => $healthChecks,
        ], status: $overallStatus ? 200 : 500);
    }

    private function checkDatabase(): array
    {
        try {
            $connection = DB::connection();
            $databaseName = $connection->getDatabaseName();
            $tableCount = Schema::getConnection()->getSchemaBuilder()->getTables();

            // Get database platform/driver name
            $platform = $connection->getDriverName();

            return [
                'status' => 'UP',
                'database' => $databaseName,
                'database_type' => $platform,
                'total_tables' => count($tableCount),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $redis = Redis::connection();
            //            $ping = $redis->ping();
            $info = $redis->command('info');
            $serviceName = $info['executable'] ?? 'NA';

            $memory = Arr::get(array: $info, key: 'Memory', default: $info);

            return [
                'status' => 'UP',
                'version' => $info['redis_version'],
                'service' => $serviceName,
                //                'ping' => $ping,
                'memory' => [
                    'used' => $memory['used_memory_human'] ?? 'NA',
                    'peak' => $memory['used_memory_peak_human'] ?? 'NA',
                ],
                //                'info' => $info,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . uniqid();
            Cache::put(key: $testKey, value: 'test', ttl: 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $value === 'test' ? 'UP' : 'DOWN',
                'driver' => Cache::getDefaultDriver(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkFileStorage(): array
    {
        try {
            $testFile = 'health_check_' . uniqid() . '.txt';
            Storage::put(path: $testFile, contents: 'Storage health check');
            $fileExists = Storage::exists($testFile);
            Storage::delete($testFile);

            return [
                'status' => $fileExists ? 'UP' : 'DOWN',
                'default_disk' => config('filesystems.default'),
                'root_path' => Storage::getConfig()['root'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $defaultQueue = config('queue.default');

            return [
                'status' => 'UP',
                'default_connection' => $defaultQueue,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkMailConnection(): array
    {
        try {
            $transport = Mail::getSymfonyTransport();

            return [
                'status' => 'UP',
                'transport' => $transport,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkDiskSpace(): array
    {
        $percentage = round((1 - disk_free_space('/') / disk_total_space('/')) * 100, 2);

        $diskSpaceReport = [
            'status' => 'UP',
            'total_space' => disk_total_space('/'),
            'free_space' => disk_free_space('/'),
            'used_percentage' => "{$percentage}%",
        ];

        if ($percentage > 90) {
            $diskSpaceReport['status'] = 'DOWN';
        }

        return $diskSpaceReport;
    }

    private function checkMigrations(): array
    {
        try {
            $pendingMigrations = DB::select('SELECT * FROM migrations');

            return [
                'total_migrations' => count($pendingMigrations),
                'status' => count($pendingMigrations) > 0 ? 'UP' : 'DOWN',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkEnvironmentConfig(): array
    {
        return [
            'status' => (bool)config('app.debug') ? 'DOWN' : 'UP',
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
        ];
    }

    private function checkPHPExtensions(): array
    {
        $requiredExtensions = [
            'pdo',
            'mbstring',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'bcmath',
        ];

        $extensionStatus = [];
        foreach ($requiredExtensions as $ext) {
            $extensionStatus[$ext] = extension_loaded($ext);
        }

        $extensionStatus['status'] = count(array_filter($extensionStatus, fn ($status) => $status === false)) === 0 ? 'UP' : 'DOWN';

        return $extensionStatus;
    }

    private function checkAkilimoCompute(): array
    {
        $baseUrl = rtrim((string)config('akilimo-compute.base_url', ''), '/');
        $healthUrl = $baseUrl !== '' ? $baseUrl . '/health' : null;

        $result = [
            'status' => 'DOWN',
            'url' => $healthUrl,
        ];

        if ($healthUrl === null) {
            $result['error'] = 'AKILIMO_COMPUTE_BASE_URL is not configured';

            return $result;
        }

        try {
            $response = Http::timeout(5)->get($healthUrl);

            $result['status'] = $response->successful() ? 'UP' : 'DOWN';
            $result['http_status'] = $response->status();

            // If the health endpoint returns JSON like {"status":"UP"}
            if ((bool)$response->json('status')) {
                $result['service_status'] = $response->json('status');
            }
        } catch (ConnectionException $e) {
            $result['error'] = 'Connection failed: ' . $e->getMessage();
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
