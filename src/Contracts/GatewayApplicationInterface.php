<?php

namespace Bandit\Pay\Contracts;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Supports\Collection;

interface GatewayApplicationInterface
{
    /**
     * To pay.
     *
     * @author Bandit <banditsmile@qq.com>
     *
     * @param string $gateway
     * @param array  $params
     *
     * @return Collection|Response
     */
    public function pay($gateway, $params);

    /**
     * Query an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string|array $order
     * @param bool         $refund
     *
     * @return Collection
     */
    public function find($order, $refund);

    /**
     * Refund an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $order
     *
     * @return Collection
     */
    public function refund($order);

    /**
     * Cancel an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function cancel($order);

    /**
     * Close an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function close($order);

    /**
     * Verify a request.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string|null $content
     * @param bool        $refund
     *
     * @return Collection
     */
    public function verify($content, $refund);

    /**
     * Echo success to server.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return Response
     */
    public function success();
}
