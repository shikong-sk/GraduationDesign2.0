<?php
require_once(dirname(__FILE__) . '/Class/Abstract/DataClass.php');

if(isset($_POST['device']) && isset($_POST['deviceName']) && isset($_POST['area']) && isset($_POST['place']) )
{
    $device = new DataClass($_POST['device'],$_POST['deviceName'],$_POST['area'],$_POST['place']);
    if(isset($_POST['pushPersonnel']) && isset($_POST['time']) && isset($_POST['name']) && isset($_POST['idCard']) && isset($_POST['come'])){
        die($device->pushPersonnel($_POST['time'],$_POST['name'],$_POST['idCard'],$_POST['come'],$_POST['temp']));
    }
    if(isset($_POST['pushCar']) && isset($_POST['time']) && isset($_POST['licensePlate']) && isset($_POST['come'])){
        die($device->pushCar($_POST['time'],$_POST['licensePlate'],$_POST['come']));
    }
    die(json_encode(Array('error' => '操作失败，参数错误'), JSON_UNESCAPED_UNICODE));
}
else{
    die(json_encode(Array('error' => '操作失败，参数错误 '), JSON_UNESCAPED_UNICODE));
}