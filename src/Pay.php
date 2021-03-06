<?php

namespace Bandit\Pay;

use Exception;
use Bandit\Pay\Contracts\GatewayApplicationInterface;
use Bandit\Pay\Exceptions\InvalidGatewayException;
use Bandit\Pay\Gateways\Alipay;
use Bandit\Pay\Gateways\Wechat;
use Bandit\Pay\Gateways\Jdpay;
use Bandit\Pay\Gateways\Cmb;
use Bandit\Pay\Listeners\KernelLogSubscriber;
use Yansongda\Supports\Config;
use Yansongda\Supports\Log;
use Yansongda\Supports\Str;

/**
 * @method static Alipay alipay(array $config) 支付宝
 * @method static Wechat wechat(array $config) 微信
 * @method static Jdpay jdpay(array $config) 京东支付
 * @method static Cmb cmb(array $config) 招行一网通
 */
class Pay
{
    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Bootstrap.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $config
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);

        $this->registerLogService();
        $this->registerEventService();
    }

    /**
     * Magic static call.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $method
     * @param array  $params
     *
     * @throws InvalidGatewayException
     * @throws Exception
     *
     * @return GatewayApplicationInterface
     */
    public static function __callStatic($method, $params): GatewayApplicationInterface
    {
        $app = new self(...$params);

        return $app->create($method);
    }

    /**
     * Create a instance.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $method
     *
     * @throws InvalidGatewayException
     *
     * @return GatewayApplicationInterface
     */
    protected function create($method): GatewayApplicationInterface
    {
        $gateway = __NAMESPACE__.'\\Gateways\\'.Str::studly($method);

        if (class_exists($gateway)) {
            return self::make($gateway);
        }

        throw new InvalidGatewayException("Gateway [{$method}] Not Exists");
    }

    /**
     * Make a gateway.
     *
     * @author Bandit <banditsmile@qq.com>
     *
     * @param string $gateway
     *
     * @throws InvalidGatewayException
     *
     * @return GatewayApplicationInterface
     */
    protected function make($gateway): GatewayApplicationInterface
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayApplicationInterface) {
            return $app;
        }

        throw new InvalidGatewayException("Gateway [{$gateway}] Must Be An Instance Of GatewayApplicationInterface");
    }

    /**
     * Register log service.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @throws Exception
     */
    protected function registerLogService()
    {
        $logger = Log::createLogger(
            $this->config->get('log.file'),
            'bandit.pay',
            $this->config->get('log.level', 'warning'),
            $this->config->get('log.type', 'daily'),
            $this->config->get('log.max_file', 30)
        );

        Log::setLogger($logger);
    }

    /**
     * Register event service.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return void
     */
    protected function registerEventService()
    {
        Events::setDispatcher(Events::createDispatcher());

        Events::addSubscriber(new KernelLogSubscriber());
    }
}
