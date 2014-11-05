<?
/*
    IsAuthorized();
    Authorize();
    Login();
    Logout();
    DenyInauthorized();
    Get($v);
    HasPrivelege($resource,$desired_access);
*/


define ('USER_VAR_LOGIN',       '_mcn_user_login_'.$_SERVER['SERVER_NAME']);
define ('USER_VAR_AUTHORIZED',  '_mcn_user_authorized_'.$_SERVER['SERVER_NAME']);
define ('USER_TABLE',           'user_users');
define ('USER_FIELD_LOGIN',     'user');
define ('USER_FIELD_PASSWORD',  'pass');
define ('USER_LOGIN_FILE',      'login.tpl');

function _acc2dec($v){
    if ($v=='r') return 1;
    if ($v=='w') return 2;
    if ($v=='a') return 3;
    return 0;
}

class password
{
    function hash($pass)
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
    $m="module_".$module;    
    global $$m;
    $act=$$m->actions[$action];
    return access($act[0],$act[1]);
}

class AuthUser {
    var $_Login = '';
    var $_IsAuthorized = 0;
    var $_Data;
    var $_Priveleges;
    var $_Client;

    function IsAuthorized(){
        return $this->_IsAuthorized;
    }
    function GetAsClient(){
        if ($this->_Client){
            return $this->_Client;
        } else return '';
    }
    function Authorize(){
        global $db;
        if (isset($_SESSION[USER_VAR_AUTHORIZED]) && isset($_SESSION[USER_VAR_LOGIN]) && $_SESSION[USER_VAR_AUTHORIZED]){
            if (!($this->_Login=$_SESSION[USER_VAR_LOGIN])) return 0;
            $db->Query('select * from '.USER_TABLE.' where '.USER_FIELD_LOGIN.'="' . $this->_Login . '" and enabled="yes"');
            if (!($this->_Data=$db->NextRecord())){
                $r = $db->GetRow('select user_impersonate as A from clients where client="'.$this->_Login.'"');
                $db->Query('select * from '.USER_TABLE.' where '.USER_FIELD_LOGIN.'="'.$r['A'].'" and enabled="yes"');
                $this->_Data=$db->NextRecord();
                $this->_Client=$this->_Login;
                $this->_Login=$r['A'];
            }
            if (isset($this->_Data['data_panel'])){
                $this->_Data['data_panel']=unserialize($this->_Data['data_panel']);
            } else {
                $this->_Data['data_panel']=array();
            }
            if (isset($this->_Data['data_flags'])){
                $this->_Data['data_flags']=unserialize($this->_Data['data_flags']);
            } else {
                $this->_Data['data_flags']=array();
            }            
            $this->_IsAuthorized=1;
            return 1;
        } else return 0;
    }
    function AuthorizeByUserId($userId){
        global $db;
        $db->Query('select * from '.USER_TABLE.' where id="' . $userId . '" and enabled="yes"');
        $this->_Data = $db->NextRecord();
        $this->_Login = $this->_Data['user'];
        if (isset($this->_Data['data_panel'])){
            $this->_Data['data_panel']=unserialize($this->_Data['data_panel']);
        } else {
            $this->_Data['data_panel']=array();
        }
        if (isset($this->_Data['data_flags'])){
            $this->_Data['data_flags']=unserialize($this->_Data['data_flags']);
        } else {
            $this->_Data['data_flags']=array();
        }
        $this->_IsAuthorized=1;
        return 1;
    }
    function DoAction($action){
        if ($action=='logout')
            $this->Logout();
        else if ($action=='login')
            $this->Login();
        else if (get_param_raw('login')!='' && get_param_raw('password')!=''){
            $this->Login();
        } else $this->Authorize();
    }
    function Login(){
        global $db,$_SERVER;
        $user=get_param_protected('login');
        if (!$user) return 0;
        $pass=get_param_raw('password');
        $data = $db->GetRow($q = 'select * from '.USER_TABLE.' where '.USER_FIELD_LOGIN.'="' . $user . '" and enabled="yes"');
        if (!$data){
            //Ю БНР РСР ОПНБЕПХЛ - БДПСЦ ЩРН ЙКХЕМР?
            $db->Query('select * from clients where client="'.$user.'"');
            if (!($data2=$db->NextRecord()) || ($data2['password']!=$pass)) {
                sleep(3);
                return 0;
            }
            $this->_Client=$data2['client'];

            $db->Query('select * from '.USER_TABLE.' where '.USER_FIELD_LOGIN.'="'.$data2['user_impersonate'].'" and enabled="yes"');
            $data=$db->NextRecord();
            if (!$data) {
                sleep(1);
                trigger_error2('Вы не прошли валидацию');
                return 0;
            }
            $pass=''; $data[USER_FIELD_PASSWORD]=password::hash('');
        }
        if ($data && ($data[USER_FIELD_PASSWORD] == password::hash($pass))) {
            $this->_Login = $user;
            $this->_IsAuthorized = 1;
            $this->_Data = $data;
            if (isset($this->_Data['data_panel'])){
                $this->_Data['data_panel']=unserialize($this->_Data['data_panel']);
            } else {
                $this->_Data['data_panel']=array();
            }
            if (isset($this->_Data['data_flags'])){
                $this->_Data['data_flags']=unserialize($this->_Data['data_flags']);
            } else {
                $this->_Data['data_flags']=array();
            }
            $_SESSION[USER_VAR_AUTHORIZED] = 1;
            $_SESSION[USER_VAR_LOGIN] = $this->_Login;
            if(isset($_SESSION["save_position"])){
                $_GET = $_SESSION["save_position"]["get"];
                $_POST = $_SESSION["save_position"]["post"];
                $_REQUEST = $_GET+$_POST;
            }
            return 1;
        } else {
            sleep(3);
            return 0;
        }
    }
    function Logout(){
        $this->_IsAuthorized = 0;
    unset($_SESSION[USER_VAR_AUTHORIZED]);
    unset($_SESSION[USER_VAR_LOGIN]);
    unset($_SESSION["save_position"]);
        $this->DenyInauthorized();
    }
    function DenyInauthorized(){
        global $design;
        if (!($this->IsAuthorized())){

            if(get_param_raw("action", "")!= "login"){
                $_SESSION["save_position"] = array(
                            "get" => $_GET,
                            "post" => $_POST
                            );
            }
            $design->AddTop('empty.tpl');
            $design->AddMain('errors.tpl');
            $design->AddMain(USER_LOGIN_FILE);
            $design->Process();
            exit;
        }
    }
    function Get($v){
        return (isset($this->_Data[$v])?$this->_Data[$v]:'');
    }

    function _ParsePriveleges($str){
        $r=explode(',',$str);
        $R=array();
        foreach ($r as $v) {
            $R[$v]=1;
        }
        return $R;
    }

    function _LoadPriveleges(){
        global $db;
        if (!$this->IsAuthorized())
            return false;

        $this->_Priveleges=array();

        $db->Query('select * from user_grant_groups where (name="'. $this->Get('usergroup'). '")');
        while ($r=$db->NextRecord()) {
            $this->_Priveleges[$r['resource']]=$this->_ParsePriveleges($r['access']);
        }

        $db->Query('select * from user_grant_users where (name="'. $this->Get('user'). '")');
        while ($r=$db->NextRecord()) {
            $ac = $this->_ParsePriveleges($r['access']);
            $this->_Priveleges[$r['resource']] = array();
            foreach($ac as $key=>$val)
                $this->_Priveleges[$r['resource']][$key]=$val;
        }
    }

    function HasPrivelege($resource,$desired_access){
        global $db;
        if(!$this->IsAuthorized())
            return false;
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

    function getSystemUserId()
    {
        global $db;

        return $db->GetValue("select id from user_users where user='system'");
    }

};
?>
