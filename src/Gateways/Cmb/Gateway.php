<?php

namespace Bandit\Pay\Gateways\Cmb;

use Bandit\Pay\Contracts\GatewayInterface;
use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Yansongda\Supports\Collection;

abstract class Gateway implements GatewayInterface
{
    /**
     * Mode.
     *
     * @var string
     */
    protected $mode;

    /**
     * Bootstrap.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->mode = Support::getInstance()->mode;
    }

    /**
     * Pay an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Collection
     */
    abstract public function pay($endpoint, array $payload);

    /**
     * Get trade type config.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return string
     */
    abstract protected function getTradeType();

    /**
     * Schedule an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $payload
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     *
     * @return Collection
     */
    protected function preOrder($payload): Collection
    {
        $payload['sign'] = Support::generateSign($payload['reqData']);

        Events::dispatch(Events::METHOD_CALLED, new Events\MethodCalled('Cmb', 'PreOrder', '', $payload));

        return Support::requestApi('netpayment/BaseHttp.dll?MB_EUserPay', $payload);
    }
}
