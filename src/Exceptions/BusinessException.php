<?php

namespace Bandit\Pay\Exceptions;

class BusinessException extends GatewayException
{
    /**
     * Bootstrap.
     *
     * @author bandit <banditsmile@qq.com>
     *
     * @param string       $message
     * @param array|string $raw
     */
    public function __construct($message, $raw = [])
    {
        parent::__construct('ERROR_BUSINESS: '.$message, $raw, self::ERROR_BUSINESS);
    }
}
