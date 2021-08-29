<?php

namespace model;

use think\DbManager;
use think\Model;

class Basic extends Model
{
    protected $pk = '_id';

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
    
    protected static function init()
    {
        $configArr = require_once CONFIG_FILE;
        $configArr = $configArr??[];
        $configArr = is_array($configArr)?$configArr:[];
        $db = new DbManager();
        $config = self::DEFAULT_CONFIG;
        $config['connections']['mongo'] = array_merge($config['connections']['mongo'], $configArr);
        $db->setConfig($config);
        self::setDb($db);
    }

    public function getIdAttr($value, $data)
    {
        return "{$data['_id']}";
    }

    public function getCtimeAttr($value, $data)
    {
        var_dump($data);
        return $data['_id']->getTimestamp();
    }
}