<?php
namespace Wechat\App\Service;

use Wechat\App\Enums\OpCode;
use Wechat\App\Library\ConnectionPool;
use Wechat\App\Library\ConnectionRelationPool;
use Wechat\App\Library\Package;
use Wechat\App\Library\Tools;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;

/**
 * 消息中转: <wechat> <<===>> <Transit> <<===>> <web>
 */
class Transit
{
    /** @var null 浏览器端配置 */
    public static $webListenAddress = null;
    public static $webListenPort = null;
    /** @var string 绑定关系的前缀 */
    protected static $webRelationSuffix = 'web_';
    /** @var Package 如果浏览器先连接上 ws，缓存 Package, 等待微信客户端初始化完成后 再发送 */
    protected static $openWechatPackage = '';

    /** @var null 微信端配置 */
    public static $wechatListenAddress = null;
    public static $wechatListenPort = null;
    /** @var string 绑定关系的前缀 */
    protected static $wechatRelationSuffix = 'wechat_';
    /** @var int 微信端开启个数，每个连接开启的微信个数 */
    protected static $wechatOpenNumber = [];
    /** @var array 微信客户端的APPID与APPKEY */
    protected static $wechatAppId = [];
    protected static $wechatAppKey = [];

    /**
     * 微信器端消息事件
     * @param Package $package
     * @return bool
     */
    public static function wechatMessage(Package $package)
    {
        // 保存微信端的信息
        $opCode = $package->getOpCode();
        switch ($opCode) {
            // ws客户端准备完毕
            case OpCode::OPCODE_READY:
                // 保存APPID和APPKEY
                self::$wechatAppId[$package->getConnection()->id] = $package->getAppId();
                self::$wechatAppKey[$package->getConnection()->id] = $package->getAppKey();
                // 获取微信客户端列表
                $body = $package->getBody();
                if (isset($body['wechatIdList']) && !empty($body['wechatIdList'])) {

                    Tools::log('Transit Wechat wechatIdList: ' . json_encode($body));

                    $wechatIdList = explode(',', $body['wechatIdList']);
                    $loginStatusPackage = clone($package);
                    $connection = $package->getConnection();
                    // 绑定关系
                    foreach ($wechatIdList ?? [] as $wechatId) {
                        $webConnection = ConnectionPool::getRand(self::$webListenPort);
                        // 绑定关系
                        self::bindWechatConnection($webConnection, $wechatId, $connection);
                        // 获取登录状态
                        $loginStatusPackage->setOpCode(OpCode::OPCODE_WECHAT_GET_LOGIN_STATUS);
                        $loginStatusPackage->setWechatId($wechatId);
                        $loginStatusPackage->setBody([]);
                        $loginStatusPackage->send();
                    }
                    // 初始化个数
                    self::$wechatOpenNumber[$package->getConnection()->id] = count($wechatIdList);
                } else {

                    Tools::log('Transit Wechat wechatIdList Empty.');
                    // 初始化个数
                    self::$wechatOpenNumber[$package->getConnection()->id] = 0;


                    if (!empty(self::$openWechatPackage)) {
                        Tools::log('Transit openPackage is not empty, send web data.');

                        $openPackage = self::$openWechatPackage;
                        self::$openWechatPackage = null;

                        self::webMessage($openPackage);

                        $wechatId = $openPackage->getWechatId();
                        $package->setWechatId($wechatId);
                    }
                }

                Tools::log('Transit Wechat wechatOpen Info: '. json_encode(self::$wechatOpenNumber));
                break;
            case OpCode::OPCODE_WECHAT_GET_LOGIN_STATUS:
                Tools::log('Transit Wechat get login STATUS: '. json_encode($package->getBody()));
                $body = $package->getBody();

                // 用户登录成功，添加检测脚本，检测微信是否退出
                // 在微信多开的情况下会有问题
                if (!empty($body['loginStatus'])) {
                    // 每5s检测一次进程
                    $timer_id = Timer::add(5, function() use($package, &$timer_id) {
                        $execString = "ps aux | grep '\\\\WeChat\\\\WeChat.exe' | grep -v grep | wc -l";

                        $processNum  = 0;
                        // 获取当前系统运行的进程数量
                        exec($execString, $processNum);
                        $processNum = $processNum[0];

                        // Tools::log('Timer runing ' . $execString . ', processNum: ' . $processNum);

                        if ($processNum <= 0) {
                            $wechatId = $package->getWechatId();

                            // 如果没有有微信ID 不做处理；如果有微信ID，返回给浏览器端
                            if (empty($wechatId)) {
                                Tools::log('Transit Wechat Message: ' . 'ConnectId=' . $package->getConnection()->id . ', opCode=' . $package->getOpCode() . ', 未获取到微信ID');
                                return true;
                            }

                            // 构造返回给浏览器端的数据
                            $data = [
                                'wechatId' => $wechatId,
                                'opCode' => 146,
                                'body' => [
                                    'isOwner' => 1,
                                    'msgType' => 1,
                                    'msgSource' => 0,
                                    'wxid' => 'filehelper',
                                    'roomId' => '',
                                    'content' => '🤬😱微信客户端异常关闭了，请重新登录',
                                ],
                            ];

                            $json = json_encode($data);
                            // 查找浏览器端的连接
                            $webConnectId = ConnectionRelationPool::getGroupId(self::$webRelationSuffix . $wechatId);
                            if ($webConnectId) {
                                $webConnectId = str_replace(self::$webRelationSuffix, '', $webConnectId);
                                $webConnection = ConnectionPool::get($webConnectId, self::$webListenPort);
                                // 转发数据
                                if ($webConnection) {
                                    $webConnection->send($json);
                                } else {
                                    Tools::log('Transit Wechat Message Error: Not Find Web Client' . 'ConnectId=' . $package->getConnection()->id . ', opCode=' . $package->getOpCode());
                                }
                            }

                            Timer::del($timer_id);
                        }
                    });
                }
                break;
        }
        $wechatId = $package->getWechatId();

        // 如果没有有微信ID 不做处理；如果有微信ID，返回给浏览器端
        if (empty($wechatId)) {
            Tools::log('Transit Wechat Message: ' . 'ConnectId=' . $package->getConnection()->id . ', opCode=' . $package->getOpCode() . ', 未获取到微信ID');
            return true;
        }

        // 构造返回给浏览器端的数据
        $data = [
            'wechatId' => $wechatId,
            'opCode' => $package->getOpCode(),
            'body' => $package->getBody(),
        ];

        if ($opCode == OpCode::OPCODE_MESSAGE_RECEIVE && $data['body']['msgType'] == 3) {
            if ( file_exists("/.dockerenv") || file_exists("/runningIn.docker") ) {
                // Tools::log('Info：PHP run in docker environment');
                // // Dockerfile 已将默认 wxfiles 目录软链接到 /wxFiles
                $imageDatPath = "/wxFiles/" . str_replace('\\', '/', $data['body']['imageFile']);
            } else {
                // Tools::log('Info：PHP run in windows environment');
                // // 获取 windows 用户目录再拼接默认 wxfiles 目录和图片路径
                $imageDatPath = getenv("USERPROFILE", true) . "\\Documents\\WeChat Files\\" . $data['body']['imageFile'];
            }

            $decodeResult = Tools::decodeDatImage($imageDatPath);
            $imageFile = [
                'status' => $decodeResult['status'],
                'code' => $decodeResult['code'],
                'message' => $decodeResult['message'],
                'base64Content' => ""
            ];
            if ($decodeResult['status']) {
                Tools::log("[Info]图片已解密并存放在：" . $decodeResult['filePath']);
                $imageFile['base64Content'] = Tools::bass64EncodeFileWithMime($decodeResult['filePath']);
            }
            $data['body']['imageFile'] = $imageFile;
        }

        $json = json_encode($data);
        // 查找浏览器端的连接
        $webConnectId = ConnectionRelationPool::getGroupId(self::$webRelationSuffix . $wechatId);
        if ($webConnectId) {
            $webConnectId = str_replace(self::$webRelationSuffix, '', $webConnectId);
            $webConnection = ConnectionPool::get($webConnectId, self::$webListenPort);
            // 转发数据
            if ($webConnection) {
                $webConnection->send($json);
            } else {
                Tools::log('Transit Wechat Message Error: Not Find Web Client' . 'ConnectId=' . $package->getConnection()->id . ', opCode=' . $package->getOpCode());
                return false;
            }
        }

        Tools::log('Transit Wechat Message: ' . 'ConnectId=' . $package->getConnection()->id . ', opCode=' . $package->getOpCode());
        return true;
    }

