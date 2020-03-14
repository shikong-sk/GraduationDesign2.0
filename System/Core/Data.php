<?php
require_once(dirname(__FILE__) . '/Class/Abstract/DataClass.php');

header('Content-Type:application/json; charset=utf-8');

/*
 * 设备上传数据
 */

if(isset($_POST['device']) && isset($_POST['deviceName']) && isset($_POST['area']) && isset($_POST['place']) )
{
    $device = new DataClass($_POST['device'],$_POST['deviceName'],$_POST['area'],$_POST['place']);
    /*
     * 来访人员数据提交
     */
    if(isset($_POST['pushPersonnel']) && isset($_POST['passTime']) && isset($_POST['name']) && isset($_POST['idCard']) && isset($_POST['come'])){
        die($device->pushPersonnel(
            $_POST['passTime'],$_POST['name'],$_POST['idCard'],$_POST['come'],$_POST['temp'],$_POST['cardType'],$_POST['sex'],
            $_POST['nation'],$_POST['countryAreaCode'],$_POST['countryAreaName'],$_POST['cardVersion'],$_POST['personImg'],$_POST['idCardImg']));
    }

    /*
     * 来访车辆数据提交
     */
    if(isset($_POST['pushCar']) && isset($_POST['passTime']) && isset($_POST['licensePlate']) && isset($_POST['come'])){
        die($device->pushCar($_POST['passTime'],$_POST['licensePlate'],$_POST['come']));
    }
    die(json_encode(Array('error' => '操作失败，参数错误'), JSON_UNESCAPED_UNICODE));
}
else{
    die(json_encode(Array('error' => '操作失败，参数错误 '), JSON_UNESCAPED_UNICODE));
}