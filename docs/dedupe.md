## Message Bus

*GOTCHA: If you are using Dedupe in tests, cron or queue workers, you may wish to explicitly specify when messages are deduplicated and dispatched by calling Dedupe::flush()... Otherwise, your tests may break (if they rely on a message being dispatched) - or - in the case of queue/cron workers, there could be a (long) delay before the message is finally dispatched.*

### Dropping duplicate messages

The [Dedupe](../src/Handlers/Dedupe.php) handler can be used to drop "duplicate" messages within the scope of a single execution (eg. http request, cli command, job dispatch, etc).

To dispatch the first instance of a message and drop subsequent duplicates:
```
use Look\Messaging\Handlers\Dedupe;

MessageBus::handle(
	'namespace.message-type',
	Dedupe::usingFirst(identifyMessageUsing: 'uuid')
);
```

To dispatch the last instance of a message and drop previous duplicates:

```
use Look\Messaging\Handlers\Dedupe;

MessageBus::handle(
	'namespace.message-type',
	Dedupe::usingLast(identifyMessageUsing: 'uuid')
);
```

*Note: this is implemented via a [shutdown function](https://www.php.net/manual/en/function.register-shutdown-function.php) which is executed at the end of the php script or when exit() is called. Given the additional overhead, this option should be used sparingly. An example use case might be if a status of an entity changes multiple times during a script but only the last status should be dispatched as an event.*

You can define how the message can be identified for the purposes of deduplication in several ways:

```
use Look\Messaging\Handlers\Dedupe;

// use a property
Dedupe::usingFirst('message-type', 'uuid');
Dedupe::usingFirst(identifyMessageUsing: 'uuid');
Dedupe::make()
	->identifyMessageUsing('uuid')
	->dispatchFirst();

// use a closure
Dedupe::usingFirst(identifyMessageUsing:function ($message) {
	return 'some calculated value here';
});
```

If no identity is defined, the [spl_object_id](https://www.php.net/manual/en/function.spl-object-id.php) will be used ensuring that the same object cannot be dispatched more than once.

This handler will not mark a message as "handled".

### Flushing messages

As mentioned above, when using the Dedupe::usingLast handler, messages will only be dispatched at shutdown.

In some circumstances, this can lead to unexpected behaviour.

For example, unit tests may fail (as messages will only be dispatched after the tests have been run); or long running processes, such as cron or queue workers, can lead to significant delays in messages being dispatched.

To overcome this, you can force queued messages to be deduped and dispatched by calling:

```
use Look\Messaging\Handler\Dedupe;

Dedupe::flush();
```

### Handling (dropped) duplicates

If needed, you also specify a closure to handle duplicates, as they are detected: 
(for example: to add logging, throwing an exception, etc)

```
Dedupe::make()
	->handleDuplicatesUsing(function ($message) {
		// do something here 
	});
```

This will behave correctly when dispatching either the first or last message.

