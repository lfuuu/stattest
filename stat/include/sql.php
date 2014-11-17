<?
################################################################################
#                                                                              #
#   PhpSiteLib. ���������� ��� ������� ���������� ������                       #
#                                                                              #
#   Copyright (�) 2005, shepik (shepik@yandex.ru) - ������ �������             #
#   Copyright (�) 2002, Ilya Blagorodov (blagorodov.ru)                        #
#                                                                              #
#   psl_mysql.inc.php                                                          #
#   ����� PslMySQL: ������������ ������� � �� MySQL.                           #
#   ������������ ���� � ����� ���� ���������� ���������� PHPLIB:               #
#   PHPLIB Copyright (c) 1998-2000 NetUSE AG Boris Erdmann, Kristian Koehntopp #
#                                                                              #
################################################################################
class MySQLDatabase {
    var $mRecord = array();
    var $mRow;

    var $mErrno = 0;
    var $mError = '';

    var $_LinkId  = 0;
    var $_QueryId = 0;
    var $_Query   = '';

	var $host,$user,$pass,$db;


    function MySQLDatabase($host = null,$user = null,$pass = null,$db = null) {
    	$this->host = $host !== null ? $host : SQL_HOST;
    	$this->user = $user !== null ? $user : SQL_USER;
    	$this->pass = $pass !== null ? $pass : SQL_PASS;
    	$this->db = $db?$db:SQL_DB;
    }

    function GetLinkId() {
        return $this->_LinkId;
    }

    function GetQueryId() {
        return $this->_QueryId;
    }

    function Connect() {
        if ($this->_LinkId == 0) {
            $this->_LinkId = @mysql_connect($this->host, $this->user, $this->pass, true);
            if (!$this->_LinkId && $this->host !== 'thiamis.mcn.ru'){
                $this->_Halt("connect failed.");
                echo "can't connect mysql ".$this->host; exit;
                return 0;
            }

            if (!@mysql_select_db($this->db, $this->_LinkId) && $this->host !== 'thiamis.mcn.ru'){
                $this->_Halt("cannot use database " . $this->db);
                echo "can't use database"; exit;
                return 0;
            }
            $this->Query("set names utf8");

		}
        return $this->_LinkId;
    }

    function SwitchDB($db)
    {
        if($this->db == $db) return;

        $this->Query("use ".$db);
        $this->db = $db;
    }
        

    function Free() {
        if(!$this->_QueryId) return;
        @mysql_free_result($this->_QueryId);
        $this->_QueryId = 0;
    }
    function QueryX($query) {
    	trigger_error2(htmlspecialchars_($query));
    	$this->Query($query);
    }

    function Query($query, $saveDefault = 1) {

        global $user;

        if(defined("print_sql") || (isset($_GET["show_sql"]) && $_GET["show_sql"] == 1))
        {
            echo "\n<br>";
            printdbg($query);
        }

        if(defined("save_sql"))
        {
            $logFile = '/tmp/log.save';
            if (is_writeable($logFile)) {
                $pFile = fopen($logFile, "a+");
                fwrite($pFile, "\n------------------------------------\n" . date("r") . ": " . $query);
                fclose($pFile);
            }
        }

        if(stripos($query, "usage_ip_") !== false && stripos($query, "select") === false)
        {
            $logFile = '/var/log/nispd/log.usage_ip';
            if (is_writeable($logFile)) {
                $pFile = fopen($logFile, "a+");
                fwrite($pFile, "\n------------------------------------\n" . date("r") . ": " . $query . "\n" . str_replace(array("\r", "\n"), "", print_r($_SESSION, true)));
                fclose($pFile);
            }
        }

        if ($query == '') return 0;
		if (DEBUG_LEVEL>=2) trigger_error2(htmlspecialchars_($query));
        
        if (!$this->Connect()) return 0;
        if ($saveDefault) {
        	$this->_Query = $query;
        	if ($this->_QueryId) $this->Free();
        }
		if (DEBUG_LEVEL>=3) time_start("sql");
        $req = @mysql_query($query, $this->_LinkId);
		if (DEBUG_LEVEL>=3) trigger_error2("it took ".time_finish("sql")." seconds");

        /*
		if(mysql_errno()>0){
            if(strpos($query, "monitor_5min_ins") === false)
            {
                if(!class_exists('Logger'))
                    require_once(dirname(__FILE__)."/Logger.php");
                try{
                    throw new Exception('mysql_error');
                }catch(Exception $e){
                    $trace = $e->getTrace();
                    $pack = "MySQL error! file:'".$trace[1]['file']."', line:'".$trace[1]['line']."', query= ".$query.", \t error:".mysql_error()." \t REQUEST:".var_export(array_merge($_GET, $_POST), true);
                    Logger::put($pack, 'mysql_error','dga@mcn.ru');
                }
            }
		}*/

        $this->mRow   = 0;
        $this->mErrno = mysql_errno();
        $this->mError = mysql_error();
       	if (!$req) $this->_Halt("Invalid SQL: " . htmlspecialchars_($query));
        if ($saveDefault && is_resource($req)) $this->_QueryId = $req;
       	return $req;
    }
    function GetInsertId() {
        return $this->_LinkId ? @mysql_insert_id($this->_LinkId) : 0;
    }

