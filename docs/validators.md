## Message Bus

Api is work-in-progress and only partially documented.

### Validators

By default, messages are validated after they are transformed but before they are handled or relayed.

For example:

```
MessageBus::validate('message.type', function ($message, $type) {
	// check message here
	return true;
});
```

### Registratation

When registering a validator you can specify the message type you wish to validate.

This can be one of:

 * fully qualified message type eg. 'namespace.type'
 * trailing wildcard eg. 'namespace.*'
 * catch-all eg. '*'

Validators themselves can be one of:

 * implementation of the Contracts\Validators (as a class name or an instantiated object)
 * invokable
 * closure

Or:

 * ValidationResult instance

```
use Look\Messaging\Validators\ValidationResult;

MessageBus::validate('namespace.message-type', MyValidator::class);
MessageBus::validate('namespace.*', MyValidator::class);
MessageBus::validate('*', new MyValidator);

class Invokable {
	public function __invoke($message) {
		// check something here
		return true;
	}
}
MessageBus::validate('namespace.message-type', new Invokable);

MessageBus::validate('namespace.message-type', function ($message, $type) {
	// check something here
	return ValidationResult::invalidMessage();
});

MessageBus::validate('*:fallback', ValidationResult::noSchema());
```

It's also possible to register validators using an associative array of type => validator (including wildcard types):

```
MessageBus::validate([
	'namespace.message-type1' => MyValidator1::class,
	'namespace.message-type2' => new MyValidator3,
	'namespace.subtype.*' => function ($message, $type) { /* ... */ return $message; },
]);
```

Or a register a validator for a list of message types (including wildcards)::

```
MessageBus::validate(
	[
		'namespace.message-type1',
		'namespace.message-type2',
		'namespace.subtype.*',
	],
	MyValidator::class
);
```

Note: validators are called using the container, and so you can inject dependencies.

For example:
```
MessageBus::validate(
	'namespace.message-type',
	function ($message, SomeDependency $dependency) {
		// check something here
		return true;
	}
);
```
