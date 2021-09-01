<?php

namespace main;

use app\Auth;
use app\Event as AppEvent;
use app\Task;
use model\Basic;
use model\User;
use MongoDB\Client;

class Event extends \swoole\event\WebSocket
{
    public static function onStart($server)
    {
        $indexs = [
            'uid' => [
                'order' => 1,
                'unique' => true,
                'background' => true,
            ],
            'token' => [
                'order' => 1,
                'unique' => true,
                'background' => true,
            ],
            'fd' => [
                'order' => -1,
                'unique' => true,
                'background' => true,
            ]
        ];
        $config = array_merge(Basic::DEFAULT_CONFIG['connections']['mongo'], $server->config['mongodb']??[]);

        $uri = 'mongodb://';
        $uri .= $config['username']?:'';
        $uri .= $config['password'] ? ":{$config['password']}@": '';
        $uri .= "{$config['hostname']}:{$config['hostport']}";

        $client = new Client($uri);
        $msg = $client->selectDatabase($config['database']?:'msg');
        $collection = $msg->selectCollection((new User)->getName());
        foreach($collection->listIndexes() as $index) {
            if (isset($indexs[$index->getName()])) unset($indexs[$index->getName()]);
        }
        $indexParam = [];
        foreach($indexs as $name => $options) {
            $order = $options['order']??1;
            $indexParam[] = array_merge([
                'key' => [$name => $order],
                'name' => $name
            ], array_diff_key($options, ['order' => 1]));
        }
        if (count($indexParam) > 0) {
            $collection->createIndexes($indexParam, []);
        }
    }
    public static function onRequest($swooleRequest, $response)
    {
        $response->detach();
        $request = new Request($swooleRequest, self::$server);
        (new Route($request))->dispatch();
    }

    public static function onHandShake($request, $response)
    {
        $request_ = new Request($request, self::$server);
        if (Auth::websocket($request_) === false) {
            $response->end();
            return;
        }
        User::userConnect($request_->fd(), $request_->uid());
        self::$server->defer(function() use($request_){
            Task::instance(self::$server)->noticeNotReadMsg($request_->fd());
        });
        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        $key = base64_encode(
            sha1(
                $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                true
            )
        );
        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }
        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }
        $response->status(101);
        $response->end();
    }

    public static function onMessage($server, $frame)
    {
        $data = json_decode($frame->data, true);
        $event = AppEvent::instance($server);
        if (json_last_error() > 0) {
            $event->noticeError($frame->fd, Msg::DATA_PARSE_FAIL, '消息格式错误');
            return;
        }
        if (empty($data['event']) || !in_array($data['event'], array_keys(AppEvent::EVENT_METHOD))) {
            $event->noticeError($frame->fd, Msg::EVENT_NOT_FOUND, "事件 {$data['event']} 不存在");
            return;
        }
        call_user_func([$event, AppEvent::EVENT_METHOD[$data['event']]], $frame->fd, $data['data']??[]);
    }

    public static function onTask($server, int $task_id, int $src_worker_id, $data)
    {
        $task = Task::instance($server);

        if (method_exists($task, $data['type'])) {
            call_user_func([$task, $data['type']], $data['data']);
        }
    }

    public static function onClose($server, int $fd, int $reactorId)
    {
        if ($server->isEstablished($fd)) {
            User::update(['fd' => 0], ['fd' => $fd]);
        }
    }
}