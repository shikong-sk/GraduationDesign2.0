<?php

require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Class/Interface/SqlMethod.php');
require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Class/SqlHelper.php');

abstract class KeepAliveClass implements KeepAliveMethod,SqlMethod
{
    var $db;
    var $try = 3; // 默认重连3次
    var $checkTime = 60; // 默认 60 秒检测一次
    var $live; // 存活状态 (0：离线 1：在线 6:重连 8：异常)
    var $lastTime; // 上一次存活时间
    var $ip; // 设备IP

    var $device; // 设备ID

    public function __construct($device)
    {
        $this->db = new SqlHelper();

        $this->device = $device;
    }


    public function checkAlive()
    {

        ignore_user_abort();//关闭浏览器后，继续执行php代码
        set_time_limit(0);//程序执行时间无限制

        while($this->try >= 0){
            /*
             * 检测
             */

            $this->search();
            // Code

            /*
             * 重试
             */
            if($this->live != 1)
            {
                /*
                 *  Code
                 */
                if(time() < $this->lastTime)
                {
                    $this->live = 8;
                    continue;
                }
                $this->try --;
            }
        }
    }

    public function update()
    {
        // TODO: Implement update() method.
    }

    public function insert()
    {
        // TODO: Implement insert() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function search()
    {
        $res = $this->db->database->query("SELECT status,UNIX_TIMESTAMP(liveTime),ip FROM ".$this->db->db_table_prefix."_".SqlHelper::Device." WHERE deviceID = $this->device")->fetch_assoc();
        $this->live = $res["status"];
        $this->lastTime = $res["liveTime"];
        $this->ip = $res["ip"];
    }


}