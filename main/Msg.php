<?php

namespace main;

class Msg
{
    const DEFAULT_MSG       = 1000;
    const AUTH_FAIL         = 1001;
    const PARAMS_CHECK      = 1002;
    const NOT_FOUND         = 1003;

    protected static $msg = [
        self::DEFAULT_MSG   => '操作失败，请重试',
        self::AUTH_FAIL     => '认证失败，请检查参数',
        self::NOT_FOUND     => '资源不存在',
    ];

    /**
     * 获取异常消息
     * @access public
     * @param   int     $code   异常状态码
     * @param   string  $msg    异常消息
     * @return  string
     */
    public static function getMsg(int $code, string $msg = null)
    {
        return $msg ?: (self::$msg[$code]??self::$msg[self::DEFAULT_MSG]);
    }
}