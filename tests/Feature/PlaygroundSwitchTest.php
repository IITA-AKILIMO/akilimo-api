<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

// PLAYGROUND_ENABLED=false (config/playground.php) takes the whole /playground area offline

it('serves the playground pages by default', function () {
    expect(config('playground.enabled'))->toBeTrue();

    $this->get('/playground/login')->assertOk();
    $this->get('/playground/register')->assertOk();
    $this->get('/playground/health')->assertOk();
});

it('answers 404 on every playground URL while the playground is switched off', function () {
    config(['playground.enabled' => false]);

    $this->get('/playground/login')->assertNotFound();
    $this->get('/playground/register')->assertNotFound();
    $this->get('/playground/health')->assertNotFound();
    $this->get('/playground/check-username?username=someone')->assertNotFound();
    $this->post('/playground/login', ['login' => 'a', 'password' => 'b'])->assertNotFound();
    $this->get('/playground')->assertNotFound();
    $this->postJson('/playground/compute', [])->assertNotFound();
    $this->getJson('/playground/tokens')->assertNotFound();
});

it('leaves the API and the welcome page alone while the playground is switched off', function () {
    config(['playground.enabled' => false]);

    $this->get('/')->assertOk();
    $this->getJson('/api/v1/currencies')->assertOk();
});
