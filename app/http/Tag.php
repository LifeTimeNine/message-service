<?php
/*
 * @Description   用户标签
 * @Author        lifetime
 * @Date          2021-08-25 12:39:18
 * @LastEditTime  2021-08-29 17:33:29
 * @LastEditors   lifetime
 */
namespace app\http;

use main\Http;
use main\Msg;
use model\User;

class Tag extends Http
{
    /**
     * 创建标签
     */
    public function save()
    {
        $data = json_decode($this->request->getContent(), true);
        if (json_last_error() > 0) {
            $this->fail(MSG::PARAMS_CHECK, 'Body参数格式不正确');
            return;
        }
        if (empty($data['tag_name'])) {
            $this->fail(Msg::PARAMS_CHECK, '标签名称不能为空');
            return;
        }
        if (strlen($data['tag_name']) > 64) {
            $this->fail(Msg::PARAMS_CHECK, '标签名称超出长度限制');
            return;
        }
        if (empty($data['uids'])) {
            $this->fail(MSG::PARAMS_CHECK, '用户标识不能为空');
            return;
        }
        if (!is_array($data['uids'])) {
            $this->fail(Msg::PARAMS_CHECK, '用户标识格式不正确');
            return;
        }
       User::where('uid', 'in', $data['uids'])->update([
           'tag' => ['$addToSet', $data['tag_name']]
       ]);
        $this->success();
    }

    /**
     * 标签信息
     */
    public function read()
    {
        $index = $this->request->route('id');
        if (!is_numeric($index)) {
            $this->fail(Msg::PARAMS_CHECK, '索引必须为整数');
            return;
        }

        $tags = User::tags($this->request->uid());

        if (!isset($tags[$index])) {
            $this->fail(Msg::NOT_FOUND);
            return;
        }
        $this->returnData(['name' => $tags[$index]]);
    }

    /**
     * 删除标签
     */
    public function delete()
    {
        $data = json_decode($this->request->getContent(), true);
        if (json_last_error() > 0) {
            $this->fail(MSG::PARAMS_CHECK, 'Body参数格式不正确');
            return;
        }
        if (empty($data['tag_name'])) {
            $this->fail(Msg::PARAMS_CHECK, '标签名称不能为空');
            return;
        }
        if (empty($data['uids'])) {
            $this->fail(MSG::PARAMS_CHECK, '用户标识不能为空');
            return;
        }
        if (!is_array($data['uids'])) {
            $this->fail(Msg::PARAMS_CHECK, '用户标识格式不正确');
            return;
        }
       User::where('uid', 'in', $data['uids'])->update([
           'tag' => ['$pull', $data['tag_name']]
       ]);
        $this->success();
    }
}
