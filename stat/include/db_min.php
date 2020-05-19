<?php


class minDB
{
    private $link = null;
    public function __construct($host, $user, $pass, $db)
    {
        $this->link = mysqli_connect($host, $user, $pass, true);
        if(!$this->link)
            throw new Exception("Connect error");
        mysqli_select_db($db, $this->link);
    }


    function Row($sql)
    {
        $q = $this->query($sql);
        return mysqli_fetch_assoc($q);
    }

    function Value($sql)
    {
        $q = $this->Row($sql);
        $ks = array_keys($q);
        return $q[$ks[0]];
    }

    function All($sql)
    {
        $q = $this->query($sql);
        $r = array();
        while($v = mysqli_fetch_assoc($q))
        {   
            $r[]  = $v; 
        }   
        return $r; 
    }

    function query($sql)
    {
        if(defined("view_sql"))
            echo "\n".$sql;

        $res = mysqli_query($sql, $this->link);

        if(!$res) {

            $errorStr = "\n-------------------------------------\nError: \n".mysqli_error()."\n------ in sql ------\n".$sql."\n";
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
        $s = str_replace("?".$name, "\"".mysqli_real_escape_string($value)."\"", $s);
    }

    function escape($t)
    {
        return mysqli_real_escape_string($t);
    }

    function q($q)
    {
        return $this->query($q);
    }
}

