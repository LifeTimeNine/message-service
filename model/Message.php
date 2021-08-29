<?php

namespace model;

class Message extends Basic
{
    protected $name = 'message';

    /**
     * 持久性消息
     * @var int
     */
    const MSG_TYPE_PERSISTENT = 1;
    /**
     * 即时消息
     * @var int
     */
    const MSG_TYPE_INSTANT = 2;
    /**
     * 延时消息
     * @var int
     */
    const MSG_TYPE_DELAY = 3;

    /**
     * 允许的消息类型
     * @var array
     */
    const MSG_TYPES = [
        self::MSG_TYPE_PERSISTENT,
        self::MSG_TYPE_INSTANT,
        self::MSG_TYPE_DELAY
    ];

    protected $schema = [
        'type' => 'int',
        'title' => 'string',
        'content' => 'string',
        'tag' => 'array',
        'uids' => 'array',
        'black_uids' => 'array',
        'delay' => 'int',
        'read_num' => 'int'
    ];

    /**
     * 创建消息
     * @access public
     * @param   array   $options
     * @return $this
     */
    public static function createMsg(array $options)
    {
        $options = array_merge([
            'type' => 0,
            'title' => '',
            'content' => '',
            'tag' => [],
            'uids' => [],
            'black_uids' => [],
            'delay' => 0,
            'read_num' => 0
        ], $options);
        return self::create($options);
    }
}