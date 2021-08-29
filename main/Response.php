<?php
/*
 * @Description   响应类
 * @Author        lifetime
 * @Date          2021-08-22 20:21:33
 * @LastEditTime  2021-08-22 20:56:01
 * @LastEditors   lifetime
 */
namespace main;

use Swoole\Http\Response as HttpResponse;

class Response
{
    /**
     * swoole 响应类
     * @var \Swoole\Http\Response
     */
    protected $response;

    /**
     * 构造函数
     * @access  public
     * @param   int     $fd     连接标识
     */
    public function __construct(int $fd)
    {
        $this->response = HttpResponse::create($fd);
    }

    /**
     * 设置 HTTP 响应的 Header 信息
     * @access  public
     * @param   array   $headers    Header列表
     * @retrun $this
     */
    public function header(array $headers)
    {
        foreach($headers as $key => $value) {
            $this->response->header($key, $value);
        }
        return $this;
    }

    /**
     * 将 Header 信息附加到 HTTP 响应的末尾，仅在 HTTP2 中可用，用于消息完整性检查，数字签名等
     * @access  public
     * @param   array   $list
     * @retrun $this
     */
    public function trailer(array $list)
    {
        foreach($list as $key => $value) {
            $this->response->trailer($key, $value);
        }
        return $this;
    }

    /**
     * 设置 HTTP 响应的 cookie 信息
     * @access  public
     * @param   string  $key    cookie 的名称
     * @param   string  $value  cookie 的值
     * @param   int     $expire 过期时间
     * @param   string  $path   cookie 的服务器路径
     * @param   string  $domain cookie 的域名
     * @param   bool    $secure 是否需要在安全的 HTTPS 连接来传输 cookie
     * @return  $this
     */
    public function cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false )
    {
        $this->response->cookie($key, $value, $expire, $path, $domain, $secure);
        return $this;
    }

    /**
     * 设置响应状态码
     * @access public
     * @param   int     $http_code  状态码
     * @param   string  $reason     原因
     * @retrun  $this
     */
    public function status(int $http_code, string $reason = null)
    {
        $this->response->status($http_code, $reason);
        return $this;
    }

    /**
     * Http 跳转。 此方法会自动 end 发送并结束响应
     * @access public
     * @param   string  $url        新地址
     * @param   int     $http_code  状态码
     * @return $this
     */
    public function redirect(string $url, int $http_code = 302)
    {
        $this->response->redirect($url, $http_code);
        return $this;
    }

    /**
     * 启用 Http Chunk 分段向浏览器发送相应内容
     * @access  public
     * @param   string  $data   要发送的数据
     * @retrn $this
     */
    public function write(string $data)
    {
        $this->response->write($data);
        return $this;
    }

    /**
     * 发送文件到浏览器
     * @access  public
     * @param   string  $filename   文件名称
     * @param   int     $offset     偏移量
     * @param   int     $length     文件尺寸
     * @return  $this
     */
    public function sendfile(string $filename, int $offset = 0, int $length = null)
    {
        $this->response->sendfile($filename, $offset, $length);
        return $this;
    }

    /**
     * 发送 Http 响应体，并结束请求处理
     * @access public
     * @param   string  $html   要发送的内容
     * @return  bool
     */
    public function end(string $html = null)
    {
        return $this->response->end($html);
    }
}
