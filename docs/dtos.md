## Message Bus

This is work-in-progress and subject to change.

### Using the ProvidesMessage interface

By implementing the [ProvidesMessage](../src/Contracts/ProvidesMessage.php) contract/interface, your own DTOs can  dispatched:

```
use Look\Messaging\Contracts\ProvidesMessage;
use Look\Messaging\Message;
use Look\Messaging\MessageBus;

// dto with provides message interface

class MyDto implements ProvidesMessage
{
    public function toMessage(): MessageInterface
    {
        return Message::make('namespace.message-type', [ /* payload */ ]);
    }
}

MessageBus::dispatch(new MyDto);

```

### Implementing a custom Message class

If required, you can create your own custom Message class by implementing the [MessageInterface](../src/Contracts/MessageInterface.php) contract/interface.

```
use Look\Messaging\Contracts\MessageInterface;
use Look\Messaging\MessageBus;

// custom message object
class MyMessage implement MessageInterface
{
}

MesageBus::dispatch(new MyMessage);
```

### Type resolvers

See [TypeResolver](../src/Contracts/TypeResolver.php) contract/interface.

More to follow.

### Code generation

See [DtoBuilder](../src/Codegen/DtoBuilder.php) class.

More to follow.
