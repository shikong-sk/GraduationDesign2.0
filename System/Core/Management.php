<?php
/**
 * Created by PhpStorm.
 * User: Shikong
 * Date: 2020/3/2
 * Time: 12:07
 */
//phpinfo();
require_once($_SERVER['DOCUMENT_ROOT'] . '/System/Core/Class/RolePermissionClass.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/System/Core/Class/Abstract/UserClass.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/System/Core/Class/ManagementClass.php');

session_start();

header('Content-Type:application/json; charset=utf-8');

//$User = new User();
//echo $User->login('1','123+AbC');

//
//$Role = new rolePermissionClass($User->getRole());
//
////if(isset($_POST['']))
//
//echo $Role->getRolePermissionList();

$Management = new ManagementClass();


/*
 * 相关信息查询
 */
if(isset($_POST['getAdminList']))
{
    die($Management->getAdminList());
}

if(isset($_POST['getRolePermissionList']))
{
    die($Management->getRolePermissionList());
}

if(isset($_POST['getRoleList']))
{
    die($Management->getRoleList());
}

if(isset($_POST['getCarList']) && isset($_POST['page']) && isset($_POST['num']))
{
    die($Management->getCarList($_POST['page'],$_POST['num'],$_POST['filter'],$_POST['arg']));
}

if(isset($_POST['getUserList']) && isset($_POST['page']) && isset($_POST['num']))
{
    die($Management->getUserList($_POST['page'],$_POST['num'],$_POST['filter'],$_POST['arg'],$_POST['extend']));
}

if(isset($_POST['getDeviceList']) && isset($_POST['page']) && isset($_POST['num']))
{
    die($Management->getDeviceList($_POST['page'],$_POST['num'],$_POST['filter'],$_POST['arg']));
}

if(isset($_POST['getPersonnelList']) && isset($_POST['page']) && isset($_POST['num']))
{
    die($Management->getPersonnelList($_POST['page'], $_POST['num'],$_POST['filter'],$_POST['arg']));
}

if(isset($_POST['getAreaList']) && isset($_POST['page']) && isset($_POST['num'])) {
    die($Management->getAreaList($_POST['page'], $_POST['num'],$_POST['filter'],$_POST['arg']));
}

if(isset($_POST['getPlaceList']) && isset($_POST['page']) && isset($_POST['num'])) {
    die($Management->getPlaceList($_POST['page'], $_POST['num'],$_POST['filter'],$_POST['arg']));
}

/*
 * 添加记录
 */
if ( isset($_POST['addCar']) && isset($_POST['licensePlate']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST['come']))
{
    die($Management->addCar($_POST['licensePlate'], $_POST['area'], $_POST['place'], $_POST['come']));
}

if(isset($_POST['addDevice']) && isset($_POST['id']) && isset($_POST['name']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST['ip']))
{
    die($Management->addDevice($_POST['id'],$_POST['name'],$_POST['area'],$_POST['place'],$_POST['ip']));
}

if(isset($_POST['addUser']) && isset($_POST['user']) && isset($_POST['password']) && isset($_POST['phone']) && isset($_POST['address']) && isset($_POST['email']) && isset($_POST['role']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST["sex"]) && isset($_POST["realName"]) && isset($_POST["idCard"]))
{
    die($Management->addUser($_POST['user'],$_POST['password'],$_POST['phone'],$_POST['address'],$_POST['email'],$_POST['role'],$_POST['area'],$_POST['place'],$_POST['sex'],$_POST['realName'],$_POST['idCard']));
}

if(isset($_POST['addRole']) && isset($_POST['id']) && isset($_POST['name']))
{
    die($Management->addRole($_POST['id'], $_POST['name']));
}

if(isset($_POST['addPlace']) && isset($_POST['id']) && isset($_POST['area']) && isset($_POST['name']))
{
    die($Management->addPlace($_POST['id'],$_POST['area'],$_POST['name']));
}

if(isset($_POST['addArea']) && isset($_POST['code']) && isset($_POST['name']) && isset($_POST['level']) && isset($_POST['parentCode']))
{
    die($Management->addArea($_POST['code'],$_POST['name'],$_POST['level'],$_POST['parentCode']));
}

if(isset($_POST['addPersonnel']) && isset($_POST['name']) && isset($_POST['idCard']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST['come']) && isset($_POST['temp']))
{
    die($Management->addPersonnel($_POST['name'],$_POST['idCard'],$_POST['area'],$_POST['place'],$_POST['come'],$_POST['temp']));
}


/*
 * 更新记录
 */
if ( isset($_POST['updateCar']) && isset($_POST['time']) && isset($_POST['licensePlate']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST['come']))
{
    die($Management->updateCar($_POST['time'],$_POST['licensePlate'], $_POST['area'], $_POST['place'], $_POST['come']));
}

