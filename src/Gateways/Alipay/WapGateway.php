<?php

namespace Bandit\Pay\Gateways\Alipay;

class WapGateway extends WebGateway
{
    /**
     * Get method config.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'alipay.trade.wap.pay';
    }

    /**
     * Get productCode config.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return string
     */
    protected function getProductCode(): string
    {
        return 'QUICK_WAP_WAY';
    }
}
