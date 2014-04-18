<?php
define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);

define("voip_debug", 1);
define("print_sql", 1);
define("exception_sql", 1);


include PATH_TO_ROOT."conf.php";


$clientId = 0;

if(isset($_SERVER["argv"]) && count($_SERVER["argv"]) > 1)
{
	$clientId = (int)$_SERVER["argv"][1];

	if($_SERVER["argv"][1] == "full")
		$clientId = "all";
}

if(isset($_GET["client_id"]) && (int)$_GET["client_id"])
	$clientId = $_GET["client_id"];

if(!$clientId)
    exit();

if($clientId == "all")
	$clientId = null;

// start
if(isset($_GET["client_id"]))
	echo "<pre>";

echo "\n***";
echo date("r")." clientId: ".$clientId;


$path_to_anonce = "/tmp/";


$mDB = $db_ats;

$db = new MySQLDatabase('localhost', 'root', '', 'nispd_test_ats2');


//$pDB = new PgSQLDatabase('localhost','pgadmin','pgadmin', 'ats2');
$pDB = new PgSQLDatabase('10.252.12.204','statconv','statconv', 'voipdb');
$pDB->Connect() or die("PgSQLDatabase not connected");

define("PG_SCHEMA", "astschema");

define("DEFAULT_TIMEOUT", 200);


// body

$mtIds = array();

$all            = loadRedirectSettings($clientId);
$sipLines       = loadSIPLines($clientId);
$sipMultitrunks = loadSIPMultitrunks($mtIds, $clientId);
$peers          = loadNumbers($mtIds, $clientId);


printdbg($sipMultitrunks, "sipMultitrunks");

//echo "\n------ all -----\n";
//print_r($all);

$a = array_merge($sipLines, $sipMultitrunks);

//$peers = array();
$inss = array(
	"sipdevices" => array(),
	"numbers" => array(),
	"numbers_forward" => array()
);

//unset($all["4000"]);
//unset($peers["4000"]);

convertSip($a, $inss["sipdevices"], $peers); //sipdevice
insertNumber($peers, $all, $inss); // numbers + numbers_forward

//define("diff_not_apply",1);

// diffs

define("print_sql", 1);
$pDB->Query("start transaction");

	$d = new diffSip($clientId);
	$d->diff($inss["sipdevices"])->apply();

	$d = new diffNumbers($clientId);
	$d->diff($inss["numbers"])->apply();

	$d = new diffNumbersFwd($clientId);
	$d->diff($inss["numbers_forward"])->apply();



$pDB->Query("commit");



class _diff
{
	private $diffs = array(
			"insert" => array(),
			"delete" => array(),
			"update" => array()
	);

	private $fields = array();

	protected $key1 = "client_id";

	protected $clientId = null;

	public function __construct($clientId = null)
	{
		$this->clientId = $clientId;
	}

	public function diff($a)
	{
		$this->_getFields($a);

		$b = $this->_loadSaved($a);

		$client_add = array_diff(array_keys($a), array_keys($b));
		$client_del = array_diff(array_keys($b), array_keys($a));

		$client_all = array_intersect(array_keys($a), array_keys($b));

		foreach($client_add as $c)
			foreach($a[$c] as $v)
				$this->diffs["insert"][] = $v;

		if(get_class($this) == "diffNumbersFwd")
		{
			$this->diffs["delete"] = $client_del; // number
		}else{
			foreach($client_del as $c)
				foreach($b[$c] as $v)
					$this->diffs["delete"][] = $v[$this->key]; // number
		}

		foreach($client_all as $clientId)
			$this->_diffStruct($a[$clientId], $b[$clientId]);

		return $this;
	}

	private function _getFields(&$a)
	{
		$this->fields = array();
		$clients = array_keys($a);

		if($clients)
		{
			$sips = array_keys($a[$clients[0]]);
			$this->fields = array_keys($a[$clients[0]][$sips[0]]);
		}
	}

