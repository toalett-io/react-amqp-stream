<?php

namespace Toalett\React\AMQP;

class Options
{
    public string $consumerTag = '';
    public bool $noLocal = false;
    public bool $noAck = false;
    public bool $exclusive = false;
    public bool $noWait = false;
    public ?int $ticket = null;
    public array $arguments = [];

    public static function create(): Options
    {
        return new Options();
    }

    public function setConsumerTag(string $consumerTag): Options
    {
        $this->consumerTag = $consumerTag;
        return $this;
    }

    public function setNoLocal(bool $noLocal): Options
    {
        $this->noLocal = $noLocal;
        return $this;
    }

    public function setNoAck(bool $noAck): Options
    {
        $this->noAck = $noAck;
        return $this;
    }

    public function setExclusive(bool $exclusive): Options
    {
        $this->exclusive = $exclusive;
        return $this;
    }

    public function setNoWait(bool $noWait): Options
    {
        $this->noWait = $noWait;
        return $this;
    }

    public function setTicket(int $ticket): Options
    {
        $this->ticket = $ticket;
        return $this;
    }

    public function setArguments(array $arguments): Options
    {
        $this->arguments = $arguments;
        return $this;
    }

}