<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UserInfoData extends Data
{
    public string $deviceToken;

    // Optional (UserInfoRules allows null); a missing name must not drop the field silently
    public ?string $farmName = null;

    public string $firstName;

    public string $lastName;

    public ?string $emailAddress;

    public ?string $phoneNumber;

    public string $gender;

    public bool $sendEmail;

    public bool $sendSms;

    public string $userName;

    public int $riskAttitude;
}
