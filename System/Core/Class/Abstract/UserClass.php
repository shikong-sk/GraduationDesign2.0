<?php

/*
 * 用户抽象类
 */

require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Class/Interface/SqlMethod.php');

require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Class/SqlHelper.php');

require_once($_SERVER['DOCUMENT_ROOT'] .'/System/Core/Class/RolePermissionClass.php');

abstract class UserClass implements SqlMethod,UserMethod {

    var $db;
    var $table;
    var $roleTable;
    var $areaTable;
    var $placeTable;
    var $rolePermissionTable;

    var $rolePermission;

    var $userName;
    var $password;

    var $id;
    var $salt;
    var $phone;
    var $address;
    var $email;
    var $role = "002";
    var $area;
    var $place;
    var $sex;
    var $realName;
    var $idCard;

    public function __construct()
    {
        $this->db = new SqlHelper();
        if(isset($_SESSION['user']))
        {
            $this->userName = $_SESSION['user'];
        }
        $this->table = $this->db->db_table_prefix."_".SqlHelper::User;
        $this->roleTable = $this->db->db_table_prefix."_".SqlHelper::Role;
        $this->areaTable = $this->db->db_table_prefix."_".SqlHelper::Area;
        $this->placeTable = $this->db->db_table_prefix."_".SqlHelper::Place;
        $this->rolePermissionTable = $this->db->db_table_prefix."_".SqlHelper::RolePermission;
    }

