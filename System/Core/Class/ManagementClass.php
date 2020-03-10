<?php

/*
 * 管理类
 */

require_once(dirname(__FILE__) . '/Interface/SqlMethod.php');

require_once(dirname(__FILE__) . '/SqlHelper.php');

require_once(dirname(__FILE__) . '/RolePermissionClass.php');

require_once(dirname(__FILE__) . '/Abstract/UserClass.php');


class  ManagementClass
{
    var $db;
    var $user;
    var $rolePermission;

    var $userTable;
    var $roleTable;
    var $areaTable;
    var $placeTable;
    var $rolePermissionTable;
    var $deviceTable;
    var $carTable;
    var $personnelTable;

    public function __construct()
    {
        $this->db = new SqlHelper();

        $this->user = new User();

        $this->rolePermission = new rolePermissionClass($this->user->getRole());

        $this->userTable = $this->db->db_table_prefix . "_" . SqlHelper::User;
        $this->roleTable = $this->db->db_table_prefix . "_" . SqlHelper::Role;
        $this->areaTable = $this->db->db_table_prefix . "_" . SqlHelper::Area;
        $this->placeTable = $this->db->db_table_prefix . "_" . SqlHelper::Place;
        $this->rolePermissionTable = $this->db->db_table_prefix . "_" . SqlHelper::RolePermission;
        $this->deviceTable = $this->db->db_table_prefix . "_" . SqlHelper::Device;
        $this->carTable = $this->db->db_table_prefix . "_" . SqlHelper::Car;
        $this->personnelTable = $this->db->db_table_prefix . "_" . SqlHelper::Personnel;

    }

    /*
     * 信息查询
     */

