<?php

namespace Bandit\Pay\Events;

class PayStarted extends Event
{
    /**
     * Endpoint.
     *
     * @var string
     */
    public $endpoint;

    /**
     * Payload.
     *
     * @var array
     */
    public $payload;

    /**
     * Bootstrap.
     *
     * @param string $driver
     * @param string $gateway
     * @param string $endpoint
     * @param array  $payload
     */
    public function __construct(string $driver, string $gateway, string $endpoint, array $payload)
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;

        parent::__construct($driver, $gateway);
    }
}
