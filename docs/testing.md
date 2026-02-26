## Message Bus

### Testing

You can quickly and easily mock handlers, relays or transports:

```
$mockHandler = MessageBus::mockHandler('namespace.type');

// check to see if handler was called
$this->assertTrue($mock->called());

// check first message handled
$this->assertEquals($message, $mock->messages()->first());
```

```
$mockRelay = MessageBus::mockRelay('namespace.type');

// check to see if handler was called
$this->assertTrue($mock->called());

// check last message relayed
$this->assertEquals($message, $mock->messages()->last());
```

```
$mock = MessageBus::mockTransport('sqs');

// prepare a couple of messages to the queue
$messages = [
    Message::make('namespace.type', [ 'value' => 1 ],
    Message::make('namespace.type', [ 'value' => 2 ],
];
$mock->push($messages);

// check to see if transport sent any messages
$this->assertTrue($mock->sent());

// check last message sent
$this->assertEquals($message, $mock->messages()->last());
```

### Dedupe gotcha

When using the Dedupe::usingLast handler, messages will only be dispatched on shutdown.

In order to assert that messages are sent in your test, you can emulate shutdown by calling:

```
use Look\Messaging\Handler\Dedupe;

Dedupe::flush();
```