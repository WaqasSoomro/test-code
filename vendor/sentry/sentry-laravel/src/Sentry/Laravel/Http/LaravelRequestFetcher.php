<?php

namespace Sentry\Laravel\Http;

use Illuminate\Container\Container;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\Integration\RequestFetcher;
use Sentry\Integration\RequestFetcherInterface;

class LaravelRequestFetcher implements RequestFetcherInterface
{
    /**
     * They key in the container where a PSR-7 instance of the current request could be stored.
     */
    public const CONTAINER_PSR7_INSTANCE_KEY = 'sentry-laravel.psr7.request';

    public function fetchRequest(): ?ServerRequestInterface
    {
        $container = Container::getInstance();

        if (!$container->bound('request')) {
            return null;
        }

        if ($container->bound(self::CONTAINER_PSR7_INSTANCE_KEY)) {
            return $container->make(self::CONTAINER_PSR7_INSTANCE_KEY);
        }

        return (new RequestFetcher)->fetchRequest();
    }
}
