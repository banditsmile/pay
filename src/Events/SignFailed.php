<?php

namespace Bandit\Pay\Events;

class SignFailed extends Event
{
    /**
     * Received data.
     *
     * @var array
     */
    public $data;

    /**
     * Bootstrap.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $driver
     * @param string $gateway
     * @param array  $data
     */
    public function __construct(string $driver, string $gateway, array $data)
    {
        $this->data = $data;

        parent::__construct($driver, $gateway);
    }
}
