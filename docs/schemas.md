## Message Bus

### Message schemas

Messages can be validated by registering json schemas:

```
MessageBus::schema(
	'example.*',
	'https://example.com/',
	'dir:/path/to/schemas'
);

MessageBus::schemas([
	'example.shop.order-created' => [
		'id' => https://example.com/shop/order-created',
		'path' => '/path/to/schema/order-created.json'
	],
	'example.shop.order-dispatched' => [
		'id' => https://example.com/shop/order-dispatched',
		'path' => '/path/to/schema/order-dispatched.json'
	],
);

MessageBus::registerSchemas([
	'looksystems.*' => [
		'prefix' => https://example.com/',
		'path' => '/path/to/schemas'
	]
);
```

*Note: The schema methods are "syntatic sugar". Under the hood, they register [JsonSchema](../src/Validators/JsonSchema.php) validators which are evaluated by the [ValidateMessage](../src/Middleware/ValidateMessage.php) middleware.*

The [official Json Schema website](https://json-schema.org) has documentation and tutorials covering details of how to create a schema.

Most but not all of the json schema syntax is supported - see the documentation of the [Opie Json Schema package](https://opis.io/json-schema) for further details.

Some examples are available in the [test resources](../tests/resources/schemas) folder.
