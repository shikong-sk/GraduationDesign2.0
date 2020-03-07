<?php

session_start();

require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Class/SqlHelper.php');

require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Class/Abstract/UserClass.php');

$User = new User();

header('Content-Type:application/json; charset=utf-8');

if(isset($_POST['login']) &&  isset($_POST['user']) && isset($_POST['password']))
{
    die($User->login($_POST['user'],$_POST['password']));
}

if(isset($_POST['register']) && isset($_POST['user']) && isset($_POST['password']) && isset($_POST['phone']) && isset($_POST['address']) && isset($_POST['email']) && isset($_POST['area']) && isset($_POST['place']) && isset($_POST["sex"]) && isset($_POST["realName"]) && isset($_POST["idCard"]))
{
    die($User->register($_POST['user'],$_POST['password'],$_POST['phone'],$_POST['address'],$_POST['email'],$_POST['area'],$_POST['place'],$_POST['sex'],$_POST['realName'],$_POST['idCard']));
}

if(isset($_REQUEST['logout']))
{
    die($User->logout());
}

if(isset($_POST['updateUser']) && isset($_POST['password']) && isset($_POST["phone"]) && isset($_POST["address"]) && isset($_POST["email"]) && isset($_POST["area"]) && isset($_POST["place"]) && isset($_POST["sex"]) && isset($_POST["realName"]) && isset($_POST["idCard"])){
    die($User->updateUser($_POST["password"],$_POST["phone"],$_POST["address"],$_POST["email"],$_POST["area"],$_POST["place"],$_POST["sex"],$_POST["realName"],$_POST["idCard"]));
}

if(isset($_POST['getUserInfo']))
{
    die($User->getUserInfo());
}

if(isset($_POST['getRolePermission']))
{
    die($User->getRolePermission());
}

die('<h1>ForBidden</h1>');