# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

`messagebus-php` is a PHP message bus library implementing the broker pattern to decouple message dispatching from transport implementation. It supports:
- Event-driven architecture for modular monoliths and microservices
- AWS SQS/SNS transports for distributed systems
- Middleware pipeline for message transformation, validation, and handling
- Laravel and Slim framework integrations
- Message schema validation and DTO code generation

## Development Commands

### Testing
```bash
# Run all tests
composer test

# Run tests without stopping on failure
composer tests

# Run specific test(s) by filter
composer filter -- <pattern>

# Generate code coverage report (HTML)
composer coverage

# Generate code coverage report (XML for CI)
composer coverage-xml
```

### Code Quality
```bash
# Run Laravel Pint code style fixer
composer csfix
```

### Documentation
```bash
# Generate API documentation
composer docs
```

## Architecture Overview

### Core Components

**MessageBus** (`src/MessageBus.php`)
- Central dispatcher using trait composition for modular functionality
- Implements middleware pipeline pattern for message processing
- Uses "Concerns" traits to organize features (see `src/Concerns/`)
- Key concerns: `RegistersHandlers`, `RegistersRelays`, `RegistersTransports`, `RegistersSchemas`, `RegistersValidators`, `CanBatch`, `TracksHistory`

**Message** (`src/Message.php`)
- Core message entity with type, payload, id, version, timestamp
- Implements ArrayAccess for payload manipulation
- Contains Envelope for metadata ("stamps")
- Messages are immutable by design (represent events that already happened)

**Envelope** (`src/Envelope.php`)
- Metadata container for messages (not part of the message payload)
- Stores "stamps" (key-value metadata) and timestamps
- Used for flow control, routing, and debugging

### Middleware Pipeline

Messages flow through a configurable middleware pipeline (see `src/Middleware/`):

1. **TransformMessage** - Applies registered transformers to convert message formats
2. **ValidateMessage** - Validates against JSON schemas or custom validators
3. **HandleMessage** - Executes registered handlers for the message type
4. **RelayMessage** - Forwards messages to registered transports (SQS, SNS, etc.)

Each middleware can control flow using the `State` object to mark messages as handled, relayed, or dispatched.

### Transports

Transports (`src/Transports/`) handle sending/receiving messages:
- **SqsTransport** - AWS SQS queues (point-to-point)
- **SnsTransport** - AWS SNS topics (pub/sub broadcast)
- Support for custom transports via `Transport` interface
- Named transport registration for cleaner configuration
- Serializers and Decorators for message format adaptation

### Type Resolution and Wildcard Matching

The library uses `TypeResolver` to extract message types and supports wildcard patterns:
- `'namespace.message-type'` - exact match
- `'namespace.*'` - matches all messages in namespace
- `'*'` - catch-all
- `'*:fallback'` - only fires if nothing else handled the message

See `src/Support/Wildcard.php` for pattern matching logic.

### Framework Integrations

**Laravel** (`src/Laravel/`)
- Auto-registered service provider
- Facade for MessageBus
- Queue transport integration (pushes to Laravel queue)
- Laravel validation rules support
- Configuration publishing

**Slim** (`src/Slim/`)
- Service provider for Slim framework

## Key Concepts

### Messages as Events vs Commands
- Prefer designing messages as "events" (things that happened) over "commands" (instructions)
- Events are immutable and represent real-world occurrences
- Keep messages simple; avoid exposing implementation details

### Modular Monolith Pattern
This library enables:
1. Start with modules in same application (in-process message handling)
2. Later split modules into separate services (add SQS/SNS relays)
3. Maintain decoupling throughout evolution

### Batching
Use `batch()` to collect messages and dispatch them together:
```php
MessageBus::batch(function ($bus) {
    $bus->dispatch($message1);
    $bus->dispatch($message2);
}); // all dispatched together at end
```

### Message History
The `TracksHistory` concern maintains a record of dispatched messages for testing and debugging.

## Testing

- Tests use Pest PHP framework (see `tests/Pest.php`)
- `tests/Unit/` contains unit tests
- `tests/Integration/` contains integration tests (may hit AWS if configured)
- `tests/MessageBusTestCase.php` provides base test utilities
- Mock implementations available in `src/Mock/`

## Code Style

- Uses Laravel Pint with Laravel preset
- Configuration in `pint.json`
- Disabled rules: `class_attributes_separation`, `not_operator_with_successor_space`
- Empty `blank_line_before_statement` statements list

## AWS Configuration

When using AWS transports, set these environment variables:
```bash
AWS_ACCESS_KEY_ID=""
AWS_SECRET_ACCESS_KEY=""
AWS_REGION="eu-west-1"
AWS_SQS_PREFIX="https://sqs.eu-west-1.amazonaws.com/ACCOUNT_ID"
AWS_SNS_PREFIX="arn:aws:sns:eu-west-1:ACCOUNT_ID"
```

## Important Implementation Details

### Pipeline Execution
The Pipeline (`src/Support/Pipeline.php`) uses the `via` method to specify which method to call on middleware. By default it calls `prepare()` on each middleware class.

### Deduplication
The library includes deduplication support via `src/Support/DedupeUtils.php` and `DedupeSession.php`. SQS/SNS transports send MessageDeduplicationId by default.

### Type Resolvers
Multiple resolvers exist in `src/Resolvers/`:
- `MessageProperty` - extracts type from message property (default: `_type`)
- `ClassType` - derives type from class name
- `AbstractResolver` - base for custom resolvers

### Schema Validation
JSON Schema validation using `opis/json-schema`. Register schemas with `MessageBus::schema()`. Schemas validate message payloads before handling/relaying.

### DTO Code Generation
`src/Codegen/DtoBuilder.php` can generate PHP DTO classes from JSON schemas using `nette/php-generator`.

## Distribution

The `dist/laravel/` directory contains a distribution build for Laravel applications with vendored dependencies.
