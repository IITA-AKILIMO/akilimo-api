<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

return [
    /*
    | The web playground: its sign-in, sign-up, health page, compute form and API key screens under /playground.
    | Set PLAYGROUND_ENABLED=false to switch it off on a deployment: every playground URL then answers 404.
    */
    'enabled' => (bool)env('PLAYGROUND_ENABLED', true),
];
