<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Exceptions\RecommendationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('stores the message and status code', function () {
    $ex = new RecommendationException('Something failed', 422);

    expect($ex->getMessage())->toBe('Something failed')
        ->and($ex->getCode())->toBe(422);
});

it('defaults the status code to 500 when none is given', function () {
    $ex = new RecommendationException('Error');
    expect($ex->getCode())->toBe(500);
});

it('normalizes status codes below 100 to 500', function () {
    $ex = new RecommendationException('Error', 0);
    expect($ex->getCode())->toBe(500);
});

it('normalizes status codes above 599 to 500', function () {
    $ex = new RecommendationException('Error', 9999);
    expect($ex->getCode())->toBe(500);
});

it('accepts valid boundary status codes', function () {
    expect((new RecommendationException('', 100))->getCode())->toBe(100)
        ->and((new RecommendationException('', 599))->getCode())->toBe(599);
});

it('extracts body from a nested data key', function () {
    $ex = new RecommendationException('Error', 422, ['data' => ['field' => 'value']]);
    expect($ex->body)->toBe(['field' => 'value']);
});

it('uses the body array as-is when no data key is present', function () {
    $ex = new RecommendationException('Error', 422, ['field' => 'value']);
    expect($ex->body)->toBe(['field' => 'value']);
});

it('serviceUnavailable() produces a 503', function () {
    $ex = RecommendationException::serviceUnavailable('Service down');
    expect($ex->getCode())->toBe(Response::HTTP_SERVICE_UNAVAILABLE)
        ->and($ex->getMessage())->toBe('Service down');
});

it('serviceUnavailable() uses default message when none given', function () {
    $ex = RecommendationException::serviceUnavailable();
    expect($ex->getCode())->toBe(503)
        ->and($ex->getMessage())->not->toBeEmpty();
});

it('notFound() produces a 404', function () {
    $ex = RecommendationException::notFound();
    expect($ex->getCode())->toBe(Response::HTTP_NOT_FOUND);
});

it('invalidData() produces a 422', function () {
    $ex = RecommendationException::invalidData('Bad input', ['field' => ['required']]);
    expect($ex->getCode())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($ex->getMessage())->toBe('Bad input');
});

it('renders to a JSON response with the correct structure', function () {
    $ex = new RecommendationException('Not found', 404);
    $request = Request::create('/');

    $response = $ex->render($request);

    expect($response->getStatusCode())->toBe(404)
        ->and($response->getData(true))->toHaveKeys(['error', 'message', 'status'])
        ->and($response->getData(true)['message'])->toBe('Not found')
        ->and($response->getData(true)['status'])->toBe(404)
        ->and($response->getData(true)['error'])->toBe('Not Found');
});

it('render returns the correct error type label for known status codes', function (int $status, string $label) {
    $ex = new RecommendationException('msg', $status);
    $response = $ex->render(Request::create('/'));
    expect($response->getData(true)['error'])->toBe($label);
})->with([
    [400, 'Bad Request'],
    [401, 'Unauthorized'],
    [403, 'Forbidden'],
    [422, 'Unprocessable Entity'],
    [503, 'Service Unavailable'],
]);
