<?php
/**
 * 公共工具类
 */
namespace Wechat\App\Library;

use Workerman\Worker;

class Tools {
    /**
     * 缓存配置
     * @var array
     */
    protected static $config = array();

    /**
     * 日记打印
     * @param $message
     */
    public static function log($message)
    {
        $timestamp = self::timestamp();
        $timestamp = explode('.',$timestamp / 1000);
        $date = date('Y-m-d H:i:s', ($timestamp[0] ?? 0)).'.'.($timestamp[1] ?? 0);
        $debug = Tools::config('debug');
        if (function_exists('getmypid')) {
            $message = '[PID:'.getmypid().' | '.$date.'] ' . $message;
        } else if (function_exists('posix_getpid')) {
            $message = '[PID:'.posix_getpid().' | '.$date.'] ' . $message;
        } else if (function_exists('getmyuid')) {
            $message = '[UID:'.getmyuid().' | '.$date.'] ' . $message;
        }
        if ($debug) {
            Worker::log($message);
        }
    }

    /**
     * 获取公共配置
     *
     * @param null $field
     * @param string $configName
     * @return array|mixed|null
     */
    public static function config($field = null, $configName = 'Config')
    {
        $config = self::loadConf($configName);
        if (is_null($field)) {
            return $config;
        }
        return self::getNestedVar($config, $field);
    }

    /**
     * 加载Config目录下的配置
     * @param string $configName 配置文件名称
     * @return array
     */
    public static function loadConf($configName = 'Config'){
        if(isset(self::$config[$configName])){
            return self::$config[$configName];
        }
        $filename = ROOT_PATH . '/Config/' . ucfirst($configName) . '.php';
        if(file_exists($filename)){
            $_config = require($filename);
            self::$config[$configName] = $_config;
            return self::$config[$configName];
        }
        return array();
    }

    /**
     * 支持用xxx.xxx.xx获取数组
     *
     * @param $context
     * @param $name
     * @return mixed|null
     */
    public static function getNestedVar($context, $name)
    {
        $pieces = explode('.', $name);
        foreach ($pieces as $piece) {
            if (!is_array($context) || !array_key_exists($piece, $context)) {
                // error occurred
                return null;
            }
            $context = &$context[$piece];
        }
        return $context;
    }

    /**
     * http_build_query数组转字符串
     *
     * @param $attr
     * @return string
     */
    public static function buildQuery($attr)
    {
        return http_build_query($attr);
    }

    /**
     * http_build_query解析成数组
     *
     * @param $attrQuery
     * @return array
     */
    public static function parseQuery($attrQuery)
    {
        $data = array();
        if (empty($attrQuery)) {
            return $data;
        }
        $attr = explode('&', $attrQuery);
        foreach ($attr as $query) {
            $attribute = explode('=', $query);
            $data[$attribute[0]] = isset($attribute[1]) ? $attribute[1] : null;
        }
        return $data;
    }

    /**
     * utf8字符转换成Unicode字符
     * @param [type] $utf8_str Utf-8字符
     * @return [type]      Unicode字符
     */
    public static function utf8ToUnicode($utf8Str)
    {
        $unicode = (ord($utf8Str[0]) & 0x1F) << 12;
        $unicode |= (ord($utf8Str[1]) & 0x3F) << 6;
        $unicode |= (ord($utf8Str[2]) & 0x3F);
        return dechex($unicode);
    }

    /**
     * Unicode字符转换成utf8字符
     * @param [type] $unicode_str Unicode字符
     * @return [type]       Utf-8字符
     */
    public static function unicodeToUtf8($unicodeStr)
    {
        $code = intval(hexdec($unicodeStr));
        //这里注意转换出来的code一定得是整形，这样才会正确的按位操作
        $ord_1 = decbin(0xe0 | ($code >> 12));
        $ord_2 = decbin(0x80 | (($code >> 6) & 0x3f));
        $ord_3 = decbin(0x80 | ($code & 0x3f));
        $utf8Str = chr(bindec($ord_1)) . chr(bindec($ord_2)) . chr(bindec($ord_3));
        return $utf8Str;
    }

