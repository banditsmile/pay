<?php

namespace Bandit\Pay\Gateways\Alipay;

use Bandit\Pay\Contracts\GatewayInterface;
use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidConfigException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Yansongda\Supports\Collection;

class TransferGateway implements GatewayInterface
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
     * @throws InvalidConfigException
     * @throws InvalidSignException
     *
     * @return Collection
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['method'] = 'alipay.fund.trans.toaccount.transfer';
        $payload['biz_content'] = json_encode(array_merge(
            json_decode($payload['biz_content'], true),
            ['product_code' => '']
        ));
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(Events::PAY_STARTED, new Events\PayStarted('Alipay', 'Transfer', $endpoint, $payload));

        return Support::requestApi($payload);
    }

    /**
     * Find.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param $order
     *
     * @return array
     */
    public function find($order): array
    {
        return [
            'method'      => 'alipay.fund.trans.order.query',
            'biz_content' => json_encode(is_array($order) ? $order : ['out_biz_no' => $order]),
        ];
    }
}
