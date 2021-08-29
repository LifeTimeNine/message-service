<?php

namespace app\http;

use app\Task;
use main\Http;
use main\Msg;
use model\Message as ModelMessage;
use model\User;

class Message extends Http
{
    /**
     * 消息列表
     */
    public function index()
    {
        $data = ModelMessage::select();
        $this->success($data);
    }

    /**
     * 创建消息
     */
    public function save()
    {
        $type = $this->request->post('type');
        if (!in_array($type, ModelMessage::MSG_TYPES)) {
            $this->fail(Msg::PARAMS_CHECK, '类型不存在');
            return;
        }
        $title = $this->request->post('title');
        if (empty($title)) {
            $this->fail(Msg::PARAMS_CHECK, '消息标题不能为空');
            return;
        }
        if (!strlen($title) > 64) {
            $this->fail(Msg::PARAMS_CHECK, '标题超出最大字数限制');
            return;
        }
        $tag = $this->request->post('tag');
        if (!is_array($tag)) {
            $this->fail(Msg::PARAMS_CHECK, '标签类型不合法');
            return;
        }
        if (count($tag) <= 0) {
            $this->fail(Msg::PARAMS_CHECK, '至少要选择一个标签');
            return;
        }
        $content = $this->request->post('content');
        if (empty($content)) {
            $this->fail(Msg::PARAMS_CHECK, '消息内容不能为空');
            return;
        }
        $delay = $this->request->post('delay', 0);
        if ($type == ModelMessage::MSG_TYPE_DELAY && $delay <= 0) {
            $this->fail(Msg::PARAMS_CHECK, '延时消息延时时间必须大于0');
            return;
        }
        $msg = ModelMessage::createMsg([
            'type' => (int)$type,
            'title' => $title,
            'tag' => $tag,
            'content' => $content,
            'delay' => (int)$delay
        ]);
        $this->server->task([
            'type' => Task::CREATE_MSG,
            'data' => $msg->toArray(),
        ]);
        $this->success();
    }

    /**
     * 删除消息
     */
    public function delete()
    {
        $id = $this->request->route('id');
        if (empty(ModelMessage::find($id))) {
            $this->fail(Msg::NOT_FOUND);
            return;
        }
        ModelMessage::destroy($id, true);
        User::update(['read_msg_id' => ['$pull', $id]], ['read_msg_id' => ['$eq', $id]]);
        $this->success();
    }
}