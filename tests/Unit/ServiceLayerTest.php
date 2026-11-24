<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

/**
 * Services reach the database through repositories (app/Repositories), never through models or the query builder.
 * A model may still be a type hint or a return type, and a service may start a transaction (DB::transaction,
 * beginTransaction, rollBack, commit), which can span several repositories.
 */
function serviceSources(): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Service')));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[str_replace('\\', '/', substr($file->getPathname(), strlen(base_path()) + 1))] = file_get_contents($file->getPathname());
        }
    }

    return $files;
}

it('keeps every service off model static calls', function () {
    $models = array_map(fn ($f) => basename($f, '.php'), glob(app_path('Models') . '/*.php'));
    $pattern = '/\b(' . implode('|', array_map('preg_quote', $models)) . ')::(?!class\b)/';

    $offenders = [];
    foreach (serviceSources() as $path => $source) {
        if (preg_match_all($pattern, $source, $m)) {
            $offenders[$path] = array_unique($m[0]);
        }
    }

    expect($offenders)->toBe([], 'Use a repository instead of calling a model: ' . json_encode($offenders));
});

it('keeps every service off the query builder', function () {
    $offenders = [];
    foreach (serviceSources() as $path => $source) {
        if (preg_match_all('/\bDB::(?!transaction\b|beginTransaction\b|rollBack\b|commit\b)\w+/', $source, $m)) {
            $offenders[$path] = array_unique($m[0]);
        }
    }

    expect($offenders)->toBe([], 'Use a repository instead of DB: ' . json_encode($offenders));
});
