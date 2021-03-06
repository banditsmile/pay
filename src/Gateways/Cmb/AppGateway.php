<?php

namespace Bandit\Pay\Gateways\Cmb;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Bandit\Pay\Gateways\Cmb;
use Yansongda\Supports\Str;

class AppGateway extends Gateway
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
     * @throws Exception
     *
     * @return Response
     */
    public function pay($endpoint, array $payload): Response
    {
        $endpoint = 'netpayment/BaseHttp.dll?MB_APPPay';

        Events::dispatch(
            Events::PAY_STARTED,
            new Events\PayStarted('Cmb', 'App', $endpoint, $payload)
        );

        return JsonResponse::create($payload);
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
        return 'APP';
    }
}
