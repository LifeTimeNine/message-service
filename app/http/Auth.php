<?php

namespace app\http;

use main\Http;
use main\Msg;
use model\User;

class Auth extends Http
{
    public function websocket()
    {
        $secret = $this->config('secret');
        $uid = $this->request->get('uid');
        $timestamp = $this->request->get('timestamp');
        $sign = $this->request->get('sign');
        $expectSign = md5(md5($uid) . md5($timestamp) . md5($secret));

        if ($sign == $expectSign) {
            $time = time();
            $token = md5($uid . $timestamp . $secret);
            $expire = $time + 3600 * 24;
            $user = User::getByUid($uid);
            if (empty($user)) {
                User::createUser([
                    'uid' => $uid,
                    'token' => $token,
                    'expire' => $expire,
                    'read_msg_id' => []
                ]);
            } else {
                $user->save([
                    'token' => $token,
                    'expire' => $expire,
                ]);
            }
            $this->success([
                'token' => $token,
                'expire' => 3600 * 24
            ]);
        } else {
            $this->fail(Msg::AUTH_FAIL);
        }
    }
}