  	function AllRecords($query='',$by_id='', $return_type=MYSQL_ASSOC) {
  		if ($query) $this->Query($query);
        if (!$this->_QueryId) return 0;
  		$R=array();

       	$this->mErrno  = mysql_errno();
       	$this->mError  = mysql_error();
		while ($r= @mysql_fetch_array($this->_QueryId,$return_type)){
        	$this->mRow++;
        	if ($by_id && isset($r[$by_id])) $R[$r[$by_id]]=$r; else $R[]=$r;
		}
      	$this->Free();
  		return $R;
  	}
  	function GetRow($query) {
  		$this->Query($query);
  		return $this->NextRecord(MYSQL_ASSOC);
  	}
    function GetValue($query){
        $r = $this->GetRow($query);
        if($r)
        {
            $k = array_keys($r);
            return $r[$k[0]];
        }
        return false;
    }

    function AllRecordsAssoc($sql, $key = false, $value = false)
    {
        $r = array();
        $rs = $this->AllRecords($sql);

        if(!$key)
        {
            $ks = array_keys($rs[0]);
            $key = $ks[0];
            $value = $ks[1];
        }

        foreach($rs as $l)
        {
            $r[$l[$key]] = $l[$value];
        }

        return $r;
    }
            
    function NextRecord($type=MYSQL_BOTH) {
        if (!$this->_QueryId) {
//            $this->_Halt("NextRecord called with no query pending.");
            return 0;
        }
    
        $this->mRecord = @mysql_fetch_array($this->_QueryId,$type);
        $this->mRow++;
        $this->mErrno  = mysql_errno();
        $this->mError  = mysql_error();
      
        $stat = is_array($this->mRecord);
        if (!$stat) {
        	$this->Free();
        	return 0;
		}
        return $this->mRecord;
    }

    function Lock($table, $mode = 'write') {
        $this->Connect();
        
        $query = "lock table ";
		$query .= $table . ' ' . $mode;
        $res = @mysql_query($query, $this->_LinkId);
        if (!$res) {
            $this->_Halt("lock($table, $mode) failed.");
            return 0;
        }
        return $res;
    }

    function Unlock() {
        $this->connect();
    
        $res = @mysql_query("unlock tables", $this->_LinkId);
        if (!$res) {
            $this->_Halt("unlock() failed.");
            return 0;
        }
        return $res;
    }


    function AffectedRows() {
        return @mysql_affected_rows($this->_LinkId);
    }

    function NumRows() {
        return @mysql_num_rows($this->_QueryId);
    }

    function NumFields() {
        return @mysql_num_fields($this->_QueryId);
    }
    function ListFields($table) {
        $res=mysql_list_fields($this->db, $table,$this->_LinkId);
        if (!$res) {
        	$this->_Halt("list_fields failed");
        	return 0;	
        }
        $c=mysql_num_fields($res);
        $R=array();
        for ($i=0;$i<$c;$i++) $R[]=mysql_field_name($res,$i);
        return $R;
    }

    function F($fieldName) {
        return isset($this->mRecord[$fieldName]) ? $this->mRecord[$fieldName] : '';
    }

    function _Halt($msg) {
        $this->mError = @mysql_error($this->_LinkId);
        $this->mErrno = @mysql_errno($this->_LinkId);
        if(defined("exception_sql")){
            throw new Exception($this->mError);
        }
		trigger_error2('Database error: ' . $msg, E_USER_NOTICE);
		trigger_error2('MySQL Error: ' . $this->mErrno . ' (' . $this->mError . ')', E_USER_NOTICE);
    }

