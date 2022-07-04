<?php
namespace App\Services\Websockets\Channels;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\Channel as BaseChannel;
use stdClass;
use Ratchet\ConnectionInterface;

class Channel extends BaseChannel
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
