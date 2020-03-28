<?php
require_once(dirname(__FILE__) . '/Class/Abstract/DataClass.php');

header('Content-Type:application/json; charset=utf-8');

/*
 * 设备上传数据
 */

$_POST = array_change_key_case($_POST,CASE_LOWER);

if(isset($_POST['equipmentid']) && isset($_POST['dareacode']) )
{

    if($_POST['areacode'] != substr($_POST['dareacode'],0,9))
    {
        $_POST['areacode'] = substr($_POST['dareacode'],0,9);
    }

    if($_POST['equipmenttype'] != substr($_POST['equipmentid'],13,2))
    {
        $_POST['equipmenttype'] = substr($_POST['equipmentid'],13,2);
    }

    if($_POST['placetype'] != substr($_POST['dareacode'],13,2))
    {
        $_POST['placetype'] = substr($_POST['dareacode'],13,2);
    }

    $equipment = new DataClass($_POST['equipmentid'],'',$_POST['areacode'],$_POST['dareacode']);

    if(!isset($_POST['status']))
    {
        $_POST['status'] = '0';
    }
    if(!isset($_POST['platenum']))
    {
        $_POST['platenum'] = '缺失车牌信息';
    }

    /*
     * 来访车辆数据提交
     */
    if(isset($_POST['passtime']) && isset($_POST['platenum']) && isset($_POST['status'])){
        die($equipment->pushCar($_POST['passtime'],$_POST['platenum'],$_POST['platecolor'],$_POST['vehicletype'],$_POST['areacode'],$_POST['x'],$_POST['y'],$_POST['equipmentid'],$_POST['equipmentname'],$_POST['equipmenttype'],$_POST['stationid'],$_POST['stationname'],
            $_POST['location'],$_POST['vehiclecolor'],$_POST['status'],$_POST['dareaname'],$_POST['dareacode'],$_POST['placetype'],$_POST['cartype'],$_POST['visitreason'],$_POST['visitor'],$_POST['driverdata'],$_POST['passengerdata'],$_POST['vehicleimg'],$_POST['plateimg']));
    }
    die(json_encode(Array('error' => '操作失败，参数错误'), JSON_UNESCAPED_UNICODE));
}
else{
    die(json_encode(Array('error' => '操作失败，参数错误 '), JSON_UNESCAPED_UNICODE));
}