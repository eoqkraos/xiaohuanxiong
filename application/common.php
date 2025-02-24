<?php
use think\facade\App;
// 应用公共文件
define('spider_db_conn', 'xxx');

function delete_dir_file($dir_name)
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DIRECTORY_SEPARATOR . $item)) {
                        delete_dir_file($dir_name . DIRECTORY_SEPARATOR . $item);
                    } else {
                        unlink($dir_name . DIRECTORY_SEPARATOR . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) { //删除空白目录
                $result = true;
            }
        }
    }
    return $result;
}

function img_process($file,$width,$height,$path){
    $image = think\Image::open($file);
    $image->thumb($width, $height,think\Image::THUMB_FIXED)->save($path);
}

function subtext($text, $length)
{
    $text2 = strip_tags($text);
    if(mb_strlen($text2, 'utf8') > $length)
        return mb_substr($text2,0,$length,'utf8');
    return $text2;
}

function xwxcms_substring($str, $lenth, $start=0)
{
    $len = strlen($str);
    $r = array();
    $n = 0;
    $m = 0;

    for($i=0;$i<$len;$i++){
        $x = substr($str, $i, 1);
        $a = base_convert(ord($x), 10, 2);
        $a = substr( '00000000 '.$a, -8);

        if ($n < $start){
            if (substr($a, 0, 1) == 0) {
            }
            else if (substr($a, 0, 3) == 110) {
                $i += 1;
            }
            else if (substr($a, 0, 4) == 1110) {
                $i += 2;
            }
            $n++;
        }
        else{
            if (substr($a, 0, 1) == 0) {
                $r[] = substr($str, $i, 1);
            }else if (substr($a, 0, 3) == 110) {
                $r[] = substr($str, $i, 2);
                $i += 1;
            }else if (substr($a, 0, 4) == 1110) {
                $r[] = substr($str, $i, 3);
                $i += 2;
            }else{
                $r[] = ' ';
            }
            if (++$m >= $lenth){
                break;
            }
        }
    }
    return  join('',$r);
}

function xwxcms_parse_sql($sql='',$limit=0, $prefix=[])
{
    // 被替换的前缀
    $from = '';
    // 要替换的前缀
    $to = '';

    // 替换表前缀
    if (!empty($prefix)) {
        $to   = current($prefix);
        $from = current(array_flip($prefix));
    }

    if ($sql != '') {
        // 纯sql内容
        $pure_sql = [];

        // 多行注释标记
        $comment = false;

        // 按行分割，兼容多个平台
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $sql = explode("\n", trim($sql));

        // 循环处理每一行
        foreach ($sql as $key => $line) {
            // 跳过空行
            if ($line == '') {
                continue;
            }

            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }

            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }

            // 多行注释开始
            if (substr($line, 0, 2) == '/*') {
                $comment = true;
                continue;
            }

            // 多行注释结束
            if (substr($line, -2) == '*/') {
                $comment = false;
                continue;
            }

            // 多行注释没有结束，继续跳过
            if ($comment) {
                continue;
            }

            // 替换表前缀
            if ($from != '') {
                $line = str_replace('`'.$from, '`'.$to, $line);
            }
            if ($line == 'BEGIN;' || $line =='COMMIT;') {
                continue;
            }
            // sql语句
            array_push($pure_sql, $line);
        }

        // 只返回一条语句
        if ($limit == 1) {
            return implode($pure_sql, "");
        }

        // 以数组形式返回sql语句
        $pure_sql = implode($pure_sql, "\n");
        $pure_sql = explode(";\n", $pure_sql);
        return $pure_sql;
    } else {
        return $limit == 1 ? '' : [];
    }
}

function new_redis(){
    $redis = new \Redis();
    $redis->connect(config('cache.host'), config('cache.port'));
    if (!empty(config('cache.password'))){
        $redis->auth(config('cache.password'));
    }
    return $redis;
}

function generateRandomString($length = 4) {
    $characters = '0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

//验证session中的验证码和手机号码是否正确
function verifycode($code,$phone){
    if (is_null(session('xwx_sms_code')) || $code != session('xwx_sms_code')){
        return 0;
    }
    if (is_null(session('xwx_cms_phone')) || $phone != session('xwx_cms_phone')){
        return 0;
    }
    return 1;
}

//16进制转10进制
function dex($str){
    return base_convert($str,16,10);
}

function curl( $url, $postFields = null ) {
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_FAILONERROR, false );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

    $postBodyString = "";
    $encodeArray = Array();
    $postMultipart = false;

    if ( is_array( $postFields ) && 0 < count( $postFields ) ) {
        foreach ( $postFields as $k => $v ) {
            $postBodyString .= $k . '=' . urlencode( $v ) . '&';
        }
        unset ( $k, $v );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, substr( $postBodyString, 0, -1 ) );
    }
    $headers = array( 'content-type: application/x-www-form-urlencoded;charset=UTF-8' );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    $reponse = curl_exec( $ch );

    if ( curl_errno( $ch ) ) {
        throw new Exception( curl_error( $ch ), 0 );
    } else {
        $httpStatusCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        if ( 200 !== $httpStatusCode ) {
            throw new Exception( $reponse, $httpStatusCode );
        }
    }

    curl_close( $ch );
    return $reponse;
}