	private function _loadSaved(&$a)
	{
		global $pDB, $mtIds;

		$b = array();

		$whereSql = "";

		if($this->clientId)
		{
			 $whereSql = " client_id = '".$this->clientId."'";

			 if(get_class($this) == "diffSip")
			 {
				 if($mtIds["stat_ids"])
				 {
				 	$whereSql = " (".$whereSql." or stat_id in ('".implode("','",$mtIds["stat_ids"])."'))";
				 }
			 }elseif(get_class($this) == "diffNumbers" || get_class($this) == "diffNumbersFwd")
			 {
			     if($mtIds["numbers"])
				 {
				 	$whereSql = " (".$whereSql." or number in ('".implode("','",$mtIds["numbers"])."'))";
				 }
			 }

			 $whereSql = " where ".$whereSql;
		}


		foreach($pDB->AllRecords($q = "select * from ".PG_SCHEMA.".".$this->table.$whereSql) as $l)
		{
			$b[$l[$this->key1]][$l[$this->key]] = $l;
		}

		return $b;
	}

	private function _diffStruct($a, $b)
	{
		$row_add = array_diff(array_keys($a), array_keys($b));
		$row_del = array_diff(array_keys($b), array_keys($a));

		foreach($row_add as $r)
			$this->diffs["insert"][] = $a[$r];

		foreach($row_del as $r)
			$this->diffs["delete"][] = $r;

		foreach(array_intersect(array_keys($a), array_keys($b)) as $r)
			$this->_diffRow($a[$r], $b[$r]);
	}

	private function _diffRow($a, $b)
	{
		$diff = array();

		foreach($this->fields as $f)
		{
			if($a[$f] != $b[$f])
			{
				if($a[$f] == "true"  && $b[$f] == "t") continue;
				if($a[$f] == "false" && $b[$f] == "f") continue;

				$diff[$f] = $a[$f];
			}
		}

		if($diff)
		{
			$diff[$this->key1] = $a[$this->key1];
			$diff[$this->key] = $a[$this->key];

			$this->diffs["update"][] = $diff;
		}
	}

	public function apply()
	{
		global $pDB;

		if(!defined("print_sql_no_select"))
			define("print_sql_no_select", 1);


		if($this->diffs["delete"])
		{
			if(get_class($this) == "diffNumbersFwd")
				$this->key = $this->key1;

            foreach($this->diffs["delete"] as $d)
            {
                $data = array($this->key => $d);
                if(!defined("diff_not_apply"))
                {
                    $pDB->QueryDelete(PG_SCHEMA.".".$this->table, $data);
                }else{
                    echo "\nQueryDelete: ".PG_SCHEMA.".".$this->table."\n";
                    print_r($data);
                }
            }
		}


		if($this->diffs["insert"])
			foreach($this->diffs["insert"] as $i)
			{
				if(!defined("diff_not_apply"))
				{
					$pDB->QueryInsert(PG_SCHEMA.".".$this->table, $i, false);
				}else{
					echo "\nQueryInsert: ".PG_SCHEMA.".".$this->table."\n";
					print_r($i);
				}
			}

		if($this->diffs["update"])
		{
			foreach($this->diffs["update"] as $i)
			{
				if(!defined("diff_not_apply"))
				{
					$pDB->QueryUpdate(PG_SCHEMA.".".$this->table, array($this->key1, $this->key), $i);
				}else{
					echo "\nQueryInsert: ".PG_SCHEMA.".".$this->table."\n";
					print_r(array($this->key1, $this->key));
					print_r($i);
				}
			}
		}
	}

	public function printDiff()
	{
		printdbg($this->diffs);
		return $this;
	}
}

class diffSip extends _diff
{
	protected $key = "stat_id";
	protected $table = "sipdevices";
}

class diffNumbers extends _diff
{
	protected $key = "number";
	protected $table = "numbers";
}

