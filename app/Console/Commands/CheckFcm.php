<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use App\Repositories\PushDeviceRepo;
use App\Service\Fcm\FcmClient;
use App\Service\MessageService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Proves push works before messages depend on it: the settings are present, the service account key is readable,
 * Google issues an access token, and FCM accepts a message (validated, not delivered). With --device it also checks that
 * phone's token, and with --send it queues a test message for it (the push itself is sent by the queue worker, like every push). The key is never printed.
 */
class CheckFcm extends Command
{
    protected $signature = 'fcm:check
        {--device= : A registered phone (its device_token) to check the token of}
        {--send : Queue a real test message for --device (a notification may show on the phone once a worker runs it)}';

    protected $description = 'Check that FCM is configured, reachable and can send messages (run after setting FCM_ENABLED, FCM_PROJECT_ID and FCM_CREDENTIALS)';

    public function handle(FcmClient $fcm, MessageService $messages, PushDeviceRepo $devices): int
    {
        if (!config('fcm.enabled')) {
            $this->components->error('FCM_ENABLED is false. Messages are stored only and never pushed.');

            return self::FAILURE;
        }
        $this->components->info('FCM_ENABLED is true');

        if (!config('fcm.project_id')) {
            $this->components->error('FCM_PROJECT_ID is empty (use the project_id of the app\'s google-services.json).');

            return self::FAILURE;
        }

        $credentials = (string)config('fcm.credentials');
        if ($credentials !== '' && !str_starts_with(ltrim($credentials), '{') && !file_exists($credentials)) {
            $this->components->error('The FCM credentials could not be loaded: the file in FCM_CREDENTIALS does not exist (quote the path in .env if it has spaces)');

            return self::FAILURE;
        }
        $this->components->info('Project: ' . config('fcm.project_id'));

        $email = $fcm->serviceAccountEmail();
        if ($email === null) {
            $this->components->error('FCM_CREDENTIALS is empty or not a service account key (a file path or the JSON itself, with client_email and private_key).');

            return self::FAILURE;
        }
        $this->components->info("Service account key read: {$email}");

        try {
            $fcm->validate(['topic' => 'fcm_connectivity_check']);
        } catch (Throwable $e) {
            $this->components->error('FCM did not accept a test message: ' . $e->getMessage());
            $this->line('Usual causes: a key from another Firebase project than FCM_PROJECT_ID, an account without the "Firebase Cloud Messaging API Admin" role, the Cloud Messaging API (V1) not enabled, or the server cannot reach Google.');

            return self::FAILURE;
        }
        $this->components->info('Google issued an access token and FCM accepted a test message (nothing was delivered)');

        if ($deviceToken = $this->option('device')) {
            $device = $devices->findByDeviceToken($deviceToken);
            if ($device === null) {
                $this->components->error('No phone is registered with that device_token (see the push_devices table).');

                return self::FAILURE;
            }

            try {
                if ($this->option('send')) {
                    // Like every push: stored, then queued for a worker. Nothing is sent from this command.
                    $result = $messages->publishPrivate([
                        'type' => 'announcement',
                        'title' => 'AKILIMO push test',
                        'body' => 'If you can read this, push works.',
                        'recipients' => [['type' => 'device', 'id' => $device->device_token]],
                    ], null);
                    $result['devices'] > 0
                        ? $this->components->info('Test message queued for that phone. It arrives when a queue worker runs it (php artisan queue:work).')
                        : $this->components->warn('Nothing was queued (' . $result['push'] . ').');
                } else {
                    $fcm->validate(['token' => $device->fcm_token]);
                    $this->components->info('The phone FCM token is accepted (use --send to queue a real test message)');
                }
            } catch (Throwable $e) {
                $this->components->error('FCM refused the phone token: ' . $e->getMessage());

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->line('FCM is ready.');

        return self::SUCCESS;
    }
}
