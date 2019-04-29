<?php

namespace Bandit\Pay\Gateways;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Bandit\Pay\Contracts\GatewayApplicationInterface;
use Bandit\Pay\Contracts\GatewayInterface;
use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidGatewayException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Bandit\Pay\Gateways\Jdpay\Support;
use Bandit\Pay\Log;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

/**
 * @method Response app(array $config) APP 支付
 * @method Collection groupRedpack(array $config) 分裂红包
 * @method Collection miniapp(array $config) 小程序支付
 * @method Collection mp(array $config) 公众号支付
 * @method Collection pos(array $config) 刷卡支付
 * @method Collection redpack(array $config) 普通红包
 * @method Collection scan(array $config) 扫码支付
 * @method Collection transfer(array $config) 企业付款
 * @method RedirectResponse wap(array $config) H5 支付
 */
class Jdpay implements GatewayApplicationInterface
{
    /**
     * 普通模式.
     */
    const MODE_NORMAL = 'normal';

    /**
     * 沙箱模式.
     */
    const MODE_DEV = 'dev';

    /**
     * Const url.
     */
    const URL = [
        self::MODE_NORMAL  => 'https://paygate.jd.com/',
        self::MODE_DEV     => 'https://paygate.jd.com/sandboxnew/',
    ];

    /**
     * Jdpay payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Jdpay gateway.
     *
     * @var string
     */
    protected $gateway;

    /**
     * Bootstrap.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param Config $config
     *
     * @throws Exception
     */
    public function __construct(Config $config)
    {
        $this->gateway = Support::create($config)->getBaseUri();
        $this->payload = [
            'appid'            => $config->get('app_id', ''),
            'mch_id'           => $config->get('mch_id', ''),
            'nonce_str'        => Str::random(),
            'notify_url'       => $config->get('notify_url', ''),
            'sign'             => '',
            'trade_type'       => '',
            'spbill_create_ip' => Request::createFromGlobals()->getClientIp(),
        ];

        if ($config->get('mode', self::MODE_NORMAL) === static::MODE_SERVICE) {
            $this->payload = array_merge($this->payload, [
                'sub_mch_id' => $config->get('sub_mch_id'),
                'sub_appid'  => $config->get('sub_app_id', ''),
            ]);
        }
    }

    /**
     * Magic pay.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $method
     * @param string $params
     *
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    public function __call($method, $params)
    {
        return self::pay($method, ...$params);
    }

    /**
     * Pay an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $gateway
     * @param array  $params
     *
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    public function pay($gateway, $params = [])
    {
        Events::dispatch(Events::PAY_STARTING, new Events\PayStarting('Jdpay', $gateway, $params));

        $this->payload = array_merge($this->payload, $params);

        $gateway = get_class($this).'\\'.Str::studly($gateway).'Gateway';

        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Not Exists");
    }

    /**
     * Verify data.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string|null $content
     * @param bool        $refund
     *
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function verify($content = null, $refund = false): Collection
    {
        $content = $content ?? Request::createFromGlobals()->getContent();

        Events::dispatch(Events::REQUEST_RECEIVED, new Events\RequestReceived('Jdpay', '', [$content]));

        $data = Support::fromXml($content);
        if ($refund) {
            $decrypt_data = Support::decryptRefundContents($data['req_info']);
            $data = array_merge(Support::fromXml($decrypt_data), $data);
        }

        Log::debug('Resolved The Received Jdpay Request Data', $data);

        if ($refund || Support::generateSign($data) === $data['sign']) {
            return new Collection($data);
        }

        Events::dispatch(Events::SIGN_FAILED, new Events\SignFailed('Jdpay', '', $data));

        throw new InvalidSignException('Jdpay Sign Verify FAILED', $data);
    }

    /**
     * Query an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string|array $order
     * @param bool         $refund
     *
     * @throws GatewayException
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function find($order, $refund = false): Collection
    {
        if ($refund) {
            unset($this->payload['spbill_create_ip']);
        }

        $this->payload = Support::filterPayload($this->payload, $order);

        Events::dispatch(Events::METHOD_CALLED, new Events\MethodCalled('Jdpay', 'Find', $this->gateway, $this->payload));

        return Support::requestApi(
            $refund ? 'pay/refundquery' : 'pay/orderquery',
            $this->payload
        );
    }

    /**
     * Refund an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $order
     *
     * @throws GatewayException
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function refund($order): Collection
    {
        $this->payload = Support::filterPayload($this->payload, $order, true);

        Events::dispatch(Events::METHOD_CALLED, new Events\MethodCalled('Jdpay', 'Refund', $this->gateway, $this->payload));

        return Support::requestApi(
            'secapi/pay/refund',
            $this->payload,
            true
        );
    }

    /**
     * Cancel an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $order
     *
     * @throws GatewayException
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function cancel($order): Collection
    {
        unset($this->payload['spbill_create_ip']);

        $this->payload = Support::filterPayload($this->payload, $order, true);

        Events::dispatch(Events::METHOD_CALLED, new Events\MethodCalled('Jdpay', 'Cancel', $this->gateway, $this->payload));

        return Support::requestApi(
            'secapi/pay/reverse',
            $this->payload,
            true
        );
    }

    /**
     * Close an order.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string|array $order
     *
     * @throws GatewayException
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function close($order): Collection
    {
        unset($this->payload['spbill_create_ip']);

        $this->payload = Support::filterPayload($this->payload, $order);

        Events::dispatch(Events::METHOD_CALLED, new Events\MethodCalled('Jdpay', 'Close', $this->gateway, $this->payload));

        return Support::requestApi('pay/closeorder', $this->payload);
    }

    /**
     * Echo success to server.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @throws InvalidArgumentException
     *
     * @return Response
     */
    public function success(): Response
    {
        Events::dispatch(Events::METHOD_CALLED, new Events\MethodCalled('Jdpay', 'Success', $this->gateway));

        return Response::create(
            Support::toXml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']),
            200,
            ['Content-Type' => 'application/xml']
        );
    }

    /**
     * Download the bill.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $params
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function download(array $params): string
    {
        unset($this->payload['spbill_create_ip']);

        $this->payload = Support::filterPayload($this->payload, $params, true);

        Events::dispatch(Events::METHOD_CALLED, new Events\MethodCalled('Jdpay', 'Download', $this->gateway, $this->payload));

        $result = Support::getInstance()->post(
            'pay/downloadbill',
            Support::getInstance()->toXml($this->payload)
        );

        if (is_array($result)) {
            throw new GatewayException('Get Jdpay API Error: '.$result['return_msg'], $result);
        }

        return $result;
    }

    /**
     * Make pay gateway.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $gateway
     *
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    protected function makePay($gateway)
    {
        $app = new $gateway();

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->gateway, array_filter($this->payload, function ($value) {
                return $value !== '' && !is_null($value);
            }));
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of GatewayInterface");
    }
}
