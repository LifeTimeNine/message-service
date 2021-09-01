WebSocket 服务主要用户客户端接收消息、上报消息等。

## 获取token

客户端在连接WebScoket服务前要先向服务获取token

签名生成方法  
MD5(用户标识) + MD5(时间戳) + MD5(系统签名秘钥)

基本信息  
**请求URL:** http://{{host}}/auth/websocket
**请求类型:** GET

请求参数

参数名称 | 示例值 | 参数描述
 --- | --- | ---
uid | user_1 | 用户唯一标识
timestamp | 1630463101 |请求时间
sign | ac00d75b8850600a7dc2c2ded320eaf8 | 签名

请求示例  
```
  http://192.168.31.200:9501/auth/websocket?uid=user_1&timestamp=1630485655&sign=97ba4c350a23d8ffa9dea98926be8340
```

返回参数

参数名称 | 示例值 | 参数描述
 --- | --- | ---
code | 0 | 错误码 0表示正常
msg | SUCCESS | 消息
content | {} | 内容
token | 9ef82c3943176c813881542d34d97f11 | token
expire | 86400 | 有效期

返回示例
```json
{
	"code": 0,
	"msg": "SUCCESS",
	"content": {
		"token": "9ef82c3943176c813881542d34d97f11",
		"expire": 86400
	}
}
```

## 连接WebSocket

 ws://{{host}}?token={{token}}

## 数据格式
数据为 JSON 格式，并且遵守一定的格式
示例：
```json
  {
    "event": 0,
    "data": {
      "code": 2000,
      "error": "ERROR"
    }
  }
```
event: 事件
data: 数据

事件列表
标识 | 方向 | 描述
-- | -- | --
0 | 服 -> 客 | 当服务端发生异常时触发的事件
1 | 服 -> 客 | 有新的消息时触发的事件
2 | 服 -> 客 | 未读消息列表,客户端连接上服务器会触发此事件
3 | 客 -> 服 | 消息已读上报

## 事件说明

### 0 异常事件
#### 示例
```json
  {
    "event": 0,
    "data": {
      "code": 2000,
      "error": "ERROR"
    }
  }
```
#### 参数说明
参数名称 | 示例值 | 描述
--- | --- | ---
code | 2000 | 错误码
error| ERROR | 异常消息

### 1 新消息通知
#### 示例
```json
  {
    "event": 1,
    "data": {
      "msg": {
        "_id": "612b723970fb1b158c7f8c72",
        "title": "测试消息",
        "content": "消息内容",
        "create_time": "2021-08-29 19:40:41"
      }
    }
  }
```

#### 参数说明
参数名称 | 示例值 | 描述
--- | --- | ---
msg | {} | 消息内容
_id | 612b723970fb1b158c7f8c72 | 消息ID
title | 测试消息 | 标题
content | 测试内容 | 内容
create_time | 2021-08-29 19:40:41 | 创建时间

### 2 未读消息通知
#### 示例
```json
  {
    "event": 2,
    "data": {
      "msg_list": [
        {
          "_id": "612b723970fb1b158c7f8c72",
          "title": "测试消息",
          "content": "消息内容",
          "create_time": "2021-08-29 19:40:41"
        },
        {
          "_id": "612ee429b3f9ce44a5335cd2",
          "title": "测试消息",
          "content": "消息内容",
          "create_time": "2021-09-01 10:23:37"
        }
      ]
    }
}
```

#### 参数说明
参数名称 | 示例值 | 描述
--- | --- | ---
msg_list | [] | 消息列表
_id | 612b723970fb1b158c7f8c72 | 消息ID
title | 测试消息 | 标题
content | 测试内容 | 内容
create_time | 2021-08-29 19:40:41 | 创建时间

### 3 已读消息上报

#### 参数说明
参数名称 | 示例值 | 描述
--- | --- | ---
msg_id | 612b723970fb1b158c7f8c72| 消息ID

#### 示例
```json
{
  "event": 2,
  "data": {
    "msg_id": "612b723970fb1b158c7f8c72"
  }
}
```

## 错误码列表

错误码 | 描述
-- | --
2000 | 未知异常
2001 | 数据解析异常
2002 | 事件不存在