    public function register($user,$password,$phone,$address,$email,$area,$place,$sex,$realName,$idCard)
    {
        if(isset($_SESSION['user'])){
            return json_encode(Array('error'=>'您已登录，无需进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        $this->userName = $user;
        $this->password = $password;
        $this->phone = $phone;
        $this->address = $address;
        $this->email = $email;
        $this->area = $area;
        $this->place = $place;
        $this->sex = $sex;
        $this->realName = $realName;
        $this->idCard = $idCard;

        if (strlen($user) == 0 || strlen($password) == 0 || strlen($area) == 0 || strlen($place) == 0) {
            return json_encode(Array('success' => '用户注册失败，请检查信息是否完整'), JSON_UNESCAPED_UNICODE);
        }

        $this->id = trim(guid(), '{}');

        $salt = ''; // 随机加密密钥
        while (strlen($salt) < 6) {
            $x = mt_rand(0, 9);
            $salt .= $x;
        }
        $this->salt = $salt;
        $this->password = sha1($password . $salt); // sha1哈希加密

        $res = $this->insert();
        if ($res) {
            return json_encode(Array('success' => '用户注册成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '用户注册失败,该用户名已存在'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function updateUser($password,$phone,$address,$email,$area,$place,$sex,$realName,$idCard){
        if(!isset($_SESSION['user'])){
            return json_encode(Array('error'=>'请先登录再进行此操作'), JSON_UNESCAPED_UNICODE);
        }
        if(strlen($password.$phone.$address.$email.$area.$sex.$realName.$idCard) == 0)
        {
            return json_encode(Array('success'=>'用户信息更新成功'), JSON_UNESCAPED_UNICODE);
        }

        $this->userName = $_SESSION['user'];
        $this->id = $_SESSION['uid'];

        $old = $this->search()->fetch_assoc();

        if(sha1($password.$old['salt']).$phone.$address.$email.$area.$place.$sex.$realName.$idCard === $old['password'].$old['phone'].$old['address'].$old['email'].$old['area'].$old['place'].$old['sex'].$old['realName'].$old['idCard'])
        {
            return json_encode(Array('success'=>'用户信息更新成功'), JSON_UNESCAPED_UNICODE);
        }

        if(strlen($password) == 0){
            $this->password = $old["password"];
            $this->salt = $old["salt"];
        }
        else{
            $salt = ''; // 随机加密密钥
            while (strlen($salt) < 6) {
                $x = mt_rand(0, 9);
                $salt .= $x;
            }
            $this->salt = $salt;
            $this->password = sha1($password . $salt); // sha1哈希加密
        }

        $this->phone = strlen($phone) == 0 ? $old["phone"] : $phone;
        $this->address = strlen($address) == 0 ? $old["address"] : $address;
        $this->email = strlen($email) == 0 ? $old["email"] : $email;
        $this->area = strlen($area) == 0 ? $old["area"] : $area;
        $this->place = strlen($place) == 0 ? $old["place"] : $place;
        $this->sex = strlen($sex) == 0 ? $old["sex"] : $sex;
        $this->realName = strlen($realName) == 0 ? $old["realName"] : $realName;
        $this->idCard = strlen($idCard) == 0 ? $old["idCard"] : $idCard;

        $res = $this->update();

        if($res && ($this->db->database->affected_rows >= 0 && $this->db->database->affected_rows <= 1) ){
            return json_encode(Array('success' => '用户信息更新成功'), JSON_UNESCAPED_UNICODE);
        }
        else{
            return json_encode(Array('error' => '用户信息更新出错'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function insert()
    {
        return $this->db->database->
        query("INSERT INTO ".$this->table ."(`id`, `userName`, `salt`, `password`, `phone`, `address`, `email`, `role`, `area`,`place`, `sex`, `realName`, `idCard`) VALUES ('$this->id', '$this->userName', '$this->salt', '$this->password', '$this->phone', '$this->address', '$this->email', '$this->role', '$this->area','$this->place', '$this->sex', '$this->realName', '$this->idCard')");
    }

    public function update()
    {
        return $this->db->database->query("UPDATE ".$this->table." SET `salt` = '$this->salt', `password` = '$this->password', `phone` = '$this->phone', `address` = '$this->address', `email` = '$this->email', `area` = '$this->area' , `place` = '$this->place' , `sex` = '$this->sex' , `realName` = '$this->realName' , `idCard` = '$this->idCard' WHERE `id` = '$this->id' AND `userName` = '$this->userName'");
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function search()
    {
        return $this->db->database->query("SELECT * FROM ".$this->table." WHERE userName = '$this->userName'");
    }

    public function login($user,$password)
    {

        if(isset($_SESSION['user'])){
            return json_encode(Array('error'=>'您已登录，无需进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        $this->userName = $user;
        $this->password = $password;

        $res = $this->search()->fetch_assoc();

        $this->rolePermission = new rolePermissionClass($res['role']);

        if(!$this->rolePermission->checkLogin())
        {
            return json_encode(Array('error'=>'登录失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if(!$res)
        {
            return json_encode(Array('error'=>'，登录失败，账号或密码错误'), JSON_UNESCAPED_UNICODE);
        }
        else
        {
            if(sha1($this->password.$res['salt']) === $res['password']){
                $this->id = $res["id"];

                $_SESSION['user'] = $this->userName;
                $_SESSION['uid'] = $this->id;

                return json_encode(Array('success'=>'登录成功'), JSON_UNESCAPED_UNICODE);
            }
            else{
                return json_encode(Array('error'=>'登录失败，账号或密码错误'), JSON_UNESCAPED_UNICODE);
            }
        }

    }

    public function logout()
    {
        if(isset($_SESSION['user'])){
            session_unset();
            session_destroy();
            return json_encode(Array('success'=>'已退出登录'), JSON_UNESCAPED_UNICODE);
        }
        else{
            return json_encode(Array('error'=>'您尚未登录，无需进行此操作'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function getUserInfo(){
        if(!isset($_SESSION['user'])){
            return json_encode(Array('error'=>'请先登录再进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        $res = $this->db->database->query("SELECT u.userName,u.phone,u.address,u.email,r.name role,a.name area,p.name place,u.sex,u.realName,u.idCard FROM $this->table u,$this->roleTable r,$this->areaTable a,$this->placeTable p WHERE u.id = '".$_SESSION['uid']."' AND u.userName = '".$_SESSION['user']."' AND r.role = u.role AND a.code = u.area AND p.area = u.area AND p.id = u.place AND a.code = p.area");
        $resNum = 0;

        $json = Array();
        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;

    }

    public function getRole(){
        if(!isset($_SESSION['user'])){
            die(json_encode(Array('error'=>'请先登录再进行此操作'), JSON_UNESCAPED_UNICODE));
        }

        $this->role = $this->search()->fetch_assoc()["role"];
        return $this->role;
    }

    public function getRolePermission(){
        if(!isset($_SESSION['user'])){
            die(json_encode(Array('error'=>'请先登录再进行此操作'), JSON_UNESCAPED_UNICODE));
        }
        $this->getRole();
        $res = $this->db->database->query("SELECT * FROM $this->rolePermissionTable WHERE role = '$this->role'");
        $resNum = 0;

        $json = Array();
        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;
    }
}

class User extends UserClass{
}