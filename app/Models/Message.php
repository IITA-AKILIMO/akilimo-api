<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Enums\PushStatus;
use App\Models\Base\Message as BaseMessage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends BaseMessage
{
    use HasUuids;

    // Columns the generated base model does not know yet (see CLAUDE.md, "Models"): the private recipient of a message
    public function __construct(array $attributes = [])
    {
        $this->mergeFillable(['recipient_device_token', 'recipient_user_id', 'push_status', 'push_error']);
        $this->mergeCasts(['recipient_user_id' => 'int', 'push_status' => PushStatus::class]);
        parent::__construct($attributes);
    }
}
