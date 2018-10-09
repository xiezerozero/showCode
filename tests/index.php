<?php


use Tourscool\Repository\Coupon;

date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL|E_STRICT|E_WARNING);

require '../vendor/autoload.php';

$couponRep = new Coupon();
var_dump($couponRep->makePlatformCoupon(1, 10));exit;

$arr = [
    [
        'region' => '区域名称',
        'subs' => [
            'name' => '二级城市或者景点名称',
            'id' => '二级城市或者景点ID',
            'type' => '类型(城市1景点2)',
            'subs' => [
                'name' => '三级城市或者景点名称',
                'id' => '三级城市或者景点ID',
                'type' => '类型(城市1景点2)',
            ],
        ],
    ],
    [
        'region' => '区域名称',
        'subs' => [
            'name' => '二级城市或者景点名称',
            'id' => '二级城市或者景点ID',
            'type' => '类型(城市1景点2)',
            'subs' => [
                'name' => '三级城市或者景点名称',
                'id' => '三级城市或者景点ID',
                'type' => '类型(城市1景点2)',
            ],
        ],
    ],
];

echo json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

