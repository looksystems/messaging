## Message Bus

### Why use a message bus?

The message bus (aka broker) pattern is used to decouple the act of sending of a message from the implementation details of how and where the message is sent and how it's handled when it reaches its final destination. Basically, dispatching a message is "fire and forget". At the point of dispatch, the sender doesn't know and shouldn't care where it goes or what's done with it (if anything)... that's determined by how the message bus is configured at the time.

### Designing messages

Messages are essentially contracts/interfaces between systems and, as such, should be designed with care.

There are two broad categories of messages:

* events ie. something happened
* commands ie. asking/telling another system to do something

As a simple rule, you should prefer using "events" over "commands".

Where possible, aim to create "events" that represent things that have happened in the real world, rather than be driven by the system architectual/technical implementation eg. NewCustomerWasRegistered vs CustomerDatabaseRecordCreated.

Another property of events is that they are immutable, as they have happen already.

Typically, "commands" introduce coupling and dependencies between systems and can lead to inadvertently creating a "distributed monolith" ie. services which can no longer be developed independently but which must be modified and deployed in lock step.

For the same reason, it is important to think carefully about fields included in a message, as they will be relied upon downstream and can become cumbersome/difficult to change in the future. Again, try to rely on representing the real world, rather than leaking/exposing implementation details eg. try to avoid just pushing the content of your database record "as is", as this will make it difficult to refactor in the future.

As rule of thumb, keep messages simple... 

When you're tempted to add more fields than is strictly necessary, view this as a signal to consider if you're emitting the right event or if the coupling/boundary between systems is correct in the first place. Sometimes it is, but, often it won't be.

### Modular monoliths

One trend in microservices is to revert to developing so called "modular monoliths".

This means the application is organised as a set of "modules" which, while they are run in a single process, are also are decoupled such that they can be quickly and easily be split/deployed as separate services.

This approach allows rapid development and innovation during the early development/discovery phase of a project; as boundaries between modules can be quick and easy changed; and the whole code base can be rapidily refactored... as we learn more about the problem domain. But, at the same time, it does not sacrafice our future ability to scale up and split out individual parts of the application as the services boundaries and real patterns of usage come into focus.

Using a message bus (and event driven architecture) in this context is a great tool.

Essentially, this approach allows us to defer the (not insignifcant) costs of building, developing and deploying distributed systems, until we have a genunine need to do so.

Sidenote: with a little self discipline - since they are decoupled - modules can be developed on "independently" by team members, in just the same way as microservice, with all the attendant benefits; even when working within the same repository. Developing "modules as packages" is one technique that can help with this.

For example, we can start with two modules in same application:

```
// IN MODULE 1

// dispatch message
MessageBus::dispatch(new SomethingHappened);

// IN MODULE 2

// during bootstrapping register handler for message type
MessageBus::handle('something.happened', SomethingHappenedHandler::class);
```

Then - at a later date - you can split the two modules into separately deployable/scalable applications:

```
// IN APPLICATION 1

// during bootstrapping register sqs relay for this message type
MessageBus::relay('something.happened', 'sqs');

// dispatch message as before
MessageBus::dispatch(new SomethingHappened);

// IN APPLICATION 2

// during bootstrapping register handler for message type
MessageBus::handle('something.happened', SomethingHappenedHandler::class);

// in a cron job check the sqs queue
MessageBus::receive('sqs')->dispatch();
```

Of course - even later - you may have several modules/applications listening to a single message in which case, with a few small configuration changes, you can switch to using an SNS relay to broadcast the message to several applications.