class diffNumbersFwd extends _diff
{
	protected $key = "fwtype";
	protected $key1 = "number";
	protected $table = "numbers_forward";
}




function loadRedirectSettings($clientId)
{
	global $mDB;

	$all = array();

	$ons = $mDB->AllRecords("
	select
		number, call_count,
		if(ifnull((select elem from rr_redirect_on r where r.number_id = n.id and elem = 'redir'),'') != '', 1,0) redir,
		if(ifnull((select elem from rr_redirect_on r where r.number_id = n.id and elem = 'redirif'),'') != '', 1,0) redirif,
		if(ifnull((select elem from rr_redirect_on r where r.number_id = n.id and elem = 'linenotavail'),'') != '', 1,0) linenotavail,
		if(ifnull((select elem from rr_redirect_on r where r.number_id = n.id and elem = 'linebusy'),'') != '', 1,0) linebusy,
		if(ifnull((select elem from rr_redirect_on r where r.number_id = n.id and elem = 'linenoanswer'),'') != '', 1,0) linenoanswer
	from
		a_number n
	where 
        enabled='yes'".($clientId ? " and client_id =  ".$clientId : ""), "number");

	foreach($ons as $v)
	{
		foreach(array("redir", "redirif", "linenotavail", "linebusy", "linenoanswer") as $section)
		{
			$all[$v["number"]][$section]["is_on"] = $v[$section];
		}
	}
	unset($ons);

	foreach($mDB->AllRecords("
	     select
	          n.number, section, call_strategy, call_wait
	     from
	          rr_number_settings s, a_number n
	     where
	               number_id=n.id
	          and s.number_id = n.id
	          and enabled='yes'
	          ".($clientId ? " and n.client_id =  ".$clientId : "")) as $v)
	{
		foreach(array("call_strategy", "call_wait") as $f)
		{
			$all[$v["number"]][$v["section"]][$f] = $v[$f];
		}
	}

/*
	foreach($mDB->AllRecords("
	     select
	          n.number, s.section, unix_timestamp(s.from) as `from`, unix_timestamp(s.to) as `to`
	     from
	          rr_period s, a_number n
	     where
	               number_id=n.id
	          and s.number_id = n.id
	          and enabled='yes'
	          and is_on = 'no'
	          ".($clientId ? " and n.client_id = ".$clientId : "")."
	          having `from` > 0 and `to` > 0 and `from` < `to` ") as $v)
	{
		foreach(array("from", "to") as $f)
		{
			$all[$v["number"]][$v["section"]]["period"][$f] = $v[$f];
		}
	}

    */


	foreach($mDB->AllRecords("
	     select
	          n.number, s.*
	     from
	          rr_weekday s, a_number n
	     where
	               number_id=n.id
	          and s.number_id = n.id
	          and enabled='yes'
	          ".($clientId ? " and n.client_id =  ".$clientId : "")) as $v)
	{
		foreach(array("from", "to") as $f)
		{
            if ($f == "to" && $v[$f] == "00:00") 
                $v[$f] = "24:00";

			$all[$v["number"]]["redirif"]["weekday"][$v["day"]][$f] = $v[$f];
		}
	}


	foreach($mDB->AllRecords("
	               select
	                    n.number, section, phone as contact
	               from
	                    rr_redirect_phones p, a_number n
	               where
	                    n.id = p.number_id
	                    ".($clientId ? " and p.client_id = ".$clientId : "")."
	               order by
	                    n.number, section, p.id
	") as $v)
	{
		$all[$v["number"]][$v["section"]]["contacts"][] = $v["contact"];
	}

    foreach($mDB->AllRecords("
                select 
                    number, section, is_on, anonce_id
                from
                    rr_anonce a, a_number n
                where 
                        n.id = a.number_id
                    and is_on = 'yes'
	            ".($clientId ? " and a.client_id = ".$clientId : "")."
                    ") as $v)
    {
        $all[$v["number"]]["anonse"][$v["section"]] = $v["anonce_id"];
    }

	return $all;
}

function loadSIPLines($clientId)
{
	global $mDB;

	$a = $mDB->AllRecords("

					SELECT
						n.number, n.client_id, n.region, call_count, n.direction,
						l.account, c_type as type, c.*
					FROM
						`a_link` k, a_number n, a_line l, a_connect c
					where
							k.number_id = n.id
						and l.id = k.c_id
						and c_type in ('line', 'trunk')
						and c.id = l.c_id
						".($clientId ? " and n.client_id =  ".$clientId : "")."
						and l.client_id = n.client_id
						and is_group = 0
						and n.enabled ='yes'
					order by k.number_id, k.id

		", "id"); // id = a_connect.id

	/// set priority
	$number = "";
	$priority = 0;
	foreach($a as &$l)
	{
		if($l["number"] != $number)
		{
			$number =$l["number"];
			$priority = 0;
		}
		$priority++;

		$l["priority"] =$priority;
	}
	unset($l);

	return $a;
}

function loadSIPMultitrunks(&$mtIds, $clientId = null)
{
	global $mDB;

	// all multitrunks load

	$mtIds = array("ids" => array(), "stat_ids" => array(), "numbers" => array());


	if($clientId)
	{
        //stat_id = a_connect.id
		foreach($mDB->AllRecords(
					"select
						m_id, m.c_id as stat_id
					from
							(select distinct if(parent_id =0, id, parent_id) as m_id from a_multitrunk where client_id = '".$clientId."') as a,
							a_multitrunk m
					where
						a.m_id = m.id ") as $t)
		{

			$mtIds["ids"][] = $t["m_id"];
			$mtIds["stat_ids"][] = $t["stat_id"];
		}

        // load multitranks linked numbers
		if($mtIds["ids"])
		{
			foreach($mDB->AllRecords("
					SELECT distinct number
					FROM `a_multitrunk` m, a_link l, a_number n
					where m.id = l.c_id and c_type = 'multitrunk' and n.id = l.number_id and n.enabled='yes'
						and m.parent_id in('".implode("','", $mtIds["ids"])."')") as $l)
			{
				$mtIds["numbers"][$l["number"]] = $l["number"];
			}
		}
	}


	// load main mutitrunks
	$trunkMain = $mDB->AllRecords("
		select
			m.id as m_id, m.name, client_id, c.*
		from
			a_multitrunk m, a_connect c
		where
				c.id = m.c_id
			and parent_id = 0
			".($clientId ? "
				and m.id in ('".implode("','", $mtIds["ids"])."')" : ""), "m_id");

    foreach($trunkMain as &$trunk)
    {
        $trunk["region"] = "all";
    }


	if(!$trunkMain) return array();

	// load linked
    foreach($mDB->AllRecords(
     					"select m.parent_id, n.direction, n.number, n.call_count
     					from a_multitrunk m, a_link l, a_number n
     					where
     							l.c_id = m.id
     						and l.c_type = 'multitrunk'
     						and n.id = l.number_id
     						and n.enabled ='yes'"
     						.($clientId !== null ? " and m.parent_id in ('".implode("','", array_keys($trunkMain))."')" : "")) as $l)
    {
    	if(!isset($trunkMain[$l["parent_id"]])) continue;

     	if(!isset($trunkMain[$l["parent_id"]]["numbers"]))
     		$trunkMain[$l["parent_id"]]["numbers"] = array();

     	$trunkMain[$l["parent_id"]]["numbers"][] = array(
     		"number" => $l["number"],
     		"direction" => $l["direction"],
     		"call_count" => $l["call_count"]
     	);

     }
    
    foreach($trunkMain as $parentId => $devNull)
    {
        foreach(getVpbxTrunkNumbers($parentId) as $t) 
        {
            $trunkMain[$parentId]["numbers"][] = array(
                    "number" => $t["number"],
                    "direction" => $t["direction"],
                    "call_count" => $t["call_count"]
                    );

            if (!isset($mtIds["numbers"][$t["number"]]))
            {
                $mtIds["numbers"][$t["number"]] = $t["number"];
            }
        }
    }


     refactMT($trunkMain);
     printdbg($trunkMain, "trunkMain - refactored");

     return $trunkMain;
}

function getVpbxTrunkNumbers($trunkId)
{
    global $db, $mDB;

    $clients = array();

    foreach(
            $db->AllRecords(
                "select 
                    c.id as client_id,
                    trunk_vpbx_id as trunk_id 
                from 
                    usage_virtpbx u, clients c , server_pbx s 
                where 
                        actual_from <= cast(now() as date) and actual_to >= cast(now() as date) 
                    and s.id = server_pbx_id 
                    and c.client = u.client
                    and trunk_vpbx_id = '".$trunkId."'
#and c.id = 8016
                group by c.id
                ") as $l)
            {
                $clients[$l["client_id"]] = 1;
            }

    if (!$clients) return array();

    $numbers = array();

    foreach($mDB->AllRecords(
                "SELECT 
                    n.number, n.call_count, n.direction
                 FROM 
                    `a_virtpbx` v, a_virtpbx_link l, a_number n 
                 where 
                        v.client_id in ('".implode("','", array_keys($clients))."') 
                    and l.virtpbx_id = v.id 
                    and n.id = l.type_id 
                    and l.type = 'number'") as $l)
    {
        $numbers[$l["number"]] = $l;
    }

    return $numbers;
}



function loadNumbers($mtIds, $clientId = null)
{
	global $mDB;

	$peers = array();

	$whereSql = "";

	if($clientId)
	{
		$whereSql = " client_id = '".$clientId."'";

		if($mtIds["numbers"])
		{
			$whereSql = " (".$whereSql." or number in ('".implode("','", $mtIds["numbers"])."'))";
		}

		$whereSql = " and ".$whereSql;
	}

	foreach($mDB->AllRecords(
                "select client_id, number, call_count, 'line' as type, region, direction
                from a_number 
                where enabled = 'yes'".$whereSql) as $v)
	{
		$peers[$v["number"]] = array(
			              "type"      => getSipType($v),
			              "_type"     => $v["type"],
			              "delimeter" => "",
			              "peers"     => array(),
			              "client_id" => $v["client_id"],
			              "ds"        => getDS($v["direction"]),
			              "cl"        => $v["call_count"],
                          "region"    => $v["region"]
		);
	}

	return $peers;
}

function convertSip(&$a, &$inss, &$peers)
{
	global $pDB;

    printdbg($peers, "peers");


	foreach($a as $n => $v)
	{
		$number = $v["number"];
		//$name = ($v["type"] == "trunk" ? "tr".$v["number"] : ($v["type"] == "multitrunk" || $v["type"] == "line" ? $v["number"] : ""));
		$name = ($v["type"] == "trunk" ? $v["account"] : ($v["type"] == "multitrunk" || $v["type"] == "line" ? $v["account"] : ""));

		$peers[$v["number"]] = array_merge($peers[$v["number"]], array(
              "type"      => getSipType($v),
              "_type"     => $v["type"],
              "delimeter" => ($v["format"] == "ats2" ? "" : $v["format"]),
              //"peers"     => array(),
              //"client_id" => $v["client_id"],
              "ds"        => getDS($v["direction"]),
              //"cl"        => $v["call_count"]
		));

		$peers[$v["number"]]["peers"][] = $name;

		$m =  array(
            "name"      =>  $name,
            "client"    =>  $v["client_id"],
            "stat_id"   =>  $v["id"],
            "client_id" =>  $v["client_id"],
            "callerid"  => ($v["type"] == "multitrunk" ? "" : $v["number"]),
            //"ast_ds"    =>  $ds,
            //"ast_cl"    =>  $v["call_count"],
            "secret"    =>  $v["password"],
            "permit"    => ($v["permit_on"] == "yes" ? str_replace(",", ";", $v["permit"]): ""),
            "deny"      => ($v["permit_on"] == "yes" ? "0.0.0.0/0.0.0.0": ""),
            "allow"     => str_replace(",", ";", $v["codec"]),
            "host"      => ($v["host_type"] == "dynamic" ? $v["host_type"] : $v["host_static"]),
		    "port"      => ($v["host_type"] == "dynamic" ? 5060 : $v["host_port_static"]),
            "insecure"  =>  $v["insecure"],
            "dtmfmode"  =>  $v["dtmf"],
            "context"   =>  $v["context"],//($v["type"] == "multitrunk" ? "c-multitrunk-out" : "c-realtime-out"),
            "is_multitrunk" => ($v["type"] == "multitrunk" ? "true" : "false"),
			"priority"  => ($v["type"] == "line" ? $v["priority"] : 1),
            "region"    => $v["region"],
            "type"      => ($v["host_type"] != "dynamic" ? "peer" : "friend"),
            "autolink_ip" => $v["permit_on"] == "auto" ? "true" : "false"
		);

		if($v["type"] == "multitrunk")
		{
			unset($m["ast_cl"], $m["ast_cnt"]);
		}

		$inss[$m["client_id"]][$m["stat_id"]] = $m;
		//$pDB->QueryInsert("sipdevices", $m, false);

	}
}

function insertNumber(&$peers, &$all, &$inss)
{
	global $pDB;

	$ins = array();

	printdbg($peers, "peers - insNum");
	printdbg($all, "all - insNum");

	foreach($peers as $number => $peerInfo)
	{
		$oNum = isset($ons[$number]) ? $ons[$number] : false;

        $strategy = array(
                "call_strategy" => "rrmemory",
                "call_wait" => 0
                );

        if (isset($all[$number]["strategy"]))
        {
            $strategy = $all[$number]["strategy"];
        }

		$m = array(
             "number"    =>  $number,
             "type"      =>  $peerInfo["type"],
             "peername"  => ($peerInfo["peers"] && $peerInfo["_type"] == "multitrunk" ? $peerInfo["peers"][0] : ""),
             "client_id" =>  $peerInfo["client_id"],
             "delim"     => ($peerInfo["type"] == "line" ? $peerInfo["delimeter"] : ""),
             "uncond"    => (_is_on($all, $number, "redir") ? "true" : "false"),
             "cond"      => (_is_on($all, $number, "redirif") ? "true" : "false"),
             "announce"  => _get_anonce($all, $number, "main"),
             "noanswer"  => (_is_on($all, $number, "linenoanswer") ? "true" : "false"),
                            //аварийная переадресация должна быть включена, если есть номера для переадресации
             "unavail"   => (_is_on($all, $number, "linenotavail") && isset($all[$number]["linenotavail"]["contacts"]) && $all[$number]["linenotavail"]["contacts"] ? "true" : "false"), 
             "busy"      => (_is_on($all, $number, "linebusy") ? "true" : "false"),
             "cl"        =>  $peerInfo["cl"],
             "ds"        =>  $peerInfo["ds"],
             "src"       => "00000000",
             "region"    => $peerInfo["region"],

             "strategy"  =>  $strategy["call_strategy"] == "ringall" ? "all" : "rr",
             "timeout"   =>  $strategy["call_wait"]
		);

		$inss["numbers"][$m["client_id"]][$m["number"]] = $m;
		//$pDB->QueryInsert("numbers", $m, false);



		$toP = array(
			"redir"        => "uncond",
			"redirif"      => "cond",
			"linenotavail" => "unavail",
			"linebusy"     => "busy",
			"linenoanswer" => "noanswer"
		);

		foreach(array("redir", "redirif", "linenotavail", "linebusy","linenoanswer") as  $l)
		{
			$m = array(
                    "number"       => $number,
                    "fwtype"       => $toP[$l],
                    "time"         => "",
                    "announce"     => _get_anonce($all, $number, $l),
                    "dial_targets" => "",
                    "strategy"     => "rr",
                    "timeout"      => DEFAULT_TIMEOUT,
                    "client_id"    => $peerInfo["client_id"],
                    "region"       => $peerInfo["region"]
			);

           /* settingss for this section are not available */
            /**/
			$s = isset($all[$number][$l]) ? $all[$number][$l] : array(
				"announce" => "",
				"strategy" => "rr",
				"timeout"  => DEFAULT_TIMEOUT,
			);


			if(isset($s["call_strategy"]) && $s["call_strategy"] != "ringall") //'ringall' && 'rrmemory'
			{
				$m["strategy"] = "rr";
				$m["timeout"] = $s["call_wait"];
			}
            /**/

			if(isset($s["contacts"]))
				$m["dial_targets"] = implode(",", $s["contacts"]);

			$m["period"] = _getPeriod($s);
			$m["time"] = _getTime($s);


			$inss["numbers_forward"][$m["number"]][$m["fwtype"]] = $m;

			//$pDB->QueryInsert("numbers_forward", $m, false);
		}
	}
}

function getDS($direction)
{
    $aDst = array(
            "full"   => "full",
            "russia" => "full-russia",
            "localmob" => "local-mobile",
            "local"    => "local"
            );
    return $ds = "my-".(isset($aDst[$direction]) ? $aDst[$direction] : $direction)."-out";
}




function _is_on($all, $number, $section)
{
	return isset($all[$number]) && isset($all[$number][$section]) && isset($all[$number][$section]["is_on"]) && $all[$number][$section]["is_on"];
}

function _get_anonce(&$all, $number, $section)
{
    global $path_to_anonce;

    if ($section == "redir") $section = "redirect";
    if ($section == "redirif") $section = "redirectif";

    if (isset($all[$number]) && isset($all[$number]["anonse"]) && isset($all[$number]["anonse"][$section]))
    {
        return $path_to_anonce.$all[$number]["anonse"][$section];
    }

    return "";
}


function _getPeriod(&$s)
{
	if(isset($s["period"]))
	{
		$p = $s["period"];
		return $p["from"]."-".$p["to"];
	}
	return "";
}

function _getTime(&$s)
{
	$times = array();

	if(isset($s["weekday"]))
	{
		_makeTime__weekdays($times, $s["weekday"]);
	}

	return implode(",", $times);
}

function _makeTime__weekdays(&$times, $weekday)
{
	$days = array(
		"all" => "*",
		"holiday" => "hol",
		"workday" => "wrk",
		"d1" => "mon",
		"d2" => "tue",
		"d3" => "wed",
		"d4" => "thu",
		"d5" => "fri",
		"d6" => "sat",
		"d7" => "sun"
	);

	foreach($days as $n => $m)
	{
		if(!isset($weekday[$n])) continue;

		$times[] = $weekday[$n]["from"]."-".$weekday[$n]["to"].",".$m;
	}
}

function getSipType(&$v)
{
	switch($v["type"])
	{
		case 'trunk': return $v["type"];
		case 'multitrunk':  return "multi";
		default: //line
			return strlen($v["number"]) > 5 ? "line" : "nonum";
	}
}

function refactMT(&$a)
{
	$trunkMain = array();

	foreach($a as $id => $t)
	{
		if(isset($t["numbers"]))
		{
			$numbers = $t["numbers"];
			unset($t["numbers"]);
			foreach($numbers as $n)
			{
				if($t && $n && is_array($t) && is_array($n))
					$trunkMain[] = array_merge($t, array_merge($n, array("type" => "multitrunk", "account" => $t["name"])));
			}
		}
	}

	$a = $trunkMain;
}




if(isset($_GET["client_id"]))
	echo "</pre>";
