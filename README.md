## 项目介绍
Leadshop是一款提供持续更新迭代服务的免费商城系统，旨在打造极致的用户体验！

Leadshop由浙江禾成云计算有限公司研发，主要面向中小型企业，助力搭建电商平台，并提供专业的技术支持。免费提供长期更新服务，应用涉及**可视化DIY拖拽装修** 、促销转化、裂变分销、用户精细化管理、数据分析等多个维度，追求极致体验，全面赋能商家，为商家创造价值。

**Leadshop无偿供大家使用，您的star是我们前进的动力** 

## 主要特性
Leadshop 开源系统，基于Yii2开发，支持composer，优化核心，减少依赖，基于全新的架构思想和命名空间
* 基于命名空间和PSR-4规范，加入PHP7新特性
* 核心功能模块化，方便开发和维护
* 强化路由功能，采用RESTful接口标准
* 灵活创建模型控制器，易于扩展开发
* 配置文件可分离，方便管理
* 重写的自动验证和完成
* 简化扩展机制，提升开发速度
* API支持完善，方便二次开发
* 内置WeChat微信开发框架，微信接入更加快捷，简单
* 使用ORM自动创建表结构，提升开发速度
* 支持数据库结构、数据、模板在线缓存清除，提升用户体验
* 客户端完善的交互效果和动画，提升用户端视觉体验
* 支持在线一键安装，方便快捷。
* 可视化DIY店铺装修，方便、快捷、直观，可以随心所欲装扮自己的店铺
* 高效的笛卡尔乘积运算，8000条规格秒加载
* 拟态Windows文件夹的素材管理结构，操作更熟悉随心

## 运行环境

Linux+Nginx+PHP7.4+MySQL5.7.3以上

## 常见问题/安装教程
<a href="https://forum.leadshop.vip/thread/35" target="_blank"> **查看常见问题/安装教程** </a>

## 下期更新计划
* <a href="https://forum.leadshop.vip/thread/347" target="_blank"> **点击查看更新预告** </a>

 <img border="0" src="https://leadshop-1302407761.cos.ap-shanghai.myqcloud.com/public/attachments/2021/06/08/ywuZAhdyTIXc0KFGURsjR2SSgE3acDtKKgiYHkSa.png">

## 更新历史
* <a href="http://help.leadshop.vip/2278957" target="_blank"> **点击查看往期更新历史** </a>

## 论坛交流
* <a href="https://forum.leadshop.vip" target="_blank"> **点击进入Leadshop官方论坛** </a>

## 体验后台
* <a href="https://demo.leadshop.vip" target="_blank"> **demo.leadshop.vip** </a>

* 账号:18888888888

* 密码:123456

* 扫码关注“企码星球”体验Leadshop公众号+小程序

 <img border="0" src="https://leadshop-1302407761.cos.ap-shanghai.myqcloud.com/public/attachments/2021/05/14/SPpkWJfKviDWHsO7e0Yh6ZUohHX718rc7gzyUlff.jpg">

* 操作文档：<a href="https://help.leadshop.vip" target="_blank"> **help.leadshop.vip** </a>

## 开源无加密
* 基于Apache License 2.0开源协议，前后端代码开源无加密，支持二次开发，支持商用。

## 核心技术
* 前端技术栈 ES6、vue、vuex、vue-router、vue-cli、axios、element-ui、uni-app

* 后端技术栈 Yii2、Jwt、Mysql、Easy-SMS

## 接口标准
* 采用标准RESTful API ，高效的API阅读性，具有扩展性强、结构清晰的特点

## 数据交互
* 采用JSON API 标准，用以定义客户端如何获取与修改资源，以及服务器如何响应对应请求。高效实现的同时，无需牺牲可读性、灵活性和可发现性

## 认证方式
* 目前所有的接口使用 Oauth2 Password 方式，也就是 JWT Authorization Bearer header 进认证。支持扩语言扩展，多平台扩展。

## 数据表格导出
* 采用高性能的 js-xlsx数据导出，易于扩展，兼容性强。

* 接口文档：<a href="http://www.leadshop.vip/api.html" target="_blank"> **www.leadshop.vip/api.html** </a>

## 安装

### 一键安装
1.上传你的代码

2.项目目录设置755权限

3.设置代码执行目录为/web

注:如果使用Apache环境 需要在.htaccess 中添加
```
 SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```
否则会导致OAuth登录模式获取不到Authorization

4.检查php禁用函数列表,symlink函数不能被禁用，否则后台页面无法访问。

5.在浏览器中输入你的域名或IP

( 例如：www.yourdomain.com)，安装程序会自动执行安装。期间系统会提醒你输入数据库信息以完成安装。

6.后台访问地址：
域名/index.php?r=admin

7.公众号首页访问地址：
域名/index.php?r=wechat

### 重新安装
1. 清除数据库
2. 删除/server/install.lock 文件

## 目录说明
```
 站点根目录
    ├─api                               //后台接口
    ├─app                               //客户端（微信小程序，公众号）
    ├─applet                            //打包后的微信小程序源码包
    ├─components                        //通用组件
    ├─config                            //配置文件目录
    ├─controllers                       //控制器
    ├─datamodel                         //模型
    ├─forms                             
    │  └─install
    ├─modules                           //模块     
    ├─stores                            //应用配置文件
    ├─system                            //系统核心目录
    │  ├─common
    │  ├─config
    │  ├─phpqrcode
    │  └─wechat
    ├─vendor                            //依赖
    ├─views
    │  ├─admin                          //后台编译包目录，其中index.php不可删除
    │  ├─wechat                         //公众号编译包目录，其中index.php不可删除
    │  └─site
    └─web
        ├─assets
        ├─static                        //静态文件
        ├─img                           //H5端图片目录
        ├─temp
        └─upload                        //上传文件
```

## Leadshop日常交流
* 官方QQ群：
    * <a target="_blank" href="https://qm.qq.com/cgi-bin/qm/qr?k=CDXQbtOCLpQcSEbE6S3DRPCYapvKI_QA&jump_from=webapi"><img border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="Leadshop开源商城①群" title="Leadshop开源商城①群"></a>：849894135
    * <a target="_blank" href="https://qm.qq.com/cgi-bin/qm/qr?k=vYNXwlsuRNAx_Ug3-j8dEbP1j1JKKnMF&jump_from=webapi"><img border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="Leadshop开源商城②群" title="Leadshop开源商城②群"></a>：422518408


* 加群管理员进Leadshop官方交流群

 <img border="0" src="https://leadshop-1302407761.cos.ap-shanghai.myqcloud.com/public/attachments/2021/06/03/CGAUS3jcr3K3BfQYwnzalpR6OvWkjDcrc9hC2wUy.png">

## Ladshop功能简介
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/1.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210608/2.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/3.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/4.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/5.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/6.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/7.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/8.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/9.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/10.png)
![展示图](https://qmxq.oss-cn-hangzhou.aliyuncs.com/readme/20210522/11.png)

## 使用须知

1.允许用于个人学习、毕业设计、教学案例、公益事业;

2.支持企业/个人免费商业使用，但必须保留leadshop版权信息;

## 版权信息

Powered By Leadshop © 2021