if(isset($_POST['updateRolePermission']) && isset($_POST['role']) && isset($_POST['login']) && isset($_POST['addUser']) && isset($_POST['deleteUser']) && isset($_POST['updateUser']) && isset($_POST['searchUser']) && isset($_POST['addDevice']) && isset($_POST['deleteDevice']) && isset($_POST['updateDevice']) && isset($_POST['searchDevice']) && isset($_POST['addPersonnel']) && isset($_POST['deletePersonnel']) && isset($_POST['updatePersonnel']) && isset($_POST['searchPersonnel']) && isset($_POST['addAdmin']) && isset($_POST['deleteAdmin']) && isset($_POST['editArea']) && isset($_POST['editPlace']) && isset($_POST['editRole']) && isset($_POST['editRolePermission']) && isset($_POST['addCar']) && isset($_POST['deleteCar']) && isset($_POST['updateCar']) && isset($_POST['searchCar']) )
{
    die($Management->updateRolePermission($_POST['role'],$_POST['login'],$_POST['addUser'],$_POST['deleteUser'],$_POST['updateUser'],$_POST['searchUser'],$_POST['addDevice'],$_POST['deleteDevice'],$_POST['updateDevice'],$_POST['searchDevice'],$_POST['addPersonnel'],$_POST['deletePersonnel'],$_POST['updatePersonnel'],$_POST['searchPersonnel'],$_POST['addAdmin'],$_POST['deleteAdmin'],$_POST['editArea'],$_POST['editPlace'],$_POST['editRole'],$_POST['editRolePermission'],$_POST['addCar'],$_POST['deleteCar'],$_POST['updateCar'],$_POST['searchCar']));
}

if(isset($_POST['updateDevice']) && isset($_POST['id']) && isset($_POST['name']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST['ip']))
{
    die($Management->updateDevice($_POST['id'],$_POST['name'],$_POST['area'],$_POST['place'],$_POST['ip']));
}

if(isset($_POST['updateUser']) && isset($_POST['id']) && isset($_POST['phone']) && isset($_POST['address']) && isset($_POST['email']) && isset($_POST['role']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST["sex"]) && isset($_POST["realName"]) && isset($_POST["idCard"]))
{
   die($Management->updateUser($_POST['id'],$_POST['phone'],$_POST['address'],$_POST['email'],$_POST['role'],$_POST['area'],$_POST['place'],$_POST['sex'],$_POST['realName'],$_POST['idCard']));
}

if(isset($_POST['updatePersonnel']) && isset($_POST['time']) && isset($_POST['name']) && isset($_POST['idCard']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST['come']) && isset($_POST['temp']))
{
    die($Management->updatePersonnel($_POST['time'],$_POST['name'],$_POST['idCard'],$_POST['area'],$_POST['place'],$_POST['come'],$_POST['temp']));
}

if(isset($_POST['addAdmin']) && isset($_POST['id']) && isset($_POST['name']))
{
    die($Management->addAdmin($_POST['id'],$_POST['name']));
}

if(isset($_POST['deleteAdmin']) && isset($_POST['id']) && isset($_POST['name']))
{
    die($Management->deleteAdmin($_POST['id'],$_POST['name']));
}

if(isset($_POST['updateRole']) && isset($_POST['id']) && isset($_POST['name']))
{
    die($Management->updateRole($_POST['id'], $_POST['name']));
}

if(isset($_POST['updatePlace']) && isset($_POST['id']) && isset($_POST['area']) && isset($_POST['name']))
{
    die($Management->updatePlace($_POST['id'],$_POST['area'],$_POST['name']));
}

if(isset($_POST['updateArea']) && isset($_POST['code']) && isset($_POST['name']) && isset($_POST['level']) && isset($_POST['parentCode']))
{
    die($Management->updateArea($_POST['code'],$_POST['name'],$_POST['level'],$_POST['parentCode']));
}


/*
 * 删除记录
 */
if(isset($_POST['deleteCar']) && isset($_POST['time']) && isset($_POST['licensePlate']) ){
    die($Management->deleteCar($_POST['time'], $_POST['licensePlate']));
}

if(isset($_POST['deleteDevice']) && isset($_POST['id']) && isset($_POST['name']))
{
    die($Management->deleteDevice($_POST['id'],$_POST['name']));
}

if(isset($_POST['deletePersonnel']) && isset($_POST['time']) && isset($_POST['name']) && isset($_POST['idCard']))
{
    die($Management->deletePersonnel($_POST['time'],$_POST['name'],$_POST['idCard']));
}

if(isset($_POST['deleteUser']) && isset($_POST['id']) && isset($_POST['user']))
{
    die($Management->deleteUser($_POST['id'],$_POST['user']));
}

if(isset($_POST['deleteRole']) && isset($_POST['id']))
{
    die($Management->deleteRole($_POST['id']));
}

if(isset($_POST['deletePlace']) && isset($_POST['id']) && isset($_POST['area']))
{
    die($Management->deletePlace($_POST['id'],$_POST['area']));
}

if(isset($_POST['deleteArea']) && isset($_POST['code']) && isset($_POST['name']) && isset($_POST['level']) && isset($_POST['parentCode']))
{
    die($Management->deleteArea($_POST['code'],$_POST['name'],$_POST['level'],$_POST['parentCode']));
}
