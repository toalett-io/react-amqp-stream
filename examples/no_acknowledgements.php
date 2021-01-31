<?php

use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\Factory;
use Toalett\React\AMQP\AMQPSource;
use Toalett\React\AMQP\Options;
use Toalett\React\Stream\StreamAdapter;

require_once __DIR__ . '/../vendor/autoload.php';

$channel = include('amqp_channel.php');
$queue = $argv[1] ?? 'default-queue';
$options = Options::create()->setNoAck(true);

$source = new AMQPSource($channel, $queue, $options);

$loop = Factory::create();
$stream = new StreamAdapter($source, $loop);
$stream->on('data', fn(AMQPMessage $m) => print($m->getBody() . PHP_EOL));

$loop->run();