    public function getAdminList(){
        if ($this->user->getRole() != '000' && $this->user->getRole() != '001') {
            return json_encode(Array('error' => '查询失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }
        $query = "SELECT u.id,u.userName,u.phone,u.address,u.email,r.name role,a.name area,p.name place,u.sex,u.realName,u.idCard FROM $this->userTable u,$this->areaTable a,$this->placeTable p,$this->roleTable r WHERE u.role = r.role AND a.code = u.area AND p.id = u.place AND a.code = p.area AND (u.role = '000' OR u.role = '001')";
        $res = $this->db->database->query($query);
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

    public function getRoleList()
    {
        return $this->rolePermission->getRoleList();
    }

    public function getRolePermissionList()
    {
        return $this->rolePermission->getRolePermissionList();
    }

    public function getCarList($page, $num, $filter,$arg)
    {
        if (!$this->rolePermission->checkSearchCar()) {
            return json_encode(Array('error' => '查询失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT c.time,c.licensePlate,a.name area,p.name place,c.come FROM $this->carTable c,$this->areaTable a,$this->placeTable p WHERE c.area = a.code AND c.place = p.id AND c.area = p.area AND p.area = a.code ";

        $page = ($page - 1) * $num;
        if(strlen($filter) != 0)
        {
            switch ($filter){
                case 'beforeTime': $query .= "AND c.time < '".base64_decode($arg)."'";break;//  base 64 加解密
                case 'afterTime': $query .= "AND c.time > '".base64_decode($arg)."'";break;
                case 'betweenTime':
                    //  JSON格式{"startTime":"2020-03-02 0:0:0","endTime":"2020-03-03 0:0:0"} base 64 加解密
                    $json = json_decode(base64_decode($arg),true);
                    if(!$json) return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
                    $query .= "AND c.time > '".$json["startTime"]."' AND c.time < '".$json["endTime"]."'";
                    break;
                case 'licensePlate': $query .= "AND c.licensePlate LIKE '%$arg%'";break;
                case 'come': $query .= "AND c.come LIKE '%$arg%'";break;
                case 'area': $query .= "AND (a.name LIKE '%$arg%' OR p.name LIKE '%$arg%')";break;
                default:
                    return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }

        $count = $this->db->database->query($query)->num_rows;
        $json = Array('0'=>$count);

        $query .= " order by c.time DESC LIMIT $page,$num";

        $res = $this->db->database->query($query);
        $resNum = 0;

        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    public function getUserList($page, $num, $filter, $arg,$extend = '')
    {
        if (!$this->rolePermission->checkSearchUser()) {
            return json_encode(Array('error' => '查询失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }
        $page = ($page - 1) * $num;
        $query = "SELECT u.id,u.userName,u.phone,u.address,u.email,r.name role,a.name area,p.name place,u.sex,u.realName,u.idCard FROM $this->userTable u,$this->areaTable a,$this->placeTable p,$this->roleTable r WHERE u.role = r.role AND a.code = u.area AND p.id = u.place AND a.code = p.area ";


        if(strlen($filter) != 0)
        {
            switch ($filter){
                case 'userName': $query .= "AND u.userName LIKE '%$arg%'";break;
                case 'email': $query .= "AND u.email LIKE '%$arg%'";break;
                case 'idCard': $query .= "AND u.idCard LIKE '%$arg%'";break;
                case 'address': $query .= "AND u.address LIKE '%$arg%'";break;
                case 'role': $query .= "AND r.name LIKE '%$arg%'";break;
                case 'realName': $query .= "AND u.realName LIKE '%$arg%'";break;
                case 'phone': $query .= "AND u.phone LIKE '%$arg%'";break;
                case 'sex': $query .= "AND u.sex LIKE '%$arg%'";break;
                case 'area': $query .= "AND (a.name LIKE '%$arg%' OR p.name LIKE '%$arg%')";break;
                default:
                    return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }

        if(strlen($extend) != 0)
        {
            switch ($extend)
            {
                case 'notAdmin': $query .= " AND (u.role != '001' AND u.role != '000')"; break;
                default:
                    return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }

        $count = $this->db->database->query($query)->num_rows;
        $json = Array('0'=>$count);

        $query .= " LIMIT $page,$num";

        $res = $this->db->database->query($query);
        $resNum = 0;

        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    public function getDeviceList($page, $num, $filter, $arg)
    {
        if (!$this->rolePermission->checkSearchDevice()) {
            return json_encode(Array('error' => '查询失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }
        $page = ($page - 1) * $num;
        $query = "SELECT d.deviceId,d.name,d.status,a.name area,p.name place,d.liveTime,d.ip FROM $this->deviceTable d,$this->areaTable a,$this->placeTable p WHERE d.area = a.code AND d.place = p.id AND d.area = p.area AND p.area = a.code ";

        if(strlen($filter) != 0)
        {
            switch ($filter){
                case 'beforeTime': $query .= "AND d.liveTime < '".base64_decode($arg)."'";break;//  base 64 加解密
                case 'afterTime': $query .= "AND d.liveTime > '".base64_decode($arg)."'";break;
                case 'betweenTime':
                    //  JSON格式{"startTime":"2020-03-02 0:0:0","endTime":"2020-03-03 0:0:0"} base 64 加解密
                    $json = json_decode(base64_decode($arg),true);
                    if(!$json) return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
                    $query .= "AND d.liveTime > '".$json["startTime"]."' AND d.liveTime < '".$json["endTime"]."'";
                    break;
                case 'ip': $query .= "AND d.ip LIKE '%$arg%'";break;
                case 'status': $query .= "AND d.status LIKE '%$arg%'";break;
                case 'name': $query .= "AND d.name LIKE '%$arg%'";break;
                case 'area': $query .= "AND (a.name LIKE '%$arg%' OR p.name LIKE '%$arg%')";break;
                default:
                    return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }

        $count = $this->db->database->query($query)->num_rows;
        $json = Array('0'=>$count);

        $query .= " LIMIT $page,$num";
        $res = $this->db->database->query($query);
        $resNum = 0;

        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    public function getPersonnelList($page, $num, $filter,$arg)
    {
        if (!$this->rolePermission->checkSearchPersonnel()) {
            return json_encode(Array('error' => '查询失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }
        $page = ($page - 1) * $num;
        $query = "SELECT pe.time,pe.name,a.name area,p.name place,pe.idCard,pe.come,pe.temp FROM $this->personnelTable pe,$this->areaTable a,$this->placeTable p WHERE pe.area = a.code AND pe.place = p.id AND pe.area = p.area AND p.area = a.code ";

        if(strlen($filter) != 0)
        {
            switch ($filter){
                case 'beforeTime': $query .= "AND pe.time < '".base64_decode($arg)."'";break;//  base 64 加解密
                case 'afterTime': $query .= "AND pe.time > '".base64_decode($arg)."'";break;
                case 'betweenTime':
                    //  JSON格式{"startTime":"2020-03-02 0:0:0","endTime":"2020-03-03 0:0:0"} base 64 加解密
                    $json = json_decode(base64_decode($arg),true);
                    if(!$json) return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
                    $query .= "AND pe.time > '".$json["startTime"]."' AND pe.time < '".$json["endTime"]."'";
                    break;
                case 'lowerTemp': $query .= "AND pe.temp < $arg";break;
                case 'higherTemp': $query .= "AND pe.temp > $arg";break;
                case 'idCard' : $query .= "AND pe.idCard LIKE '%$arg%'";break;
                case 'come': $query .= "AND pe.come LIKE '%$arg%'";break;
                case 'name': $query .= "AND pe.name LIKE '%$arg%'";break;
                case 'area': $query .= "AND (a.name LIKE '%$arg%' OR p.name LIKE '%$arg%')";break;
                default:
                    return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }

        $count = $this->db->database->query($query)->num_rows;
        $json = Array('0'=>$count);

        $query .= " order by pe.time DESC LIMIT $page,$num";

        $res = $this->db->database->query($query);
        $resNum = 0;

        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    public function getAreaList($page, $num ,$filter, $arg)
    {
//        if(!$this->rolePermission->checkEditArea()){
//            return json_encode(Array('error'=>'查询失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
//        }
        $page = ($page - 1) * $num;
        $query = "SELECT code area,name,level,parentCode FROM $this->areaTable WHERE 1=1 ";
        $count = "SELECT count(*) as num FROM $this->areaTable WHERE 1=1 ";

        if(strlen($filter) != 0)
        {
            switch ($filter){
                case 'name': $query .= "AND name LIKE '%$arg%'"; $count .= "AND name LIKE '%$arg%'";break;
                case 'level': $query .= "AND level = $arg"; $count .= "AND level = $arg";break;
                default:
                    return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }

        $count = $this->db->database->query($count)->fetch_assoc()['num'];
        $json = Array('0'=>intval($count));

        $query .= " LIMIT $page,$num";
        $res = $this->db->database->query($query);
        $resNum = 0;

        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    public function getPlaceList($page, $num, $filter, $arg)
    {
//        if(!$this->rolePermission->checkEditArea()){
//            return json_encode(Array('error'=>'查询失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
//        }
        $page = ($page - 1) * $num;
        $query = "SELECT p.id place,a.name area,a.code areaCode,p.name FROM $this->placeTable p,$this->areaTable a WHERE p.area = a.code ";

        $count = "SELECT count(*) as num FROM $this->placeTable p,$this->areaTable a WHERE p.area = a.code ";

        if(strlen($filter) != 0)
        {
            switch ($filter){
                case 'name': $query .= "AND p.name LIKE '%$arg%'";$count .= "AND p.name LIKE '%$arg%'";break;
                case 'area': $query .= "AND (a.name LIKE '%$arg%')";$count .= "AND p.name LIKE '%$arg%'";break;
                case 'areaCode': $query .= "AND a.code = '$arg'";$count .= "AND a.code = '$arg'";break;
                default:
                    return json_encode(Array('error' => '查询失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }

        $count = $this->db->database->query($count)->fetch_assoc()['num'];
        $json = Array('0'=>intval($count));

        $query .= " LIMIT $page,$num";
        $res = $this->db->database->query($query);
        $resNum = 0;

        while ($res->data_seek($resNum)) {
            $data = $res->fetch_assoc();
            array_push($json, $data);
            $resNum++;
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    /*
     * 添加记录
     */
    public function addCar($licensePlate, $area, $place, $come)
    {
        if (!$this->rolePermission->checkAddCar()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($licensePlate) < 7 || strlen($area) == 0 || strlen($place) == 0 || strlen($come) == 0 ) {
            return json_encode(Array('error' => '操作失败，请检查信息是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "INSERT INTO $this->carTable(`time`, `licensePlate`, `area`, `place`, `come`) VALUES (from_unixtime(" . time() . "), '$licensePlate', '$area', '$place', $come)";

        $res = $this->db->database->query($query);
        $err = $this->db->database->errno;
        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,区域 或 场所不存在'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function addDevice($id, $name, $area, $place, $ip)
    {
        if (!$this->rolePermission->checkAddDevice()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        $query = "INSERT INTO $this->deviceTable(`deviceID`, `name`, `status`, `area`, `place`, `liveTime`, `ip`) VALUES ('$id', '$name', 0, '$area', '$place',from_unixtime(" . time() . ") , '$ip')";

        $res = $this->db->database->query($query);

        $err = $this->db->database->errno;
        if ($err == 1062) {
            return json_encode(Array('error' => '操作失败,设备id与已有设备重复'), JSON_UNESCAPED_UNICODE);
        }

        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,区域 或 场所不存在'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function addPersonnel($name, $idCard, $area, $place, $come, $temp)
    {
        if (!$this->rolePermission->checkAddPersonnel()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }


        if(strlen($temp) != 0)
        {
            if(floatval($temp) <= 0 || floatval($temp) >= 100)
            {
                return json_encode(Array('error' => '操作失败，请检查信息是否正确'), JSON_UNESCAPED_UNICODE);
            }
        }
        else{
            $temp = 0;
        }
        if (strlen($name) == 0 || strlen($idCard) == 0 || strlen($area) == 0 || strlen($place) == 0 || strlen($come) == 0 || intval($come) < 0 || intval($come) > 1) {
            return json_encode(Array('error' => '操作失败，请检查信息是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "INSERT INTO $this->personnelTable(`time`, `name`, `idCard`, `area`, `place`, `come`, `temp`) VALUES (from_unixtime(" . time() . "), '$name', '$idCard', '$area', '$place', $come, $temp)";

        $res = $this->db->database->query($query);
        $err = $this->db->database->errno;
        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,区域 或 场所不存在'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function addUser($user, $password, $phone, $address, $email, $role, $area, $place, $sex, $realName, $idCard)
    {
        if (!$this->rolePermission->checkAddUser()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if(strlen($role) == 0)
        {
            return json_encode(Array('error' => '操作失败，请选择用户组'), JSON_UNESCAPED_UNICODE);
        }

        if ($role == '000') {
            return json_encode(Array('error' => '操作失败，不可添加超级管理员用户'), JSON_UNESCAPED_UNICODE);
        }
        if ($role == '001') {
            if (!$this->rolePermission->checkAddAdmin()) {
                return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
            }
        }

        if (strlen($user) == 0 || strlen($password) == 0 || strlen($area) == 0 || strlen($place) == 0) {
            return json_encode(Array('success' => '操作失败，请检查信息是否完整'), JSON_UNESCAPED_UNICODE);
        }

        $id = trim(guid(), '{}');

        $salt = ''; // 随机加密密钥
        while (strlen($salt) < 6) {
            $x = mt_rand(0, 9);
            $salt .= $x;
        }

        $password = sha1($password . $salt); // sha1哈希加密

        $res = $this->db->database->query("INSERT INTO " . $this->userTable . "(`id`, `userName`, `salt`, `password`, `phone`, `address`, `email`, `role`, `area`,`place`, `sex`, `realName`, `idCard`) VALUES ('$id', '$user', '$salt', '$password', '$phone', '$address', '$email', '$role', '$area','$place', '$sex', '$realName', '$idCard')");

        if ($res) {
            return json_encode(Array('success' => '用户添加成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '用户添加失败,该用户已存在'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function addRole($id, $name)
    {
        if (!$this->rolePermission->checkEditRole()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) != 3) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "INSERT INTO $this->roleTable(`role`, `name`) VALUES ('$id', '$name')";
        $addRoleRes = $this->db->database->query($query);
        $query = "INSERT INTO $this->rolePermissionTable(`role`, `login`, `addUser`, `deleteUser`, `updateUser`, `searchUser`, `addDevice`, `deleteDevice`, `updateDevice`, `searchDevice`, `addPersonnel`, `deletePersonnel`, `updatePersonnel`, `searchPersonnel`, `addAdmin`, `deleteAdmin`, `editArea`, `editPlace`, `editRole`, `editRolePermission`, `addCar`, `deleteCar`, `updateCar`, `searchCar`) VALUES ('$id', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
        $addRolePermissionRes = $this->db->database->query($query);

        if ($this->db->database->errno == 1062) {
            return json_encode(Array('error' => '操作失败，id 或 名称重复'), JSON_UNESCAPED_UNICODE);
        }

        if ($addRoleRes && $addRolePermissionRes) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function addPlace($id, $area, $name)
    {
        if (!$this->rolePermission->checkEditPlace()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) != 5) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
        $query = "INSERT INTO $this->placeTable(`id`, `area`, `name`) VALUES ('$id', '$area', '$name');";

        $res = $this->db->database->query($query);
        $err = $this->db->database->errno;

        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,区域不存在'), JSON_UNESCAPED_UNICODE);
        }
        if ($err == 1062) {
            return json_encode(Array('error' => '操作失败,场所id已被使用'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function addArea($code, $name, $level, $parentCode)
    {
        if (!$this->rolePermission->checkEditArea()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($code) != 12 || strlen($parentCode) == 0 || strlen($name) == 0 || strlen($level) == 0 || intval($level) <= 0 || intval($level) > 5) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT code FROM $this->areaTable WHERE level = " . (intval($level) - 1) . " AND code = '$parentCode'";
        $res = $this->db->database->query($query)->fetch_assoc();

        if (!$res) {
            return json_encode(Array('error' => '操作失败，父级区域不存在'), JSON_UNESCAPED_UNICODE);
        }

        $query = "INSERT INTO $this->areaTable(`code`, `name`, `level`, `parentCode`) VALUES ('$code', '$name', " . intval($level) . ", '$parentCode')";

        $res = $this->db->database->query($query);
        $err = $this->db->database->errno;

        if ($err == 1062) {
            return json_encode(Array('error' => '操作失败,区域已存在'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    /*
     * 更新记录
     */
    public function updateRolePermission($role,$login,$addUser,$deleteUser,$updateUser,$searchUser,$addDevice,$deleteDevice,$updateDevice,$searchDevice,$addPersonnel,$deletePersonnel,$updatePersonnel,$searchPersonnel,$addAdmin,$deleteAdmin,$editArea,$editPlace,$editRole,$editRolePermission,$addCar,$deleteCar,$updateCar,$searchCar)
    {
        if (!$this->rolePermission->checkEditRolePermission()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if($role == '000'){
            return json_encode(Array('error' => '操作失败，不可修改超级管理员的权限'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT role FROM $this->userTable WHERE id='".$_SESSION['uid']."'";
        $res = $this->db->database->query($query)->fetch_assoc()['role'];

        if($res == $role && ($res != '000' || $res != '001'))
        {
            return json_encode(Array('error' => '操作失败，你无权修改自身用户组的权限'), JSON_UNESCAPED_UNICODE);
        }

        if($addUser == '1' && !$this->rolePermission->checkAddUser())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }
        else if($deleteUser == '1' && !$this->rolePermission->checkDeleteUser())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($updateUser == '1' && !$this->rolePermission->checkUpdateUser())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($searchUser == '1' && !$this->rolePermission->checkSearchUser())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($addDevice == '1' && !$this->rolePermission->checkAddDevice())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($deleteDevice == '1' && !$this->rolePermission->checkDeleteDevice())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($updateDevice == '1' && !$this->rolePermission->checkUpdateDevice())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($searchDevice == '1' && !$this->rolePermission->checkSearchDevice())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($addPersonnel == '1' && !$this->rolePermission->checkAddPersonnel())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($deletePersonnel == '1' && !$this->rolePermission->checkDeletePersonnel())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($updatePersonnel == '1' && !$this->rolePermission->checkUpdatePersonnel())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($searchPersonnel == '1' && !$this->rolePermission->checkSearchPersonnel())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($addAdmin == '1' && !$this->rolePermission->checkAddAdmin())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($deleteAdmin == '1' && !$this->rolePermission->checkDeleteAdmin())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($editArea == '1' && !$this->rolePermission->checkEditArea())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($editPlace == '1' && !$this->rolePermission->checkEditPlace())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($editRole == '1' && !$this->rolePermission->checkEditRole())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($editRolePermission == '1' && !$this->rolePermission->checkEditRolePermission())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($addCar == '1' && !$this->rolePermission->checkAddCar())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($deleteCar == '1' && !$this->rolePermission->checkDeleteCar())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($updateCar == '1' && !$this->rolePermission->checkUpdateCar())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }else if($searchCar == '1' && !$this->rolePermission->checkSearchCar())
        {
            return json_encode(Array('error' => '操作失败，你无法给予比你用户组权限更大的权限'), JSON_UNESCAPED_UNICODE);
        }

//        $role = $this->db->database->query("SELECT r.role FROM $this->roleTable r,$this->rolePermissionTable rp WHERE r.name = '$role'")->fetch_assoc()['role'];

        $query = "UPDATE $this->rolePermissionTable SET `login` = $login, `addUser` = $addUser, `deleteUser` = $deleteUser, `updateUser` = $updateUser, `searchUser` = $searchUser, `addDevice` = $addDevice, `deleteDevice` = $deleteDevice, `updateDevice` = $updateDevice, `searchDevice` = $searchDevice, `addPersonnel` = $addPersonnel, `deletePersonnel` = $deletePersonnel, `updatePersonnel` = $updatePersonnel, `searchPersonnel` = $searchPersonnel, `addAdmin` = $addAdmin, `deleteAdmin` = $deleteAdmin, `editArea` = $editArea, `editPlace` = $editPlace, `editRole` = $editRole, `editRolePermission` = $editRolePermission, `addCar` = $addCar, `deleteCar` = $deleteCar, `updateCar` = $updateCar, `searchCar` = $searchCar WHERE `role` = '$role'";

        $res = $this->db->database->query($query);

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && ($this->db->database->affected_rows == 1)) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function updateUser($id, $phone, $address, $email, $role, $area, $place, $sex, $realName, $idCard)
    {
        if (!$this->rolePermission->checkUpdateUser()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($phone . $address . $email . $area . $place . $sex . $realName . $idCard) == 0) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT role FROM $this->userTable WHERE id='$id'";

        $res = $this->db->database->query($query)->fetch_assoc()['role'];

        if ($res != $role && ($res == '000' || $role == '000')) {
            return json_encode(Array('error' => '操作失败，不可添加或删除超级管理员'), JSON_UNESCAPED_UNICODE);
        }

        if ($res != $role && !$this->rolePermission->checkEditRole()) {
            return json_encode(Array('error' => '操作失败，您没有修改用户组的权限'), JSON_UNESCAPED_UNICODE);
        } elseif ($res != $role && ($role == '001' && !$this->rolePermission->checkAddAdmin())) {
            return json_encode(Array('error' => '操作失败，您没有添加管理员的权限'), JSON_UNESCAPED_UNICODE);
        } elseif ($res != $role && ($res == '001' && !$this->rolePermission->checkDeleteAdmin())) {
            return json_encode(Array('error' => '操作失败，您没有删除管理员的权限'), JSON_UNESCAPED_UNICODE);
        }

        $res = $this->db->database->query("UPDATE " . $this->userTable . " SET `phone` = '$phone', `address` = '$address', `email` = '$email', `role` = '$role', `area` = '$area' , `place` = '$place' , `sex` = '$sex' , `realName` = '$realName' , `idCard` = '$idCard' WHERE `id` = '$id'");

        $err = $this->db->database->errno;
        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,请先选择区域或场所'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }
        if ($res && ($this->db->database->affected_rows == 1)) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateDevice($id, $name, $area, $place, $ip)
    {
        if (!$this->rolePermission->checkUpdateDevice()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) == 0 || strlen($name) == 0 || strlen($area) == 0 || strlen($place) == 0 || strlen($ip) == 0) {
            return json_encode(Array('success' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "UPDATE $this->deviceTable SET `area` = '$area', `place` = '$place', `name` = '$name',`ip` = '$ip' WHERE `deviceId` = '$id'";

        $res = $this->db->database->query($query);

        $err = $this->db->database->errno;
        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,请先选择区域或场所'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateCar($time, $licensePlate, $area, $place, $come)
    {
        if (!$this->rolePermission->checkUpdateCar()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        $time = base64_decode($time);

        if ( strlen($time) == 0|| strlen($licensePlate) < 7 || strlen($area) == 0 || strlen($place) == 0 || strlen($come) == 0 ) {
            return json_encode(Array('success' => '操作失败，请检查信息是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "UPDATE $this->carTable SET `time` = '$time',`area` = '$area', `place` = '$place', `come` = $come WHERE `time` = '$time' AND `licensePlate` = '$licensePlate'";

        $res = $this->db->database->query($query);

        $err = $this->db->database->errno;
        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,请先选择区域或场所'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function updatePersonnel($time, $name, $idCard, $area, $place, $come, $temp)
    {
        if (!$this->rolePermission->checkUpdatePersonnel()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        $time = base64_decode($time);

//        if (strlen($time) == 0 || strlen($name) == 0 || floatval($temp) <= 0 || floatval($temp) >= 100 || strlen($idCard) == 0 || strlen($area) == 0 || strlen($place) == 0 || strlen($come) == 0 || intval($come) < 0 || intval($come) > 1) {
//            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
//        }

        $query = "UPDATE $this->personnelTable SET `time` = '$time',`area` = '$area', `place` = '$place', `come` = $come, `temp` = $temp WHERE `time` = '$time' AND `name` = '$name' AND `idCard` = '$idCard'";

        $res = $this->db->database->query($query);

        $err = $this->db->database->errno;
        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,请先选择区域或场所'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function addAdmin($id, $name)
    {
        if (!$this->rolePermission->checkAddAdmin()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if ($id == $_SESSION['uid'] || $name == $_SESSION['user']) {
            return json_encode(Array('error' => '操作失败，你无权修改自己的用户组'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT role FROM $this->userTable WHERE id='$id' AND userName = '$name'";

        $res = $this->db->database->query($query)->fetch_assoc()['role'];

        if ($res == $this->rolePermission->role || $res == '000') {
            return json_encode(Array('error' => '操作失败，你无权修改与你相同用户组的用户'), JSON_UNESCAPED_UNICODE);
        }

        if ($res == '001') {
            return json_encode(Array('error' => '操作失败，该用户已经是管理员'), JSON_UNESCAPED_UNICODE);
        }

        $query = "UPDATE $this->userTable SET `role` = '001' WHERE `id` = '$id' AND `userName` = '$name'";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteAdmin($id, $name)
    {
        if (!$this->rolePermission->checkDeleteAdmin()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if ($id == $_SESSION['uid'] || $name == $_SESSION['user']) {
            return json_encode(Array('error' => '操作失败，不可删除或修改自己的权限'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT role FROM $this->userTable WHERE id='$id' AND userName = '$name'";

        $res = $this->db->database->query($query)->fetch_assoc()['role'];

        if ($res != '001') {
            return json_encode(Array('error' => '操作失败，该用户不是管理员，无需进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        if ($res == '000') {
            return json_encode(Array('error' => '操作失败，不可删除或修改超级管理员'), JSON_UNESCAPED_UNICODE);
        }

        $query = "UPDATE $this->userTable SET `role` = '002' WHERE `id` = '$id' AND `userName` = '$name'";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateRole($id, $name)
    {
        if (!$this->rolePermission->checkEditRole()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) != 3) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "UPDATE $this->roleTable SET `name` = '$name' WHERE `role` = '$id'";

        $res = $this->db->database->query($query);

        if ($this->db->database->errno == 1062) {
            return json_encode(Array('error' => '操作失败，id 或 名称重复'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function updatePlace($id, $area, $name)
    {
        if (!$this->rolePermission->checkEditPlace()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) != 5) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
        $query = "UPDATE $this->placeTable SET `name` = '$name' WHERE `id` = '$id' AND `area` = '$area'";

        $res = $this->db->database->query($query);
        $err = $this->db->database->errno;

        if ($err == 1452) {
            return json_encode(Array('error' => '操作失败,区域不存在'), JSON_UNESCAPED_UNICODE);
        }
        if ($err == 1062) {
            return json_encode(Array('error' => '操作失败,场所id已被使用'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateArea($code, $name, $level, $parentCode)
    {
        if (!$this->rolePermission->checkEditArea()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if ($code == '0') {
            return json_encode(Array('error' => '操作失败，该区域为根级区域，不可修改'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($code) != 12 || strlen($parentCode) == 0 || strlen($name) == 0 || strlen($level) == 0 || intval($level) <= 0 || intval($level) > 5) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "UPDATE $this->areaTable SET `name` = '$name' WHERE `code` = '$code' AND `level` = '$level' AND `parentCode` = '$parentCode'";

        $res = $this->db->database->query($query);

        if ($res && ($this->db->database->affected_rows == 0)) {
            return json_encode(Array('info' => '数据未更改'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    /*
     * 删除记录
     */
    public function deleteCar($time, $licensePlate)
    {
        if (!$this->rolePermission->checkDeletePersonnel()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        $time = base64_decode($time);

        if (strlen($time) == 0 || strlen($licensePlate) < 7) {
            return json_encode(Array('success' => '操作失败，请检查信息是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "DELETE FROM $this->carTable WHERE `time` = '$time' AND `licensePlate` = '$licensePlate'";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteDevice($id, $name)
    {
        if (!$this->rolePermission->checkDeleteDevice()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) == 0 || strlen($name) == 0) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "DELETE FROM $this->deviceTable WHERE `deviceId` = '$id' AND `name` = '$name'";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function deletePersonnel($time, $name, $idCard)
    {
        if (!$this->rolePermission->checkDeletePersonnel()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        $time = base64_decode($time);

        if (strlen($time) == 0 || strlen($name) == 0 || strlen($idCard) == 0) {
            return json_encode(Array('error' => '操作失败，请检查信息是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "DELETE FROM $this->personnelTable WHERE `time` = '$time' AND `name` = '$name' AND `idCard` = '$idCard'";

        $res = $this->db->database->query($query);

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteUser($id, $user)
    {
        if (!$this->rolePermission->checkDeleteUser()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT role FROM $this->userTable WHERE id='$id' AND userName = '$user'";

        $res = $this->db->database->query($query)->fetch_assoc()['role'];


        if ($res == '000') {
            return json_encode(Array('error' => '操作失败，不可删除超级管理员用户'), JSON_UNESCAPED_UNICODE);
        }
        if ($res == '001') {
            if (!$this->rolePermission->checkDeleteAdmin()) {
                return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
            }
        }

        $res = $this->db->database->query("DELETE FROM $this->userTable WHERE id = '$id' AND userName = '$user'");

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function deletePlace($id, $area)
    {
        if (!$this->rolePermission->checkEditPlace()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) != 5) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        if ($id == '00000') {
            return json_encode(Array('error' => '操作失败，该场所为系统自动生成，不可修改'), JSON_UNESCAPED_UNICODE);
        }

        $area = $this->db->database->query("SELECT p.area FROM $this->areaTable a,$this->placeTable p WHERE p.area = a.code AND a.name = '$area' AND p.id = '$id'")->fetch_assoc()['area'];

        $query = "DELETE FROM $this->placeTable WHERE id = '$id' AND area = '$area'";

        $res = $this->db->database->query($query);

        if ($this->db->database->errno == 1451) {
            return json_encode(Array('error' => '操作失败，该场所已有用户或设备，请先修改其场所后再进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function deleteRole($id)
    {
        if (!$this->rolePermission->checkEditRole()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($id) != 3) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        if ($id == '000' || $id == '888' || $id == '001' || $id == '002') {
            return json_encode(Array('error' => '操作失败，该用户组为系统用户组，不可修改'), JSON_UNESCAPED_UNICODE);
        }

        $query = "DELETE FROM $this->rolePermissionTable WHERE role = '$id'";

        $deleteRolePermissionRes = $this->db->database->query($query);

        if ($this->db->database->errno == 1451) {
            return json_encode(Array('error' => '操作失败，该用户组已有用户，请先修改其用户组后再进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        $query = "DELETE FROM $this->roleTable WHERE role = '$id'";

        $deleteRoleRes = $this->db->database->query($query);

        if ($deleteRolePermissionRes && $deleteRoleRes && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

    }

    public function deleteArea($code, $name, $level, $parentCode)
    {
        if (!$this->rolePermission->checkEditArea()) {
            return json_encode(Array('error' => '操作失败，该用户组无此权限'), JSON_UNESCAPED_UNICODE);
        }

        if ($code == '0') {
            return json_encode(Array('error' => '操作失败，该区域为根级区域，不可删除'), JSON_UNESCAPED_UNICODE);
        }

        if (strlen($code) != 12 || strlen($parentCode) == 0 || strlen($name) == 0 || strlen($level) == 0 || intval($level) <= 0 || intval($level) > 5) {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $query = "SELECT code FROM $this->areaTable WHERE level = " . (intval($level) + 1) . " AND parentCode = '$code'";
        $res = $this->db->database->query($query)->fetch_assoc();

        if ($res) {
            return json_encode(Array('error' => '操作失败，该区域已有子级，请先删除子级后再进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        $query = "DELETE FROM $this->areaTable WHERE `code` = '$code' AND `name` = '$name' AND `level` = '$level' AND `parentCode` = '$parentCode'";

        $res = $this->db->database->query($query);
        $err = $this->db->database->errno;

        if ($err == 1451) {
            return json_encode(Array('error' => '操作失败，该区域已被使用，请先修改后再进行此操作'), JSON_UNESCAPED_UNICODE);
        }

        if ($res && $this->db->database->affected_rows == 1) {
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }
}