## Message Bus

### Relays

By default, messages are first handled then relayed.

Relays queue messages to be handled at a later date - usually by another process, such as a queue worker, or by another application. 

When registering a relay you can specify the message type you wish to relay.

This can be one of:

 * fully qualified message type eg. 'namespace.type'
 * trailing wildcard eg. 'namespace.*'
 * catch-all eg. '*'

Relays are usually defined using a transport name and, optionally, a comma separated list of arguments.

Via SQS:
```
MessageBus::relay('namespace.message-type', 'sqs:custom'); // send to "custom" queue
MessageBus::relay('namespace.*', 'sqs'); // send to default queue
```

Via SNS:
```
MessageBus::relay('namespace.message-type', 'sns:topic1,topic2'); // publish to "topic1" & "topic" 2
MessageBus::relay('namespace.*', 'sns'); // publish to default topic
```

Custom relays can also be defined as one of:

 * implementation of the Contracts\Relay interface (as a class name or an instantiated object)
 * invokable
 * closure

```
MessageBus::relay('namespace.message-type', MyRelay::class);
MessageBus::relay('namespace.*', MyRelay::class);
MessageBus::relay('*', new MyRelay);

class Invokable {
	public function __invoke($message) {
		// do something here
	}
}
MessageBus::relay('namespace.message-type', new Invokable);

MessageBus::relay('namespace.message-type', function ($message) {
	// relay message here
});
```

It's also possible to register relays using an associative array of type => relay (including wildcard types):

```
MessageBus::relay([
	'namespace.message-type1' => 'sqs:queue1',
	'namespace.message-type2' => 'sns:topic1,topic2',
	'namespace.subtype.*' => 'sqs:queue3',
]);
```

Or a register a relay for a list of message types (including wildcards):

```
MessageBus::relay(
	[
		'namespace.message-type1',
		'namespace.message-type2',
		'namespace.subtype.*',
	],
	'sns:topic'
);
```

Finally, it's possible to register fallbacks, which only apply if the message hasn't been handled/relayed:

```
MessageBus::relay('*', MyFallbackRelay::class, fallback: true);
MessageBus::relay('*:fallback', MyFallbackRelay::class);
```

Note: relays are called using the container, and so you can inject dependencies.

For example:
```
MessageBus::relay(
	'namespace.message-type',
	function ($message, SomeDependency $dependency) {
		// do something here
	}
);
```
