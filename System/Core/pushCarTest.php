<?php
require_once(dirname(__FILE__) . '/Class/Abstract/DataClass.php');

header('Content-Type:application/json; charset=utf-8');

/*
 * 设备上传数据
 */

if(isset($_POST['equipmentId']) && isset($_POST['equipmentName']) && isset($_POST['areaCode']) && isset($_POST['dareaCode']) )
{

    $equipment = new DataClass($_POST['equipmentId'],$_POST['equipmentName'],$_POST['areaCode'],$_POST['dareaCode']);

    /*
     * 来访车辆数据提交
     */
    if(isset($_POST['passTime']) && isset($_POST['plateNum']) && isset($_POST['status'])){
        die($equipment->pushCar($_POST['passTime'],$_POST['plateNum'],$_POST['plateColor'],$_POST['vehicleType'],$_POST['areaCode'],$_POST['x'],$_POST['y'],$_POST['equipmentId'],$_POST['equipmentName'],$_POST['equipmentType'],$_POST['stationId'],$_POST['stationName'],
            $_POST['location'],$_POST['vehicleColor'],$_POST['status'],$_POST['dareaName'],$_POST['dareaCode'],$_POST['placeType'],$_POST['carType'],$_POST['visitReason'],$_POST['visitor'],$_POST['driverData'],$_POST['passengerData'],$_POST['vehicleImg'],$_POST['plateImg']));
    }
    die(json_encode(Array('error' => '操作失败，参数错误'), JSON_UNESCAPED_UNICODE));
}
else{
    die(json_encode(Array('error' => '操作失败，参数错误 '), JSON_UNESCAPED_UNICODE));
}