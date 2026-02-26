## Message Bus

### Handlers

By default, messages are first handled then relayed.

When registering a handler you can specify the message type you wish to handle.

This can be one of:

 * fully qualified message type eg. 'namespace.type'
 * trailing wildcard eg. 'namespace.*'
 * catch-all eg. '*'

Handlers themselves can be one of:

 * implementation of the Contracts\Handler interface (as a class name or an instantiated object)
 * invokable
 * closure

```
MessageBus::handle('namespace.message-type', MyHandler::class);
MessageBus::handle('namespace.*', MyHandler::class);
MessageBus::handle('*', new MyHandler);

class Invokable {
	public function __invoke($message) {
		// do something here
	}
}
MessageBus::handle('namespace.message-type', new Invokable);

MessageBus::handle('namespace.message-type', function ($message) {
	// do something here
});
```

It's also possible to register handlers using an associative array of type => handler (including wildcard types):

```
MessageBus::handle([
	'namespace.message-type1' => MyHandler1::class,
	'namespace.message-type2' => new MyHandler3,
	'namespace.subtype.*' => function ($message) { /* ... */ },
]);
```

Or a register a handler for a list of message types (including wildcards):

```
MessageBus::handle(
	[
		'namespace.message-type1',
		'namespace.message-type2',
		'namespace.subtype.*',
	],
	MyHandler::class
);
```

Finally, it's possible to register fallbacks, which only apply if the message hasn't been handled/relayed:

```
MessageBus::handle('*', MyFallbackHandler::class, fallback: true);
MessageBus::handle('*:fallback', MyFallbackHandler::class);
```

Note: handlers are called using the container, and so you can inject dependencies.

For example:
```
MessageBus::handle(
	'namespace.message-type',
	function ($message, SomeDependency $dependency) {
		// do something here
	}
);
```
