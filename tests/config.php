<?php

$config = [
    'cmb'=>[
        'version'       =>'1.0',
        'charset'       =>'UTF-8',
        'signType'      =>'SHA-256',
        'branchNo'      =>'0755',
        'merchantNo'    =>'432823',
        'mode'          =>'dev',
        'payNoticeUrl'  =>'http://www.merchant.com/path/payNotice.do',
        'signNoticeUrl' =>'http://www.merchant.com/path/payNotice.do',
        'log'           =>[
                            'file'=>'bandit.pay',
                            'level'=>'warning',
                            'type'=>'daily',
                            'max_file'=>'30',
                        ],
    ],
];
return $config;