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
 * @property string env
 * @property array config
 * @property string baseUri
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

    private $mode;
    private $env;

    /**
     * Bootstrap.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param Config $config
     */
    private function __construct(Config $config)
    {
        $this->config = $config;
        $this->env = $config->get('env');
        $this->mode = $config->get('mode', Cmb::MODE_NORMAL);
        $this->baseUri = Cmb::URL[$this->env][$this->mode];

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
        Events::dispatch(
            Events::API_REQUESTING,
            new Events\ApiRequesting('Cmb', '', self::$instance->getBaseUri().$endpoint, $data)
        );

        $result = self::$instance->post(
            $endpoint,
            $data,
            $cert ? [
                'cert'    => self::$instance->cert_client,
                'ssl_key' => self::$instance->cert_key,
            ] : []
        );
        $result = is_array($result) ? $result : json_decode($result, true);

        Events::dispatch(
            Events::API_REQUESTED,
            new Events\ApiRequested('Cmb', '', self::$instance->getBaseUri().$endpoint, $result)
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
        $payload['sign'] = self::generateSign($payload['reqData']);
        //$payload['sign'] = self::sign2($payload['reqData']);

        return $payload;
    }

    /**
     * Generate Cmb sign.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param array $reqData
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function generateSign($reqData): string
    {
        $key = self::$instance->key;

        if (is_null($key)) {
            throw new InvalidArgumentException('Missing Cmb Config -- [key]');
        }

        $strToSing = self::getSignContent($reqData);
        $string = hash(
            'sha256',
            $strToSing.'&'.$key
        );

        Log::debug('Cmb Generate Sign Before UPPER', [$reqData, $string]);

        return $string;
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
        $buff =  trim($buff, '&');
        $buff = mb_convert_encoding($buff, "UTF-8");

        Log::debug('Cmb Generate Sign Content', [$data, $buff]);
        return $buff;
    }

    /**
     * 招行没有退款通知
     * @deprecated
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
     * 招行返回的是json数据
     * @deprecated
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
     * 招行返回的是json数据
     * @deprecated
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

        if (!isset($result['rspData']['rspCode']) || $result['rspData']['rspCode'] != 'SUC0000') {
            throw new GatewayException(
                'Get Cmb API Error:'.($result['rspData']['rspMsg'] ?? $result['rspData']['rspMsg'] ?? ''),
                $result
            );
        }

        if (self::verify($result['rspData'], $result['sign'], self::getInstance()->getConfig('pubKey'))) {
            return new Collection($result);
        }

        Events::dispatch(Events::SIGN_FAILED, new Events\SignFailed('Cmb', '', $result));

        throw new InvalidSignException('Cmb Sign Verify FAILED', $result);
    }

    /**
     * @param $data    array  待验证签名字符串
     * @param $sig_dat string 签名结果(strSign) 
     * @param $pub_key string 公钥
     * @return bool
     */
    public static function verify($data, $sig_dat, $pub_key)
    {
        //待验证签名字符串
        $toSign_str = self::getSignContent($data);
        
        //处理证书
        $pem = chunk_split($pub_key, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $pkid = openssl_pkey_get_public($pem);
        if (empty($pkid)) {
            return false;
        }
        
        //验证
        $ok = openssl_verify($toSign_str, base64_decode($sig_dat), $pkid, OPENSSL_ALGO_SHA1);
        
        return $ok===1;
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

    public function post(string $endPoint,array $data)
    {
        $options = [
            'form_params' => ['jsonRequestData'=>json_encode($data)],
            'verify'  => false,
            'headers' => [],
        ];
        return $this->request('post', $endPoint, $options);
    }
}
