<?php
include 'vendor/autoload.php';
use Bandit\Pay\Pay;
use Bandit\Pay\Log;

class controller{

    private $conf;

    public function __construct()
    {
        $conf = include './config.php';
        $this->conf = $conf['cmb'];
    }

    private function output($data)
    {
        var_dump($data);
    }
    public function index($argv)
    {
        $action = $argv[1];
        $param = array_slice($argv, 2);
        call_user_func_array([$this, $action], $param);
    }

    public function order($param)
    {
        $order = [
            'orderNo' => time(),
            'amount'  => 1,
            'payNoticeUrl'  => 'http://45.76.223.240/',
            'signNoticeUrl' => 'http://45.76.223.240/',
            'returnUrl'     => 'http://45.76.223.240/',//返回商户地址，支付成功页面、支付失败页面上“返回商户”按钮跳转地址
            'cardType'      => '',//支付卡类型默认所有，'A'只能使用储蓄卡
        ];

        $result = Pay::cmb($this->conf)->wap($order);
        $this->output($result);
    }

    public function find($param)
    {
        $order = [
            'dateTime' => date("YmdHis"),
            'type'     =>'B',
            'date'     => date("Ymd"),
            'orderNo'  => time(),
        ];
        $result =  Pay::cmb($this->conf)->find($order);
        $this->output($result);
    }
}
$c = new controller();
$c->index($argv);