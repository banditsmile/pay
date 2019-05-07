<?php

namespace Bandit\Pay\Gateways\Cmb;

use Exception;
use Bandit\Pay\Events;
use Bandit\Pay\Exceptions\BusinessException;
use Bandit\Pay\Exceptions\GatewayException;
use Bandit\Pay\Exceptions\InvalidArgumentException;
use Bandit\Pay\Exceptions\InvalidSignException;
use Bandit\Pay\Gateways\Cmb;
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
     * Cmb gateway.
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
        $this->baseUri = Cmb::URL[$config->get('mode', Cmb::MODE_NORMAL)];
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
            throw new InvalidArgumentException('You Should [Create] First Before Using');
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
     * Request Cmb api.
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
        Events::dispatch(Events::API_REQUESTING, new Events\ApiRequesting('Cmb', '', self::$instance->getBaseUri().$endpoint, $data));

        $result = self::$instance->post(
            $endpoint,
            self::toXml($data),
            $cert ? [
                'cert'    => self::$instance->cert_client,
                'ssl_key' => self::$instance->cert_key,
            ] : []
        );
        $result = is_array($result) ? $result : self::fromXml($result);

        Events::dispatch(Events::API_REQUESTED, new Events\ApiRequested('Cmb', '', self::$instance->getBaseUri().$endpoint, $result));

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
    public static function filterPayload($payload, $params, $preserve_notify_url = false): array
    {
        $type = self::getTypeName($params['type'] ?? '');

        $payload = array_merge(
            $payload,
            is_array($params) ? $params : ['out_trade_no' => $params]
        );
        $payload['appid'] = self::$instance->getConfig($type, '');

        if (self::$instance->getConfig('mode', Cmb::MODE_NORMAL) === Cmb::MODE_SERVICE) {
            $payload['sub_appid'] = self::$instance->getConfig('sub_'.$type, '');
        }

        unset($payload['trade_type'], $payload['type']);
        if (!$preserve_notify_url) {
            unset($payload['notify_url']);
        }

        $payload['sign'] = self::generateSign($payload);

        return $payload;
    }

    /**
     * Generate Cmb sign.
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
        $key = self::$instance->key;

        if (is_null($key)) {
            throw new InvalidArgumentException('Missing Cmb Config -- [key]');
        }

        $string = hash(
            'sha256',
            self::getSignContent($data).'&key='.$key
        );

        Log::debug('Cmb Generate Sign Before UPPER', [$data, $string]);

        return bin2hex($string);
    }

    /**
     * Generate sign content.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $data
     *
     * @return string
     */
    public static function getSignContent($data): string
    {
        $buff = '';

        $keys = array_keys($data);
        $keysLower = array_map('strtolower', $keys);
        $keysMap = array_combine($keys, $keysLower);
        asort($keysMap);
        foreach ($keysMap as $key=>$val) {
            if ($val=='sign') {
                continue;
            }
            $value = $data[$key];
            $buff .= $key.'='.$value.'&';
        }
        Log::debug('Cmb Generate Sign Content Before Trim', [$data, $buff]);
        return trim($buff, '&');
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
            throw new InvalidArgumentException('Convert To Xml Error! Invalid Array!');
        }

        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<'.$key.'>'.$val.'</'.$key.'>' :
                                       '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }
        $xml .= '</xml>';

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
            throw new InvalidArgumentException('Convert To Array Error! Invalid Xml!');
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
     * @param $uri
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
        if (!isset($result['return_code']) || $result['return_code'] != 'SUCCESS') {
            throw new GatewayException(
                'Get Cmb API Error:'.($result['return_msg'] ?? $result['retmsg'] ?? ''),
                $result
            );
        }

        if (isset($result['result_code']) && $result['result_code'] != 'SUCCESS') {
            throw new BusinessException(
                'Cmb Business Error: '.$result['err_code'].' - '.$result['err_code_des'],
                $result
            );
        }

        if ($endpoint === 'pay/getsignkey' ||
            strpos($endpoint, 'mmpaymkttransfers') !== false ||
            self::generateSign($result) === $result['sign']) {
            return new Collection($result);
        }

        Events::dispatch(Events::SIGN_FAILED, new Events\SignFailed('Cmb', '', $result));

        throw new InvalidSignException('Cmb Sign Verify FAILED', $result);
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
        if (self::$instance->mode == Cmb::MODE_DEV) {
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
