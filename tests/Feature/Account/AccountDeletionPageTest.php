<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Mail\AccountCodeMail;
use App\Models\ApiRequest;
use App\Models\User;
use App\Service\AdviceAnonymiser;
use App\Service\FarmerAccountService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

function deletionUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'Farmer',
        'username' => 'f_' . str()->random(8),
        'email' => 'f_' . strtolower(str()->random(6)) . '@example.com',
        'password' => Hash::make('long-enough-1'),
        'role' => EnumUserRole::User,
    ], $attributes));
}

function deletionCode(): string
{
    $code = null;
    Mail::assertSent(AccountCodeMail::class, function ($m) use (&$code) {
        if ($m->purpose === AccountCodeMail::DELETE) {
            $code = $m->code;
        }

        return true;
    });

    return $code;
}

beforeEach(function () {
    RateLimiter::clear('sensitive|127.0.0.1');
    Mail::fake();
});

it('shows the public deletion page', function () {
    $this->get('/account/delete')->assertOk()->assertSee('Delete your AKILIMO account');
});

it('emails a code and deletes the account when it is entered', function () {
    $user = deletionUser();

    $this->post('/account/delete', ['email' => $user->email])->assertOk()->assertSee('we sent it a code');
    $code = deletionCode();

    $this->post('/account/delete/confirm', ['email' => $user->email, 'code' => $code])
        ->assertOk()->assertSee('Your account has been deleted');

    expect(User::find($user->id))->toBeNull();
});

it('answers the same for an address with no account and sends nothing', function () {
    $this->post('/account/delete', ['email' => 'ghost@example.com'])->assertOk()->assertSee('we sent it a code');

    Mail::assertNothingSent();
});

it('keeps the account when the code is wrong', function () {
    $user = deletionUser();
    $this->post('/account/delete', ['email' => $user->email])->assertOk();

    $this->post('/account/delete/confirm', ['email' => $user->email, 'code' => 'WRONG000'])
        ->assertOk()->assertSee('wrong or has expired');

    expect(User::find($user->id))->not->toBeNull();
});

it('never deletes a staff account through the page', function () {
    $admin = deletionUser(['role' => EnumUserRole::Admin]);

    $this->post('/account/delete', ['email' => $admin->email])->assertOk();

    Mail::assertNothingSent();
    expect(User::find($admin->id))->not->toBeNull();
});

it('scrubs the personal details and the PDFs from the advice it keeps', function () {
    $user = deletionUser();
    $row = ApiRequest::create([
        'request_id' => (string)str()->uuid(),
        'device_token' => 'phone-1',
        'user_id' => $user->id,
        'droid_request' => [
            'user_info' => [
                'first_name' => 'Amina', 'last_name' => 'Juma', 'user_name' => 'amina', 'email_address' => 'a@example.com',
                'phone_number' => '+255700000000', 'farm_name' => 'Shamba', 'device_token' => 'phone-1', 'gender' => 'F',
            ],
            'compute_request' => ['farm_information' => ['country_code' => 'TZ']],
        ],
        'plumber_request' => ['firstName' => 'Amina', 'country' => 'TZ'],
    ]);

    app(FarmerAccountService::class)->deleteAccount($user);

    $kept = $row->fresh();
    $anon = app(AdviceAnonymiser::class)->anonId($row->request_id);
    $info = $kept->droid_request['user_info'];
    expect($kept->user_id)->toBeNull()
        ->and($kept->device_token)->toBeNull()
        ->and($info['first_name'])->toBe("anon-{$anon}")
        ->and($info['last_name'])->toBe("anon-{$anon}")
        ->and($info['user_name'])->toBe("anon-{$anon}")
        ->and($info['email_address'])->toBe("anon-{$anon}@deleted.invalid")
        ->and($info['phone_number'])->toBe("anon-{$anon}")
        ->and($info['farm_name'])->toBe("anon-{$anon}")
        ->and($info['device_token'])->not->toBe('phone-1')->and(Str::isUuid($info['device_token']))->toBeTrue()
        ->and($info['gender'])->toBe('F')
        ->and($kept->droid_request['compute_request']['farm_information']['country_code'])->toBe('TZ')
        ->and($kept->plumber_request['firstName'])->toBe("anon-{$anon}")
        ->and($kept->plumber_request['country'])->toBe('TZ');
});
