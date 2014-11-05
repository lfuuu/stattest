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
class PgSQLDatabase {
    var $mRecord = array();

    var $mErrno = 0;
    var $mError = '';

    var $_LinkId  = 0;
    var $_QueryId = 0;
    var $_Query   = '';

	var $host,$user,$pass,$db;

    function __construct($host = null,$user = null,$pass = null,$db = null) {
    	$this->host = $host?$host:PGSQL_HOST;
    	$this->user = $user?$user:PGSQL_USER;
    	$this->pass = $pass?$pass:PGSQL_PASS;
    	$this->db = $db?$db:PGSQL_DB;
    }

    function GetLinkId() {
        return $this->_LinkId;
    }

    function GetQueryId() {
        return $this->_QueryId;
    }

    function Connect() {
        if ($this->_LinkId == 0) {
        	
            $this->_LinkId = @pg_connect("host=".$this->host." port=5432 dbname=".$this->db." user=".$this->user." password=".$this->pass);
            if (!$this->_LinkId && $this->host !== 'thiamis.mcn.ru'){
                $this->_Halt("connect failed.");
                
				throw new Exception("can't connect postgres");
            }

		}
        return $this->_LinkId;
    }

    function Free() {
        if(!$this->_QueryId) return;
        @pg_free_result($this->_QueryId);
        $this->_QueryId = 0;
    }
    function QueryX($query) {
    	trigger_error2(htmlspecialchars_($query));
    	$this->Query($query);
    }

    function Query($query, $saveDefault = 1) {
        //echo "<br>".$query."<br>";
        /*
        $pFile = fopen("/tmp/sqldump", "a+");
        fwrite($pFile, "\n".$query);
        fclose($pFile);
        */

        if(defined("print_sql"))
        {
            echo "\n<br>";
            print_r($query);
        }

        if ($query == '') return 0;
		if (DEBUG_LEVEL>=2) trigger_error2(htmlspecialchars_($query));

		if (!$this->Connect()) return 0;
        if ($saveDefault) {
        	$this->_Query = $query;
        	if ($this->_QueryId) $this->Free();
        }
		if (DEBUG_LEVEL>=3) time_start("sql");
        $req = pg_query($this->_LinkId, $query);
		if (DEBUG_LEVEL>=3) trigger_error2("it took ".time_finish("sql")." seconds");


      $this->mErrno = 0;
      $this->mError = '';

      if (!$req) {$this->_Halt("Invalid SQL: " . htmlspecialchars_($query));}
        if ($saveDefault) $this->_QueryId = $req;
       	return $req;
    }
    function GetInsertId($table) {
		$req = @pg_query($this->_LinkId, "SELECT currval('".$table."_id_seq')");
    	if ($this->_LinkId && $req){
    		$r = @pg_fetch_array($req,0,PGSQL_ASSOC);
    		return $r['currval']; 
    	}else return 0;
    }
    function GetNextId($table) {
    	$this->Connect();
		$req = pg_query($this->_LinkId, "SELECT nextval('".$table."_id_seq')");
    	if ($this->_LinkId && $req){
    		$r = @pg_fetch_array($req,0,PGSQL_ASSOC);
    		return $r['nextval']; 
    	}else return 0;
    }    

