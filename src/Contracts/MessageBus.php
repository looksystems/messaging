<?php

namespace Look\Messaging\Contracts;

use Closure;
use Look\Messaging\Envelope;
use Look\Messaging\Middleware\Middleware;
use Look\Messaging\Mock\MockHandler;
use Look\Messaging\Mock\MockRelay;
use Look\Messaging\Mock\MockTransformer;
use Look\Messaging\Mock\MockTransport;
use Look\Messaging\Mock\MockValidator;
use Look\Messaging\Support\ListOfMessages;
use Look\Messaging\Support\MessageHistory;
use Look\Messaging\Validators\ValidationActions;

interface MessageBus
{
    // BOOTING

    public function booting(Closure $callback): self;

    // DISPATCH

    public function dispatch(object|array|string $messageOrType, object|array $payload = [], Envelope|array $envelope = [], bool $throwIfInvalid = true): self;

    // HISTORY

    public function dispatched(): MessageHistory;
    public function handled(): MessageHistory;
    public function relayed(): MessageHistory;

    // TRANSFORMERS

    public function transform(array|string $typeOrList, $transformer = null): self;
    public function registerTransformers(array $list): self;
    public function listOfTransformers(?string $type = null): array;
    public function mockTransformer(string $type, $transformer = null): MockTransformer;

    // SCHEMAS

    public function schema(string $type, array|string $idOrSchema, ?string $schema = null): self;
    public function schemas(array|string $typeOrList, array $schema = []): self;
    public function registerSchemas(array $list): self;

    // VALIDATORS

    public function validate(array|string $typeOrList, $validator = null, ?ValidationActions $actions = null, bool $fallback = false): self;
    public function registerValidators(array $list, ?ValidationActions $actions = null, bool $fallback = false): self;
    public function listOfValidators(?string $type = null, ?bool $fallback = null): array;
    public function mockValidator(string $type, $validator = null, bool $fallback = false, ?ValidationActions $actions = null): MockValidator;
    public function actions(?ValidationActions $actions = null): ValidationActions|self;

    // HANDLERS

    public function handle(array|string $typeOrList, $handler = null, bool $fallback = false): self;
    public function registerHandlers(array $list, bool $fallback = false): self;
    public function listOfHandlers(?string $type = null, ?bool $fallback = null): array;
    public function mockHandler(string $type, $handler = null, bool $fallback = false): MockHandler;

    // RELAYS

    public function relay(array|string $typeOrList, $relay = null, bool $fallback = false): self;
    public function registerRelays(array $list, bool $fallback = false): self;
    public function listOfRelays(?string $type = null, ?bool $fallback = null): array;
    public function mockRelay(string $type, $relay = null, bool $fallback = false): MockRelay;

    // TRANSPORTS

    public function transport(string $name): ?Transport;
    public function registerTransport(string $name, $transport): self;
    public function registerTransports(array $transports): self;
    public function listOfTransports(): array;
    public function mockTransport(string $name): MockTransport;

    // RECEIVE

    public function receive(string $transport): ListOfMessages;

    // TYPE RESOLVER

    public function setTypeResolver(TypeResolver $resolver): self;
    public function getTypeResolver(): TypeResolver;

    // MIDDLEWARE

    public function middleware(Middleware|array $middlewareOrList, bool $replace = false): self;
    public function replaceMiddleware(Middleware|array $middlewareOrList): self;
    public function prependMiddleware(Middleware|array $middlewareOrList): self;
    public function appendMiddleware(Middleware|array $middlewareOrList): self;
    public function dropMiddleware(Middleware|array $middlewareOrList): self;
    public function listOfMiddleware(): array;
}
