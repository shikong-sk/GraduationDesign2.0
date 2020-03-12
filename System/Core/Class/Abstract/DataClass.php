<?php

require_once(dirname(__FILE__) .'/../Interface/SqlMethod.php');

require_once(dirname(__FILE__) .'/../SqlHelper.php');

require_once(dirname(__FILE__) .'/../RolePermissionClass.php');

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
        }
    }

    public function pushPersonnel($time,$name,$idCard,$come,$temp){
        if($temp == null){
            $temp = 0;
        }
        // time 为 标准24小时制时间 例：2020-03-10 17:15:00
        $query = "INSERT INTO $this->personnelTable(`time`, `name`, `idCard`, `area`, `place`, `come`, `temp`,`device`) VALUES ('$time', '$name', '$idCard', '$this->area', '$this->place', $come, $temp,'$this->device')";

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