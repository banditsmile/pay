<?php

namespace Bandit\Pay\Gateways\Jdpay;

use Exception;
use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\BusinessException;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Bandit\Pay\Gateways\Jdpay;
use Bandit\Pay\Log;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\HasHttpRequest;

/**
 * @author bandit <banditsmile@qq.com>
 *
 * @property string appid
 * @property string app_id
 * @property string miniapp_id
 * @property string sub_appid
 * @property string sub_app_id
 * @property string sub_miniapp_id
 * @property string mch_id
 * @property string sub_mch_id
 * @property string key
 * @property string return_url
 * @property string cert_client
 * @property string cert_key
 * @property array log
 * @property array http
 * @property string mode
 */
class Support
{
    use HasHttpRequest;

    /**
     * Jdpay gateway.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Instance.
     *
     * @var Support
     */
    private static $instance;

    /**
     * Bootstrap.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param Config $config
     */
    private function __construct(Config $config)
    {
        $this->baseUri = Jdpay::URL[$config->get('mode', Jdpay::MODE_NORMAL)];
        $this->config = $config;

        $this->setHttpOptions();
    }

    /**
     * __get.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param $key
     *
     * @return mixed|null|Config
     */
    public function __get($key)
    {
        return $this->getConfig($key);
    }

    /**
     * create.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param Config $config
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     *
     * @return Support
     */
    public static function create(Config $config)
    {
        if (php_sapi_name() === 'cli' || !(self::$instance instanceof self)) {
            self::$instance = new self($config);

            self::setDevKey();
        }

        return self::$instance;
    }

    /**
     * getInstance.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @throws InvalidArgumentException
     *
     * @return Support
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            $msg = 'You Should [Create] First Before Using';
            throw new InvalidArgumentException($msg);
        }

        return self::$instance;
    }

    /**
     * clear.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return void
     */
    public static function clear()
    {
        self::$instance = null;
    }

    /**
     * Request Jdpay api.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $endpoint
     * @param array  $data
     * @param bool   $cert
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     *
     * @return Collection
     */
    public static function requestApi($endpoint, $data, $cert = false): Collection
    {
        $url = self::$instance->getBaseUri().$endpoint;
        Events::dispatch(
            Events::API_REQUESTING,
            new Events\ApiRequesting('Jdpay', '', $url, $data)
        );

        $req = self::toXml($data);
        var_dump($req);
        $options = ['Content-Type'=>'application/xml;charset=utf-8'];
        if ($cert) {
            $options = array_merge(
                $options, [
                'cert'    => self::$instance->cert_client,
                'ssl_key' => self::$instance->cert_key,]
            );
        }
       /* $result = self::$instance->post(
            $endpoint,
            $req,
            $options
        );
        echo __LINE__,PHP_EOL;
        echo ($result),PHP_EOL;*/

        $result = HttpUtils::http_post_data($url, $req);
        var_dump($result);

        return $result;

        $result = is_array($result) ? $result : self::fromXml($result);

        Events::dispatch(
            Events::API_REQUESTED,
            new Events\ApiRequested('Jdpay', '', $url, $result)
        );

        return self::processingApiResult($endpoint, $result);
    }

    /**
     * Filter payload.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array        $payload
     * @param array|string $params
     * @param bool         $preserve_notify_url
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public static function filterPayload($payload, $params=[], $preserve_notify_url = false): array
    {
        $payload = array_merge(
            $payload,
            is_array($params) ? $params : ['tradeNum' => $params]
        );

        $payload['sign'] = self::generateSign($payload);

        return $payload;
    }

    /**
     * Generate Jdpay sign.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function generateSign($data): string
    {
        $string = SignUtil::sign($data, ['sign']);

        Log::debug('Jdpay Generate Sign Before UPPER', [$data, $string]);

        return $string;
    }

    /**
     * Decrypt refund contents.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $contents
     *
     * @return string
     */
    public static function decryptRefundContents($contents): string
    {
        return openssl_decrypt(
            base64_decode($contents),
            'AES-256-ECB',
            md5(self::$instance->key),
            OPENSSL_RAW_DATA
        );
    }

    /**
     * Convert array to xml.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function toXml($data): string
    {
        if (!is_array($data) || count($data) <= 0) {
            $msg = 'Convert To Xml Error! Invalid Array!';
            throw new InvalidArgumentException($msg);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?><jdpay>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<'.$key.'>'.$val.'</'.$key.'>' :
                                       '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }
        $xml .= '</jdpay>';

        return $xml;
    }

    /**
     * Convert xml to array.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $xml
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public static function fromXml($xml): array
    {
        if (!$xml) {
            $msg ='Convert To Array Error! Invalid Xml!';
            throw new InvalidArgumentException($msg);
        }

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * Get service config.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param null|string $key
     * @param null|mixed  $default
     *
     * @return mixed|null
     */
    public function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config->all();
        }

        if ($this->config->has($key)) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Get app id according to param type.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string $type
     *
     * @return string
     */
    public static function getTypeName($type = ''): string
    {
        switch ($type) {
            case '':
                $type = 'app_id';
                break;
            case 'app':
                $type = 'appid';
                break;
            default:
                $type = $type.'_id';
        }

        return $type;
    }

    /**
     * Get Base Uri.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * 根据具体业务调整请求域名
     *
     * @param $uri
     *
     * @return $this
     */
    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
        return $this;
    }

    /**
     * processingApiResult.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param       $endpoint
     * @param array $result
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     *
     * @return Collection
     */
    protected static function processingApiResult($endpoint, array $result)
    {
        if (!isset($result['result']['code']) || $result['result']['code'] != '000000') {
            throw new GatewayException(
                'Get Jdpay API Error:'.($result['result']['desc'] ??  ''),
                $result
            );
        }

        if (isset($result['result_code']) && $result['result_code'] != 'SUCCESS') {
            throw new BusinessException(
                'Jdpay Business Error: '.$result['err_code'].' - '.$result['err_code_des'],
                $result
            );
        }

        if ($endpoint === 'pay/getsignkey' ||
            strpos($endpoint, 'mmpaymkttransfers') !== false ||
            self::generateSign($result) === $result['sign']) {
            return new Collection($result);
        }

        Events::dispatch(Events::SIGN_FAILED, new Events\SignFailed('Jdpay', '', $result));

        throw new InvalidSignException('Jdpay Sign Verify FAILED', $result);
    }

    /**
     * setDevKey.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     * @throws Exception
     *
     * @return Support
     */
    private static function setDevKey()
    {
        if (self::$instance->mode == Jdpay::ENV_TEST) {
            $data = [
                'mch_id'    => self::$instance->mch_id,
                'nonce_str' => Str::random(),
            ];
            $data['sign'] = self::generateSign($data);

            $result = self::requestApi('pay/getsignkey', $data);

            self::$instance->config->set('key', $result['sandbox_signkey']);
        }

        return self::$instance;
    }

    /**
     * Set Http options.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @return self
     */
    private function setHttpOptions(): self
    {
        if ($this->config->has('http') && is_array($this->config->get('http'))) {
            $this->config->forget('http.base_uri');
            $this->httpOptions = $this->config->get('http');
        }

        return $this;
    }
}
