<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

/**
 * Firebase Cloud Messaging (HTTP v1) for pushing messages to the mobile app by topic. Off until a service account is set:
 * messages are then only stored and reach phones through the sync (GET /v1/messages).
 */
return [
    'enabled' => (bool)env('FCM_ENABLED', false),

    // Firebase project id (the one in the app's google-services.json)
    'project_id' => env('FCM_PROJECT_ID'),

    // Service account key of that project: a path to the JSON file, or the JSON itself. Keep it out of the repository.
    'credentials' => env('FCM_CREDENTIALS'),

    'timeout' => (int)env('FCM_TIMEOUT', 10),

    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
];
