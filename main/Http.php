<?php

namespace main;

class Http
{
    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * Swoole 服务器对象
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * 响应对象
     * @var Response
     */
    public $response;

    /**
     * 分页参数Key
     * @var array
     */
    protected $pageKey = ['page', 'list_rows'];


    /**
     * 构造函数
     * @access  public
     * @param   Request     $request    请求对象
     * @param   array       $config     配置
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->server = $request->swooleServer();
        if (isset($this->server->config['route']['page_index_key']) && !empty($this->server->config['route']['page_index_key'])) {
            $this->pageKey[0] = $this->server->config['route']['page_index_key'];
        }
        if (isset($this->server->config['route']['page_list_rows_key']) && !empty($this->server->config['route']['page_list_rows_key'])) {
            $this->pageKey[1] = $this->server->config['route']['page_list_rows_key'];
        }
    }

    /**
     * 获取配置
     * @access protected
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    protected function config(string $name = '', $default = null)
    {
        $data = $this->server->config;
        if ('' === $name) {
            return $data;
        }

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
     * 创建响应对象
     * @access protected
     * @return Response
     */
    protected function createResponse()
    {
        $this->response = new Response($this->request->fd());
        return $this->response;
    }

    /**
     * 操作成功响应
     * @access  protected
     * @param   mixed   $content
     */
    protected function success($content = null)
    {
        $this->createResponse()
            ->header([
                'content-type' => 'text/json'
            ])
            ->end(json_encode([
                'code' => 0,
                'msg' => 'SUCCESS',
                'content' => $content
            ]));
    }

    /**
     * 操作失败响应
     * @access  protected
     * @param   int     $code
     * @param   string  $msg
     */
    protected function fail(int $code, string $msg = null)
    {
        $this->createResponse()
            ->header([
                'content-type' => 'text/json'
            ])
            ->end(json_encode([
                'code' => $code,
                'msg' => Msg::getMsg($code, $msg),
            ]));
    }

    /**
     * 返回数组
     * @access protected
     * @param  array    $list
     */
    protected function returnArr(array $list)
    {
        $this->success([
            'list' => $list
        ]);
    }

    /**
     * 返回数据
     * @access protected
     * @param   array   $data
     */
    protected function returnData(array $data)
    {
        $this->success([
            'data' => $data
        ]);
    }

    /**
     * 返回分页
     * @access protected
     * @param  mixed $model
     */
    protected function returnPage($model)
    {
        $this->success([
            'page' => [
                $this->pageKey[0] => (int)$this->request->get($this->pageKey[0], 1),
                $this->pageKey[1] => (int)$this->request->get($this->pageKey[1], 10),
                'count' => $model->count(),
                'list' => $model->page(
                    (int)$this->request->get($this->pageKey[0], 1),
                    (int)$this->request->get($this->pageKey[1], 10)
                )->select()->toArray(),
            ]
        ]);
    }
}