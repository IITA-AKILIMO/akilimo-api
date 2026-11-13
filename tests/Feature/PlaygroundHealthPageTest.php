<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

beforeEach(fn () => $this->withoutVite());

it('shows the system health page without signing in', function () {
    $this->get('/playground/health')
        ->assertOk()
        ->assertSee('System Health');
});

it('links the welcome page to the health page, not the raw JSON', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="/playground/health"', false)
        ->assertDontSee('href="/health"', false);
});
