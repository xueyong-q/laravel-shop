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

## 安装部署

首先将本项目克隆至本地开发目录或服务器部署目录上。

然后安装扩展包：
```sh
$ composer install
```

安装完扩展包后配置项目环境变量，修改 `.env` 配置文件。如不存在 `.env` 配置文件则复制 `.env.example` 文件为 `.env` 并修改配置。

将环境变量配置好后执行数据迁移：
```sh
php artisan migrate
```

然后数据填充【可选】：
```sh
php artisan db:seed
```

创建上传文件目录的软链接
```sh
$ php artisan storage:link
```

编译前端资源
```sh
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