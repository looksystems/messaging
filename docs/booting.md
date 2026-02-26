## Message Bus

### Booting

Good practice is to wrap the registration of handlers, relays and transports in a closure using the MessageBus::booting method:

```
MessageBus::booting(function ($bus)) {
	$bus->handle('namespace.message-type', MyHandler::class);
});
```

This will defer registration so that it's only called if/when the first event is dispatched.

It also means that any dependencies will be fully initialised.

_For example, the boot order of Laravel's service providers is not guaranteed, such that the config provided by one is used during the registration of handlers in another. Using a closure ensures all service providers will be fully initialised._

_Speak to me after class if you're firing events while booting your service provider! ;-)_
