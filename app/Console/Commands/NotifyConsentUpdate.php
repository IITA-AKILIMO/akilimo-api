<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use App\Repositories\PushDeviceRepo;
use App\Repositories\UserRepo;
use App\Service\MessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * After FARMER_CONSENT_VERSION is raised (new privacy policy or terms), tells the farmers who agreed to an older version
 * and have a phone registered for push to open the app and accept again. One private message per farmer in the language of
 * their phone, pushed through the queue; a farmer is told once per version, so running it again is harmless.
 */
class NotifyConsentUpdate extends Command
{
    protected $signature = 'farmers:notify-consent {--dry-run : Only count the farmers, send nothing}';

    protected $description = 'Notify farmers who agreed to an older consent version (run after raising FARMER_CONSENT_VERSION)';

    public function handle(MessageService $messages, UserRepo $users, PushDeviceRepo $devices): int
    {
        $version = (string)config('farmer-accounts.consent_version');
        $languages = (array)config('farmer-notifications.languages');

        $farmers = $users->farmersOnOlderConsent($version, $devices->userIdsWithDevices());

        $byLanguage = [];
        foreach ($farmers as $farmer) {
            if (!Cache::add("consent-notice:{$version}:{$farmer->id}", true, now()->addYear())) {
                continue; // already told for this version
            }
            $language = $devices->languageOfUser($farmer->id);
            $byLanguage[in_array($language, $languages, true) ? $language : 'en'][] = ['type' => 'user', 'id' => (string)$farmer->id];
        }

        $total = array_sum(array_map('count', $byLanguage));
        if ($this->option('dry-run')) {
            // The lock above was taken for the farmers counted, so give it back
            foreach ($byLanguage as $recipients) {
                foreach ($recipients as $r) {
                    Cache::forget("consent-notice:{$version}:{$r['id']}");
                }
            }
            $this->components->info("{$total} farmer(s) would be notified of consent version {$version}.");

            return self::SUCCESS;
        }

        foreach ($byLanguage as $language => $recipients) {
            $messages->publishPrivate([
                'type' => 'announcement',
                'title' => __('farmer_notice.consent.title', [], $language),
                'body' => __('farmer_notice.consent.body', [], $language),
                'recipients' => $recipients,
                'expires_at' => now()->addDays((int)config('farmer-notifications.expires_days')),
            ], null);
        }
        $this->components->info("{$total} farmer(s) notified of consent version {$version}; the pushes are queued.");

        return self::SUCCESS;
    }
}
