<?php
/*
 * @Description   用户标签
 * @Author        lifetime
 * @Date          2021-08-25 12:39:18
 * @LastEditTime  2021-08-25 14:51:51
 * @LastEditors   lifetime
 */
namespace app\http;

use main\Http;
use main\Msg;
use model\User;

class Tag extends Http
{
    /**
     * 标签列表
     */
    public function index()
    {
        $this->returnArr(User::tags($this->request->uid()));
    }

    /**
     * 创建标签
     */
    public function save()
    {
        $name = $this->request->post('name');
        if (strlen($name) > 64) {
            $this->fail(Msg::PARAMS_CHECK, '标签名称超出长度限制');
            return;
        }
        User::update(['tag'=> ['$addToSet', $name]], ['uid' => $this->request->uid()]);
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
     * 修改标签
     */
    public function update()
    {
        $index = $this->request->route('id');
        $name = $this->request->post('name');
        if (!is_numeric($index)) {
            $this->fail(Msg::PARAMS_CHECK, '索引必须为整数');
            return;
        }
        if (strlen($name) > 64) {
            $this->fail(Msg::PARAMS_CHECK, '标签名称超出长度限制');
            return;
        }

        $tags = User::tags($this->request->uid());

        if (isset($tags[$index])) {
            $tags[$index] = $name;
            User::update(['tag' => $tags], ['uid' => $this->request->uid()]);
            $this->success();
            return;
        } else {
            $this->fail(Msg::NOT_FOUND);
            return;
        }
    }

    /**
     * 删除标签
     */
    public function delete()
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
        } else {
            unset($tags[$index]);
            $tags = array_values($tags);
            User::update(['tag' => $tags], ['uid' => $this->request->uid()]);
            $this->success();
            return;
        }
    }
}
