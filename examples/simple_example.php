<?php

use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\Factory;
use Toalett\React\AMQP\AMQPSource;
use Toalett\React\Stream\StreamAdapter;

require_once __DIR__ . '/../vendor/autoload.php';

$channel = include('amqp_channel.php');
$queueName = $argv[1] ?? 'default-queue';

$amqpSource = new AMQPSource($channel, $queueName);

$loop = Factory::create();
$stream = new StreamAdapter($amqpSource, $loop);
$stream->on('data', function (AMQPMessage $message) {
    print('Message: ' . $message->getBody() . PHP_EOL);
    $message->ack();
});

$loop->run();
