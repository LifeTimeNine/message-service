HTTP的接口主要用来管理消息和用户信息

## 接口认证

在访问这些接口的时候需要在 Header 中额外加入两个参数（timestamp，sign）

timestamp：生成签名时的秒级时间戳
sign：最终生成的签名

### 签名生成
签名串一共有5行，行尾以\n结束，包括最后一行。
```
  请求的URL\n
  请求方法\n
  请求体\n
  时间戳\n
  系统秘钥的MD5字符串\n
```
第一步，获取请求URL，不包含域名，如果有Query参数，也要包含Query参数。
```
  /message
```
第二步，获取请求方法，（GET，POST，DELETE）等
```
  GET
```
第三步，请求体如果没有请求体时，请使用 `{}`, 如果 content-type 为 applocation\json等时，请使用真实的请求体，然后将请求体转JSON格式。
```
  {}
```
第四步，获取发起请求时的系统当前时间戳，即格林威治时间1970年01月01日00时00分00秒(北京时间1970年01月01日08时00分00秒)起至现在的总秒数，作为请求时间戳。系统会拒绝5秒以前发起的请求，请保持自身系统的时间准确。
```
  1630464641
```
第五步，系统秘钥的MD5字符串，
```
  5e50b447820dea06567ed12ed4aab0ec
```
第六步，按照规则构建的签名串为：
```
  /message\n
  GET\n
  {}\n
  1630464641\n
  5e50b447820dea06567ed12ed4aab0ec\n
```
第七步，对签名串进行 base64 编码
```
  L21lc3NhZ2UKICBHRVQKICB7fVxuCiAgMTYzMDQ2NDY0MQogIDVlNTBiNDQ3ODIwZGVhMDY1NjdlZDEyZWQ0YWFiMGVjCg==
```

## 用户

### 用户列表
分页获取用户列表  

#### 基本信息
**请求URL:** http://{{host}}/user  
**请求方式:** GET
**Content-Type:** none

#### 请求参数  
参数名 | 示例值 | 参数描述
--- | --- | ---
page | 1 | 分页 当前页数
list_rows | 10 | 分页 每页记录数

#### 请求示例
```
http://192.168.31.200:9501/user?page=1&list_rows=10
```
#### 返回参数
参数名 | 示例值 | 参数描述
--- | --- | ---
uid | user_1 | 用户唯一标识
tag | ["tag1"] | 标签列表
last_connect_time | 2021-08-29 20:21:06 | 最后连接时间
create_time | 2021-08-29 20:11:37 | 接入时间
online | true | 当前是否在线
#### 响应示例
```json
{
	"code": 0,
	"msg": "SUCCESS",
	"content": {
		"page": {
			"page": 1,
			"list_rows": 10,
			"count": 1,
			"list": [
				{
					"uid": "user_1",
					"tag": [
						"tag1"
					],
					"last_connect_time": "2021-08-29 20:21:06",
					"create_time": "2021-08-29 20:11:37",
					"online": true
				}
			]
		}
	}
}
```

### 用户详情
获取用户的详情信息

#### 基本信息
**请求URL:** http://{{host}}/user/:uid
**请求方式:** GET
**Content-Type:** none

#### 请求参数  
无

#### 请求示例
```
http://192.168.31.200:9501/user/user_1
```

#### 返回参数
参数名 | 示例值 | 参数描述
--- | --- | ---
uid | user_1 | 用户唯一标识
tag | ["tag1"] | 标签列表
last_connect_time | 2021-08-29 20:21:06 | 最后连接时间
create_time | 2021-08-29 20:11:37 | 接入时间
online | true | 当前是否在线
read_message_count | 0 | 已读消息数量

#### 响应示例
```json
{
	"code": 0,
	"msg": "SUCCESS",
	"content": {
		"data": {
			"uid": "user_1",
			"tag": [
				"tag1"
			],
			"last_connect_time": "2021-08-29 20:21:06",
			"create_time": "2021-08-29 20:11:37",
			"online": true,
			"read_message_count": 1
		}
	}
}
```
## 标签

### 添加标签
给一批用户添加一个标签

#### 基本信息
**请求URL:** http://{{host}}/tag
**请求方式:** POST
**Content-type:** application/json

#### 请求参数
参数名 | 示例值 | 参数描述
--- | --- | ---
tag_name | tag_1 | 标签名称
uids | ["user_1"] | 用户标识列表

#### 请求示例
```
  http://192.168.31.200:9501/tag
  {
    "tag_name": "tag1",
    "uids": [
      "user_1"
    ]
  }
```

#### 返回参数
无

#### 响应示例
```json
  {
    "code": 0,
    "msg": "SUCCESS",
    "content": null
  }
```

### 删除标签
给一批用户删除一个标签

#### 基本信息
**请求URL:** http://{{host}}/tag
**请求方式:** DELETE
**Content-type:** application/json

#### 请求参数
参数名 | 示例值 | 参数描述
--- | --- | ---
tag_name | tag_1 | 标签名称
uids | ["user_1"] | 用户标识列表

#### 请求示例
```
  http://192.168.31.200:9501/tag
  {
    "tag_name": "tag1",
    "uids": [
      "user_1"
    ]
  }
```

#### 返回参数
无

#### 响应示例
```json
  {
    "code": 0,
    "msg": "SUCCESS",
    "content": null
  }
```

### 获取某个用户的标签
获取某个用户的所有标签

#### 基本信息
**请求URL:** http://{{host}}/tag/:uid
**请求方式:** GET
**Content-type:** none

