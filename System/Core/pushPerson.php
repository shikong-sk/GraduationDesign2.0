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
    if(!isset($_POST['idcard']))
    {
        $_POST['idcard'] = '缺失人员信息'.trim(guid(), '{}');
    }
    if(!isset($_POST['name']))
    {
        $_POST['name'] = '缺失人员信息'.trim(guid(), '{}');
    }

    /*
     * 来访人员数据提交
     */
    if(isset($_POST['passtime']) && isset($_POST['name']) && isset($_POST['idcard']) && isset($_POST['status'])){

        die($equipment->pushPersonnel(
            $_POST['passtime'],$_POST['name'],$_POST['gender'],$_POST['nation'],$_POST['idcard'],$_POST['cardtype'],$_POST['countryorareacode'],$_POST['countryorareaname'],$_POST['cardversion'],
            $_POST['currentapplyorgcode'],$_POST['signnum'],$_POST['birthday'],$_POST['address'],$_POST['authority'],$_POST['validtystart'],$_POST['validtyend'],
            $_POST['personimg'],$_POST['idcardimg'],$_POST['temp'],$_POST['areacode'],$_POST['x'],$_POST['y'],$_POST['equipmentid'],$_POST['equipmentname'],$_POST['equipmenttype'],$_POST['stationid'],$_POST['stationname'],
            $_POST['location'],$_POST['status'],$_POST['dareaname'],$_POST['dareacode'],$_POST['dareatype'],$_POST['identity'],$_POST['homedarea'],$_POST['contact'],$_POST['isconsist'],$_POST['comparescore'],
            $_POST['openmode'],$_POST['visitreason'],$_POST['mac'],$_POST['imsi'],$_POST['imei'],$_POST['visitor']
        ));
    }
    die(json_encode(Array('error' => '操作失败，参数错误'), JSON_UNESCAPED_UNICODE));
}
else{
    die(json_encode(Array('error' => '操作失败，参数错误2'), JSON_UNESCAPED_UNICODE));
}