本项目是使用 PHP + Swoole + MongoDB 开发的一个消息推送服务，使用HTTP接口管理消息，使用WebScoket消费消息。

## 目录结构
```
根目录
├┄ app                      应用目录
│    ├┄ http                http 模块
│    │    ├┄ Auth.php       认证控制器
│    │    ├┄ Index.php      Index控制器
│    │    ├┄ Message.php    消息控制器
│    │    ├┄ Tag.php        标签控制器
│    │    ├┄ User.php       用户控制器
│    │    └┄ ...
│    ├┄ Auth.php            签名验证类
│    ├┄ config.php          系统配置文件
│    ├┄ Event.php           WebSocket事件类
│    ├┄ route.php           路由注册文件
│    └┄ Task.php            异步事件文件
├┄ main                     应用主目录
│    ├┄ Event.php           服务器事件类
│    ├┄ Http.php            http控制器基类
│    ├┄ Msg.php             返回消息常量类
│    ├┄ Request.php         请求信息类
│    ├┄ Respose.php         相应类
│    ├┄ Route.php           路由类
│    ├┄ RouteItem.php       路由项类
│    └┄ Service.php         服务器类
├┄ model                    模型目录
│    ├┄ Basic.php           模型基类
│    ├┄ Message.php         消息模型
│    ├┄ User.php            用户模型
│    └┄ ...
├┄ vendor                   composer类库文件
├┄ composer.json            composer定义文件
└┄ service                  命令行入口文件
```

## HTTP
  [HTTP接口文档](<http.md>)

## WebScoket
  [WebSocket文档](<websocket.md>)

## 配置
```php
<?php

return [
    // 系统签名秘钥
    'secret' => 'secret_dev',
    // 服务器相关配置
    'server' => [
        'host' => '0.0.0.0', // 监听地址
        'port' => 9501, // 监听端口
        'worker_num' => 1, // 工作进程数量
        'task_worker_num' => 1, // 任务进程数量
        'daemonize' => false, // 常驻进程
        'pid_file' => '', // pid文件路径
    ],
    // 路由相关配置
    'route' => [
        'default_class' => 'Index', // 默认类
        'default_method' => 'index', // 默认方法
        'msg_401' => '401 Unauthorized ', // 401 消息
        'msg_404' => '404 Not Found', // 404消息
        'msg_500' => '500 Server Error', // 500 消息
        'page_index_key' => 'page', // 分页 当前页数Query参数key
        'page_list_rows_key' => 'list_rows', // 分页 每页数量Query参数key
    ],
    // MongoDB相关配置
    'mongodb' => [
        'hostname' => 'localhost', // 数据库地址
        'database' => 'msg', // 数据库名称
        'username' => 'admin', // 用户名
        'password' => 'admin', // 密码
        'hostport' => '27017', // 端口
    ],
];
```

## 启动和停止 
在根目录下执行如下命令启动
```
php service
```
命令格式
```
php service [action]
```
支持的操作
操作 | 描述
-- | --
start | 启动
stop | 停止
reload | 柔性重启
restart | 重启


## 路由
路由是针对 HTTP 模块设计的一个简易的路由。所有接口都必须在路由中注册才可以访问。

简单注册一个路由
```php
<?php
\main\Route::rule('index', 'Index@index');
```

启动服务，在浏览器中访问 `/index`， 就会执行 `app\http\Index` 类的 `index` 方法中。

也可以设置请求方法和是否验证签名
```php
<?php
\main\Route::rule('user', 'User@index', 'GET|POST', true);
```
我们也可以按照不同的请求类型使用快捷方法注册
```php
<?php
\main\Route::get('index', 'Index@index', true); // 定义GET请求路由规则，并且需要验证签名
\main\Route::post('index', 'Index@index'); // 定义POST请求路由规则，不需要验证签名
\main\Route::put('index', 'Index@index'); // 定义PUT请求规则
\main\Route::delete('index', 'Index@index'); // 定义DELETE请求规则
```
路由地址中也可以传递参数
```php
<?php
\main\Route::rule('user/:id', 'User@index');
\main\Route::rule('user/<id>', 'User@index'); // 另一种写法

// 可选参数
\main\Route::rule('user/[:id]', 'User@index');
\main\Route::rule('user/<?id>', 'User@index');// 另一种写法
```

## 控制器
定义控制器
```php
<?php

namespace app\http;

class Index extend \main\Http
{
  public function index()
  {
    $this->createResponse()
      ->end('index');
  }
}
```

控制器建议继承 `\main\Http` 类，Http 中 封装了常用的获取信息和返回数据的方法。
方法名 | 描述
-- | --
config | 获取配置文件的配置
createResponse | 创建响应类
success | 返回成功的响应信息
fali  | 返回失败的响应信息
returnArr | 返回数组数据
returnData | 返回对象数据
returnPage  | 返回分页数据

可以通过 `$this->request` 来获取请求对象，也可以通过 `$this->server` 获取 Swoole的Server对象

## 请求信息
Request 对象将请求数据整理成一个类，可以通过相对应的方法获取相关参数。
方法名 | 描述
-- | --
header | 获取当前请求的header参数
server | 获取获取当前请求的Server参数
cookie | 获取获取当前请求的Cookie参数
get | 获取获取当前请求的Get参数
post | 获取获取当前请求的Post参数
file | 获取获取当前请求的File参数
host | 获取获取当前请求的Host参数
port | 获取当前请求的端口号
protocol | 获取当前请求的协议
remotePort | 获取客户端的端口号
remoteAddr | 获取客户端IP地址
time | 获取当前请求的时间
method | 获取当前的请求方法
uri | 获取当前请求的URL
pathinfo | 获取当前请求的地址
query | 获取Query字符串
getContent | 获取原始的POST包体
getHttpData | 获取原始的HTTP请求报文
fd | 获取连接标识
swooleServer | 获取Swoole的Server对象
config | 获取配置文件配置
getRoute | 获取当前路由对象
route | 获取路由参数
param | 获取请求参数
uid | 获取用户标识

## 响应
在控制器中使用 `$this->createResponse()` 来创建相应对象，调用`end()` 方法发送响应体，并结束请求处理。注意，执行end方法之后不能再有其他的响应。

方法列表
方法名 | 描述
-- | --
header | 设置响应头
cookie | 设置cookie
status | 设置响应状态码
redirect | 跳转
write | 分段向浏览器发送相应内容
sendfile | 发送文件到浏览器
end | 发送响应体，并结束响应

