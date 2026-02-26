## Message Bus

*Please note: if you want to use AWS SQS or SNS, you will need to set-up the environment variables required by the [AWS PHP SDK](https://github.com/aws/aws-sdk-php) - see [transports](transports.md) for more details.*

### Laravel

The [MessagingServiceProvider](../src/Laravel/MessagingServiceProvider.php) should be registered automatically and provides the following:

**1. The MessageBus facade**

```
use Look\Messaging\Laravel\Facades\MessageBus;
```

**2. A laravel "queue" transport**

As you would expect, this pushes messages onto the laravel queue for them to be dispatched and handled by queue workers.

```
MessageBus::relay('namespace.message-type', 'queue:custom'); // send to "custom" queue
MessageBus::relay('namespace.*', 'queue'); // send to default queue
```

**3. Config**

You can publish [config](../src/Laravel/config/messaging.php) as follows:

```
./artisan vendor:publish --provider='\Look\Messaging\Laravel\MessagingServiceProvider' --tag=config
```

This provides an alternative method for registering handlers, relays and transports.

**4. Message validation**

In addition to [json schemas](schemas.md), messages can be validated using Laravel rules:
```
MessageBus::rules(
	'namespace.message-type',
	[
		'first_name' => 'max:255|required',
		'last_name' => 'max:255|required',
		'email' => 'email|nullable'
	)
);

MessageBus::rules(
	'namespace.entity.*',
	[
		'entity_id' => 'exists:entity_table,id',
	)
);
```

It's also possible to register rules using an associative array of type => rules (including wildcard types):
```
MessageBus::rules([
	'namespace.message-type-1' => [
		'first_name' => 'required',
	],
	'namespace.message-type-2' => [
		'last_name' => 'required',
	],
	'namespace.entity.*' => [
		'entity_id' => 'exists:entity_table,id',
	]
);
```

Or a register a set of rules for a list of message types (including wildcards):
```
MessageBus::rules(
	[
	'namespace.message-type-1',
	'namespace.message-type-2',
	],
	[
		'first_name' => 'required',
	]
);
```

**5. Console commands**

To follow.
