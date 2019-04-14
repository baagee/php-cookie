<?php
/**
 * Desc: CookieAbstract
 * User: baagee
 * Date: 2019/4/14
 * Time: 20:01
 */

namespace BaAGee\Cookie\Base;

/**
 * Class CookieAbstract
 * @package BaAGee\Cookie\Base
 */
abstract class CookieAbstract
{
    /**
     * @var bool 是否完成初始化了
     */
    protected static $init;

    /**
     * @var array cookie 设置参数
     */
    protected static $config = [
        'prefix'     => '', // cookie 名称前缀
        'expire'     => 0, // cookie 保存时间
        'path'       => '/', // cookie 保存路径
        'domain'     => '', // cookie 有效域名
        'secure'     => false, //  cookie 启用安全传输
        'httponly'   => false, // httponly 设置
        'setcookie'  => true, // 是否使用 setcookie
        'encryptkey' => '',//cookie加密密钥
    ];

    /**
     * Cookie初始化
     * @access public
     * @param  array $config 配置参数
     * @return void
     */
    public static function init(array $config = [])
    {
        self::$config = array_merge(self::$config, array_change_key_case($config));
        if (!empty(self::$config['httponly'])) {
            ini_set('session.cookie_httponly', 1);
        }
        self::$init = true;
    }

    /**
     * 设置或者获取 cookie 作用域（前缀）
     * @access public
     * @param  string $prefix 前缀
     * @return string|
     */
    public static function prefix($prefix = '')
    {
        if (empty($prefix)) {
            return self::$config['prefix'];
        }
        return self::$config['prefix'] = $prefix;
    }

    /**
     * 字符串加解密
     * @param string $string    要加解密的字符串
     * @param string $operation 'DECODE'解密 'ENCODE'加密
     * @param string $key       密钥
     * @return bool|string
     */
    protected function secure($string, $key, $operation = 'DECODE')
    {
        $ckey_length   = 4; // 随机密钥长度 取值 0-32;
        $key           = md5($key);
        $keya          = md5(substr($key, 0, 16));
        $keyb          = md5(substr($key, 16, 16));
        $keyc          = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey      = $keya . md5($keya . $keyc);
        $key_length    = strlen($cryptkey);
        $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result        = '';
        $box           = range(0, 255);
        $rndkey        = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp     = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a       = ($a + 1) % 256;
            $j       = ($j + $box[$a]) % 256;
            $tmp     = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result  .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}
