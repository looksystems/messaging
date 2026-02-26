## Message Bus

### Message state

In addition to the message itself, handlers and relays also receive a [State](../src/State.php) object. 

```
use Look\Messaging\Middleware\State;

function ($message, $state) {
	// do something here
}
```

This can be used to check whether the message has been handled or relayed; as well as specify how the message is (or is not) processed going forward.

```
$state->wasHandled()
$state->stopHandling()
$state->wasRelayed()
$state->stopRelaying()
$state->markAsNotDispatched()
$state->wasDispatched()
$state->stop() // stop all processing
$state->abort() // stop and mark as not dispatched
```

Note: when a handler or relay ```returns false``` then the message will not be marked as having been handled or relayed.
