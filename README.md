## 项目概述

本项目是基于 Laravel 7.x 框架开发而成的简易商城系统。

实现了以下功能：
* 用户中心（设置收货地址、我的订单、我的收藏、我的购物车）
* 商品展示
* 商品收藏
* 购物车
* 生成订单功能
* 支付功能
* 退款功能
* 商品评价功能
* 优惠券功能

## 项目依赖环境

依赖应用尽量使用最新稳定版本
* PHP7.2+
* MySQL5.7
* Redis5.0
* Composer1.10
* node12.18.4+
* yarn1.22+

## 安装部署

首先将本项目克隆至本地开发目录或服务器部署目录上。

然后安装扩展包：
```sh
$ composer install
```

安装完扩展包后配置项目环境变量，修改 `.env` 配置文件。如不存在 `.env` 配置文件则复制 `.env.example` 文件为 `.env` 并修改配置。
```
# 项目名称配置
APP_NAME="Laravel Shop"
# 项目环境标识配置，在正式环境中应当配置为 production
APP_ENV=local
# 应用秘钥
APP_KEY=
# 调试环境配置，在正式环境中应该配置为 false
APP_DEBUG=true
# 项目域名配置
APP_URL=

LOG_CHANNEL=stack

# 数据库类型
DB_CONNECTION=mysql
# 数据库访问地址
DB_HOST=127.0.0.1
# 数据库访问端口
DB_PORT=3306
# 数据库名
DB_DATABASE=laravel_shop
# 访问数据库用户名
DB_USERNAME=root
# 访问数据库用户密码
DB_PASSWORD=root

BROADCAST_DRIVER=log
# 缓存类型
CACHE_DRIVER=redis
# 队列连接方式
QUEUE_CONNECTION=redis
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Redis 访问地址
REDIS_HOST=127.0.0.1
# Redis 访问密码
REDIS_PASSWORD=null
# Redis 访问端口
REDIS_PORT=6379

# 使用支持 ESMTP 的 SMTP 服务器发送邮件
MAIL_MAILER=smtp
# 邮箱的 SMTP 服务器地址
MAIL_HOST=smtp.163.com
# SMTP 服务器端口
MAIL_PORT=25
# 邮箱的 SMTP 服务器地址
MAIL_USERNAME=xxxxxxxx@163.com
# 授权码
MAIL_PASSWORD=
# 加密类型
MAIL_ENCRYPTION=tls
# 此值必须同 MAIL_USERNAME 一致
MAIL_FROM_ADDRESS=xxxxxxxx@163.com
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# 支付宝支付配置
ALIPAY_APP_ID=
ALIPAY_PUBLIC_KEY=
ALIPAY_PRIVATE_KEY=

# 微信支付配置
WECHAT_APP_ID=
WECHAT_MCH_ID=
WECHAT_KEY=
```
>注意：微信支付下载的证书需存放在 `resources/wechat_pay` 目录下。

将环境变量配置好后执行数据迁移
```sh
php artisan migrate
```

生成应用秘钥
```sh
$ php artisan key:generate
```

然后数据填充【可选】
```sh
php artisan db:seed
```

创建上传文件目录的软链接
```sh
$ php artisan storage:link
```

编译前端资源
```sh
# 安装依赖
$ SASS_BINARY_SITE=http://npm.taobao.org/mirrors/node-sass yarn
# 编译前端资源，编译完成后使用 Ctrl + C 中断即可
$ npm run watch
```

启动队列处理器
```sh
$ php artisan queue:work
```

如果是正式环境则执行以下步骤优化其性能。  
生成配置缓存
```sh
$ php artisan config:cache
```

生成路由缓存
```sh
$ php artisan route:cache
```

类映射加载优化
```sh
$ php artisan optimize --force
```

## 依赖扩展

| 扩展依赖名称          | 说明           |
| --------------------- | -------------- |
| encore/laravel-admin  | 后台 UI 扩展   |
| yansongda/pay         | 支付扩展       |
| endroid/qr-code       | 二维码生成扩展 |
| overtrue/laravel-lang | 语言包扩展     |
| jxlwqq/quill          | 编辑器扩展     |