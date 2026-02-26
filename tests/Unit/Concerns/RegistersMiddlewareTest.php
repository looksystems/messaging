<?php

namespace Tests\Concerns;

use Tests\Fixtures\TestMiddleware;

test('can register middleware', function () {

    $bus = $this->makeBus();
    $middleware = new TestMiddleware;

    $bus->middleware($middleware);

    $registered = $bus->listOfMiddleware();
    expect(end($registered))->toEqual($middleware);

});

test('can append middleware', function () {

    $bus = $this->makeBus();
    $middleware = new TestMiddleware;

    $bus->appendMiddleware($middleware);

    $registered = $bus->listOfMiddleware();
    expect(end($registered))->toEqual($middleware);

});

test('can prepend middleware', function () {

    $bus = $this->makeBus();
    $middleware = new TestMiddleware;

    $bus->prependMiddleware($middleware);

    $registered = $bus->listOfMiddleware();
    expect(current($registered))->toEqual($middleware);

});

test('can replace middleware when registering', function () {

    $bus = $this->makeBus();

    $registered = $bus->listOfMiddleware();
    expect(count($registered))->toEqual(4);

    $middleware = new TestMiddleware;
    $bus->middleware($middleware, replace: true);

    $registered = $bus->listOfMiddleware();
    expect(current($registered))->toEqual($middleware);
    expect(count($registered))->toEqual(1);

});

test('can replace middleware', function () {

    $bus = $this->makeBus();

    $registered = $bus->listOfMiddleware();
    expect(count($registered))->toEqual(4);

    $middleware = new TestMiddleware;
    $bus->replaceMiddleware($middleware);

    $registered = $bus->listOfMiddleware();
    expect(current($registered))->toEqual($middleware);
    expect(count($registered))->toEqual(1);

});

test('can drop middleware by class', function () {

    $bus = $this->makeBus();
    $middleware1 = new TestMiddleware;
    $middleware2 = new TestMiddleware;
    $bus->middleware([$middleware1, $middleware2]);

    $bus->dropMiddleware(TestMiddleware::class);

    $registered = $bus->listOfMiddleware();
    expect(count($registered))->toEqual(4);
    expect(end($registered))->not->toEqual($middleware1);
    expect(end($registered))->not->toEqual($middleware2);

});

test('can drop middleware by object', function () {

    $bus = $this->makeBus();
    $middleware = new TestMiddleware;
    $bus->middleware($middleware);

    $bus->dropMiddleware($middleware);

    $registered = $bus->listOfMiddleware();
    expect(count($registered))->toEqual(4);
    expect(end($registered))->not->toEqual($middleware);

});

test('can drop list of middleware objects', function () {

    $bus = $this->makeBus();
    $middleware1 = new TestMiddleware;
    $middleware2 = new TestMiddleware;
    $bus->middleware([$middleware1, $middleware2]);

    $bus->dropMiddleware([$middleware1, $middleware2]);

    $registered = $bus->listOfMiddleware();
    expect(count($registered))->toEqual(4);
    expect(end($registered))->not->toEqual($middleware1);
    expect(end($registered))->not->toEqual($middleware2);

});
