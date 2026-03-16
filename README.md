# Simple PHP CMS

一个简单的 PHP + MySQL CMS 示例项目，用于个人学习与研究 PHP 网站开发。

该项目包含：

- 简单的产品展示 CMS
- 后台管理系统
- SEO 友好 URL
- HTML 静态缓存支持
- Bootstrap 前端模板

------------------------------------------------------------------------

# 免责声明

本项目 **仅用于个人学习和研究目的**。

前端页面基于以下模板修改：

https://bootstrapmade.com/flexor-free-multipurpose-bootstrap-template/

该模板属于 **BootstrapMade**，请遵守其授权协议。

**禁止将本项目用于商业用途。**

如果需要商业使用，请自行购买或获取模板的商业授权。

------------------------------------------------------------------------

# 系统环境要求

请自行配置 **LNMP 环境**。

推荐环境：

Linux\
Nginx\
MySQL 5.7+\
PHP 8.0+

PHP 需要启用扩展：

PDO\
pdo_mysql\
mbstring\
json\
fileinfo

------------------------------------------------------------------------

# 安装方法

## 1 上传程序

将项目上传到网站目录，例如：

/www/wwwroot/yourdomain.com/

------------------------------------------------------------------------

## 2 创建数据库

在服务器创建一个 MySQL 数据库，例如：

cms

------------------------------------------------------------------------

## 3 访问安装程序

打开浏览器访问：

http://你的域名/install/install.php

填写：

- 数据库地址
- 数据库名称
- 数据库用户名
- 数据库密码
- 管理员账号
- 管理员密码

安装程序会自动：

- 导入数据库结构
- 写入 core.php
- 创建管理员账号

------------------------------------------------------------------------

## 4 删除安装目录

安装完成后请删除：

/install/

------------------------------------------------------------------------

# URL 重写（Nginx）

本系统需要开启 **URL Rewrite（伪静态）**。

如果使用 **宝塔面板**，可以在 **网站 → 伪静态** 中添加以下规则：

    # =========================
    # HTML 静态缓存优先规则
    # =========================
    
    # 首页
    location = /index.html {
        try_files /cache/index.html /index.php?$args;
    }
    
    location = / {
        try_files /cache/index.html /index.php?$args;
    }
    
    # FAQ
    location = /faq.html {
        try_files /cache/faq.html /faq.php?$args;
    }
    
    # About
    location = /about.html {
        try_files /cache/about.html /about.php?$args;
    }
    
    # Contact
    location = /contact.html {
        try_files /cache/contact.html /contact.php?$args;
    }
    
    # /products_list/index.html → products_list.php
    rewrite ^/products_list/index\.html$ /products_list.php last;
    
    # /products_list/Barbed.html → products_list.php?category=xxx
    rewrite ^/products_list/([A-Za-z0-9\-_]+)\.html$ /products_list.php?category=$1 last;
    
    # 产品详情（/products/xxx.html）
    rewrite ^/products/([^/]+)\.html$ /products.php?slug=$1&__static=1 last;

------------------------------------------------------------------------

# 后台登录

后台地址：

http://你的域名/admin/

使用安装时创建的管理员账号登录。

------------------------------------------------------------------------

# 目录结构

/admin 后台管理\
/install 安装程序\
/cache HTML缓存\
/uploads 上传文件\
/core.php 系统核心配置

------------------------------------------------------------------------

# 说明

本项目为 **学习用途示例 CMS**：

- 代码结构尽量保持简单
- 适合学习 PHP CMS 开发
- 不建议直接用于生产环境

------------------------------------------------------------------------

# License

本项目仅用于 **学习用途**。

禁止商业使用。

前端模板版权归 **BootstrapMade** 所有。
