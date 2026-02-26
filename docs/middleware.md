## Message Bus

### Middleware

You can create and register [Middleware](../src/Middleware):

For example, to drop test messages:

```
use Look\Messaging\Middleware\Middleware;

DropTestMessages extends Middleware
{
	public function handle($message)
	{
		if (!empty($message->test)) {
			$this->state->abort();
			return null;
		}

		return $this->next($message);
	}
}

MessageBus::prependMiddleware(new DropTestMessages);
```

Or to log all undispatched messages:

```
use Look\Messaging\Middleware\Middleware;
use Look\Support\Log;

LogWhenNotDispatched extends Middleware
{
	public function handle($message)
	{
		$message = $this->next($message);
		if (!$this->state->wasDispatched()) {
			Log::info($this->original);
		}

		return $message;
	}
}

MessageBus::appendMiddleware(new LogWhenNotDispatched);
```
