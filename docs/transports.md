## Message Bus

### Transports

Transports are used to send/receive messages.

See [relays](./relays.md) and [receiving messages](./receiving.md) docs for more details.

#### AWS

When using AWS, the following environment variables need to be set:

```
AWS_ACCESS_KEY_ID=""
AWS_SECRET_ACCESS_KEY=""
AWS_REGION="eu-west-1"
```

For SQS you can set-up your queue prefix.

For example:

```
AWS_SQS_PREFIX="https://sqs.eu-west-1.amazonaws.com/457947850338"
```

For SNS you can set-up your topic prefix.

For example:

```
AWS_SNS_PREFIX="arn:aws:sns:eu-west-1:457947850338"
```

By default the SQS and SNS relays will send a MessageDeduplicationId.

Currently this will fallback to using php's uniqid() function but the type resolvers will be updated to extract the message id, where provided;

#### Named transports

You can register "named" transports.

For example, individually:

```
// using string syntax (same as relays)
MessageBus::registerTransport('finder-ingest', 'sqs:FinderJobs-dev');

// using array syntax
MessageBus::registerTransport(
	'finder-events',
	[
		'type' => 'sns',
		'queues' => 'FinderServiceEvents-dev.fifo',
		'decorate' => [
			'envelope:environment'
		],
	]
);
```

Or as a list (usually loaded from config):

```
MessageBus::registerTransports([
	'finder-ingest' => 'sqs:FinderJobs-dev',
	'finder-events' => 'sns:FinderServiceEvents-dev.fifo',
	// etc
])
```

These can then be used in relays:

```
MessageBus::relay('finder.events.*', 'finder-events');
```

Or when receiving:

```
MessageBus::receive('finder-ingest')->dispatch();
```

#### Custom transports

You can also register your own custom transports which implement the [Transport](../src/Contracts/Transport.php) interface:

```
// class name
MessageBus::registerTransport('custom', MyTransport::class);

// transport instance
MessageBus::registerTransport('custom', new MyTransport);

// closure returning a transport instance
MessageBus::registerTransport('custom', function () {
	return new MyTransport;
});
```

#### Serializers

If your incoming or outgoing messages do not conform to the default format provided by MessageBus, you can implement your a custom [Serializer](../src/Contracts/Transport.php).

This can then be registered as the [default serializer](../src/Serializers/DefaultSerializer.php) or apply it to a specific transport - see the [WithSerializationMethods](../src/Transports/Concerns/WithSerializationMethods.php) trait for more information.


For example:
```
// using a custom Serializer class
MessageBus::registerTransport('custom', function () {
	return (new SqsTransport)
		->serializeUsing(new MySerializer)
		->unserializeUsing(new MySerializer);
});

// using closures
MessageBus::registerTransport('custom', function () {
	return (new SqsTransport)
		>serializeUsing(function ($message) {
			return [ /* hydrate here */];
		})
		->unserializeUsing(function ($data) {
			return Message::make();
		});
});
```

#### Decorators

As an alternative to Serializer, you can adapt messages using the [Decorators](../src/Contracts/Decorator.php).

Either you can apply individual decorators via the [DefaultDecorator](../src/Decorators/DefaultDecorator.php) and [StandardDecorator](../src/Decorators/StandardDecorator.php) classes.

For example:
```
DefaultDecorator::init([
	'env' => $_ENV['APP_ENV'] ?? 'local'
])
```

Or you can apply decorators to a specific transport - see the [WithDecorationMethods](../src/Transports/Concerns/WithDecorationMethods.php) trait for more information.

For example:
```
// using a custom Decorator class
MessageBus::registerTransport('custom', function () {
	return (new SqsTransport)
		->decorateUsing(new MyDecorator);
});

// using closures
MessageBus::registerTransport('custom', function () {
	return (new SqsTransport)
		->decorateUsing(function (object $data) {
			return $decorated;
		});
});
```