<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Jobs;

use App\Jobs\Core\AbstractJob;
use App\Service\FarmerNotifier;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

/**
 * The delayed notice for an admin change. Queued once per kind and country until it starts running ("unique until
 * processing"), so every edit in the delay window joins the same notice; an edit after it started queues the next one.
 */
class SendChangeNotice extends AbstractJob implements ShouldBeUniqueUntilProcessing
{
    protected string $failureMessage = 'Farmer notice failed';

    /** Seconds the de-duplication lock lives if the job is lost, longer than any delay. */
    public int $uniqueFor = 7200;

    public function __construct(public string $kind, public ?string $country = null) {}

    public function uniqueId(): string
    {
        return $this->kind . '|' . ($this->country ?? '*');
    }

    protected function failureContext(): array
    {
        return ['kind' => $this->kind, 'country' => $this->country];
    }

    public function handle(FarmerNotifier $notifier): void
    {
        $notifier->deliver($this->kind, $this->country);
    }
}
