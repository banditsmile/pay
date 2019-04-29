<?php

namespace Bandit\Pay\Exceptions;

class InvalidSignException extends Exception
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
        parent::__construct('INVALID_SIGN: '.$message, $raw, self::INVALID_SIGN);
    }
}
