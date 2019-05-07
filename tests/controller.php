<?php
include 'vendor/autoload.php';
use Bandit\Pay\Pay;
use Bandit\Pay\Log;

class controller{
    public function index($argv)
    {
        $action = $argv[1];
        $param = array_slice($argv, 2);
        call_user_func_array([$this, $action], $param);
    }

    public function cmdOrder($a)
    {
        $conf = [

        ];

        $param = [];
        $order = [
            'out_trade_no' => time(),
            'total_fee' => '1', // **单位：分**
            'body' => 'test body - 测试',
            'openid' => 'onkVf1FjWS5SBIixxxxxxx',
        ];

        $pay = Pay::wechat($conf)->wap($order);
    }
}
$c = new controller();
$c->index($argv);