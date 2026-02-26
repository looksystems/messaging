## Message Bus

### Transformers

By default, messages are transformed before they are handled or relayed.

Transformers can be used to modify messages eg. upcasting a message from an older version.

For example:

```
MessageBus::transform('message.type', function ($message) {
	if ($message->version === 1) {
		$message->version = 2;
		$message->new_property = $message->old_property;
		unset($message->old_property);
	}

	return $message;
});
```

### Registratation

When registering a transformer you can specify the message type you wish to transform.

This can be one of:

 * fully qualified message type eg. 'namespace.type'
 * trailing wildcard eg. 'namespace.*'
 * catch-all eg. '*'

Transformers themselves can be one of:

 * implementation of the Contracts\Transformer interface (as a class name or an instantiated object)
 * invokable
 * closure

```
MessageBus::transform('namespace.message-type', MyTransformer::class);
MessageBus::transform('namespace.*', MyTransformer::class);
MessageBus::transform('*', new MyTransformer);

class Invokable {
	public function __invoke($message) {
		// do something here
		return $message;
	}
}
MessageBus::transform('namespace.message-type', new Invokable);

MessageBus::transform('namespace.message-type', function ($message) {
	// do something here
	return $message;
});
```

It's also possible to register transformers using an associative array of type => transformer (including wildcard types):

```
MessageBus::transform([
	'namespace.message-type1' => MyTransformer1::class,
	'namespace.message-type2' => new MyTransformer3,
	'namespace.subtype.*' => function ($message) { /* ... */ return $message; },
]);
```

Or a register a transformer for a list of message types (including wildcards)::

```

MessageBus::transform(
	[
		'namespace.message-type1',
		'namespace.message-type2',
		'namespace.subtype.*',
	],
	MyTransformer::class
);
```

Note: transformers are called using the container, and so you can inject dependencies.

For example:
```
MessageBus::transform(
	'namespace.message-type',
	function ($message, SomeDependency $dependency) {
		// do something here
	}
);
```
