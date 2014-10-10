<?
	//этот файл может использоваться для аяксовых вызовов. и всё.
	define("PATH_TO_ROOT",'./');
	define('NO_WEB',1);

    define("DEBUG_LEVEL", 1);

	define('INCLUDE_PATH',			PATH_TO_ROOT.'include/');
		require_once(INCLUDE_PATH.'sql.php');
		$db		= new MySQLDatabase("albis.mcn.ru", "tib_stat","b8f89b601fd7ca", "nispd");

        $p = array();
        foreach($db->AllRecords("select * from permit_ip") as $v)

{
    if(isset($p[$v["client"]][$v["callerid"]]))
        $p[$v["client"]][$v["callerid"]]["permit"] .= ";".$v["permit"];
    else
        $p[$v["client"]][$v["callerid"]] = array("permit" => $v["permit"], "cl" => $v["cl"], "enable" => $v["enable"]);

}



$s = "";

        foreach($p as $clientId => $l1)
            foreach($l1 as $callerid => $l)
                $s .= "('".$clientId."', '".$callerid."', '".normalizePermit($l["permit"])."', '".$l["cl"]."', '".$l["enable"]."'),";

                if($s){
                    $db	= new MySQLDatabase("localhost", "stat_operator","3616758a", "nispd");

                    $db->Query("truncate voip_permit");

                    $db->Query("insert into voip_permit values ".substr($s, 0, strlen($s)-1));
                    //
                }




    function normalizePermit($p)
{
    $a = array();
    foreach(explode(";", $p) as $v){
        $a[$v] = 1;
    }

    return implode(", ",array_keys($a));
}


                


?>
