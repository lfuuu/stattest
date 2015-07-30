<?php

class MySQLDatabase {
    var $mRecord = array();
    var $mRow;

    var $mErrno = 0;
    var $mError = '';

    var $_LinkId  = 0;
    var $_QueryId = 0;

    var $host,$user,$pass,$db;


    function MySQLDatabase() {
        $this->host = SQL_HOST;
        $this->user = SQL_USER;
        $this->pass = SQL_PASS;
        $this->db = SQL_DB;
    }

    function Connect() {
        if ($this->_LinkId == 0) {
            $this->_LinkId = @mysql_connect(SQL_HOST, SQL_USER, SQL_PASS, true);
            if (!$this->_LinkId){
                $this->_Halt("connect failed.");
                echo "can't connect mysql ".$this->host; exit;
                return 0;
            }

            if (!@mysql_select_db(SQL_DB, $this->_LinkId)){
                $this->_Halt("cannot use database " . SQL_DB);
                echo "can't use database"; exit;
                return 0;
            }
            $this->Query("set names utf8");
            $this->Query("SET @@session.time_zone = '+00:00'");

        }
        return $this->_LinkId;
    }

    function Free() {
        if(!$this->_QueryId) return;
        @mysql_free_result($this->_QueryId);
        $this->_QueryId = 0;
    }

    function Query($query, $saveDefault = 1) {

        if ($query == '') return 0;

        if (!$this->Connect()) return 0;
        if ($saveDefault) {
        	if ($this->_QueryId) $this->Free();
        }
        $req = @mysql_query($query, $this->_LinkId);

        $this->mRow   = 0;
        $this->mErrno = mysql_errno();
        $this->mError = mysql_error();
       	if (!$req) $this->_Halt("Invalid SQL: " . htmlspecialchars_($query));
        if ($saveDefault && is_resource($req)) {
            $this->_QueryId = $req;
        }
       	return $req;
    }
    function GetInsertId() {
        return $this->_LinkId ? @mysql_insert_id($this->_LinkId) : 0;
    }

  	function AllRecords($query='',$by_id='', $return_type=MYSQL_ASSOC) {
        try {
            $result = Yii::$app->db->createCommand($query)->queryAll();
        } catch (\yii\db\Exception $e) {
            $this->_HaltEx("Invalid SQL: " . htmlspecialchars_($query), $e);
            return 0;
        }

        if ($by_id) {
            $newResult = [];
            foreach ($result as $row) {
                if (isset($row[$by_id])) {
                    $newResult[$row[$by_id]]=$row;
                } else {
                    $newResult[]=$row;
                }
            }
            return $newResult;
        } else {
            return $result;
        }
  	}
  	function GetRow($query) {
        try {
            return Yii::$app->db->createCommand($query)->queryOne();
        } catch (\yii\db\Exception $e) {
            $this->_HaltEx("Invalid SQL: " . htmlspecialchars_($query), $e);
            return 0;
        }
  	}
    function GetValue($query){
        try {
            return Yii::$app->db->createCommand($query)->queryScalar();
        } catch (\yii\db\Exception $e) {
            $this->_HaltEx("Invalid SQL: " . htmlspecialchars_($query), $e);
            return false;
        }
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
		trigger_error2('Database error: ' . $msg);
		trigger_error2('MySQL Error: ' . $this->mError);
    }

    function _HaltEx($msg, Exception $e) {
        $this->mError = $e->getMessage();
        $this->mErrno = $e->getCode();
        trigger_error2('Database error: ' . $msg);
        trigger_error2('MySQL Error:' . $this->mError);
    }

    function ExecuteQuery($query) {
        try {
            Yii::$app->db->createCommand($query)->execute();
            return true;
        } catch (\yii\db\Exception $e) {
            $this->_HaltEx("Invalid SQL: " . htmlspecialchars_($query), $e);
            return false;
        }
    }

    function QueryInsert($table,$data, $get_new_id=true) {
    	$V=array();
    	foreach ($data as $k=>$v)
        if (is_array($v))
          $V[]=$v[0];
        else
        {
          if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
          $V[]=$v;
        }

        $query = 'insert into '.$table.' (`'.implode('`,`',array_keys($data)).'`) '.
            'values ('.implode(',',$V).')';

        if ($this->ExecuteQuery($query) && $get_new_id) {
            return Yii::$app->db->lastInsertID;
        } else {
            return false;
        }
    }
    function QueryInsertEx($table,$fields,$datas) {
        $str0 = 'insert into '.$table.' ('.implode(',',$fields).') values ';
        $str = ''; $c = 0;
        foreach ($datas as $data) {
            if ($str) $s = ',('; else $s = '(';
            foreach ($data as $k=>$v)
            {
                if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
                $s.=($k?',':'').$v;
            }
            $str.=$s.')';
            $c++;

            if ($c>100) {
                $this->ExecuteQuery($str0.$str);
                $str = ''; $c = 0;
            }
        }
        if ($c>0) {
            $this->ExecuteQuery($str0.$str);
        }
    }

    function QuerySelectAll($table, $data){

        $V=array();
        foreach ($data as $k=>$v)
            if (is_array($v))
                $V[]=$k.'='.addslashes($v[0]);
            else
            {
                if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
                $V[]=$k.'='.$v;
            }

        $query = 'select * from '.$table.' where ('.implode(') AND (',$V).')';
        try {
            return Yii::$app->db->createCommand($query)->queryAll();
        } catch (\yii\db\Exception $e) {
            $this->_HaltEx("Invalid SQL: " . htmlspecialchars_($query), $e);
            return false;
        }

    }
    function QueryDelete($table,$data) {
        $V=array();
        foreach ($data as $k=>$v) {
            if (is_array($v)) {
                $V[] = $k . '=' . addslashes($v[0]);
            } else {
                if (gettype($v) != 'integer') $v = '\'' . $this->escape($v) . '\'';
                $V[] = $k . '=' . $v;
            }
        }

        return $this->ExecuteQuery('delete from '.$table.' where ('.implode(') AND (',$V).')');
    }
    function QueryUpdate($table,$keys,$data) {
        $V1=array(); $V2=array();
        if (!is_array($keys)) $keys=array($keys);
        foreach ($data as $k=>$v)
            if (in_array($k,$keys))
            {
                if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
                $V2[]='`'.$k.'`='.$v;
            }
            elseif (!is_array($v))
            {
                if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
                $V1[]='`'.$k.'`='.$v;
            }
            else $V1[]='`'.$k.'`='.$v[0];

        return $this->ExecuteQuery('update '.$table.' SET '.implode(',',$V1).' WHERE ('.implode(') AND (',$V2).')');

    }
    function QuerySelectRow($table,$data) {

        $V=array();
        foreach ($data as $k=>$v)
            if (is_array($v))
                $V[]=$k.'='.addslashes($v[0]);
            else
            {
                if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
                $V[]=$k.'='.$v;
            }

        $query = 'select * from '.$table.' where ('.implode(') AND (',$V).')';
        try {
            return Yii::$app->db->createCommand($query)->queryOne();
        } catch (\yii\db\Exception $e) {
            $this->_HaltEx("Invalid SQL: " . htmlspecialchars_($query), $e);
            return false;
        }

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
        if (!$this->_LinkId)
            $this->Connect();

        return mysql_real_escape_string($str);
    }

}
?>
