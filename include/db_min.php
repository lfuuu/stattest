<?php


class minDB
{
    private $link = null;
    public function minDB($host, $user, $pass, $db)
    {
        $this->link = mysql_connect($host, $user, $pass, true);
        if(!$this->link)
            throw new Exception("Connect error");
        mysql_select_db($db, $this->link);
    }


    function Row($sql)
    {
        $q = $this->query($sql);
        return mysql_fetch_assoc($q);
    }

    function Value($sql)
    {
        $q = Row($sql);
        $ks = array_keys($q);
        return $q[$ks[0]];
    }

    function All($sql)
    {
        $q = $this->query($sql);
        $r = array();
        while($v = mysql_fetch_assoc($q))
        {   
            $r[]  = $v; 
        }   
        return $r; 
    }

    function query($sql)
    {
        if(defined("view_sql"))
            echo "\n".$sql;

        $res = mysql_query($sql, $this->link);

        if(!$res) {

            $errorStr = "\n-------------------------------------\nError: \n".mysql_error()."\n------ in sql ------\n".$sql."\n";
            try {
                throw new Exception;
            } catch(Exception $e) {
                $errorStr .="\n". $e->getTraceAsString();
            }
            die($errorStr);
        }
        return $res;

    }

    function Param(&$s, $name, $value)
    {
        $s = str_replace("?".$name, "\"".mysql_escape_string($value)."\"", $s);
    }

    function escape($t)
    {
        return mysql_escape_string($t);
    }

    function q($q)
    {
        return $this->query($q);
    }
}

