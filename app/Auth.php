<?php
/*
 * @Description   Token 验证类
 * @Author        lifetime
 * @Date          2021-08-25 12:06:08
 * @LastEditTime  2021-08-29 18:16:37
 * @LastEditors   lifetime
 */

namespace app;

use main\Request;
use model\User;

class Auth
{
    public static function http(Request &$request)
    {
        $signBody = [
            $request->uri() . ($request->server('query_string') ? "?{$request->server('query_string')}" : ''),
            $request->method(),
            !in_array($request->header('content-type'), [
                'application/json',
                'application/xml',
                'application/javascript',
                'text/plain',
                'text/html',
            ]) ?
                json_encode($request->post(), JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE) :
                $request->getContent(),
            $request->header('timestamp')
        ];
        $signStr = implode("\n", $signBody) . "\n";
        $sign = base64_encode($signStr);
        if ($request->header('sign') <> $sign || time() - $request->header('timestamp') > 5) {
            return false;
        }
        return true;
    }

    public static function websocket(Request &$request)
    {
        $user = User::getByToken($request->get('token'));
        if (empty($user)  || $user->expire < time()) return false;
        $request->setUid($user->uid);
        return true;
    }
}