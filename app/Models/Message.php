<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\Message as BaseMessage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends BaseMessage
{
    use HasUuids;
}
