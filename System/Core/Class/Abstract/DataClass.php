<?php

require_once(dirname(__FILE__) .'/../Interface/SqlMethod.php');

require_once(dirname(__FILE__) .'/../SqlHelper.php');

require_once(dirname(__FILE__) .'/../RoleClass.php');

require_once(dirname(__FILE__) . '/FileClass.php');

header('Content-Type:application/json; charset=utf-8');

class DataClass{
    var $db;

    var $area;
    var $darea;
    var $equipmentId;
    var $equipmentTable;
    var $personnelTable;
    var $carTable;
    var $planTable;
    public function __construct($equipmentId,$equipmentName,$area,$darea)
    {
        session_start();
        $this->db = new SqlHelper();
        $this->equipmentTable = $this->db->db_table_prefix . "_" . SqlHelper::Equipment;
        $this->personnelTable = $this->db->db_table_prefix . "_" . SqlHelper::Personnel;
        $this->carTable = $this->db->db_table_prefix . "_" . SqlHelper::Car;
        $this->planTable = $this->db->db_table_prefix . "_" . SqlHelper::Plan;

        $query = "SELECT equipmentId FROM $this->equipmentTable WHERE `equipmentId` = '$equipmentId' AND `equipmentName` = '$equipmentName' AND `area` = '$area' AND `darea` = '$darea'";

        $res = $this->db->database->query($query)->fetch_assoc()['equipmentId'];
        if(!$res){
            die(json_encode(Array('error' => '设备认证失败'), JSON_UNESCAPED_UNICODE));
        }
        else{
            $this->equipmentId = $res;
            $this->area = $area;
            $this->darea = $darea;
            $_SESSION['user'] = $equipmentId;
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

    public function pushPersonnel(
        $passTime,$name,$gender,$nation='',$idCard,$cardType,$countryOrAreaCode="",$countryOrAreaName="",$cardVersion="",
        $currentApplyOrgCode="",$signNum="",$birthDay="",$address="",$authority="",$validtyStart="",$validtyEnd="",
        $personImg="",$idCardImg="",$temp=0,$area,$x,$y,$equipmentId,$equipmentName,$equipmentType,$stationId="",$stationName="",
        $location="",$status,$dareaName,$darea,$dareaType,$identity,$homedarea="",$contact="",$isConsist="",$compareScore="",
        $openMode="",$visitReason="",$mac="",$imsi="",$imei="",$visitor=""
    ){

        if (strlen($area) != 9) {
            return json_encode(Array('error' => '行政区域代码错误'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen(strval($darea)) != 19) {
            return json_encode(Array('error' => '场所代码错误'), JSON_UNESCAPED_UNICODE);
        }

        if ($area !== substr($darea, 0, 9)) {
            return json_encode(Array('error' => '地区与场所不符'), JSON_UNESCAPED_UNICODE);
        }

        $areaName = $this->getAreaName($area);
        $dareaName = $this->getDareaName($darea);


        if (strlen(mb_split(':', $dareaName)[1]) == 0) {
        return json_encode(Array('error' => '操作失败，该场所不存在'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($equipmentId) != 18) {
            return json_encode(Array('error' => 'equipmentId 参数错误'), JSON_UNESCAPED_UNICODE);
        } else if ($area !== substr($equipmentId, 0, 9)) {
            return json_encode(Array('error' => 'equipmentId 参数错误'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($equipmentType) != 2 || $equipmentType !== substr($equipmentId, 13, 2)) {
            return json_encode(Array('error' => 'equipmentType 参数错误'), JSON_UNESCAPED_UNICODE);
        }

        $f = new FileManager();

        if (isset($personImg) && strlen($personImg) != 0) {
            $personImg = json_decode($f->uploadImage($personImg, 'personnel', $passTime, $name . '_现场'), true)[0];
        }
        
        if (isset($idCardImg) && strlen($idCardImg) != 0) {
            $idCardImg = json_decode($f->uploadImage($idCardImg, 'personnel', $passTime, $name . '_证件'), true)[0];
        }

        if (strlen($countryOrAreaCode) != 0 && (strlen($countryOrAreaCode) != 3)) {
            return json_encode(Array('error' => 'countryOrAreaCode 参数错误'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($status) != 1) {
            return json_encode(Array('error' => 'status 参数错误'), JSON_UNESCAPED_UNICODE);
        }

        if (is_array($visitor)) {
            $visitor = json_encode($visitor, JSON_UNESCAPED_UNICODE);
        }

        $query = "INSERT INTO $this->personnelTable(`passTime`, `name`, `gender`, `nation`, `idCard`, `cardType`, `countryOrAreaCode`, `countryOrAreaName`, `cardVersion`, `currentApplyOrgCode`, `signNum`, `birthDay`, `address`, `authority`, `validtyStart`, `validtyEnd`, `personImg`, `idCardImg`, `temp`, `area`, `x`, `y`, `equipmentId`, `equipmentName`, `equipmentType`, `stationId`, `stationName`, `location`, `status`, `darea`, `dareaName`, `placeType`, `identity`, `homePlace`, `contact`, `isConsist`, `compareScore`, `openMode`, `visitReason`, `mac`, `imsi`, `imei`, `visitor`) VALUES ('{$passTime}', '{$name}', '{$gender}', '{$nation}', '{$idCard}', '{$cardType}', '{$countryOrAreaCode}', '{$countryOrAreaName}', '{$cardVersion}', '{$currentApplyOrgCode}', '{$signNum}', '{$birthDay}', '{$address}', '{$authority}', '{$validtyStart}', '{$validtyEnd}', '{$personImg}', '{$idCardImg}', '{$temp}', '{$area}', '{$x}', '{$y}', '{$equipmentId}', '{$equipmentName}', '{$equipmentType}', '{$stationId}', '{$stationName}', '{$location}', '{$status}', '{$darea}', '{$dareaName}', '{$dareaType}', '{$identity}', '{$homedarea}', '{$contact}', '{$isConsist}', '{$compareScore}', '{$openMode}', '{$visitReason}', '{$mac}', '{$imsi}', '{$imei}', '{$visitor}')";
        
        $res = $this->db->database->query($query);
        $err = $this->db->database->errno;

//        echo $query;

        if ($err == 1062) {
            return json_encode(Array('error' => '该数据已存在'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            $f->deleteImage($personImg,'personnel');
            $f->deleteImage($idCardImg,'personnel');
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
        
    }

    public function pushCar($time,$licensePlate,$come){
        // time 为 标准24小时制时间 例：2020-03-10 17:15:00
        $query = "INSERT INTO $this->carTable(`time`, `licensePlate`, `area`, `darea`, `come`, `device`) VALUES ('$time', '$licensePlate', '$this->area', '$this->darea', $come, '$this->device')";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function getAreaName($area)
    {

        $query = "SELECT DISTINCT areaName FROM $this->planTable WHERE area = '$area' ";
        $res = $this->db->database->query($query);
        return $res->fetch_assoc()['areaName'];
    }

    public function getDareaName($darea)
    {
        $query = "SELECT DISTINCT dareaName FROM $this->planTable WHERE darea = '$darea' ";
        $res = $this->db->database->query($query)->fetch_assoc();
        return $this->getAreaName(substr($darea, 0, 9)) . ':' . $res['dareaName'];
    }

}