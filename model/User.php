<?php

namespace model;

class User extends Basic
{
    protected $name = 'user';

    protected $schema = [
        '_id' => 'int',
        'uid' => 'string',
        'token' => 'string',
        'expire' => 'int',
        'read_msg_id' => 'array',
        'tag' => 'array',
        'fd' => 'int',
        'last_connect_time' => 'int'
    ];

    /**
     * 创建用户
     * @access public
     * @param   array   $options
     * @return $this
     */
    public static function createUser(array $options)
    {
        $options = array_merge([
            'uid' => 'string',
            'token' => '',
            'expire' => 0,
            'read_msg_id' => [],
            'tag' => [],
            'fd' => 0,
            'last_connect_time' => 0
        ], $options);
        return self::create($options);
    }

    /**
     * 连接
     * @access public
     * @param   int     $fd
     * @param   string  $uid
     * @return $this
     */
    public static function userConnect(int $fd, string $uid)
    {
        return self::update([
            'fd' => $fd,
            'last_connect_time' => time(),
        ], ['uid' => $uid]);
    }
}