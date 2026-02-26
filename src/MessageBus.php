<?php

namespace Look\Messaging;

use Look\Messaging\Contracts\MessageBus as MessageBusContract;
use Look\Messaging\Contracts\TypeResolver;
use Look\Messaging\Exceptions\InvalidMessageException;
use Look\Messaging\Middleware\State;
use Look\Messaging\Resolvers\MessageProperty;
use Look\Messaging\Support\ListOfMessages;
use Look\Messaging\Support\Pipeline;
use Look\Messaging\Support\Str;
use Psr\Container\ContainerInterface;

class MessageBus implements MessageBusContract
{
    use Concerns\AppliesConfig;
    use Concerns\CanBatch;
    use Concerns\CanBoot;
    use Concerns\RegistersHandlers;
    use Concerns\RegistersMiddleware;
    use Concerns\RegistersRelays;
    use Concerns\RegistersSchemas;
    use Concerns\RegistersTransformers;
    use Concerns\RegistersTransports;
    use Concerns\RegistersValidators;
    use Concerns\TracksHistory;
    use Concerns\UsesContainer;
    use Concerns\UsesTypeResolver;

    protected Pipeline $pipeline;

    // INSTANTIATION

    public function __construct(
        ?ContainerInterface $container = null,
        ?array $transports = null,
        ?array $middleware = null,
        ?TypeResolver $resolver = null
    ) {
        $this->pipeline = new Pipeline($container);

        if (is_null($transports)) {
            $transports = [
                'sqs' => Transports\SqsTransport::class,
                'sns' => Transports\SnsTransport::class,
            ];
        }
        $this->registerTransports($transports);

        if (is_null($middleware)) {
            $middleware = [
                new Middleware\TransformMessage,
                new Middleware\ValidateMessage,
                new Middleware\HandleMessage,
                new Middleware\RelayMessage,
            ];
        }

        $this->middleware($middleware);

        $this->setTypeResolver(
            $resolver ?? new MessageProperty('_type')
        );
    }

    // DISPATCH

    /**
     * @throws InvalidMessageException
     */
    public function dispatch(object|array|string $messageOrType, object|array $payload = [], Envelope|array $envelope = [], bool $throwIfInvalid = true): self
    {
        if (is_string($messageOrType)) {
            $message = Message::make($messageOrType);
        } else {
            $message = $this->resolve($messageOrType);
        }

        if (!$message) {
            if ($throwIfInvalid) {
                throw new InvalidMessageException;
            }

            return $this;
        }

        if ($payload) {
            $message->merge($payload);
        }

        if ($envelope) {
            $message->envelope()->applyStamps($envelope);
        }

        if ($this->batchLevel) {
            $this->pending[] = $message;
            return $this;
        }

        $this->boot();

        $state = new State;

        $this->pipeline
            ->via('prepare')
            ->send([$message, $this, $state])
            ->through($this->listOfMiddleware())
            ->thenReturn();

        if ($state->wasDispatched()) {
            $this->dispatched()->push($message);
        }

        return $this;
    }

    // RECEIVE

    public function receive(string $transport): ListOfMessages
    {
        $this->boot();

        [$transportName, $transportArgs] = Str::nameAndArgs($transport);
        $transport = $this->transport($transportName);
        if (!$transport) {
            return new ListOfMessages($this);
        }

        $messages = $transport->receive($transportArgs);

        return new ListOfMessages($this, $messages);
    }
}
