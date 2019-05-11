<?php

namespace Bandit\Pay\Gateways\Cmb;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Bandit\Pay\Gateways\Cmb;
use Bandit\Pay\Events;
use Yansongda\Supports\Collection;
use Symfony\Component\HttpFoundation\JsonResponse;

class MiniappGateway extends Gateway
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
     * @return RedirectResponse
     */
    public function pay($endpoint, array $payload): RedirectResponse
    {
        $endpoint = 'netpayment/BaseHttp.dll?MB_APPPay';

        Events::dispatch(
            Events::PAY_STARTED,
            new Events\PayStarted('Cmb', 'MiniApp', $endpoint, $payload)
        );


        $mweb_url = $this->preOrder($payload)->get('returnUrl');

        $url = is_null(Support::getInstance()->return_url) ? $mweb_url : $mweb_url.
            '&returnUrl='.urlencode(Support::getInstance()->return_url);

        return RedirectResponse::create($url);
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
        return 'Miniapp';
    }
}
