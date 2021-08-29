<?php
/*
 * @Description   WebSocket 事件
 * @Author        lifetime
 * @Date          2021-08-26 18:16:48
 * @LastEditTime  2021-08-28 20:07:08
 * @LastEditors   lifetime
 */
namespace app;

use model\Message;
use model\User;

class Event
{
    /**
     * 错误消息事件
     * @var int
     */
    const NOTICE_ERROR = 0;
    /**
     * 新消息事件
     * @var int
     */
    const NOTICE_NEW_MSG = 1;
    /**
     * 未读消息事件
     * @var int
     */
    const NOTICE_NOT_READ_MSG = 2;
    /**
     * 已读上报
     * @var int
     */
    const READ_REPORT = 3;

    /**
     * 事件对应方法（服务器收到的事件）
     * @var array
     */
    const EVENT_METHOD = [
        self::READ_REPORT => 'readReport'
    ];

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
     * 错误消息
     * @access public
     * @param   int     $fd
     * @param   string  $error
     */
    public function noticeError(int $fd, string $error = 'Error')
    {
        if ($this->server->isEstablished($fd)) {
            $this->server->push($fd, json_encode([
                'event' => self::NOTICE_ERROR,
                'error' => $error
            ]));
        }
    }

    /**
     * 新消息事件
     * @access public
     * @param   int     $fd
     * @param   array   $msg
     */
    public function noticeNewMsg(int $fd, array $msg)
    {
        if ($this->server->isEstablished($fd)) {
            $this->server->push($fd, json_encode([
                'event' => self::NOTICE_NEW_MSG,
                'msg' => $msg
            ]));
        }
    }

    /**
     * 未读消息事件
     * @access public
     * @param   int     $fd
     * @param   array   $msgList
     */
    public function noticeNotReadMsg(int $fd, array $msgList)
    {
        if ($this->server->isEstablished($fd)) {
            $this->server->push($fd, json_encode([
                'event' => self::NOTICE_NOT_READ_MSG,
                'msg_list' => $msgList
            ]));
        }
    }

    /**
     * 已读消息上报
     * @access public
     * @param   int     $fd 
     * @param   string  $msgId
     */
    public function readReport(int $fd, string $msgId)
    {
        if (empty(Message::find($msgId))) {
            $this->noticeError($fd, '消息不存在');
            return;
        }
        $readMsgId = User::getByFd($fd)->read_msg_id;
        if (in_array($msgId, $readMsgId?:[])) return;
        User::update(['read_msg_id' => ['$addToSet', $msgId]], ['fd' => $fd]);
        Message::update(['read_num' => ['$inc', 1]], ['_id' => $msgId]);
    }
}
