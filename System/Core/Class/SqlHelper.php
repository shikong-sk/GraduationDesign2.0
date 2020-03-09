<?php

/*
 * 数据库操作类
 */

//require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Core.php');
//require_once(realpath('./'). '/../../Core.php');
require_once(dirname(__FILE__).'../../Core.php');

require_once(dirname(__FILE__) .'../../../Config/db.config.php');

class SqlHelper
{

    /*
     * 数据库参数
     */
    private $db_ip; // 数据库 IP
    private $db_port; // 数据库端口
    private $db_user; // 数据库用户名
    private $db_password; // 数据库密码
    private $db_name; // 数据库名
    public $db_table_prefix; // 数据库表前缀

    /*
     * 数据库表定义
     */

    const Area = "area"; // 行政区域表
    const Place = "place"; // 场所表
    const User = "user"; // 用户数据表
    const Role = "role"; // 角色表
    const RolePermission = "role_permission"; // 角色权限表
    const Device = "device"; // 设备信息表
    const Car = "car"; // 车辆信息表
    const Personnel = "personnel"; // 人员信息表

    var $database;

    public function __construct()
    {

        global $db_ip;
        global $db_port;
        global $db_user;
        global $db_password;
        global $db_name;
        global $db_table_prefix;

        $this->db_ip = $db_ip;
        $this->db_port = $db_port;
        $this->db_user = $db_user;
        $this->db_password = $db_password;
        $this->db_name = $db_name;
        $this->db_table_prefix = $db_table_prefix;
        $this->database = new mysqli();
        $this->database->connect($this->db_ip . ':' . $this->db_port, $this->db_user, $this->db_password);
        if ($this->database->connect_error) {

            die(json_encode(Array('error' => '数据库连接失败,请检查数据库配置文件 ./Core/System/Config/db.config.php 配置是否有误'), JSON_UNESCAPED_UNICODE));
        }

        $this->database->query('set names utf8');
        $this->database->query('use ' . $this->db_name);
    }

    public function __destruct()
    {
        $this->database->close();
    }
}