<?php

return [
    // 服务器相关配置
    'server' => [
        'host' => '0.0.0.0', // 监听地址
        'port' => 9501, // 监听端口
        'worker_num' => 1, // 工作进程数量
        'task_worker_num' => 1, // 任务进程数量
        'daemonize' => false, // 协程化
        'pid_file' => '', // pid文件路径
    ],
    // 路由相关配置
    'route' => [
        'default_class' => 'Index', // 默认类
        'default_method' => 'index', // 默认方法
        'msg_401' => '401 Unauthorized ', // 401 消息
        'msg_404' => '404 Not Found', // 404消息
        'msg_500' => '500 Server Error', // 500 消息
    ],
    // MongoDB相关配置
    'mongodb' => [
        'hostname' => 'localhost',
        'database' => 'msg',
        'username' => 'admin',
        'password' => 'admin',
        'hostport' => '27017',
    ],
];