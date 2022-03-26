# Linux docker wechat hook镜像
[![dockeri.co](https://dockeri.co/image/endokai/wechatpchook)](https://hub.docker.com/r/endokai/wechatpchook/tags)

在 Linux 下可一键启动的 Wechat Hook 镜像，仅供学习交流，严禁用于商业用途，请于24小时内删除。

项目的 Docker 镜像基于：[DoChat](https://github.com/huan/docker-wechat) ，Hook程序基于：[wechatPc](https://github.com/chengciming/wechatPc)，十分感谢两位作者。

## Change Log
[v0.2.3 实现发文件 API ，发图片 API](./ChangeLog.md#v023)

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
  -e APP_ID=1234567890ABCDEFGHIJKLMNOPQRSTUV \
  -e APP_KEY=1234567890ABCDEFGHIJKLMNOPQRSTUV \
  -e PHPDEBUG=true \
  -e PHPLOG_MAX_LENGTH=0 \
  -e WECHAT_DEST_VERSION=3.3.0.115 \
  -dti  \
  --ipc=host \
  -v ~/DoChat/WeChat\ Files/:'/home/user/WeChat Files/'  \
  -v ~/DoChat/Application\ Data:'/home/user/.wine/drive_c/users/user/Application Data/' \
  --privileged \
  endokai/wechatpchook
</pre>

或者使用 [docker-compose](https://github.com/tom-snow/docker-wechatPc/blob/master/docker-compose.yml)

### 参数说明
* 端口：
  * 5905: VNC 的端口
  * 5678: Websocket API 的通信端口

* 挂载：
  * 这两个目录是微信的数据目录，继承自 DoChat 镜像。

* 环境变量：
  * VNCPASS: 设置 VNC 的密码【若在互联网上公开了 vnc 端口，请自行设置强秘码】
  * APP_ID 和 APP_KEY: 设置 wechatPc 的密钥【自 [v0.2.2](./ChangeLog.md#v022) 起，必须 docker 端与 web 端设置相同的 APP_ID 和 APP_KEY 】
  * WECHAT_DEST_VERSION: 微信自定义版本号，默认 3.3.0.115。（伪装微信版本所用，必须是 a.b.c.d 格式的版本号）【可参考 [wechat-windows-versions](https://github.com/tom-snow/wechat-windows-versions/releases) 】
  * PHPDEBUG: 日志详细开关，默认打开，设置为 false 可关闭。
  * PHPLOG_MAX_LENGTH: PHP 单条日志最大长度(对使用 `Tools::log();` 方法打印的日志全局生效)，小于等于 0 为不限制。

## 使用说明：
### API使用方法：
API 使用方法参见 Web 文件夹下的 [websocket.js](https://github.com/endokai/docker-wechatPc/blob/master/Web/common/js/websocket.js) 文件，配合查看 [WechatOffset.h](https://github.com/endokai/docker-wechatPc/blob/master/WechatDll/WechatDll/WechatOffset.h)

### Web端使用方法(此 Web 端仅供测试【接口演示】)：
wechatPc 提供一个简易的 Web 端。下载此项目，打开 Web 文件夹。(或者使用 [DownGit](https://minhaskamal.github.io/DownGit/#/home) 仅下载 Web 目录)

修改 Web/common/js/websocket.js 文件靠后面的一个 Websocket 连接地址为程序能够访问到的 API 端口地址，进入 Web 目录下，使用浏览器打开 index.html 即可使用。
          
          
## 其他说明
1. 使用过程中日志或 VNC 屏幕显示的 **Web 端** 均是广义的 Web 端(通过 websocket 连接到 php 服务端)，例如 [efb-wechat-pc-slave](https://github.com/Tedrolin/efb-wechat-pc-slave) 中的 python 端。

2. 若服务端(docker端)与 Web端 在不同的主机上(通常是 docker 端主机性能较差，同时运行 docker 与 python 会吃不消)，请参考 [websocket + ssl](https://www.workerman.net/doc/workerman/faq/secure-websocket-server.html) 将 ws 升级为 wss 以提升安全性【以单纯的 ws 在网络中明文传输机密数据是非常不明智的】。

   你需要为此准备一个域名及域名对应的 ssl 证书(请自行研究)。

   如果你打算采用 [直接用Workerman开启SSL](https://www.workerman.net/doc/workerman/faq/secure-websocket-server.html#方法一%20，直接用Workerman开启SSL) 的方式开启 ssl ，你只需要修改 [/ServerPhp/App/Service/Listener/WorkerStart.php](https://github.com/tom-snow/docker-wechatPc/blob/66f4832be94d9917647a1c13c740e62e46faeb95/ServerPhp/App/Service/Listener/WorkerStart.php#L40) 文件。

3. 此项目当前尚不完善，且很多代码并不规范，各位还请海涵。同时无偿招募大佬优化代码(不要吝啬你的 PR )。亦可加入 [TG群](https://t.me/+bHJc6QsHG1xmYTdh) 进行交流。
