<?php
    namespace App\Services\Websockets;
    use BeyondCode\LaravelWebSockets\WebSockets\Channels\PresenceChannel as BasePresenceChannel;
    use Illuminate\Support\Facades\Redis;
    use stdClass;
    use Ratchet\ConnectionInterface;
    class PresenceChannel extends BasePresenceChannel
    {
        public function unsubscribe(ConnectionInterface $connection)
        {
            if (isset($this->subscribedConnections[$connection->socketId])) {

            }

            parent::unsubscribe($connection);
        }

    }
