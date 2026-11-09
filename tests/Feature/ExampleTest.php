<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
