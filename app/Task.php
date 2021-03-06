<?php
/*
 * @Description   异步事件类
 * @Author        lifetime
 * @Date          2021-08-26 16:55:26
 * @LastEditTime  2021-08-29 19:19:54
 * @LastEditors   lifetime
 */
namespace app;

use model\Message;
use model\User;

class Task
{
    /**
     * 创建消息异步任务
     * @var string
     */
    const CREATE_MSG = 'createMsg';

    /**
     * 通知未读消息
     * @var string
     */
    const NOTICE_NOT_READ_MSG = 'noticeNotReadMsg';
    
    /**
     * swoole 服务器类
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * 实例列表
     * @var array
     */
    protected static $instances = [];

    /**
     * 构造函数
     * @access private
     * @param   \Swoole\Server $server
     */
    private function __construct(\Swoole\Server $server)
    {
        $this->server = $server;
    }

    /**
     * 构造函数
     * @access private
     * @param   \Swoole\Server $server
     * @return $this
     */
    public static function instance(\Swoole\Server $server)
    {
        $key = md5(get_called_class());
        if (isset(static::$instances[$key])) return static::$instances[$key];
        return static::$instances[$key] = new static($server);
    }

    /**
     * 创建消息异步任务处理方法
     * @access public
     * @param   array   $msg
     */
    public function createMsg(array $msg)
    {
        $event = Event::instance($this->server);
        $msg = Message::find($msg['msg_id'])->toArray();
        switch($msg['type']) {
            case Message::MSG_TYPE_PERSISTENT:
            case Message::MSG_TYPE_INSTANT:
                $users = User::where('fd', '<>', 0)
                ->whereOr(['tag' => $msg['tag']])
                ->whereNotIn('uid', $msg['black_uids'])
                ->whereOr('uid', 'in', $msg['uids'])
                ->select()
                ->toArray();
                foreach($users as $user) {
                    $event->noticeNewMsg($user['fd'], [
                        'title' => $msg['title'],
                        'content' => $msg['content'],
                        'create_time' => $msg['create_time']
                    ]);
                }
                break;
            case Message::MSG_TYPE_DELAY:
                $this->server->after($msg['delay'] * 1000, function() use($msg, $event) {
                    $users = User::where('fd', '<>', 0)
                    ->whereOr(['tag' => $msg['tag']])
                    ->whereNotIn('uid', $msg['black_uids'])
                    ->whereOr('uid', 'in', $msg['uids'])
                    ->select()
                    ->toArray();
                    foreach($users as $user) {
                        $event->noticeNewMsg($user['fd'], [
                            'title' => $msg['title'],
                            'content' => $msg['content'],
                            'create_time' => $msg['create_time']
                        ]);
                    }
                });
                break;
        }
    }

    /**
     * 通知未读消息
     * @access public
     * @param   int $fd
     */
    public function noticeNotReadMsg(int $fd)
    {
        $user = User::getByFd($fd);
        
        $msgList = Message::where('type', Message::MSG_TYPE_PERSISTENT)
            ->whereNotIn('_id', $user->read_msg_id)
            ->whereOr(['tag' => $user->tag])
            ->whereOr('uids', 'in', [$user->uid])
            ->whereNotIn('black_uids', [$user->uid])
            ->visible(['_id', 'title', 'content', 'create_time'])
            ->select()
            ->toArray();
        if (count($msgList) > 0) {
            Event::instance($this->server)->noticeNotReadMsg($fd, $msgList);
        }
    }
}
