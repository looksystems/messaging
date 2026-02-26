## Message Bus

### Batching messages

Just as with multi-step database operations, usually messages should only be dispatched if/when an operation is successful.

MessageBus supports this behaviour via the batch/release/drop methods.

After MessageBus::batch() is called, messages will be held and then only dispatched when the MessageBus::release() method is called. Or, if an operation fails, MessageBus::drop() will remove all pending messages without dispatching them.

```
try {
	// start batching messages
	MessageBus::batch();

	// ... do something that dispatches messages but could fail

	// messages will only really be dispatched here
	MessageBus::release();
} catch(Exception $e) {
	// or the messages will be dropped if an exception is thrown
	MessageBus::drop();
}
```

The code above can also be written more succinctly using a closure:

```
MessageBus::batch(function () {
	// ... do something that dispatches messages but could fail
	// ... MessageBus will wrap in try/catch and either release()
	// ... or drop() and then re-throw the exception
});
```

Note: the closure is called using a container so, if required, dependencies can be injected.

Note: batches can be nested but will only be dispatched or dropped when the last release() or drop() is called. If drop is called at any nesting level, all pending messages will be dropped().

If required, "pending" messages can be accessed:

```
$pending = MessageBus::pending();
```
