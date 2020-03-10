<?php

/*
 * 系统核心函数
 */

error_reporting(0);

date_default_timezone_set('PRC'); // 默认时区 中国

//$global_lifeTime = 60 * 60 * 2 * 1;
//session_set_cookie_params($global_lifeTime); // 全局默认SESSION 有效时间：（60）秒 （60）分 （24）时 （365）天

session_start();

function mTime()
{
    $t = explode(' ',microtime());
    $t = $t[0] * 1000;
    $t = round($t,0);
    if($t < 100) $t = '0'.strval($t);
    if($t < 10) $t = '0'.strval($t);
    if($t < 1) $t = '0'.strval($t);
    return $t;
}

function uDate($format)
{
    return date(preg_replace('`(?<!\\\\)u`',mTime(),$format));
}

/*
 * 生成唯一ID
 */
function guid() {
    if (function_exists('com_create_guid')){
        $uuid = com_create_guid();
    }else{
        mt_srand();//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = "-";
        $uuid = "{"
            .substr($charid,0,8 ).$hyphen
            .substr($charid, 8,4 ).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20)
            ."}";
    }
    return $uuid;
}

/*
 * 兼容raw json格式数据
 */
if(count($_POST) == 0){
    $_POST = json_decode(file_get_contents("php://input"),true);
}

/*
 * 过滤非法参数（$_POST 通用）
 */
$blacklist = Array("order by",'or','and','rpad','concat',' ','union','%a0',',','if','xor','join','rand','floor','outfile','mid','#','\|\|','--+','0[xX][0-9a-fA-F]+');
foreach ($_POST as $key => $value)
{
    foreach ($blacklist as $blackItem){
        if (preg_match('/\b' . $blackItem . '\b/im', $value)) {
            die('非法参数'.$value);
        }
    }
}
