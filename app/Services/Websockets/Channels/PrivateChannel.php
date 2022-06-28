<?php
namespace App\Services\Websockets\Channels;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\PrivateChannel as BasePrivateChannel;
use stdClass;
use Ratchet\ConnectionInterface;

class PrivateChannel extends BasePrivateChannel
{

    public function subscribe(ConnectionInterface $connection, stdClass $payload)
    {
        parent::subscribe($connection, $payload);
    }

    public function unsubscribe(ConnectionInterface $connection)
    {
        parent::unsubscribe($connection);
    }

}
