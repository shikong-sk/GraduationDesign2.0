<?php

require_once(dirname(__FILE__) .'/../Interface/SqlMethod.php');
require_once(dirname(__FILE__) .'/../SqlHelper.php');

session_start();
abstract class KeepAliveClass implements KeepAliveMethod,SqlMethod
{
    var $db;
    var $try = 3; // 默认重连3次
    var $checkTime = 5; // 默认 60 秒检测一次
    var $live; // 存活状态 (0：离线 1：在线 6:重连 8：异常)
    var $lastTime; // 上一次存活时间
    var $ip; // 设备IP

    var $device; // 设备ID
    var $deviceTable;

    public function setTry(int $try): void
    {
        $this->try = $try;
    }

    public function setCheckTime(int $checkTime): void
    {
        $this->checkTime = $checkTime;
    }

    public function __construct($device)
    {
        error_reporting(2);

        $this->db = new SqlHelper();

        $this->deviceTable = $this->db->db_table_prefix.'_'.SqlHelper::Device;

        $this->device = $device;

    }

    public function checkAlive()
    {

        ignore_user_abort();//关闭浏览器后，继续执行php代码
        set_time_limit($this->checkTime * ($this->try));//程序执行时间无限制


        $device = $this->db->database->query("SELECT deviceID FROM $this->deviceTable WHERE deviceID = '$this->device'")->fetch_assoc()['deviceID'];


        if(!$device)
        {
            die(json_encode(Array('error' => '此设备不存在或尚未登记'), JSON_UNESCAPED_UNICODE));
        }

//        flock();
//
        $this->search();
//
//        if(time() - $this->lastTime < $this->checkTime )
//        {
//            var_dump(time() - $this->lastTime );
//            echo '早于检测时间';
//        }
//
        if($this->lastTime < (time() - ($this->checkTime * $this->try)))
        {
            $query = "UPDATE $this->deviceTable SET `status` = '0',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
            $this->db->database->query($query);
            echo json_encode(Array('info' => '设备已离线'), JSON_UNESCAPED_UNICODE);
        }
        else{
            $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
            $this->db->database->query($query);
            echo json_encode(Array('info' => '设备在线'), JSON_UNESCAPED_UNICODE);
        }
//
//        sleep($this->checkTime * 2 + 1);
        sleep($this->checkTime * 2);
//
        $this->search();

//        $log = fopen('log.txt','w+');
//
//        fwrite($log,$this->lastTime.'\n');
//        fwrite($log,time() - ($this->checkTime * 2) + 1);

//        if($this->lastTime < (time() - ($this->checkTime * 2) + 1))
            if($this->lastTime <= (time() - ($this->checkTime * 2)))
        {
            $query = "UPDATE $this->deviceTable SET `status` = '0',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
            $this->db->database->query($query);
            die(json_encode(Array('info' => '设备已离线'), JSON_UNESCAPED_UNICODE));
        }
//        while (true)
//        {
//            if(time() - $this->lastTime < $this->checkTime )
//            {
//                var_dump(time() - $this->lastTime );
//                echo '早于检测时间';
//                break;
//            }
//
//            if($this->lastTime < (time() - ($this->checkTime * $this->try)))
//            {
//                echo '离线';
//                $query = "UPDATE $this->deviceTable SET `status` = '0',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }
//            else{
//                echo '在线';
//                $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }

//            if($this->lastTime >= time() - $this->checkTime * 2 && $this->lastTime <= time() - $this->checkTime ){
//                echo '在线';
//                $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }

//            sleep($this->checkTime);
//            echo var_dump(time() - $this->lastTime );
//            $this->search();
//
//            if($this->lastTime < (time() - ($this->checkTime * $this->try)))
//            {
//                echo '离线';
//                $query = "UPDATE $this->deviceTable SET `status` = '0',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }
//            else{
//                echo '在线';
//                $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }

//            if($this->lastTime > time() - $this->checkTime ){
//                echo '在线';
//                $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }
//            break;

//            elseif($this->lastTime > time() - ($this->checkTime * $this->try) )
//            {
//                echo '重连';
//                $query = "UPDATE $this->deviceTable SET `status` = '6',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }

//            elseif($this->lastTime >= time() - $this->checkTime * 2 && $this->lastTime <= time() - $this->checkTime ){
//                echo '在线';
//                $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//            }

//            sleep($this->checkTime);
//            $this->search();
//            if($this->lastTime >= time())
//            {   echo '在线';
//                $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//                break;
//            }
//            elseif($this->lastTime < (time() - ($this->checkTime)))
//            {
//                echo '离线';
//                $query = "UPDATE $this->deviceTable SET `status` = '0',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//                break;
//            }
//        }


//        while($this->try >= 0){
//            /*
//             * 检测
//             */
//
//            $this->search();
//            // Code
//
//            $query = "UPDATE $this->deviceTable SET `status` = '1',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//            $this->db->database->query($query);
//            /*
//             * 重试
//             */
//            if($this->live != 1)
//            {
//                /*
//                 *  Code
//                 */
//                $query = "UPDATE $this->deviceTable SET `status` = '6',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//
//                if(time() < $this->lastTime)
//                {
//                    $this->live = 8;
//                    continue;
//                }
//                $this->try --;
//            }
//            if($this->try <= 0)
//            {
//                $query = "UPDATE $this->deviceTable SET `status` = '0',`liveTime` = from_unixtime(" . time() . ") WHERE `deviceId` = '$device'";
//                $this->db->database->query($query);
//                break;
//            }
//            echo '检测完成';
//            sleep($this->checkTime);
//        }
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
        $res = $this->db->database->query("SELECT status,UNIX_TIMESTAMP(liveTime) liveTime,ip FROM ".$this->db->db_table_prefix."_".SqlHelper::Device." WHERE deviceID = '$this->device'")->fetch_assoc();
        $this->live = $res["status"];
        $this->lastTime = $res["liveTime"];
        $this->ip = $res["ip"];
    }


}