<html>
<head>
<STYLE>
<!--
body, table, tr, td {font-size: 12px; font-family: Verdana, MS sans serif, Arial, Helvetica, sans-serif}
td.index {font-size: 10px; color: #000000; font-weight: bold}
td.empty {font-size: 10px; color: #000000; font-weight: bold}
td.dt_string {font-size: 10px; color: #000090; font-weight: bold}
td.dt_int {font-size: 10px; color: #909000; font-weight: bold}
td.dt_float {font-size: 10px; color: #007000; font-weight: bold}
td.dt_date  {font-size: 10px; color: #008080; font-weight: bold}
td.dt_unknown {font-size: 10px; background-color: #f0d0d0; font-weight: bold}
td.empty {font-size: 10px; background-color: #f0f0f0; font-weight: bold}
-->
</STYLE>
</head>
<body bgcolor="#ffffff" text="#000000" topmargin="0" leftmargin="10" marginwidth="0" marginheight="0" link="#000000" vlink="#000000" alink="#000000">

<table width="100%" bgcolor="#006699">
<tr>
	<td>&nbsp;</td>
	<td><font size="+3" color="#FFFFFF">ABC Excel Parser Pro</font></td>
	<td>&nbsp;</td>
</tr>
</table>

<p>&nbsp;</p>

<?php
////td.dt_string {font-size: 10px; color: #000090; font-weight: bold}

include ("../../excel.php");
function getmicrotime() {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

function uc2html($str) {
	$ret = '';
	for( $i=0; $i<strlen($str)/2; $i++ ) {
		$charcode = ord($str[$i*2])+256*ord($str[$i*2+1]);
		$ret .= '&#'.$charcode;
	}
	return $ret;
}

function show_time() {
	global $time_start,$time_end;

	$time = $time_end - $time_start;
	echo "Анализ сделан за $time секунды<hr size=1><br>";
}

function fatal($msg = '') {
	echo '[Fatal error]';
	if( strlen($msg) > 0 )
		echo ": $msg";
	echo "<br>\nВыполнение Скрипта прервано <br>\n";
	if( $f_opened) @fclose($fh);
	exit();
};

$err_corr = "Неподдерживаемый формат или битый файл";

$excel_file_size;
$excel_file = $_FILES['excel_file'];
if( $excel_file )
	$excel_file = $_FILES['excel_file']['tmp_name'];

if( $excel_file == '' ) fatal("Файл не загружен");

$exc = new ExcelFileParser("debug.log", ABC_NO_LOG );//ABC_VAR_DUMP);

$style = $_POST['style'];
if( $style == 'old' )
{
	$fh = @fopen ($excel_file,'rb');
	if( !$fh ) fatal("Файл не загружен");
	if( filesize($excel_file)==0 ) fatal("Файл не загружен");
	$fc = fread( $fh, filesize($excel_file) );
	@fclose($fh);
	if( strlen($fc) < filesize($excel_file) )
		fatal("Немогу прочитать файл");
		
	$time_start = getmicrotime();
	$res = $exc->ParseFromString($fc);
	$time_end = getmicrotime();
}
elseif( $style == 'segment' )
{
	$time_start = getmicrotime();
	$res = $exc->ParseFromFile($excel_file);
	$time_end = getmicrotime();
}

switch ($res) {
	case 0: break;
	case 1: fatal("Невозможно открыть файл");
	case 2: fatal("Файл, слишком маленький чтобы быть файлом Excel");
	case 3: fatal("Ошибка чтения заголовка файла");
	case 4: fatal("Ошибка чтения файла");
	case 5: fatal("Это - не файл Excel или файл, сохраненный в Excel < 5.0");
	case 6: fatal("Битый файл");
	case 7: fatal("В файле не найдены данные  Excel");
	case 8: fatal("Неподдерживаемая версия файла");

	default:
		fatal("Неизвестная ошибка");
}

/*
print '<pre>';
print_r( $exc );
print '</pre>';
exit;
*/

show_time();

echo <<<LEG
<b>Легенда:</b><br><br>
<table border=1 cellspacing=0 cellpadding=3>
<tr><td>Тип данных</td><td>Описание</td></tr>
<tr><td class=empty>&nbsp;</td><td class=index>Пустая ячейка</td></tr>
<tr><td class=dt_string>ABCabc</td><td class=index>Строка</td></tr>
<tr><td class=dt_int>12345</td><td class=index>Целое</td></tr>
<tr><td class=dt_float>123.45</td><td class=index>С плавающей запятой</td></tr>
<tr><td class=dt_date>123.45</td><td class=index>Дата</td></tr>
<table>
<br><br>

LEG;

	for( $ws_num=0; $ws_num<count($exc->worksheet['name']); $ws_num++ )
	{
		print "<b>Рабочий лист: \"";
		if( $exc->worksheet['unicode'][$ws_num] ) {
			print uc2html($exc->worksheet['name'][$ws_num]);
		} else
			print $exc->worksheet['name'][$ws_num];

		print "\"</b>";
		$ws = $exc->worksheet['data'][$ws_num];

		if( is_array($ws) &&
		    isset($ws['max_row']) && isset($ws['max_col']) ) {
		 echo "\n<br><br><table border=1 cellspacing=0 cellpadding=2>\n";

		 print "<tr><td>&nbsp;</td>\n";
		 for( $j=0; $j<=$ws['max_col']; $j++ ) {
			print "<td class=index>&nbsp;";
			if( $j>25 ) print chr((int)($j/26)+64);
			print chr(($j % 26) + 65)."&nbsp;</td>";
		 }

		 for( $i=0; $i<=$ws['max_row']; $i++ ) {
		  print "<tr><td class=index>".($i+1)."</td>\n";
		  if(isset($ws['cell'][$i]) && is_array($ws['cell'][$i]) ) {
		   for( $j=0; $j<=$ws['max_col']; $j++ ) {

			if( ( is_array($ws['cell'][$i]) ) &&
			    ( isset($ws['cell'][$i][$j]) )
			   ){

			 // Печать данных ячейки
			 print "<td class=\"";
			 $data = $ws['cell'][$i][$j];

			 $font = $ws['cell'][$i][$j]['font'];
			 $style = " style ='".ExcelFont::ExcelToCSS($exc->fonts[$font])."'";

		   switch ($data['type']) {
			// строка
			case 0:
				print "dt_string\"".$style.">";
				$ind = $data['data'];
				if( $exc->sst['unicode'][$ind] ) {
					$s = uc2html($exc->sst['data'][$ind]);
				} else
					$s = $exc->sst['data'][$ind];
				if( strlen(trim($s))==0 )
					print "&nbsp;";
				else
					print $s;
				break;
			//целое число
			case 1:
				print "dt_int\"".$style.">";
				print (int)($data['data']);
				break;
			//вещественное число
			case 2:
				print "dt_float\"".$style.">";
				print (float)($data['data']);
				break;
			// дата
			case 3:
				print "dt_date\"".$style.">";

				$ret = $exc->getDateArray($data['data']);
				printf ("%s-%s-%s",$ret['day'], $ret['month'], $ret['year']);
				break;
			default:
				print "dt_unknown\"".$style."> &nbsp;";
				break;
		   }
			 print "</td>\n";
			} else {
				print "<td class=empty>&nbsp;</td>\n";
			}
		   }
		  } else {
			// печать пустой записи
			for( $j=0; $j<=$ws['max_col']; $j++ )
				print "<td class=empty>&nbsp;</td>";
			print "\n";
		  }
		  print "</tr>\n";
		 }

		 echo "</table><br>\n";
		} else {
			// пустой рабочий лист
			print "<b> - Пусто</b><br>\n";
		}
		print "<br>";
	}

/*	print "Форматы<br>";
	foreach($exc->format as $value) {
		printf("( %x )",array_search($value,$exc->format));
		print htmlentities($value,ENT_QUOTES);
		print "<br>";
	}

    print "XFs<br>";
	for( $i=0;$i<count($exc->xf['format']);$i++) {
		printf ("(%x)",$i);
		printf (" Формат (%x) шрифт (%x)",$exc->xf['format'][$i],$exc->xf['font'][$i]);

		print "<br>";
	}
*/


?>

<p>&nbsp;</p>

<table width="100%"><tr><td width="100%" align="right"><a href="http://www.zakkis.ca" style="font-size: 9px; text-decoration: none; font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">ZAKKIS Tech. 2002  All Rights Reserved.</a>&nbsp;&nbsp;</td></tr></table>

</body>
</html>