    /**
     * 微信器端连接事件
     * @param TcpConnection $connection
     */
    public static function wechatConnect($connection)
    {
        // 保存连接对象
        ConnectionPool::add($connection, self::$wechatListenPort);
        Tools::log('Transit Wechat Connect: ' . 'ConnectId=' . $connection->id);
    }

    /**
     * 微信端断开连接事件
     * @param TcpConnection $connection
     */
    public static function wechatClose($connection)
    {
        // 删除初始化个数
        if (isset(self::$wechatOpenNumber[$connection->id])) {
            unset(self::$wechatOpenNumber[$connection->id]);
        }
        // 删除APPID和APPKEY
        if (isset(self::$wechatAppId[$connection->id])) {
            unset(self::$wechatAppId[$connection->id]);
        }
        if (isset(self::$wechatAppKey[$connection->id])) {
            unset(self::$wechatAppKey[$connection->id]);
        }
        // 解绑微信端与微信ID关系
        ConnectionRelationPool::removeGroup(self::$wechatRelationSuffix . $connection->id);
        // 删除连接对象
        ConnectionPool::remove($connection, self::$wechatListenPort);
        Tools::log('Transit Wechat Close: ' . 'ConnectId=' . $connection->id);
    }

    /** =============================================================================================================================================== */

