<?php

namespace main;

use swoole\Config;
use swoole\server\WebSocket;

class Service
{
    /**
     * 允许的 操作
     * @var array
     */
    protected $actions = ['start', 'stop', 'reload', 'restart'];

    /**
     * cli 参数
     * @var array
     */
    protected $argv;

    /**
     * 配置
     * @var array
     */
    protected $config = [
        // 服务器相关配置
        'server' => [
            'host' => '0.0.0.0', // 监听地址
            'port' => 9501, // 监听端口
            'worker_num' => 1, // 工作进程数量
            'task_worker_num' => 1, // 任务进程数量
            'daemonize' => false, // 协程化
            'pid_file' => '', // pid文件路径
        ],
    ];

    public function __construct($argv = null)
    {
        require ROOT_PATH . "/app/route.php";
        $config = require ROOT_PATH . "/app/config.php";
        if (is_array($config)) $this->config = array_merge($this->config, $config);
        Route::setConfig($this->config['route']??[]);
        
        if (null === $argv) {
            $argv = $_SERVER['argv'];
            // 去除命令名
            array_shift($argv);
        }
        $this->argv = $argv;
    }

    protected function getAction()
    {
        if (!empty($this->argv[0])) {
            $action = $this->argv[0];
        } else {
            $action = 'start';
        }

        if (!in_array($action, $this->actions)) {
            throw new \Exception("unknown action: {$action}");
        }
        return $action;
    }

    /**
     * 运行
     * @access  pulic
     */
    public function run()
    {
        $config = new Config($this->config['server']);
        $config->setEventClass(Event::class);
        $config->setWebsocketHandshake(true);
        $server = WebSocket::instance($config);
        $action = $this->getAction();
        if ($action == 'start') {
            $server->initServer();
            $server->getServer()->config = $this->config;
        }
        $server->{$this->getAction()}();
    }
}