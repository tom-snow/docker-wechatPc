# Change Log

## v0.2
1. 接收图片后会尝试自动获取(默认获取5次)并解密图片然后以 base64 发至 web 端
TODO: 
> 1. 完善 API 文档；
> 2. 找出 `Run Time Check Failure #‘2 Stack around the variable'*wWxid' was corrupted.` 报错的原因

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
