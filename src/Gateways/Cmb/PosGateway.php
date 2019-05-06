<?php

namespace Bandit\Pay\Gateways\Cmb;

use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Yansongda\Supports\Collection;

class PosGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     *
     * @return Collection
     */
    public function pay($endpoint, array $payload): Collection
    {
        unset($payload['trade_type'], $payload['notify_url']);

        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(Events::PAY_STARTED, new Events\PayStarted('Cmb', 'Pos', $endpoint, $payload));

        return Support::requestApi('pay/micropay', $payload);
    }

    /**
     * Get trade type config.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return string
     */
    protected function getTradeType(): string
    {
        return 'MICROPAY';
    }
}
