<?php

class MySQLDatabase_yii {

    private $db = null;
    private $reader = null;


    public function MySQLDatabase_yii($db) 
    {
    	$this->db = $db;
    }

    public function Connect() 
    {
        //nothing
    }

    public function SwitchDB($db) 
    {
        throw new Exception("not supported");
    }

    public function Free() 
    {
        //nothing
    }

    public function Query($query, $saveDefault = 1) 
    {
        $reader = $this->db->createCommand($query)->query();

        if ($saveDefault && $reader) 
            $this->reader = $reader;

       	return $reader;
    }

    public function GetInsertId() 
    {
        return $this->db->getLastInsertID();
    }

    public function AllRecords($query='',$by_id='', $return_type=MYSQL_ASSOC) 
    {
        $res = $this->db->createCommand($query)->queryAll($this->_getType($return_type));

        $R = [];
		foreach ($res as $r) {
            if ($by_id && isset($r[$by_id])) {
                $R[$r[$by_id]]=$r; 
            } else {
                $R[]=$r;
            }
        }

  		return $R;
    }

    public function GetRow($query) 
    {
  		return $this->db->createCommand($query)->queryOne(\PDO::FETCH_ASSOC);
    }

    public function GetValue($query)
    {
        return $this->db->createCommand($query)->queryScalar();
    }

    public function AllRecordsAssoc($sql, $key = false, $value = false)
    {
        $r = array();
        $rs = $this->AllRecords($sql);

        if(!$key) {
            $ks = array_keys($rs[0]);
            $key = $ks[0];
            $value = $ks[1];
        }

        foreach($rs as $l) {
            $r[$l[$key]] = $l[$value];
        }

        return $r;
    }
            
    public function NextRecord($type = MYSQL_BOTH) 
    {
        if (!$this->reader) {
            return 0;
        }

        $this->reader->setFetchMode($this->_getType($type));
    
      
        return $this->reader->read();
    }

    public function Lock($table, $mode = 'write') 
    {
        $query = "lock table ";
		$query .= $table . ' ' . $mode;
        $res = $this->db->createCommand($query)->execute();
        if (!$res) {
            $this->_Halt("lock($table, $mode) failed.");
            return 0;
        }
        return $res;
    }

    public function Unlock() 
    {
        $res = $this->Query("unlock tables");
        if (!$res) {
            $this->_Halt("unlock() failed.");
            return 0;
        }
        return $res;
    }


    public function ListFields($table) 
    {
        throw new Exception("not supported");
    }

    public function F($fieldName) 
    {
        throw new Exception("not supported");
    }

    public function _Halt($msg) 
    {
        if(defined("exception_sql")){
            throw new Exception($this->mError);
        }
		trigger_error2('Database error: ' . $msg, E_USER_NOTICE);
    }

    public function QueryInsert($table,$data, $get_new_id=true) 
    {
    	$V=array();
    	foreach ($data as $k=>$v)
        if (is_array($v))
          $V[]=$v[0];
        else
        {
          if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
          $V[]=$v;
        }
    	$res = $this->Query('insert into '.$table.' (`'.implode('`,`',array_keys($data)).'`) '.
    				'values ('.implode(',',$V).')',0);
      if ($get_new_id)
		    return $this->GetInsertId();
      else
        return $res;
    }

    public function QueryInsertEx($table,$fields,$datas) 
    {
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
                $this->Query($str0.$str,0);
                $str = ''; $c = 0;
            }
        }
        if ($c>0) {
            $this->Query($str0.$str,0);
            $str = ''; $c = 0;
        }
    }

    public function QuerySelect($table,$data)
    {
    	$V=array();
    	foreach ($data as $k=>$v)
        if (is_array($v))
          $V[]=$k.'='.addslashes($v[0]);
        else
        {
          if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
          $V[]=$k.'='.$v;
        }

    	return $this->Query('select * from '.$table.' where ('.implode(') AND (',$V).')');
    }

    public function QuerySelectAll($table, $data)
    {
        $rs = $this->QuerySelect($table, $data);
        $this->reader->setFetchMode($this->_getType(MYSQL_ASSOC));
        return $this->reader->readAll();
    }

    public function QueryDelete($table,$data) 
    {
    	$V=array();
    	foreach ($data as $k=>$v)
        if (is_array($v))
          $V[]=$k.'='.addslashes($v[0]);
        else
        {
          if (gettype($v) != 'integer') $v = '\''.$this->escape($v).'\'';
          $V[]=$k.'='.$v;
        }
    	return $this->Query('delete from '.$table.' where ('.implode(') AND (',$V).')');
    }

    public function QueryUpdate($table,$keys,$data) 
    {
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
    	return $this->Query('update '.$table.' SET '.implode(',',$V1).' WHERE ('.implode(') AND (',$V2).')');
    }

    public function QuerySelectRow($table, $data)
    {
        $rs = $this->QuerySelect($table, $data);
        $this->reader->setFetchMode($this->_getType(MYSQL_ASSOC));
        return $this->reader->read();
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

    public static function Generate($where, $can_null = 0)
    {
    	if (is_array($where)) {
    		if (count($where)<=1) return ($can_null?"":"1");
    		if (count($where)==2) return MySQLDatabase::Generate($where[1], $can_null);
    		$s='('.MySQLDatabase::Generate($where[1],0);
            for ($i=2;$i<count($where);$i++) 
                $s.=' '.$where[0].' '.MySQLDatabase::Generate($where[$i],0);
    		$s.=')';
    		return $s;
    	} else {
    		return '('.$where.')';
    	}
    }

    public function escape($str) 
    {
        $str .= "";

        $str = $this->db->quoteValue($str);

        if ($str[0] == "'") {
            $str = mb_substr($str, 1, mb_strlen($str)-2);
        }

        return $str;
    }

    private function _getType($type) 
    {
        if ($type == MYSQL_ASSOC)
            $type = \PDO::FETCH_ASSOC;

        if ($type == MYSQL_BOTH)
            $type = \PDO::FETCH_BOTH;

        return $type;
    }


}
?>
