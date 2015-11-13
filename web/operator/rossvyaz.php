<?php
define('NO_WEB',1);
define("PATH_TO_ROOT",'../../stat/');
header("Content-Type: text/html; charset=UTF-8");
include PATH_TO_ROOT."conf_yii.php";

function clear_raw_table()
{
  global $pg_db;
  $pg_db->Query('TRUNCATE TABLE geo.rossvyaz_raw');
}

function parse_rossvyaz($filename)
{
  global $pg_db;
  $file = file_get_contents('http://www.rossvyaz.ru/docs/articles/'.$filename);
  $m = array();
  preg_match_all('|<tr>	<td>	(\d+)	</td><td>	(\d+)	</td><td>	(\d+)	</td><td>	(\d+)	</td><td>	(.*)	</td><td>	(.*)	</td>	</tr>|', $file, $m, PREG_SET_ORDER);

  unset($file);
  $l = 0;

  $cc = 0;

  if (count($m) > 0)
  {
    $q = '';
    foreach($m as $r)
    {
      if ($q == '')
        $q = 'insert into geo.rossvyaz_raw("def","from","to","block","oper","geo")values';
      else
        $q .= ",\n";

      if (strlen($r[6]) > $l) $l = strlen($r[6]);

      $r[5] = pg_escape_string($r[5]);
      $r[6] = pg_escape_string($r[6]);

      $q .= "('{$r[1]}','{$r[2]}','{$r[3]}','{$r[4]}','{$r[5]}','{$r[6]}')";

	  if (($cc++ % 1000) == 0) {
		  $pg_db->Query($q, false);
		  $q = '';
	  }
    }

	if ($q) {
	  $pg_db->Query($q, false);
	}
    unset($q);
  }
  unset($m);

  echo "OK ".$filename." - ".$cc." lines<br/>\n";
  flush();
}


clear_raw_table();
parse_rossvyaz('ABC-3x.html');
parse_rossvyaz('ABC-4x.html');
parse_rossvyaz('ABC-8x.html');
parse_rossvyaz('DEF-9x.html');

echo "parse - OK<br/>\n";


$pgDb = new PgSQLDatabase(PGSQL_HOST, PGSQL_USER, PGSQL_PASS, "voipdb");
$regions = $pgDb->AllRecords("select * from astschema.region");


$sqlIns = array();
$sqlDel = array();

$pgDb = new PgSQLDatabase(PGSQL_HOST, PGSQL_USER, PGSQL_PASS, "nispd");

$countAll = 0;
foreach($regions as $region)
{
    echo "\n<br>region: ".$region["id"];
    $sqlDel[$region["id"]] = "delete from astschema.extensions where context = 'my-local-".$region["code"]."-mobile-out' and region ='".$region["id"]."'";

    $sqlIns[$region["id"]] = array();


    $count = 0;
    foreach($pgDb->AllRecords("select prefix, sufix from voip.openca_get_mobile_prefix(".$region["id"].")") as $l)
    {
        $count++;
        $countAll++;

        $len = strlen($l["prefix"])+($l["sufix"]? 1 : 0);

        $l["ext_str"] = "_".$l["prefix"].($l["sufix"] ? "[".preg_replace("/[^\d]/", "", $l["sufix"])."]":"").str_repeat("X", 11-$len);
        //echo "\n".$l["prefix"]." + ".$l["sufix"]." => ".$l["ext_str"];

        $sqlIns[$region["id"]][] = "('my-local-".$region["code"]."-mobile-out', '".$l["ext_str"]."', 1, 'Return',  '\${MOBLOC}02',    'mcn-route',  't',   '".$region["id"]."')";

    }
    echo " => ".$count;
}

$pgDb = new PgSQLDatabase(PGSQL_HOST, PGSQL_USER, PGSQL_PASS, "voipdb");
$pgDb->Query("start transaction");
foreach($regions as $region)
{
    //echo "\n".$sqlDel[$region["id"]];
    $pgDb->Query($sqlDel[$region["id"]]);
    $ins = "insert into astschema.extensions (context, exten, priority, app, appdata, client, enabled, region) values ".
        implode(",\n", $sqlIns[$region["id"]]);

    //echo "\n".$ins;
    $pgDb->Query($ins);

}
$pgDb->Query("commit");

echo "\n<br/>fill ".$countAll." mobile out extensions\n<br/> complete";