#### 请求参数
无

#### 请求示例
```
  http://192.168.31.200:9501/tag/user_1
```

#### 返回参数
参数名 | 示例值 | 参数描述
--- | --- | ---
list | ["tag1"] | 标签列表

#### 响应示例
```json
  {
    "code": 0,
    "msg": "SUCCESS",
    "content": {
      "list": [
        "tag1"
      ]
    }
  }
```

## 消息

### 创建消息
创建一个消息

#### 基本信息
**请求URL:** http://{{host}}/message
**请求方式:** POST
**Content-type:** application/json

#### 请求参数
参数名 | 示例值 | 参数描述
--- | --- | ---
type | 1 | 类型 1-持久性 2-即时型 3-延时型
black_uids | [] | 黑名单用户标识列表
uids | ["user_1"] | 指定用户标识
tag | ["tag1"] | 指定标签列表
title | 这是一个标题 | 消息标题
content | 这是内容 | 消息内容
delay | 0 | 延时时间（秒），类型为延时型时，此参数必须大于0

#### 请求示例
```
  http://192.168.31.200:9501/message
  {
    "black_uids": [],
    "content": "消息内容",
    "delay": 0,
    "tag": [
      "tag1"
    ],
    "title": "测试消息",
    "type": 1,
    "uids": [
      "user_1"
    ]
  }
```

#### 返回参数
无

#### 响应示例
```json
  {
    "code": 0,
    "msg": "SUCCESS",
    "content": null
  }
```

### 消息列表 
分页获取消息列表

#### 基本信息
**请求URL:** http://{{host}}/message
**请求方式:** GET
**Content-type:** none

#### 请求参数
参数名 | 示例值 | 参数描述
--- | --- | ---
page | 1 | 分页 当前页数
list_rows| 10 | 分页 每页记录数

#### 请求示例
```
   http://192.168.31.200:9501/message?page=1&list_rows=10
```

#### 返回参数
参数名 | 示例值 | 参数描述
--- | --- | ---
_id | 612b72901124f36ac66062c2 | 消息ID
type | 1 | 类型 1-持久性 2-即时型 3-延时型
black_uids | [] | 黑名单用户标识列表
uids | ["user_1"] | 指定用户标识
tag | ["tag1"] | 指定标签列表
title | 这是一个标题 | 消息标题
content | 这是内容 | 消息内容
delay | 0 | 延时时间（秒）
read_num | 0 | 已读人数
create_time | 2021-08-29 19:42:08 | 消息创建时间

#### 响应示例
```json
{
	"code": 0,
	"msg": "SUCCESS",
	"content": {
		"page": {
			"page": 1,
			"list_rows": 10,
			"count": 3,
			"list": [
				{
					"_id": "612b72901124f36ac66062c2",
					"type": 1,
					"title": "测试消息",
					"content": "消息内容",
					"tag": [
						"tag1"
					],
					"uids": [
						"user_1"
					],
					"black_uids": [],
					"delay": 0,
					"read_num": 0,
					"create_time": "2021-08-29 19:42:08"
				},
				{
					"_id": "612b723970fb1b158c7f8c72",
					"type": 1,
					"title": "测试消息",
					"content": "消息内容",
					"tag": [
						"tag1"
					],
					"uids": [
						"user_1"
					],
					"black_uids": [],
					"delay": 0,
					"read_num": 0,
					"create_time": "2021-08-29 19:40:41"
				}
			]
		}
	}
}
```

### 消息详情
获取一个消息的详情信息

#### 基本信息
**请求URL:** http://{{host}}/message/:mesage_id
**请求方式:** GET
**Content-type:** none

#### 请求参数
无

#### 请求示例
```
  http://192.168.31.200:9501/message/612ee429b3f9ce44a5335cd2
```

#### 返回参数
参数名 | 示例值 | 参数描述
--- | --- | ---
_id | 612b72901124f36ac66062c2 | 消息ID
type | 1 | 类型 1-持久性 2-即时型 3-延时型
black_uids | [] | 黑名单用户标识列表
uids | ["user_1"] | 指定用户标识
tag | ["tag1"] | 指定标签列表
title | 这是一个标题 | 消息标题
content | 这是内容 | 消息内容
delay | 0 | 延时时间（秒）
read_num | 0 | 已读人数
create_time | 2021-08-29 19:42:08 | 消息创建时间

#### 返回示例
```json
{
	"code": 0,
	"msg": "SUCCESS",
	"content": {
		"data": {
			"_id": "612ee429b3f9ce44a5335cd2",
			"type": 1,
			"title": "测试消息",
			"content": "消息内容",
			"tag": [
				"tag1"
			],
			"uids": [
				"user_1"
			],
			"black_uids": [],
			"delay": 0,
			"read_num": 0,
			"create_time": "2021-09-01 10:23:37"
		}
	}
}
```

### 删除消息
删除一个消息，同时也会删除用户的已读记录

#### 基本信息
**请求URL:** http://{{host}}/message/:mesage_id
**请求方式:** DELETE
**Content-type:** none

#### 请求参数
无

#### 请求示例
```
  http://192.168.31.200:9501/message/612ee429b3f9ce44a5335cd2
```

#### 返回参数
无

#### 响应示例
```json
  {
    "code": 0,
    "msg": "SUCCESS",
    "content": null
  }
```

## 错误码列表
错误码 | 描述
-- | --
1000 | 未知错误
1001 | 签名验证失败
1002 | 参数解析失败或不合法
1003 | 资源不存在