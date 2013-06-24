<?php
define('NO_WEB',1);
define("PATH_TO_ROOT",'../');
header("Content-Type: text/html; charset=UTF-8");
include PATH_TO_ROOT."conf.php";

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
  preg_match_all('|<tr>	<td>	(\d+)	</td>	<td>	(\d+)	</td>	<td>	(\d+)	</td>	<td>	(\d+)	</td>	<td>	(.*)	</td>	<td>	(.*)	</td>	</tr>|', $file, $m, PREG_SET_ORDER);
  unset($file);
  $l = 0;
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

      $r[5] = pg_escape_string(iconv('windows-1251', 'koi8-r', $r[5]));
      $r[6] = pg_escape_string(iconv('windows-1251', 'koi8-r', $r[6]));

      $q .= "('{$r[1]}','{$r[2]}','{$r[3]}','{$r[4]}','{$r[5]}','{$r[6]}')";
    }
  }
  unset($m);

  $pg_db->Query($q, false);

  unset($q);

  echo "OK ".$filename." <br/>\n";
  flush();
}


clear_raw_table();
parse_rossvyaz('ABC-3x.html');
parse_rossvyaz('ABC-4x.html');
parse_rossvyaz('ABC-8x.html');
parse_rossvyaz('DEF-9x.html');

echo "OK";