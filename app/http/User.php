<?php
/*
 * @Description   用户管理
 * @Author        lifetime
 * @Date          2021-08-29 20:10:20
 * @LastEditTime  2021-08-29 20:36:57
 * @LastEditors   lifetime
 */
namespace app\http;

use main\Http;
use main\Msg;
use model\Message;
use model\User as ModelUser;

class User extends Http
{
    /**
     * 获取用户列表
     */
    public function index()
    {
        $model = ModelUser::order('create_time', 'desc')
            ->visible(['uid', 'last_connect_time', 'tag', 'create_time'])
            ->withAttr([
                'online' => function($value, $data) {
                    return $data['fd'] > 0;
                },
                'last_connect_time' => function($value) {
                    return date('Y-m-d H:i:s', $value);
                },
            ])
            ->append(['online']);
        $this->returnPage($model);
    }

    /**
     * 获取用户详情信息
     */
    public function read()
    {
        $user = ModelUser::getByUid($this->request->route('id'));
        if (empty($user)) {
            $this->fail(Msg::NOT_FOUND);
            return;
        }
        $user->visible(['uid', 'last_connect_time', 'tag', 'create_time'])
            ->withAttr([
                'online' => function($value, $data) {
                    return $data['fd'] > 0;
                },
                'read_message_count' => function($value, $data) {
                    return count($data['read_msg_id']);
                },
                'last_connect_time' => function($value) {
                    return date('Y-m-d H:i:s', $value);
                },
            ])
            ->append(['online', 'read_message_count']);
        $this->returnData($user->toArray());
    }
}