    /**
     * 获取当前时间戳 - 精切到毫秒
     * @return float
     */
    public static function timestamp()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    /**
     * 取一维数组总最小值的那个KEY
     * @param $array
     * @return int|string|null
     */
    public static function getArrayKeyByMinValue($array)
    {
        $key = null;
        $minValue = null;
        foreach ($array ?? [] as $k=>$v) {
            if (is_null($key)) {
                $key = $k;
                $minValue = $v;
            }
            if ($v < $minValue) {
                $key = $k;
                $minValue = $v;
            }
        }
        return $key;
    }

    /**
     * base64Encode 文件（含 mime_type， 不建议使用）
     * @param string $sourcePath 被 encode 的文件路径
     * @return string|null "data:$mime_type;base64,$base64_encoded_file"
     */
    public static function encodeFileWithMime($sourcePath)
    {
        $base64_encoded_file = self::encodeFile($sourcePath);
        if (!$base64_encoded_file) {
            Tools::log('Error： function encodeFileWithMime. $base64_encoded_file is null');
            return $base64_encoded_file;
        }
        $mime_type = mime_content_type($sourcePath);
        $mime_data = 'data:'. $mime_type .';base64,'. $base64_encoded_file ;
        return $mime_data ;
    }

    /**
     * base64Encode 文件（纯 base64）
     * @param string $sourcePath 被 encode 的文件路径
     * @return string|null "$base64_encoded_file"
     */
    public static function encodeFile($sourcePath)
    {
        $base64_encoded_file = null;
        if (!file_exists($sourcePath) || !is_readable($sourcePath) ) {
            Tools::log('Error： function encodeFile. $sourcePath: file not exists or not readable');
            return $base64_encoded_file;
        }
        $source = file_get_contents($sourcePath);
        if (empty($source) ) {
            Tools::log('Error： function encodeFile. $sourcePath: file is empty');
            return $base64_encoded_file;
        }
        $base64_encoded_file = base64_encode($source);
        return $base64_encoded_file;
    }

    /**
     * base64Decode 文件（含 mime_type，不建议使用）
     * @param string $mime_data 源数据，格式："data:$mime_type;base64,$base64_encoded_file"
     * @param string $output_filename 输出的文件名（不含后缀）
     * @param string $ext 输出文件的后缀（不指定则根据 $mime_type 判断: https://www.iana.org/assignments/media-types/media-types.xhtml ）
     * @param string $output_path 输出文件所在的路径（默认 '/tmp/'）
     * @return string|null decode 后输出文件的完整路径
     */
    public static function decodeFileWithMime($mime_data, $output_filename, $ext = null, $output_path = '/tmp/')
    {
        $pattern = "/(?<=^data:)\w+\/[\w\-\+\d.]+(?=;base64,)/i";
        $default_mime_type = "application/octet-stream";
        if (!preg_match($pattern, $mime_data, $mime_type)) {
            Tools::log('Error： function decodeFileWithMime. $mime_data: not match "data:$mime_type;base64,$base64_encoded_file". (pattern: ' . $pattern . ')');
            return null;
        }
        if (!$ext) {
            $ext = Tools::config($mime_type[0], "MimeType");
            if ($ext) {
                Tools::log('Info： function decodeFileWithMime. Matched: "'.$mime_type[0].'" -> "'.$ext.'"');
            } else {
                $ext = Tools::config($default_mime_type , "MimeType");
                Tools::log('Warnning： function decodeFileWithMime. No MimeType Matched: "'.$mime_type[0].'". Using default ext: "'.$ext.'"');
            }
        }
        $file = self::decodeFile(explode(",", $mime_data, 2)[1], $output_filename, $ext, $output_path);
        return $file;
    }

    /**
     * base64Decode 文件（纯 base64）
     * @param string $encoded_data 源数据，格式："$base64_encoded_file"
     * @param string $output_filename 输出的文件名（不含后缀）
     * @param string $ext 输出文件的后缀（不指定则根据 $mime_type 判断: https://www.iana.org/assignments/media-types/media-types.xhtml ）
     * @param string $output_path 输出文件所在的路径（默认 '/tmp/'）
     * @return string|null decode 后输出文件的完整路径
     */
    public static function decodeFile($encoded_data, $output_filename, $ext = null, $output_path = '/tmp/')
    {
        $targetPath = $output_path . $output_filename . $ext;
        try {
            $data = base64_decode($encoded_data);
            $file = fopen($targetPath, 'w');
            fwrite($file, $data);
            fclose($file);
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $targetPath;
    }
}
