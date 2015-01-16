<?php

use app\classes\StatModule;

class password
{
    public static function hash($pass)
    {
        if (defined('USE_MD5') && USE_MD5==1)
        {
            return md5($pass);
        } else {
            return $pass;
        }
    }
}

function access($option,$acc){
    global $user;
    return $user->HasPrivelege($option,$acc);
}

function access_action($module,$action){
    $act=StatModule::getHeadOrModule($module)->actions[$action];
    return access($act[0],$act[1]);
}

class AuthUser {
    var $_Login = '';
    var $_Data;
    var $_Priveleges;

    function GetAsClient(){
        return '';
    }
    function AuthorizeByUserId($userId){
        $this->loadUserData($userId);
    }

    function loadUserData($userId = null){
        global $db;
        if ($userId === null) {
            $userId = Yii::$app->user->getId();
        }
        $db->Query('select * from user_users where id="' . $userId . '"');
        $this->_Data = $db->NextRecord();
        $this->_Login = $this->_Data['user'];
        if (isset($this->_Data['data_flags'])){
            $this->_Data['data_flags']=unserialize($this->_Data['data_flags']);
        } else {
            $this->_Data['data_flags']=array();
        }
    }

    function Get($v){
        return (isset($this->_Data[$v])?$this->_Data[$v]:'');
    }

    function _ParsePriveleges($str){

        if (!$str)
            return false;

        $r=explode(',',$str);
        $R=array();
        foreach ($r as $v) {
            $R[$v]=1;
        }
        return $R;
    }

    function _LoadPriveleges(){
        global $db;

        if (Yii::$app->user->isGuest) {
            return false;
        }

        $this->_Priveleges=array();

        $db->Query('select * from user_grant_groups where (name="'. $this->Get('usergroup'). '")');
        while ($r=$db->NextRecord()) {
            $this->_Priveleges[$r['resource']]=$this->_ParsePriveleges($r['access']);
        }

        $db->Query('select * from user_grant_users where (name="'. $this->Get('user'). '")');
        while ($r=$db->NextRecord()) {
            $ac = $this->_ParsePriveleges($r['access']);
            $this->_Priveleges[$r['resource']] = array();
            if ($ac) {
                foreach($ac as $key=>$val) {
                    $this->_Priveleges[$r['resource']][$key]=$val;
                }
            }
        }

    }

    function HasPrivelege($resource,$desired_access){
        if (Yii::$app->user->isGuest) {
            return false;
        }
        if(!$resource)
            return true;
        if(in_array($desired_access, array('test','vip','gen_mans'))){
            $u = $this->Get('user');
            if($desired_access=='test' && in_array($u,array('dns','mak'))){
                return true;
            }elseif($desired_access=='vip' && in_array($u,array('dns'))){
                return true;
            }elseif($desired_access=='gen_mans' && in_array($u,array('dns','mak','bnv','pma'))){
                return true;
            }else
                return false;
        }
        if(!isset($this->_Priveleges) || !is_array($this->_Priveleges))
            $this->_LoadPriveleges();
        if(isset($this->_Priveleges[$resource][$desired_access]))
            return true;
        return false;
    }
    function Flag($flag) {
        if (isset ($this->_Data['data_flags'][$flag])) return $this->_Data['data_flags'][$flag];
        return 0;
    }
    function SetFlag($flag,$value) {
        global $db;
        if (!is_array($this->_Data['data_flags'])) $this->_Data['data_flags']=array();
        if (!isset($this->_Data['data_flags'][$flag]) || $this->_Data['data_flags'][$flag]!=$value) {
            $this->_Data['data_flags'][$flag]=$value;
            $db->Query('update user_users set data_flags="'.AddSlashes(serialize($this->_Data['data_flags'])).'" where id='.$this->_Data['id']);
        }
    }

    public static function getSystemUserId()
    {
        global $db;

        return $db->GetValue("select id from user_users where user='system'");
    }

};
?>
