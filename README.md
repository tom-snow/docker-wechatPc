# Linux docker wechat hook镜像
[![dockeri.co](https://dockeri.co/image/endokai/wechatpchook)](https://hub.docker.com/r/endokai/wechatpchook/tags)

在 Linux 下可一键启动的 Wechat Hook 镜像，仅供学习交流，严禁用于商业用途，请于24小时内删除。

项目的 Docker 镜像基于：[DoChat](https://github.com/huan/docker-wechat) ，Hook程序基于：[wechatPc](https://github.com/chengciming/wechatPc)，十分感谢两位作者。

## 镜像启动的组件：
1. PHP7.2 运行 ServerPhp 目录下的 Websocket 接口服务器，端口 5678
2. tigerVNC 服务器用于提供图形显示，端口 5905
3. Wine 环境运行 Hook 程序
4. Wine 环境运行微信程序

## 运行镜像：
<pre>
docker run \
  --name wechathook  \
  -p 127.0.0.1:5905:5905 \
  -p 127.0.0.1:5678:5678 \
  -e VNCPASS=asdfgh123 \
  -e APP_ID=CD7160A983DD8A288A56BAA078781FCA \
  -e APP_KEY=F2B283D51B3F4A1A4ECCB7A3621E7740 \
  -e PHPDEBUG=true \
  -e WECHAT_DEST_VERSION=3.3.0.115 \
  -dti  \
  --ipc=host \
  -v ~/DoChat/WeChat\ Files/:'/home/user/WeChat Files/'  \
  -v ~/DoChat/Application\ Data:'/home/user/.wine/drive_c/users/user/Application Data/' \
  --privileged \
  endokai/wechatpchook
</pre>
### 参数说明
* 端口：
  * 5905: VNC 的端口
  * 5678: Websocket API 的通信端口

* 挂载：
  * 这两个目录是微信的数据目录，继承自 DoChat 镜像。

* 环境变量：
  * VNCPASS: 设置 VNC 的密码
  * APP_ID和APP_KEY: 设置 wechatPc 的密钥
  * WECHAT_DEST_VERSION: 微信自定义版本号，默认 3.3.0.115。（伪装微信版本所用）
  * PHPDEBUG: 日志详细开关，默认打开，设置为 false 可关闭。

## 使用说明：
### API使用方法：
API 使用方法参见 Web 文件夹下的 [websocket.js](https://github.com/endokai/docker-wechatPc/blob/master/Web/common/js/websocket.js) 文件，配合查看 [WechatOffset.h](https://github.com/endokai/docker-wechatPc/blob/master/WechatDll/WechatDll/WechatOffset.h)

### Web端使用方法：
wechatPc 提供一个简易的 Web 端。下载此项目，打开 Web 文件夹。（或者使用 [DownGit](https://minhaskamal.github.io/DownGit/#/home) 仅下载 Web 目录）

修改 Web/common/js/websocket.js 文件靠后面的一个 Websocket 连接地址，改为 API 端口地址，进入 Web 目录下，使用浏览器打开 index.html 即可使用。
          
          
-------
-------
-------

以下是 wechatPc 项目的原说明
-------
> <h1 align="left">PC端微信Hook源码</h1>
> 
> # PC微信hook源码，仅供学习，请不要用于商业、违法途径，本人不对此源码造成的违法负责！
> 
> ## 说明
> 请看完下面每一步说明与操作，可以进群咨询。
> 
> ## 技术学习与交流群
> 1. QQ群：147694968
> 2. 说明：此群仅限学习和交流，无其他用处
> 
> ## 目录说明
> 1. WechatSetup：微信安装包，也可到官网下载对应版本安装包。
> 2. WechatRobot：Hook端主程序源码目录，C++程序，编译后放到Bin目录下运行。
> 3. WechatDll：注入库源码，C++程序，编译后放到Bin目录下运行。
> 4. ServerPhp：服务端源码，PHP程序，wokerman框架v3.5，相关框架资料可以从官网查看。
> 5. Web：web端源码，HTML5程序。
> 6. Bin：Hook端和注入库编译后的存放与运行目录。
> 7. PhpBin：PHP Windows运行环境，如果不会PHP安装的可以直接使用这个。
> 
> ## 程序说明
> 1. Hook原理：exe注入DLL到微信进程，实现对微信的消息拦截、方法调用。
> 2. 程序语言：exe和DLL库是C++编写，服务端是PHP的workman框架编写，web端是HTML5编写。
> 3. 限制：此程序仅限PC端微信2.8.0.121版本，需要其他版本则需要更新Hook偏移量。
> 4. 升级：微信2.8.0.121升级到其他版本，可以借助《微信基址-2.8.0.121.txt》里面的特征码进行OD捕获偏移量。
> 5. 实现的功能：[点击这里查看](https://github.com/chengciming/wechatPc/blob/master/WechatDll/WechatDll/WechatOffset.h)
> 
> ## 程序语言版本
> 1. C++是基于VS2017开发，SDK版本：10.0.17763.0，工具集：v141，MFC：标准Window库
> 2. PHP限制版本看workman官方v3.5版本限制，建议PHP7以上
> 
> ## 环境
> 1. 客户端：windows
> 2. 服务端：windows or linux --> for PHP
> 3. 通讯机制：WebSocket
> 4. 通讯流程：WechatDll(注入到微信) <--> [WS] <--> WechatRobot(操作界面) <--> [WS] <--> ServerPhp(服务端) <--> [WS] <--> Web(HTML操作界面)
> 
> ## 配置
> 
> ### 服务端配置(ServerPhp)
> 1. 配置文件：wechatPc/ServerPhp/Config/Config.php
> 2. debug项为调试开关，开启将打印消息到屏幕
> 3. listen项为Hook端通讯监听地址和端口，Hook端即为WechatRobot
> 4. web_listen项为web端通讯监听端口
> 5. worker项则为workman框架的配置，可以看官网解说
> ### Hook端配置(WechatRobot --> Bin)
> 1. 配置文件：wechatPc/Bin/Debug/Config.txt
> 2. app_id、app_key为认证授权使用，未认证的不可通讯
> 3. wesocket_server_address和wesocket_server_port为服务端通讯的IP和端口，对应服务端配置listen项，服务端即ServerPhp
> ### web端配置(Web)
> 1. 配置文件：
> 2. 文件最后面找到：ws = new WebSocket("ws://127.0.0.1:5678");
> 3. 将127.0.0.1:5678改为服务端IP和端口，对应服务端配置web_listen项，服务端即ServerPhp
> 
> ## 启动
> 
> ### 服务端启动
> ```shell
> E:\git\wechatPc>
> E:\git\wechatPc>cd ./ServerPhp
> E:\git\ServerPhp>composer install  // 此操作是安装workman包(安装一次即可)，不会安装composer的同学到群里下载vendor目录放进去
> E:\git\ServerPhp>E:\git\wechatPc\PhpBin\php index.php start
> ----------------------- WORKERMAN -----------------------------
> Workerman version:3.5.31          PHP version:7.2.25
> ------------------------ WORKERS -------------------------------
> worker               listen                              processes status
> Server:Wechat        websocket://0.0.0.0:8686            4         [ok]
> [PID:1640 | 2020-09-06 08:53:28.985] Worker Started.
> [PID:1640 | 2020-09-06 08:53:29.432] Transit Web Connect: ConnectId=1
> [PID:1640 | 2020-09-06 08:53:31.056] Transit Wechat Connect: ConnectId=2
> ```
> ### web端启动
> ```shell
> 1. 直接浏览器打开访问index.html
> 2. 打开后点击左下角图标，弹窗里面点击“新开一个”，exe将自动启动微信并且注入
> ```
> ### Hook端启动
> ```shell
> 1. 环境：window环境下
> 2. 操作：点击运行wechatPc/Bin/Debug/WechatRobot.exe文件
> ```
> 
> ### 使用说明
> 1. exe和dll的操作界面是开发调试使用，非开发情况下不要操作。
> 2. 使用web端操作，包括启动微信。
> 
> ## 大佬，打个赏吧
> ![微信打赏二维码](https://github.com/chengciming/wechatPc/blob/master/images/微信打赏二维码.jpg)
> ![支付宝打赏二维码](https://github.com/chengciming/wechatPc/blob/master/images/支付宝打赏二维码.jpg)
