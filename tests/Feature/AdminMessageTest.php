<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->withoutVite());

function messagesAdmin(EnumUserRole $role = EnumUserRole::Admin): User
{
    return User::create([
        'name' => ucfirst($role->value) . ' Messages',
        'username' => $role->value . '_' . Str::random(6),
        'email' => $role->value . '_' . Str::random(6) . '@example.com',
        'password' => Hash::make('secret123'),
        'role' => $role,
    ]);
}

it('lists the messages sent, newest first', function () {
    Message::create(['type' => 'advice', 'title' => 'Old', 'body' => 'b', 'created_at' => now()->subDay()]);
    Message::create(['type' => 'advice', 'title' => 'New', 'body' => 'b', 'created_at' => now()]);

    $this->actingAs(messagesAdmin())->get('/admin/messages')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Messages/Index')
            ->where('messages.data.0.title', 'New')
            ->where('messages.data.1.title', 'Old')
            ->where('types', ['advice', 'reminder', 'announcement']));
});

it('stores a message from the form and says the push is switched off', function () {
    config(['fcm.enabled' => false]);

    $this->actingAs(messagesAdmin())->post('/admin/messages', [
        'type' => 'announcement', 'title' => 'Hello', 'body' => 'World', 'language' => 'sw',
    ])->assertRedirect('/admin/messages')->assertSessionHas('success');

    expect(Message::where('title', 'Hello')->first())->language->toBe('sw');
});

it('rejects an invalid message', function () {
    $this->actingAs(messagesAdmin())->post('/admin/messages', ['type' => 'nope', 'title' => '', 'body' => ''])
        ->assertSessionHasErrors(['type', 'title', 'body']);
});

it('keeps partners and guests out', function () {
    $this->actingAs(messagesAdmin(EnumUserRole::Partner))->get('/admin/messages')->assertRedirect();
    auth()->logout();
    $this->post('/admin/messages', [])->assertRedirect();
});