    /**
     * 浏览器端消息事件，发送到微信
     * @param Package $package
     * @return bool
     * @throws \ErrorException
     */
    public static function webMessage($package)
    {
        // 绑定关系与解绑关系
        $opCode = $package->getOpCode();
        switch ($opCode) {
            // 新开一个微信
            case OpCode::OPCODE_WECHAT_OPEN:

                if (self::$openWechatPackage ) {
                    return;
                }
                
                // 自动生成一个微信客户端ID
                $wechatId = strtoupper(md5(rand(100000, 999999) . Tools::timestamp() . rand(100000, 999999)));
                $package->setWechatId($wechatId);

                if (empty(self::$wechatOpenNumber)) {
                    Tools::log('Transit Web Relation Not Wechat Online, Cache package: ' . 'ConnectId=' . $package->getConnection()->id);

                    self::$openWechatPackage = $package;
                    return false;
                }
                
                // 绑定关系
                self::bindWechatConnection($package->getConnection(), $wechatId);
                break;
            case OpCode::OPCODE_MESSAGE_SEND_IMAGE:
                $body = $package->getBody();
                $filename = Tools::timestamp();
                $imageUrl = Tools::bass64DecodeFileWithMime($body["base64Content"], $filename);
                unset($body["base64Content"]);
                $body["imageUrl"] = "Z:" . str_replace('/', '\\', $imageUrl);
                $package->setBody($body);
                break;
            case OpCode::OPCODE_MESSAGE_SEND_FILE:
                $body = $package->getBody();
                $filename = explode('.', $body["fileName"], 2);
                $fileUrl = Tools::bass64DecodeFileWithMime($body["base64Content"], $filename[0], "." . $filename[1]);
                unset($body["base64Content"]);
                unset($body["fileName"]);
                $body["fileUrl"] = "Z:" . str_replace('/', '\\', $fileUrl);
                $package->setBody($body);
                break;
        }
        $wechatId = $package->getWechatId();
        // 转发消息
        $wechatConnectId = ConnectionRelationPool::getGroupId(self::$wechatRelationSuffix . $wechatId);
        if (!$wechatConnectId) {
            Tools::log('Transit Web Not WechatConectionId: ' . 'ConnectId=' . $package->getConnection()->id);
            return false;
        }
        // 获取微信端连接对象
        $wechatConnectId = str_replace(self::$wechatRelationSuffix, '', $wechatConnectId);
        $wechatConnection = ConnectionPool::get($wechatConnectId, self::$wechatListenPort);
        if (!$wechatConnection) {
            Tools::log('Transit Web Not WechatConection: ' . 'ConnectId=' . $package->getConnection()->id);
            return false;
        }
        // 发送消息
        $sender = Send::getSender($package->getOpCode());
        if ($sender) {
            $sender->setAppId(self::$wechatAppId[$wechatConnectId] ?? null);
            $sender->setAppKey(self::$wechatAppKey[$wechatConnectId] ?? null);
            $sender->setConnection($wechatConnection);
            $sender->setWechatId($package->getWechatId());
            $sender->setOpCode($package->getOpCode());
            $sender->setBody($package->getBody());
            $sender->send();
            Tools::log('Transit Web Message: ' . 'ConnectId=' . $package->getConnection()->id);
            return true;
        }
        Tools::log('Transit Web Message: ' . 'ConnectId=' . $package->getConnection()->id . ', Invalid Send OpCode.');
        return false;
    }

    /**
     * 浏览器端连接事件
     * @param TcpConnection $connection
     */
    public static function webConnect($connection)
    {
        ConnectionPool::add($connection, self::$webListenPort);
        Tools::log('Transit Web Connect: ' . 'ConnectId=' . $connection->id);
    }

    /**
     * 浏览器端断开连接事件
     * @param TcpConnection $connection
     */
    public static function webClose($connection)
    {
        // 解绑浏览器端与微信ID关系
        ConnectionRelationPool::removeGroup(self::$webRelationSuffix . $connection->id);
        // 删除连接对象
        ConnectionPool::remove($connection, self::$webListenPort);
        Tools::log('Transit Web Close: ' . 'ConnectId=' . $connection->id);
    }

    /**
     * 绑定微信ID与终端关系
     *
     * 微信 ID 对应的 ws 连接
     * "web_08917EFAA5DCD5858EA1D25440D7A989":"web_3",
     * "wechat_08917EFAA5DCD5858EA1D25440D7A989":"wechat_2"
     *
     *
     * @param $webConnection
     * @param $wechatId
     * @param TcpConnection|null $wechatConnection
     */
    protected static function bindWechatConnection($webConnection, $wechatId, $wechatConnection = null)
    {
        // 绑定浏览器端与微信ID的关系
        ConnectionRelationPool::add(self::$webRelationSuffix . $wechatId, self::$webRelationSuffix . $webConnection->id);

        // 绑定微信端与微信ID的关系
        // Tools::getArrayKeyByMinValue(self::$wechatOpenNumber); 获取连接最少的客户端微信连接
        $wechatConnectId = !is_null($wechatConnection) ? $wechatConnection->id : Tools::getArrayKeyByMinValue(self::$wechatOpenNumber);

        if (!empty($wechatConnectId)) {
            ConnectionRelationPool::add(self::$wechatRelationSuffix . $wechatId, self::$wechatRelationSuffix . $wechatConnectId);
        }
    }
}
