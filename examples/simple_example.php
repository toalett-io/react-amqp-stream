<?php

use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\Factory;
use Toalett\React\AMQP\AMQPSource;
use Toalett\React\Stream\StreamAdapter;

require_once __DIR__ . '/../vendor/autoload.php';

$channel = include('amqp_channel.php');
$queue = $argv[1] ?? 'default-queue';

$source = new AMQPSource($channel, $queue);

$loop = Factory::create();
$stream = new StreamAdapter($source, $loop);
$stream->on('data', function (AMQPMessage $message) {
    print('Message: ' . $message->getBody() . PHP_EOL);
    $message->ack();
});

$loop->run();
