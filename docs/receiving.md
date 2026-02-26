## Message Bus

### Receiving

Usually, messages would be received from a transport and then be dispatched on the local message bus.

For example:

```
MessageBus::receive('sqs')->dispatch();
```

Optionally, you can specify an exception handler:
```
MessageBus::receive('sqs')->dispatch(
  function ($exception, $message) {
    // handle exception here
  }
);
```

Of course, you can just receive messages and process them yourself:

```
$messages = MessageBus::receive('sqs');
foreach ($messages as $message) {
   // do something here
}
```
