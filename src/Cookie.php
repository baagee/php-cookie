<?php
/**
 * Desc: Cookie操作类
 * User: baagee
 * Date: 2019/4/14
 * Time: 20:00
 */

namespace BaAGee\Cookie;

use BaAGee\Cookie\Base\CookieAbstract;

/**
 * Class Cookie
 * @package BaAGee\Cookie
 */
final class Cookie extends CookieAbstract
{
    /**
     * Cookie 设置、获取、删除
     * @param  string $name   cookie 名称
     * @param  mixed  $value  cookie 值
     * @param  mixed  $option 可选参数 可能会是 null|integer|string
     * @return void
     */
    public static function set($name, $value = '', $option = null)
    {
        !isset(self::$init) && self::init();
        // 参数设置(会覆盖黙认设置)
        if (!is_null($option)) {
            if (is_numeric($option)) {
                $option = ['expire' => $option];
            } elseif (is_string($option)) {
                parse_str($option, $option);
            }
            $config = array_merge(self::$config, array_change_key_case($option));
        } else {
            $config = self::$config;
        }
        $name = $config['prefix'] . $name;
        // 设置 cookie
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (!empty(self::$config['encryptkey'])) {
            $value = self::secure($value, self::$config['encryptkey'], 'ENCODE');
        }
        $expire = !empty($config['expire']) ? $_SERVER['REQUEST_TIME'] + intval($config['expire']) : 0;
        if ($config['setcookie']) {
            setcookie($name, $value, $expire, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
        }

        $_COOKIE[$name] = $value;
    }

    /**
     * 永久保存 Cookie 数据
     * @param  string $name   cookie 名称
     * @param  mixed  $value  cookie 值
     * @param  mixed  $option 可选参数 可能会是 null|integer|string
     * @return void
     */
    public static function forever($name, $value = '', $option = null)
    {
        if (is_null($option) || is_numeric($option)) {
            $option = [];
        }
        $option['expire'] = 315360000;
        self::set($name, $value, $option);
    }

    /**
     * 判断是否有 Cookie 数据
     * @param  string      $name   cookie 名称
     * @param  string|null $prefix cookie 前缀
     * @return bool
     */
    public static function has($name, $prefix = null)
    {
        !isset(self::$init) && self::init();
        $prefix = !is_null($prefix) ? $prefix : self::$config['prefix'];
        return isset($_COOKIE[$prefix . $name]);
    }

    /**
     * 获取 Cookie 的值
     * @param string      $name   cookie 名称
     * @param string|null $prefix cookie 前缀
     * @return mixed
     */
    public static function get($name = '', $prefix = null)
    {
        !isset(self::$init) && self::init();
        $prefix = !is_null($prefix) ? $prefix : self::$config['prefix'];
        $key    = $prefix . $name;
        if ('' == $name) {
            // 获取全部
            if ($prefix) {
                $value = [];
                foreach ($_COOKIE as $k => $val) {
                    if (0 === strpos($k, $prefix)) {
                        $value[$k] = $val;
                    }
                }
            } else {
                $value = $_COOKIE;
            }
        } elseif (isset($_COOKIE[$key])) {
            $value = $_COOKIE[$key];
        } else {
            $value = null;
        }
        if (is_array($value)) {
            foreach ($value as &$val) {
                if (!empty(self::$config['encryptkey'])) {
                    $val = self::secure($val, self::$config['encryptkey'], 'DECODE');
                }
            }
        } else {
            if (!empty(self::$config['encryptkey'])) {
                $value = self::secure($value, self::$config['encryptkey'], 'DECODE');
            }
            if ($res = json_decode(strval($value), true)) {
                $value = $res;
            }
        }
        return $value == '' ? null : $value;
    }

    /**
     * 删除 Cookie
     * @param  string      $name   cookie 名称
     * @param  string|null $prefix cookie 前缀
     * @return void
     */
    public static function delete($name, $prefix = null)
    {
        !isset(self::$init) && self::init();
        $prefix = !is_null($prefix) ? $prefix : self::$config['prefix'];
        $name   = $prefix . $name;
        self::realDelete($name);
    }

    /**
     * 清除指定前缀的所有 cookie
     * @param  string|null $prefix cookie 前缀
     * @return void
     */
    public static function clear($prefix = null)
    {
        if (empty($_COOKIE)) {
            return;
        }
        !isset(self::$init) && self::init();
        // 要删除的 cookie 前缀，不指定则删除 config 设置的指定前缀
        $prefix = !is_null($prefix) ? $prefix : self::$config['prefix'];
        if ($prefix) {
            foreach ($_COOKIE as $key => $val) {
                if (0 === strpos($key, $prefix)) {
                    self::realDelete($key);
                }
            }
        } else {
            foreach ($_COOKIE as $key => $val) {
                self::realDelete($key);
            }
        }
    }

    /**
     * 真正的删除
     * @param $key
     */
    private static function realDelete($key)
    {
        if (self::$config['setcookie']) {
            setcookie(
                $key, '', $_SERVER['REQUEST_TIME'] - 3600, self::$config['path'],
                self::$config['domain'], self::$config['secure'], self::$config['httponly']
            );
        }
        unset($_COOKIE[$key]);
    }
}