    function QueryInsert($table,$data, $get_new_id=true) {
    	$V=array();
    	foreach ($data as $k=>$v)
        if (is_array($v))
          $V[]=$v[0];
        else
        {
          if (gettype($v) != 'integer') $v = '\''.mysql_real_escape_string($v).'\'';
          $V[]=$v;
        }
    	$res = $this->Query('insert into '.$table.' (`'.implode('`,`',array_keys($data)).'`) '.
    				'values ('.implode(',',$V).')',0);
      if ($get_new_id)
		    return $this->GetInsertId();
      else
        return $res;
    }
    function QueryInsertEx($table,$fields,$datas) {
    	$str0 = 'insert into '.$table.' ('.implode(',',$fields).') values ';
    	$str = ''; $c = 0;
    	foreach ($datas as $data) {
	    	if ($str) $s = ',('; else $s = '(';
	    	foreach ($data as $k=>$v)
        {
          if (gettype($v) != 'integer') $v = '\''.mysql_real_escape_string($v).'\'';
          $s.=($k?',':'').$v;
        }
    		$str.=$s.')';
    		$c++;
    		
    		if ($c>100) {
    			$this->Query($str0.$str,0);
    			$str = ''; $c = 0;
    		}
    	}
   		if ($c>0) {
   			$this->Query($str0.$str,0);
   			$str = ''; $c = 0;
   		}
    }
    function QuerySelect($table,$data,$x = 0) {
    	$V=array();
    	foreach ($data as $k=>$v)
        if (is_array($v))
          $V[]=$k.'='.addslashes($v[0]);
        else
        {
          if (gettype($v) != 'integer') $v = '\''.mysql_real_escape_string($v).'\'';
          $V[]=$k.'='.$v;
        }
    	if (!$x) return $this->Query('select * from '.$table.' where ('.implode(') AND (',$V).')');
    		else return $this->QueryX('select * from '.$table.' where ('.implode(') AND (',$V).')');
    }
    function QuerySelectAll($table, $data, $x=0){
        $r = array();
        $rs = $this->QuerySelect($table, $data, $x);
        while($l = $this->NextRecord(MYSQL_ASSOC)) $r[] =$l;
        return $r;
    }
    function QueryDelete($table,$data) {
    	$V=array();
    	foreach ($data as $k=>$v)
        if (is_array($v))
          $V[]=$k.'='.addslashes($v[0]);
        else
        {
          if (gettype($v) != 'integer') $v = '\''.mysql_real_escape_string($v).'\'';
          $V[]=$k.'='.$v;
        }
    	return $this->Query('delete from '.$table.' where ('.implode(') AND (',$V).')');
    }
    function QueryUpdate($table,$keys,$data) {
    	$V1=array(); $V2=array();
    	if (!is_array($keys)) $keys=array($keys);
    	foreach ($data as $k=>$v)
    		if (in_array($k,$keys))
        {
          if (gettype($v) != 'integer') $v = '\''.mysql_real_escape_string($v).'\'';
    			$V2[]='`'.$k.'`='.$v;
        }
    		elseif (!is_array($v))
        {
          if (gettype($v) != 'integer') $v = '\''.mysql_real_escape_string($v).'\'';
    			$V1[]='`'.$k.'`='.$v;
        }
    		else $V1[]='`'.$k.'`='.$v[0];
    	return $this->Query('update '.$table.' SET '.implode(',',$V1).' WHERE ('.implode(') AND (',$V2).')');
    }
    function QuerySelectRow($table,$data,$x = 0) {
    	$this->QuerySelect($table,$data,$x);
    	return $this->NextRecord(MYSQL_ASSOC);
    }

    public function Begin()
    {
        $this->Query("start transaction");
    }

    public function Commit()
    {
        $this->Query("commit");
    }

    public function Rollback()
    {
      $this->Query("rollback");
    }

    public static function Generate($where,$can_null = 0) {
    	if (is_array($where)) {
    		if (count($where)<=1) return ($can_null?"":"1");
    		if (count($where)==2) return MySQLDatabase::Generate($where[1], $can_null);
    		$s='('.MySQLDatabase::Generate($where[1],0);
    		for ($i=2;$i<count($where);$i++) $s.=' '.$where[0].' '.MySQLDatabase::Generate($where[$i],0);
    		$s.=')';
    		return $s;
    	} else {
    		return '('.$where.')';
    	}
    }
    function escape($str) {
        return mysql_real_escape_string($str);
    }


}
?>
