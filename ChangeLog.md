
# Change Log

TODO: 
> 1. 完善 API 文档；
> 2. 找出 `Run Time Check Failure #‘2 Stack around the variable'*wWxid' was corrupted.` 报错的原因
> 3. 完善收发文件 API ，发图片 API ；查看能否支持语音
> 4. 尝试解决微信客户端不自动下载图片的问题

## v0.2.3
1. 实现发文件 API ，发图片 API

## v0.2.2
1. Web 使用 websocket 连接至 php 时需要使用简单的鉴权

## v0.2.1
1. 接收图片后会尝试自动获取(默认获取5次)并解密图片然后以 base64 发至 web 端

## v0.1.2
1. 使用 dll 读写内存修改微信版本号（放弃 memscan 方案，最后采用读取环境变量的方案）
2. 调整默认 dpi 为 100%
3. 合并上层仓库优化解决内存泄漏的代码
4. 将 c++ 源码部分文件编码统一为 utf-8

## v0.1.1
1. 优化 Dockerfile 
2. 移除不必要的文件

## v0.1
1. WechatPc 项目 Docker 化
2. memscan 自动修改微信版本号，绕过版本过旧提示
