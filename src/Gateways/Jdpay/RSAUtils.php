<?php

namespace Bandit\Pay\Gateways\Jdpay;

/**
 * Class RSAUtils
 *
 * @package Bandit\Pay\Gateways\Jdpay
 */
class RSAUtils {

    /**
     * @param $data
     * @return string
     */
    public static function encryptByPrivateKey($data)
    {
        //这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id


        $privateKey = Support::getInstance()->getConfig('privateKey');
        $pi_key = openssl_pkey_get_private(file_get_contents($privateKey));
        $encrypted = "";
        //私钥加密
        openssl_private_encrypt($data, $encrypted, $pi_key, OPENSSL_PKCS1_PADDING);
        //加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

    /**
     * @param $data
     * @return string
     */
    public static function decryptByPublicKey($data)
    {
        //这个函数可用来判断公钥是否是可用的，可用返回资源id Resource id
        $publicKey = Support::getInstance()->getConfig('publicKey');
        $pu_key = openssl_pkey_get_public(file_get_contents($publicKey));
        $decrypted = "";
        $data = base64_decode($data);
        //公钥解密
        openssl_public_decrypt($data, $decrypted, $pu_key);
        return $decrypted;
    }
}

?>