  	function AllRecords($query='',$by_id='', $return_type=PGSQL_ASSOC) {
  		if ($query !== '') $this->Query($query);
        if (!$this->_QueryId) return 0;
  		$R=array();
       	
       	while ($row=@pg_fetch_array($this->_QueryId,null,$return_type)){
        	if ($by_id && isset($row[$by_id])) $R[$row[$by_id]]=$row; else $R[]=$row;
       	}
		$this->Free();
  		return $R;
  	}
  	function GetRow($query) {
  		$this->Query($query);
  		return $this->NextRecord(PGSQL_ASSOC);
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
    function NextRecord($type=PGSQL_BOTH) {
        if (!$this->_QueryId) {
//            $this->_Halt("NextRecord called with no query pending.");
            return 0;
        }
        $this->mRecord = @pg_fetch_array($this->_QueryId,null,$type);
        $this->mErrno  = 0;
        $this->mError  = pg_last_error($this->_LinkId);
      
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
        $res = @pg_query($this->_LinkId, $query);
        if (!$res) {
            $this->_Halt("lock($table, $mode) failed.");
            return 0;
        }
        return $res;
    }

    function Unlock() {
        $this->connect();
    
        $res = @pg_query($this->_LinkId, "unlock tables");
        if (!$res) {
            $this->_Halt("unlock() failed.");
            return 0;
        }
        return $res;
    }
    function Begin() {
        $this->Connect();
        
        $res = @pg_query($this->_LinkId, 'BEGIN');
        if (!$res) {
            $this->_Halt("BEGIN failed.");
            return 0;
        }
        return $res;
    }

    function Commit() {
        $this->connect();
    
        $res = @pg_query($this->_LinkId, 'COMMIT');
        if (!$res) {
            $this->_Halt("COMMIT failed.");
            return 0;
        }
        return $res;
    }
    
    function Rollback() {
        $this->connect();
    
        $res = @pg_query($this->_LinkId, 'ROLLBACK');
        if (!$res) {
            $this->_Halt("ROLLBACK failed.");
            return 0;
        }
        return $res;
    }
    
    function AffectedRows() {
        return pg_affected_rows($this->_QueryId);
    }

    function NumRows() {
        return @pg_num_rows($this->_QueryId);
    }

    function NumFields() {
        return @pg_num_fields($this->_QueryId);
    }
    function ListFields($table) {
    	
		  $res = pg_query($this->_LinkId, "select * from ".$table." where 1 = 0");
	        if (!$res) {
	        	$this->_Halt("list_fields failed");
	        	return 0;	
	        }
		  $i = pg_num_fields($res);
          $R=array();
		  for ($j = 0; $j < $i; $j++) {
		      $R[] = pg_field_name($res, $j);
		  }
        return $R;
    }

    function F($fieldName) {
        return isset($this->mRecord[$fieldName]) ? $this->mRecord[$fieldName] : '';
    }

    function _Halt($msg) {
        $this->mErrno = 1;
        $this->mError = @pg_last_error($this->_LinkId);
        $this->mErrno = ($this->mError ? 1 : 0);
        if(defined("exception_sql")){
            throw new Exception($this->mError);
        }
		trigger_error2('Database error: ' . $msg, E_USER_NOTICE);
		trigger_error2('PgSQL Error: ' . $this->mErrno . ' (' . $this->mError . ')', E_USER_NOTICE);
    }

    function QueryInsert($table,$data, $getid=true) {
    	$V=array();
    	foreach ($data as $k=>$v) if (is_array($v)) $V[]=$v[0]; elseif ($v !== null) $V[]='\''.pg_escape_string($v).'\''; else $V[]='NULL';
    	$req = $this->Query('insert into '.$table.' ("'.implode('","',array_keys($data)).'") '.
    				'values ('.implode(',',$V).')',0);
    	if (!$req) return 0;
		if ($getid) return $this->GetInsertId($table); else return 0;
    }
    function QueryInsertEx($table,$fields,$datas) {
    	$str0 = 'insert into '.$table.' ('.implode(',',$fields).') values ';
    	$str = ''; $c = 0;
    	foreach ($datas as $data) {
	    	if ($str) $s = ',('; else $s = '(';
	    	foreach ($data as $k=>$v) $s.=($k?',':'').'"'.pg_escape_string($v).'"';
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
    	foreach ($data as $k=>$v) if (is_array($v)) $V[]=$k.'='.addslashes($v[0]); else $V[]=$k.'="'.addslashes($v).'"';
    	if (!$x) return $this->Query('select * from '.$table.' where ('.implode(') AND (',$V).')');
    		else return $this->QueryX('select * from '.$table.' where ('.implode(') AND (',$V).')');
    }
    function QuerySelectAll($table, $data, $x=0){
        $r = array();
        $rs = $this->QuerySelect($table, $data, $x);
        while($l = $this->NextRecord(PGSQL_ASSOC)) $r[] =$l;
        return $r;
    }
    function QueryDelete($table,$data) {
    	$V=array();
    	foreach ($data as $k=>$v) if (is_array($v)) $V[]=$k.'='.addslashes($v[0]); else $V[]=$k.'=\''.addslashes($v).'\'';
    	return $this->Query('delete from '.$table.' where ('.implode(') AND (',$V).')');
    }
    function QueryUpdate($table,$keys,$data) {
    	$V1=array(); $V2=array();
    	if (!is_array($keys)) $keys=array($keys);
    	foreach ($data as $k=>$v) {
            $key = '"'.$k.'"'.'=';
    		if (in_array($k,$keys)) {
                $val = $v !== null ? '\''.pg_escape_string($v).'\'' : 'NULL';
                $V2[] = $key . $val;
            } elseif (!is_array($v)) {
                $val = $v !== null ? '\''.pg_escape_string($v).'\'' : 'NULL';
                $V1[] = $key . $val;
            } else {
                $V1[] = $key . $v[0];
            }
        }
        return $this->Query('update '.$table.' SET '.implode(',',$V1).' WHERE ('.implode(') AND (',$V2).')');
    }
    function QuerySelectRow($table,$data,$x = 0) {
    	$this->QuerySelect($table,$data,$x);
    	return $this->NextRecord(PGSQL_ASSOC);
    }
    public static function Generate($where,$can_null = 0) {
    	if (is_array($where)) {
    		if (count($where)<=1) return ($can_null?"":"1");
    		if (count($where)==2) return PgSQLDatabase::Generate($where[1], $can_null);
    		$s='('.PgSQLDatabase::Generate($where[1],0);
    		for ($i=2;$i<count($where);$i++) $s.=' '.$where[0].' '.PgSQLDatabase::Generate($where[$i],0);
    		$s.=')';
    		return $s;
    	} else {
    		return '('.$where.')';
    	}
    }
    function escape($str) {
        return pg_escape_string($str);
    }

}
?>
