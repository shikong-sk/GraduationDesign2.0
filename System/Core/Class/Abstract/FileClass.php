<?php

require_once(dirname(__FILE__). '/../Abstract/UserClass.php');
require_once(dirname(__FILE__). '/../RolePermissionClass.php');

header('Content-Type:application/json; charset=utf-8');

abstract class FileClass
{
    var $fileName;
    var $fileSize;
    var $allowDirs = Array();
    var $MaxFileSize = 500;
    var $_root_;

    var $user;
    var $rolePermission;


    public function __construct()
    {
        if(!isset($_SESSION['user']))
        {
            die(json_encode(Array('error'=>'请先登录再进行此操作'), JSON_UNESCAPED_UNICODE));
        }
        else{
            $this->_root_ = dirname(__FILE__) .'/../../../../Storage';
            $this->user = new User();
            $this->rolePermission = new rolePermissionClass($this->user->getRole());
            if($this->rolePermission->checkAddCar() || $this->rolePermission->checkUpdateCar() || $this->rolePermission->checkDeleteCar()){
                $this->allowDirs['car']='/Car';
            }
            if($this->rolePermission->checkAddPersonnel() || $this->rolePermission->checkUpdatePersonnel() || $this->rolePermission->checkDeletePersonnel()){
                $this->allowDirs['personnel']='/Personnel';
            }

            if(count($this->allowDirs) <= 0){
                die(json_encode(Array('error'=>'您没有该权限'), JSON_UNESCAPED_UNICODE));
            }
        }
    }

    public function uploadImage($imgData,$source,$time){
        if(strlen($imgData) <= 0 && strlen($source) <= 0 && strlen($time) <= 0)
        {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        $time = strtotime($time);
        $Y = date('Y',$time);
        $m = date('m',$time);
        $d = date('d',$time);
        $H = date('H',$time);
        $i = date('i',$time);

        switch ($source){
            case 'personnel' : $dir = $this->_root_.$this->allowDirs['personnel'];$file_path = '/Storage'.$this->allowDirs['personnel'];break;
            case 'car' : $dir = $this->_root_.$this->allowDirs['car'];$file_path = '/Storage'.$this->allowDirs['car'];break;
            default :
                return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        if(!file_exists($dir))
        {
            mkdir($dir);
        }
        if(!file_exists($dir.'/'.$Y))
        {
            mkdir($dir.'/'.$Y);
        }
        if(!file_exists($dir.'/'.$Y.'/'.$m))
        {
            mkdir($dir.'/'.$Y.'/'.$m);
        }
        if(!file_exists($dir.'/'.$Y.'/'.$m.'/'.$d))
        {
            mkdir($dir.'/'.$Y.'/'.$m.'/'.$d);
        }
        if(!file_exists($dir.'/'.$Y.'/'.$m.'/'.$d.'/'.$H))
        {
            mkdir($dir.'/'.$Y.'/'.$m.'/'.$d.'/'.$H);
        }
        if(!file_exists($dir.'/'.$Y.'/'.$m.'/'.$d.'/'.$H.'/'.$i))
        {
            mkdir($dir.'/'.$Y.'/'.$m.'/'.$d.'/'.$H.'/'.$i);
        }

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $imgData, $result)) {
            $type = $result[2];

            $file_name = trim(guid(), '{}').".$type";
            $new_file = $dir.'/'.$Y.'/'.$m.'/'.$d.'/'.$H.'/'.$i."/".$file_name;

            $img = str_replace($result[1], '', $imgData);
            $imgLen = strlen($img);
            $imgSize = $imgLen - ($imgLen / 8) * 2;

            $imgSize = $imgSize / 1024;
            if($imgSize > $this->MaxFileSize)
            {
                return json_encode(Array('error' => "操作失败，文件大小不能大于 $this->MaxFileSize kB"), JSON_UNESCAPED_UNICODE);
            }

            if (file_put_contents($new_file, base64_decode($img))) {
                return json_encode(Array(0=>$file_path.'/'.$Y.'/'.$m.'/'.$d.'/'.$H.'/'.$i.'/'.$file_name,'success' => '文件上传成功'), JSON_UNESCAPED_UNICODE);
            }

        }

    }

    public function deleteImage($imgPath,$source){
        if(strlen($imgPath) <= 0)
        {
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

        switch ($source){
            case 'personnel' : break;
            case 'car' : break;
            default :
                return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }

//        die($this->_root_.'/..'.$imgPath);
        if(unlink($this->_root_.'/..'.stripcslashes($imgPath))){
            return json_encode(Array('success' => '操作成功'), JSON_UNESCAPED_UNICODE);
        }
        else{
            return json_encode(Array('error' => '操作失败，请检查参数是否正确'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function setRoot($root){
        $this->_root_ = $root;
    }
}

class FileManager extends FileClass{

}