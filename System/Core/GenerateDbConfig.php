<?php

/*
 * 数据库配置文件生成测试
 */

$file = '../Config/db.config.php';

$db_ip = "127.0.0.1";
$db_port = "3306";
$db_user = "tw";
$db_password = "123456";

$db_name = "tw_demo";
$db_table_prefix="tw";
$f = fopen($file,'w+');

//fwrite($f,"<?php\n\theader(\"HTTP/1.1 403 Forbidden\");\n");

fwrite($f,"<?php\n");

fwrite($f,"\t".'$db_ip = "' . "$db_ip" . '";' ."\n");
fwrite($f,"\t".'$db_port = "' . "$db_port" . '";' ."\n");
fwrite($f,"\t".'$db_user = "' . "$db_user" . '";' ."\n");
fwrite($f,"\t".'$db_password = "' . "$db_password" . '";' ."\n");
fwrite($f,"\t".'$db_name = "' . "$db_name" . '";' ."\n");
fwrite($f,"\t".'$db_table_prefix = "' . "$db_table_prefix" . '";' ."\n");
