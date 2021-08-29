<?php

namespace model;

use think\DbManager;
use think\Model;

class Basic extends Model
{
    protected $pk = '_id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $dateFormat = 'Y-m-d H:i:s';

    const DEFAULT_CONFIG = [
        'default' => 'mongo',
        'connections' => [
            'mongo' => [
                'type' => 'mongo',
                'hostname' => 'localhost',
                'database' => 'msg',
                'username' => 'admin',
                'password' => 'admin',
                'hostport' => '27017',
                'charset' => 'utf8mb4',
            ],
        ],
    ];

    protected static $userConfig = [];

    public static function setUserConfig(array $config)
    {
        self::$userConfig = $config;
    }
    
    protected static function init()
    {
        $db = new DbManager();
        $config = self::DEFAULT_CONFIG;
        $config['connections']['mongo'] = array_merge($config['connections']['mongo'], self::$userConfig);
        $db->setConfig($config);
        self::setDb($db);
    }

    public function getIdAttr($value, $data)
    {
        return "{$data['_id']}";
    }
}