# 🚽 Toalett

Welcome to Toalett, a humble initiative. Toalett is the Norwegian word for toilet 💩.

## What is `toalett/react-amqp-stream`?

This is a libary that allows you to interact with an AMQP message queue as if it were a readable stream in ReactPHP.
It's very lightweight - its only dependencies
are [`toalett/react-stream-adapter`](https://packagist.org/packages/toalett/react-stream-adapter)
and [`php-amqplib/php-amqplib`](https://packagist.org/packages/php-amqplib/php-amqplib).

The class [`AMQPSource`](src/AMQPSource.php) implements the `Toalett\React\Stream\Source` interface
from [`toalett/react-stream-adapter`](https://packagist.org/packages/toalett/react-stream-adapter). It needs an instance
of `PhpAmqpLib\Channel\AMQPChannel` and the name of the queue to read from. You may provide additional options for the
call to `AMQPChannel::basic_consume()` by passing in an `Options` object as the third parameter of the constructor.

The `Toalett\React\Stream\StreamAdapter` wraps a `Source` and makes it approachable as a
[readable stream](https://reactphp.org/stream/) in an [event loop](https://reactphp.org/event-loop/).

## Installation

It is available on [Packagist](https://packagist.org/packages/toalett/):

```bash
composer require toalett/react-amqp-stream
```

## Motivation

_Note:_ this motivation is the same as given
in [`toalett/react-stream-adapter`](https://packagist.org/packages/toalett/react-stream-adapter).

I was working on a project that required an application to respond to AMQP messages in a non-blocking way. The
application made use of an event loop. Initially I used a periodic timer with a callback, but as the application grew
this became a cluttered mess. It slowly started to feel more natural to treat the message queue as a stream. This makes
sense if you think about it:

> In computer science, a stream is a sequence of data elements made available over time. A stream can be thought of as items
> on a conveyor belt being processed one at a time rather than in large batches.
>
> &mdash; <cite> [Stream (computing) on Wikipedia](https://en.wikipedia.org/wiki/Stream_(computing)) </cite>

This definition suits a message queue.

In the project I mentioned earlier, I use this library to poll an AMQP queue every 10 seconds. This keeps my load low
and allows me to do other things in the meantime. This abstraction turned out really useful, so I thought that others
might enjoy it too.

## How do I use this?

The library tries to stay out of your way as much as possible. Going from an AMQP connection to a readable stream only
takes a few lines of code! 😀

1. Create a connection to an AMQP host with PhpAmqpLib, and grab a channel.
1. Pass the channel instance and the name of the queue to consume, optionally with an instance
   of [`Options`](src/Options.php), to the constructor of [`AMQPSource`](src/AMQPSource.php).
1. Create an event loop using the `React\EventLoop\Factory`.
1. Create a `StreamAdapter`, and pass the [`AMQPSource`](src/AMQPSource.php) and the event loop to the constructor.
1. Interact with the adapter as if it were any other `ReadableInputStream`.

Let us see this in action with two examples.

```php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\Factory as EventLoopFactory;
use Toalett\React\AMQP\AMQPSource;
use Toalett\React\Stream\StreamAdapter;

$channel = (new AmqpStreamConnection(/* ... */))->channel();
$queueName = 'my-app.work-queue';
$amqpSource = new AMQPSource($channel, $queueName);

$eventLoop = EventLoopFactory::create();
$stream = new StreamAdapter($amqpSource, $eventLoop);

$stream->on('data', fn(AMQPMessage $m) => /* ... */);
$stream->on('error', fn(RuntimeException $e) => /* ... */);

$eventLoop->run();
```

As you can see, it takes only 2 SLOC to go from an AMQP connection or channel to a readable stream.

The [`Options`](src/Options.php) class provides a way to pass arguments to the call to `AMQPChannel::basic_consume`.
Once you passed the [`Options`](src/Options.php) instance to the constructor of [`AMQPSource`](src/AMQPSource.php), you
lose the ability to change them; the [`Options`](src/Options.php) instance is cloned to prevent unexpected behaviour.

```php
use Toalett\React\AMQP\AMQPSource;
use Toalett\React\AMQP\Options;

// ...
$options = (new Options)
    ->setConsumerTag('worker.1')
    ->setNoAck(true);

$amqpSource = new AMQPSource($channel, $queueName, $options);
// ...
```

If you don't provide a [consumer tag](https://www.rabbitmq.com/consumers.html#consumer-tags), the server will assign
one. You can retrieve this consumer tag from an [`AMQPSource`](src/AMQPSource.php) with `getConsumerTag()`.

Check out the [examples](examples) folder for some simple implementations. They are not much different than the ones
given in the above two code blocks. Feel free to play around with them.

_Note:_ There are some concessions with regards to latency due to the fact that the stream adapter library uses polling
under the hood. Please refer
to [`toalett/react-stream-adapter`](https://packagist.org/packages/toalett/react-stream-adapter) for more information
about this subject.

## Questions

__Q__: _How do I handle stream errors_?  
__A__: The RuntimeException that is passed to the `on('error', ...)` callback contains the exception that was actually
thrown by the `Source`. Calling `getPrevious()` on the RuntimeException gives you the original exception.

__Q__: _Where are the tests_?  
__A__: Tests might be added later. Feel free to create an issue if this bothers you!
