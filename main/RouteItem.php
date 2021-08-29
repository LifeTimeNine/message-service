<?php

namespace main;

class RouteItem
{
    /**
     * 路由规则
     * @var mixed
     */
    protected $rule;

    /**
     * 路由地址
     * @var string
     */
    protected $route;

    /**
     * 请求类型
     * @var string
     */
    protected $method;

    /**
     * 路由变量
     * @var array
     */
    protected $vars = [];

    /**
     * 路由正则
     * @var string
     */
    protected $preg;

    /**
     * 路由参数
     * @var array
     */
    protected $options = [];

    /**
     * 是否验证Token
     * @var bool
     */
    protected $checkToken = true;

    /**
     * 构造函数
     * @access public
     * @param   string  $rule   路由规则
     * @param   string  $routo  路由地址
     * @param   string  $method 请求类型
     * @param   bool    $checkToken 是否验证token
     */
    public function __construct(string $rule, string $route, string $method = '*', bool $checkToken = true)
    {
        $this->setRule($rule);
        $this->route = $route;
        $this->method = $method;
        $this->checkToken = $checkToken;

        $this->parseRule();
    }

    /**
     * 路由规则预处理
     * @access protected
     * @param  string      $rule     路由规则
     * @return void
     */
    protected function setRule(string $rule)
    {

        $rule = '/' != $rule ? ltrim($rule, '/') : '';

        if (false !== strpos($rule, ':')) {
            $this->rule = preg_replace(['/\[\:(\w+)\]/', '/\:(\w+)/'], ['<\1?>', '<\1>'], $rule);
        } else {
            $this->rule = $rule;
        }
    }

    /**
     * 解析规则
     * @access  protected
     * @return void
     */
    protected function parseRule()
    {
        $preg = preg_replace_callback('/\<(\w+)(\??)\>/', function($matches) {
            $this->vars[] = $matches[1];
            return $matches[2] == '?' ? '(\w+)?' : '(\w+)';
        }, $this->rule);

        $preg = str_replace('/', '\\/', $preg);
        $this->preg = "/^{$preg}$/";
    }

    /**
     * 验证路由
     * @access public
     * @param   string  $url    请求地址
     * @param   string  $method 请求方法
     * @return bool
     */
    public function check(string $url, string $method)
    {
        $url = '/' != $url ? ltrim($url, '/') : '';
        $res = preg_match($this->preg, $url, $options);
        if ($res && ($this->method == '*' || strpos($this->method, $method) !== false)) {
            foreach($this->vars as $key => $name) {
                $this->options[$name] = $options[$key + 1];
            }
            return true;
        }
        return false;
    }

    /**
     * 获取路由参数
     * @access public
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 获取路由
     * @access public
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * 获取当前路由是否验证Token
     * @access public
     * @return bool
     */
    public function getCheckToken()
    {
        return $this->checkToken;
    }
}