<?php

namespace Toalett\React\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Toalett\React\Stream\Source;

class AMQPSource implements Source
{
    private AMQPChannel $channel;
    private Options $options;
    private string $queueName;
    private bool $closed = true;
    private array $pendingMessages = [];

    public function __construct(AMQPChannel $channel, string $queueName, ?Options $options = null)
    {
        $this->channel = $channel;
        $this->queueName = $queueName;
        $this->options = is_null($options) ? new Options() : clone $options;
        $this->open();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getConsumerTag(): string
    {
        return $this->options->consumerTag;
    }

    public function open(): void
    {
        if (false === $this->closed) {
            return;
        }

        $this->options->consumerTag = $this->channel->basic_consume(
            $this->queueName,
            $this->options->consumerTag,
            $this->options->noLocal,
            $this->options->noAck,
            $this->options->exclusive,
            $this->options->noWait,
            fn(AMQPMessage $message) => $this->pendingMessages[] = $message,
            $this->options->ticket,
            $this->options->arguments
        );
        $this->closed = false;
    }

    public function select(): ?AMQPMessage
    {
        if ($this->closed) {
            return null;
        }

        $this->channel->wait(null, true);
        return array_shift($this->pendingMessages);
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->channel->basic_cancel($this->options->consumerTag);
        $this->closed = true;
    }

    public function eof(): bool
    {
        return false;
    }
}