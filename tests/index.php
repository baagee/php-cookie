<?php
/**
 * Desc:
 * User: baagee
 * Date: 2019/4/14
 * Time: 20:10
 */
include __DIR__ . '/../vendor/autoload.php';

$config = [
    'prefix'     => 'test_', // cookie 名称前缀
    'expire'     => 10, // cookie 保存时间
    'path'       => '/', // cookie 保存路径
    'domain'     => '', // cookie 有效域名
    'secure'     => false, //  cookie 启用安全传输
    'httponly'   => true, // httponly 设置
    'setcookie'  => true, // 是否使用 setcookie
    'encryptkey' => '786tgytr7tgbjkKYURTdta3uuedie',//是否加密，有值cookie就加密
];
// 初始化
\BaAGee\Cookie\Cookie::init($config);
// 获取
var_dump(\BaAGee\Cookie\Cookie::get('name'));
//设置
\BaAGee\Cookie\Cookie::prefix('test2_');
\BaAGee\Cookie\Cookie::set('name', '很健康');
// 获取
var_dump(\BaAGee\Cookie\Cookie::get('name'));
// 清空
// var_dump($_COOKIE);
\BaAGee\Cookie\Cookie::clear('test2_');
// var_dump($_COOKIE);

// 获取所有
var_dump(\BaAGee\Cookie\Cookie::get());

// // 设置 第三个参数为int时，是有效期 20秒
\BaAGee\Cookie\Cookie::set('age', mt_rand(1, 99), 20);
var_dump(\BaAGee\Cookie\Cookie::has('age'));
\BaAGee\Cookie\Cookie::delete('age');
var_dump(\BaAGee\Cookie\Cookie::get('age'));
\BaAGee\Cookie\Cookie::forever('for', 'fff');
var_dump(\BaAGee\Cookie\Cookie::get('for'));

// 特殊对待，使用query_string方式设置特有的前缀或者其他配置
\BaAGee\Cookie\Cookie::set('name', '很健康', 'prefix=user_&expire=60');
// 特殊对待，传入前缀获取
var_dump(\BaAGee\Cookie\Cookie::get('name', 'user_'));
// 只会获取init时设置的公共的前缀值，没有user_
var_dump(\BaAGee\Cookie\Cookie::get());

// 设置
\BaAGee\Cookie\Cookie::set('arr', json_encode([
    'time' => time(),
    'name' => "啊哈哈"
]));
// 获取，默认返回已经decode之后的数组
var_dump(\BaAGee\Cookie\Cookie::get('arr'));
echo 'over';