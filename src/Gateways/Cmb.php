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
use Bandit\Pay\Gateways\Cmb\Support;
use Bandit\Pay\Log;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

/**
 * @method Response app(array $config) APP 支付
 * @method Collection miniapp(array $config) 小程序支付
 * @method Collection mp(array $config) 公众号支付
 * @method Collection pos(array $config) 刷卡支付
 * @method Collection scan(array $config) 扫码支付
 * @method Collection transfer(array $config) 企业付款
 * @method RedirectResponse wap(array $config) H5 支付
 */
class Cmb implements GatewayApplicationInterface
{

    /**
     * 沙箱模式.
     */
    const ENV_DEV = 'dev';

    /**
     * 生成模式.
     */
    const ENV_PRO = 'pro';

    /**
     * 支付请求
     */
    const MODE_NET_PAY = 'net_pay';
    /**
     * 普通模式.
     */
    const MODE_NORMAL = 'normal';
    /**
     * 签约
     */
    const MODE_MOBILE = 'mobile';
    /**
     * 用户协议
     */
    const MODE_B2B = 'b2b';

    /**
     * Const url.
     */
    const URL = [
        self::ENV_PRO=>[
            self::MODE_NET_PAY     => 'https://netpay.cmbchina.com/',
            self::MODE_MOBILE      => 'https://mobile.cmbchina.com/',
            self::MODE_NORMAL      => 'https://payment.ebank.cmbchina.com/',
            self::MODE_B2B         => 'http://121.15.180.66:801/',
        ],
        self::ENV_DEV=>[
            self::MODE_NET_PAY     => 'https://netpay.cmbchina.com/',
            self::MODE_MOBILE      => 'https://netpay.cmbchina.com/',
            self::MODE_NORMAL      => 'https://payment.ebank.cmbchina.com/',
            self::MODE_B2B         => 'http://121.15.180.72/',
        ]
    ];

    /**
     * Cmb payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Cmb gateway.
     *
     * @var string
     */
    protected $gateway;

    private $env = self::ENV_PRO;
    private $model = self::MODE_NORMAL;

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
            'version'   => $config->get('version', ''),
            'charset'   => $config->get('charset', ''),
            'signType'  => $config->get('signType', ''),
            'reqData'   => []
        ];
        $this->env = $config->get('env');
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
        Events::dispatch(
            Events::PAY_STARTING,
            new Events\PayStarting('Cmb', $gateway, $params)
        );

        $this->payload['reqData'] = array_merge($this->payload['reqData'], $params);

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

        Events::dispatch(
            Events::REQUEST_RECEIVED,
            new Events\RequestReceived('Cmb', '', [$content])
        );

        $data = Support::fromXml($content);
        if ($refund) {
            $decrypt_data = Support::decryptRefundContents($data['req_info']);
            $data = array_merge(Support::fromXml($decrypt_data), $data);
        }

        Log::debug('Resolved The Received Cmb Request Data', $data);

        if ($refund || Support::generateSign($data) === $data['sign']) {
            return new Collection($data);
        }

        Events::dispatch(
            Events::SIGN_FAILED,
            new Events\SignFailed('Cmb', '', $data)
        );

        throw new InvalidSignException('Cmb Sign Verify FAILED', $data);
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

        $this->payload['reqData']
            = Support::filterPayload($this->payload['reqData'], $order);

        Events::dispatch(
            Events::METHOD_CALLED,
            new Events\MethodCalled('Cmb', 'Find', $this->gateway, $this->payload)
        );

        return Support::requestApi(
            $refund ? 'Netpayment/BaseHttp.dll?QueryRefundByDate':'Netpayment/BaseHttp.dll?QuerySingleOrder' ,
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

        Events::dispatch(
            Events::METHOD_CALLED,
            new Events\MethodCalled('Cmb', 'Refund', $this->gateway, $this->payload)
        );

        return Support::requestApi(
            'NetPayment/BaseHttp.dll?DoRefund',
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
        $this->payload = Support::filterPayload($this->payload, $order, true);

        Events::dispatch(
            Events::METHOD_CALLED,
            new Events\MethodCalled('Cmb', 'Cancel', $this->gateway, $this->payload)
        );

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

        Events::dispatch(
            Events::METHOD_CALLED,
            new Events\MethodCalled('Cmb', 'Close', $this->gateway, $this->payload)
        );

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
        Events::dispatch(
            Events::METHOD_CALLED,
            new Events\MethodCalled('Cmb', 'Success', $this->gateway)
        );

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

        Events::dispatch(
            Events::METHOD_CALLED,
            new Events\MethodCalled('Cmb', 'Download', $this->gateway, $this->payload)
        );

        $result = Support::getInstance()->post(
            'pay/downloadbill',
            Support::getInstance()->toXml($this->payload)
        );

        if (is_array($result)) {
            throw new GatewayException('Get Cmb API Error: '.$result['return_msg'], $result);
        }

        return $result;
    }

    /**
     * 查询招行公钥
     *
     * @return array|string
     * @throws \Bandit\Pay\Exceptions\GatewayException
     */
    public function pubkey()
    {
        $support = Support::getInstance();
        $reqData = [
            'dateTime'  =>date("YmdHis"),
            'txCode'    =>'FBPK',
            'branchNo'  =>$support->getConfig('branchNo'),
            'merchantNo'=>$support->getConfig('merchantNo'),
        ];
        $this->payload['reqData'] = $reqData;

        $this->payload = Support::filterPayload($this->payload);

        Events::dispatch(
            Events::METHOD_CALLED,
            new Events\MethodCalled('Cmb', 'pubkey', $this->gateway, $this->payload)
        );
        $env = Support::getInstance()->env;
        $mode = self::MODE_B2B;
        Support::getInstance()->setBaseUri(Cmb::URL[$env][$mode]);

        $result = Support::getInstance()->post(
            'CmbBank_B2B/UI/NetPay/DoBusiness.ashx',
            $this->payload,
            $options = [
                'form_params' => ['jsonRequestData'=>json_encode($this->payload)],
                'verify'  => false,
                'headers' => [],
            ]
        );

        $result = json_decode($result, true);
        if (!is_array($result)) {
            throw new GatewayException('Get Cmb API Error: ', $result);
        }
        if (!isset($result['rspData']['rspCode'])
            || $result['rspData']['rspCode']!=='SUC0000'
        ) {
            throw new GatewayException('Get Cmb API Error: ', $result);
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
            return $app->pay(
                $this->gateway,
                array_filter(
                    $this->payload, function ($value) {
                        return !is_null($value);
                    }
                )
            );
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of GatewayInterface");
    }
}
