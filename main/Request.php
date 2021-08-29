<?php
/*
 * @Description   请求类
 * @Author        lifetime
 * @Date          2021-08-22 12:18:34
 * @LastEditTime  2021-08-25 14:33:44
 * @LastEditors   lifetime
 */

namespace main;

class Request
{
    /**
     * 当前 HEADER 参数
     * @var array
     */
    protected $header = [];

    /**
     * 当前 SERVER 参数
     * @var array
     */
    protected $server = [];

    /**
     * 当前 COOKIE 参数
     * @var array
     */
    protected $cookie = [];

    /**
     * 当前 GET 参数
     * @var array
     */
    protected $get = [];

    /**
     * 当前 POST 参数
     * @var array
     */
    protected $post = [];

    /**
     * 当前 FILES 参数
     */
    protected $files = [];

    /**
     * 原始的 POST 包体
     * @var string
     */
    protected $content;

    /**
     * 原始请求报文
     * @var string
     */
    protected $data;

    /**
     * 当前连接标识
     * @var int
     */
    protected $fd;

    /**
     * 当前路由对象
     * @var RouteItem
     */
    protected $route;

    /**
     * 用户标识
     * @var string
     */
    protected $uid;

    /**
     * 当前 Swoole 服务器对象
     * @var \Swoole\Server
     */
    protected $swooleServer;

    public function __construct(\Swoole\Http\Request $request, \Swoole\Server $swooleServer)
    {
        $this->header = $request->header ?: [];
        $this->server = $request->server ?: [];
        $this->cookie = $request->cookie ?: [];
        $this->get = $request->get ?: [];
        $this->post = $request->post ?: [];
        $this->files = $request->files ?: [];
        $this->fd = $request->fd;
        $this->content = $request->getContent();
        $this->data = $request->getData();
        $this->swooleServer = $swooleServer;
    }

    /**
     * 获取数据
     * @access public
     * @param  array  $data 数据源
     * @param  string $name 字段名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    protected function getData(array $data, string $name, $default = null)
    {
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                return $default;
            }
        }

        return $data;
    }

    /**
     * 获取当前的Header
     * @access public
     * @param  string $name header名称
     * @param  string $default 默认值
     * @return string|array
     */
    public function header(string $name = '', string $default = null)
    {
        if ('' === $name) {
            return $this->header;
        }

        $name = str_replace('_', '-', strtolower($name));

        return $this->header[$name] ?? $default;
    }

    /**
     * 获取server参数
     * @access public
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function server(string $name = '', string $default = '')
    {
        if ('' === $name) {
            return $this->server;
        }

        return $this->server[$name] ?? $default;
    }

    /**
     * 获取cookie参数
     * @access public
     * @param  mixed        $name 数据名称
     * @param  string       $default 默认值
     * @return mixed
     */
    public function cookie(string $name = '', $default = null)
    {
        if (!empty($name)) {
            $data = $this->getData($this->cookie, $name, $default);
        } else {
            $data = $this->cookie;
        }
        return $data;
    }

     /**
     * 获取get参数
     * @access public
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function get(string $name = '', string $default = '')
    {
        if ('' === $name) {
            return $this->get;
        }

        return $this->get[$name] ?? $default;
    }

    /**
     * 获取post参数
     * @access public
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function post(string $name = '', string $default = '')
    {
        if ('' === $name) {
            return $this->post;
        }

        return $this->post[$name] ?? $default;
    }

    /**
     * 获取file参数
     * @access public
     * @param  mixed        $name 数据名称
     * @param  string       $default 默认值
     * @return mixed
     */
    public function file(string $name = '', $default = null)
    {
        if (!empty($name)) {
            $data = $this->getData($this->files, $name, $default);
        } else {
            $data = $this->files;
        }
        return $data;
    }

    /**
     * 当前请求的host
     * @access public
     * @return string
     */
    public function host()
    {
        return explode(':', $this->header['host'])[0];
    }

    /**
     * 当前请求URL地址中的port参数
     * @access public
     * @return int
     */
    public function port()
    {
        return (int) $this->server('server_port');
    }

    /**
     * 当前请求 SERVER_PROTOCOL
     * @access public
     * @return string
     */
    public function protocol()
    {
        return $this->header('server_protocol', '');
    }

    /**
     * 当前请求 REMOTE_PORT
     * @access public
     * @return int
     */
    public function remotePort()
    {
        return (int) $this->server('remote_port', '');
    }

    /**
     * 当前请求 REMOTE_ADDR
     * @access public
     * @return string
     */
    public function remoteAddr()
    {
        return $this->server('remote_addr', '');
    }

    /**
     * 获取当前请求的时间
     * @access public
     * @param  bool $float 是否使用浮点类型
     * @return integer|float
     */
    public function time(bool $float = false)
    {
        return $float ? $this->server('request_time_float') : $this->server('request_time');
    }

    /**
     * 当前的请求类型
     * @access public
     * @return string
     */
    public function method()
    {
        return $this->server('request_method') ?: 'GET';
    }

    /**
     * 获取 URI 信息
     * @access  public
     * @return string
     */
    public function uri()
    {
        return $this->server('request_uri', '');
    }

    /**
     * 获取 pathinfo 信息
     * @access public
     * @return string
     */
    public function pathinfo()
    {
        return $this->server('path_info', '');
    }

    /**
     * 获取 query 信息
     * @access public
     * @return string
     */
    public function query()
    {
        return $this->server('query_string', '');
    }

    /**
     * 获取原始的 POST 包体
     * @access public
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * 获取完整的原始 Http 请求报文
     * @access public
     * @return string
     */
    public function getHttpData()
    {
        return $this->data;
    }

    /**
     * 获取连接标识
     * @access public
     * @return int
     */
    public function fd()
    {
        return $this->fd;
    }

    /**
     * 获取 Swoole 服务器对象
     * @access  public
     * @retrun  \Swoole\Server
     */
    public function swooleServer()
    {
        return $this->swooleServer;
    }

    /**
     * 设置当前路由对象
     * @access public
     * @param   RouteItem   $route
     * @return $this
     */
    public function setRoute(RouteItem $route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * 获取当前路由对象
     * @access public
     * @return RouteItem
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * 获取路由参数
     * @access public
     * @param  string       $name 变量名
     * @param  mixed        $default 默认值
     * @return mixed
     */
    public function route(string $name = '', $default = null)
    {
        if ('' === $name) {
            return $this->route->getOptions();
        }

        return $this->getData($this->route->getOptions(), $name, $default);
    }

    /**
     * 获取请求参数
     * @access public
     * @param  string       $name 变量名
     * @param  mixed        $default 默认值
     * @return mixed
     */
    public function param(string $name = '', $default = null)
    {
        $data = array_merge([], $this->get(), $this->post(), $this->route());

        if ('' === $name) {
            return $data;
        }

        return $this->getData($data, $name, $default);
    }

    /**
     * 设置用户标识
     * @access public
     * @param   string  $uid    用户标识
     * @return $this
     */
    public function setUid(string $uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * 获取用户标识
     * @access public
     * @return string
     */
    public function uid()
    {
        return $this->uid;
    }
}