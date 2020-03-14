<?php

require_once(dirname(__FILE__) .'/../Interface/SqlMethod.php');

require_once(dirname(__FILE__) .'/../SqlHelper.php');

require_once(dirname(__FILE__) .'/../RolePermissionClass.php');

require_once(dirname(__FILE__) . '/FileClass.php');

header('Content-Type:application/json; charset=utf-8');

class DataClass{
    var $db;

    var $area;
    var $place;
    var $device;
    var $deviceTable;
    var $personnelTable;
    var $carTable;
    public function __construct($device,$deviceName,$area,$place)
    {
        session_start();
        $this->db = new SqlHelper();
        $this->deviceTable = $this->db->db_table_prefix . "_" . SqlHelper::Device;
        $this->personnelTable = $this->db->db_table_prefix . "_" . SqlHelper::Personnel;
        $this->carTable = $this->db->db_table_prefix . "_" . SqlHelper::Car;

        $query = "SELECT deviceID FROM $this->deviceTable WHERE `deviceID` = '$device' AND `name` = '$deviceName' AND `area` = '$area' AND `place` = '$place'";

        $res = $this->db->database->query($query)->fetch_assoc()['deviceID'];
        if(!$res){
            die(json_encode(Array('error' => '设备认证失败'), JSON_UNESCAPED_UNICODE));
        }
        else{
            $this->device = $res;
            $this->area = $area;
            $this->place = $place;
            $_SESSION['user'] = $device;
            $_SESSION['device'] = '1';
        }
    }

    public function __destruct()
    {
        if(isset($_SESSION['device']))
        {
            session_unset();
            session_destroy();
        }
    }

    public function pushPersonnel($time,$name,$idCard,$come,$temp,$cardType = '',$sex = '',$nation='',$countryAreaCode='',$countryAreaName='',$cardVersion='',$personImg='',$idCardImg=''){
        if($temp == null){
            $temp = 0;
        }
        // time 为 标准24小时制时间 例：2020-03-10 17:15:00
//        $query = "INSERT INTO $this->personnelTable(`time`, `name`, `idCard`, `area`, `place`, `come`, `temp`,`device`) VALUES ('$time', '$name', '$idCard', '$this->area', '$this->place', $come, $temp,'$this->device')";

        $f = new FileManager();

        if($personImg != null && strlen($personImg)!=0)
        {
            $tmp = json_decode($f->uploadImage($personImg,'personnel',"$time"),true);
            $personImg = $tmp[0];
            if(isset($tmp['error'])){
                return json_encode(Array('error'=>$tmp['error']));
            }
        }

        if($idCardImg != null && strlen($idCardImg)!=0)
        {
            $tmp = json_decode($f->uploadImage($idCardImg,'personnel',"$time"),true);
            $idCardImg = $tmp[0];
            if(isset($tmp['error'])){
                return json_encode(Array('error'=>$tmp['error']));
            }
        }

        $query = "INSERT INTO $this->personnelTable(`time`, `name`, `idCard`, `cardType`, `area`, `place`, `come`, `temp`, `device`, `sex`, `nation`, `countryAreaCode`, `countryAreaName`, `cardVersion`, `personImg`, `idCardImg`) VALUES ('$time', '$name', '$idCard', '$cardType', '$this->area', '$this->place', '$come', '$temp', '$this->device', '$sex', '$nation', '$countryAreaCode', '$countryAreaName', '$cardVersion', '$personImg', '$idCardImg')";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function pushCar($time,$licensePlate,$come){
        // time 为 标准24小时制时间 例：2020-03-10 17:15:00
        $query = "INSERT INTO $this->carTable(`time`, `licensePlate`, `area`, `place`, `come`, `device`) VALUES ('$time', '$licensePlate', '$this->area', '$this->place', $come, '$this->device')";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }
}