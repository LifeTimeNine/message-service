<?php

namespace main;

use app\Auth;

class Route
{
    /**
     * 路由列表
     * @var array
     */
    protected static $routes = [];

    /**
     * REST定义
     * @var array
     */
    protected static $rest = [
        'index'  => ['GET', '', 'index'],
        'read'   => ['GET', '/<id>', 'read'],
        'save'   => ['POST', '', 'save'],
        'update' => ['PUT', '/<id>', 'update'],
        'delete' => ['DELETE', '/<id>', 'delete'],
    ];

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * 配置
     * @var array
     */
    protected static $config = [
        'default_class' => 'Index', // 默认类
        'default_method' => 'index', // 默认方法
        'msg_401' => '401 Unauthorized ', // 401 消息
        'msg_404' => '404 Not Found', // 404消息
        'msg_500' => '500 Server Error', // 500 消息
    ];

    /**
     * 构造函数
     * @access  public
     * @param   Request $request 请求对象
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 路由调度
     * @access  public
     * @return  \Swoole\Http\Response
     */
    public function dispatch()
    {
        $routeItem = $this->checkRule();
        if (empty($routeItem)) {
            $response = new Response($this->request->fd());
            $response->status(404)->end(self::$config['msg_404']);
            return;
        }
        if ($routeItem->getCheckSign() && Auth::http($this->request) == false) {
            $response = new Response($this->request->fd());
            $response->status(401)->end(self::$config['msg_401']);
            return;
        }
        $this->request->setRoute($routeItem);
        $routeInfo = explode('@', $routeItem->getRoute());
        $routeInfo[0] = $routeInfo[0]?:self::$config['default_class'];
        $className = "\\app\\http\\{$routeInfo[0]}";
        if (!class_exists($className)) {
            $response = new Response($this->request->fd());
            $response->status(404)->end(self::$config['msg_404']);
            return;
        }
        $class = new $className($this->request);
        $method = $routeInfo[1] ?? self::$config['default_method'];
        if (!method_exists($class, $method)) {
            $response = new Response($this->request->fd());
            $response->status(404)->end(self::$config['msg_404']);
            return;
        }
        try {
            $class->$method();
        } catch (\Throwable $th) {
            (new Response($this->request->fd()))->status(500)->end(self::$config['msg_500']);
            echo "{$th}\n";
            return;
        }
        if (empty($class->response)) {
            (new Response($this->request->fd()))->end();
        }
    }

    /**
     * 检测路由
     * @access  protected
     * @return RouteItem|null
     */
    protected function checkRule()
    {
        $routeItem = null;
        foreach(self::$routes as $item) {
            if ($item->check($this->request->uri(), $this->request->method())) {
                $routeItem = $item;
                break;
            }
        }
        return $routeItem;
    }

    /**
     * 注册路由规则
     * @access  public
     * @param   string  $rule       路由规则
     * @param   string  $routo      路由地址
     * @param   string  $method     请求类型
     * @param   bool    $checkSign 是否验证token
     */
    public static function rule(string $rule, string $route, string $method = '*', bool $checkSign = false)
    {
        self::$routes[] = new RouteItem($rule, $route, $method, $checkSign);
    }

    /**
     * 注册GET路由
     * @access public
     * @param string $rule  路由规则
     * @param string $route 路由地址
     * @param bool   $checkSign 是否验证token
     */
    public static function get(string $rule, string $route, bool $checkSign = false)
    {
        self::rule($rule, $route, 'GET', $checkSign);
    }

    /**
     * 注册POST路由
     * @access public
     * @param string $rule  路由规则
     * @param string $route 路由地址
     * @param bool   $checkSign 是否验证token
     */
    public static function post(string $rule, string $route, bool $checkSign = false)
    {
        self::rule($rule, $route, 'POST', $checkSign);
    }

    /**
     * 注册PUT路由
     * @access public
     * @param string $rule  路由规则
     * @param string $route 路由地址
     * @param bool   $checkSign 是否验证token
     */
    public static function put(string $rule, string $route, bool $checkSign = false)
    {
        self::rule($rule, $route, 'PUT', $checkSign);
    }

    /**
     * 注册DELETE路由
     * @access public
     * @param string $rule  路由规则
     * @param string $route 路由地址
     * @param bool   $checkSign 是否验证token
     */
    public static function delete(string $rule, string $route, bool $checkSign = false)
    {
        self::rule($rule, $route, 'DELETE', $checkSign);
    }

    /**
     * 注册资源路由
     * @access public
     * @param   string  $rule   路由规则
     * @param   string  $toute  路由地址
     * @param bool   $checkSign 是否验证token
     */
    public static function resource(string $rule, string $route, bool $checkSign = false)
    {
        foreach(self::$rest as $item) {
            self::rule($rule . $item[1], $route . '@' . $item[2], $item[0], $checkSign);
        }
    }

    /**
     * 设置配置
     * @access public
     * @param   array   $config
     */
    public static function setConfig(array $config)
    {
        self::$config = array_merge(self::$config, $config);
    }
}