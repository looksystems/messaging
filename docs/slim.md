## Message Bus

*Please note: if you want to use AWS SQS or SNS, you will need to set-up the environment variables required by the [AWS PHP SDK](https://github.com/aws/aws-sdk-php) - see [transports](transports.md) for more details.*

### Slim

Start by initialising the MessageBus singleton/facade and passing in a dependency injection container:

```
use DI\Container;
use Look\Messaging\Slim\MessageBus;

$container = new Container;

MessageBus::init($container);
```

This will also register the message bus as a singleton with the container, allowing it to be injected into other services via the MessageBus contract.

For example:

```
use Look\Messaging\Contracts\MessageBus;

class MyService {
	public function  __construct(protected MessageBus $bus)
	{
	}
}

$service = $container->get(MyService::class);
```

#### Configuration

When initialising the MessageBus, you can also pass in configuration.

This provides an method for defining handlers, relays and transports:

```
use DI\Container;
use Look\Support\Env;
use Look\Messaging\Slim\MessageBus;

$container = new Container;

$config = [
    'transformers' => [
        //
    ],
    'schemas' => [
        //
    ],
    'validators' => [
        //
    ],
    'handlers' => [
        //
    ],
    'relays' => [
        //
    ],
    'transports' => [
        'sqs' => [
            'type' => 'sqs',
            'queues' => Env::get('MESSAGING_DEFAULT_SQS_QUEUES'),
        ],
        'sns' => [
            'type' => 'sns',
            'topics' => Env::get('MESSAGING_DEFAULT_SNS_TOPICS'),
        ],
    ],
    'decorate' => [
        'environment' => Env::get('MESSAGING_ENVIRONMENT'),
    ],
];

MessageBus::init($container, $config);

// OR

MessageBus::init()
	->setContainer($container)
	->applyConfig($config);
```
