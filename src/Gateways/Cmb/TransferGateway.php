<?php

namespace Bandit\Pay\Gateways\Cmb;

use Symfony\Component\HttpFoundation\Request;
use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Bandit\Pay\Gateways\Cmb;
use Yansongda\Supports\Collection;

class TransferGateway extends Gateway
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
        if ($this->mode === Cmb::MODE_SERVICE) {
            unset($payload['sub_mch_id'], $payload['sub_appid']);
        }

        $type = Support::getTypeName($payload['type'] ?? '');

        $payload['mch_appid'] = Support::getInstance()->getConfig($type, '');
        $payload['mchid'] = $payload['mch_id'];

        if (php_sapi_name() !== 'cli' && !isset($payload['spbill_create_ip'])) {
            $payload['spbill_create_ip']
                = Request::createFromGlobals()->server->get('SERVER_ADDR');
        }

        unset(
            $payload['appid'], $payload['mch_id'], $payload['trade_type'],
            $payload['notify_url'], $payload['type']
        );

        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(
            Events::PAY_STARTED,
            new Events\PayStarted('Cmb', 'Transfer', $endpoint, $payload)
        );

        return Support::requestApi(
            'mmpaymkttransfers/promotion/transfers',
            $payload,
            true
        );
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
        return '';
    }
}
