<?php
/**
 * Desc: 禁止new clone
 * User: baagee
 * Date: 2019/3/14
 * Time: 下午2:08
 */

namespace BaAGee\Cookie\Base;
trait ProhibitNewClone
{
    // 禁止
    final private function __construct()
    {
    }

    // 禁止
    final private function __clone()
    {
    }
}
