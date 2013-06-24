<?php
define('NO_WEB',1);
define('PATH_TO_ROOT','./');
include PATH_TO_ROOT."conf.php";



$region = 87;



//$db->Query("delete from voip_numbers where region = '".$region."'");


$sql = "";
for($i=0;$i<500;$i++)
{

 $num = '78633090'.str_pad($i, 3, "0", STR_PAD_LEFT); 

 $sql .= ($sql ? "," : "").'("'.$num.'",'.$region.')';
}

$db->Query('insert into voip_numbers(number,region) values'.$sql);

work();

$db->Query("update `voip_numbers` set price = null where region = ".$region." and beauty_level = 1");
$db->Query("update `voip_numbers` set price = 29999 where region = ".$region." and beauty_level = 2");
$db->Query("update `voip_numbers` set price = 12999 where region = ".$region." and beauty_level = 3");
$db->Query("update `voip_numbers` set price = 3999 where region = ".$region." and beauty_level = 4");
$db->Query("update `voip_numbers` set price = 0 where region = ".$region." and beauty_level = 0");


exit();








function getNumCat($num)
{
$cat = 0;
	if (
	// XXXXXXX	семь цифр подряд	222-22-22
		preg_match('/^(\d)\1\1\1\1\1\1$/', $num) ||
	// Z-XXXXXX	шесть одинаковых цифр подряд	288-88-88
	// YYYYYY-X		7-777-770
		preg_match('/^(\d)\1\1\1\1\1\d$/', $num) ||
		preg_match('/^\d(\d)\1\1\1\1\1$/', $num) ||
	// XXX-Y-XXX	шесть цифр не подряд + 1 в середине	666-2-666
		preg_match('/^(\d)\1\1\d\1\1\1$/', $num) ||
	// XYX-XX-XX	шесть цифр не подряд	939-99-99
	// XX-Y-XXXX		44-3-4444
	// XXXX-Y-XX		2222-3-22
	// XXXXX-Y-X		55555-6-5
		preg_match('/^(\d)\d\1\1\1\1\1$/', $num) ||
		preg_match('/^(\d)\1\d\1\1\1\1$/', $num) ||
		// preg_match('/^(\d)\1\1\d\1\1\1$/', $num) ||
		preg_match('/^(\d)\1\1\1\d\1\1$/', $num) ||
		preg_match('/^(\d)\1\1\1\1\d\1$/', $num) ||
	// XX-YYYYY	пять цифр подряд + две одинаковые цифры	225-55-55
	// XXXXX-YY		333-33-44
	// X-YYYYY-X		7-00000-7
		preg_match('/^(\d)\1(\d)\2\2\2\2$/', $num) ||
		preg_match('/^(\d)\1\1\1\1(\d)\2$/', $num) ||
		preg_match('/^(\d)(\d)\2\2\2\2\1$/', $num) ||
	// XXX-YYYY	3+4, 4+3 одинаковые цифры подряд	444-22-22
	// XXXX-YYY		22-22-444
		preg_match('/^(\d)\1\1(\d)\2\2\2$/', $num) ||
		preg_match('/^(\d)\1\1\1(\d)\2\2$/', $num) ||
	// XX-YYY-XX	3 одинаковых в середине сподряд + 4 одинаковых	22-555-22
		preg_match('/^(\d)\1(\d)\2\2\1\1$/', $num) ||
	// XXXX-YY-X	5 одинаковых цифр + 2 одинаковые в середине номера	222-23-32
	// X-YY-XXXX		2-33-2222
	// XX-YY-XXX		22-33-222
	// XXX-YY-XX		222-33-22
		preg_match('/^(\d)\1\1\1(\d)\2\1$/', $num) ||
		preg_match('/^(\d)(\d)\2\1\1\1\1$/', $num) ||
		preg_match('/^(\d)\1(\d)\2\1\1\1$/', $num) ||
		preg_match('/^(\d)\1\1(\d)\2\1\1$/', $num) ||
	// XY-ZZZZZ	пять цифр подряд + две разные цифры	245-55-55
	// XXXXX-YZ		555-55-32
	// YXXXXXZ		3-55555-2
		preg_match('/^\d\d(\d)\1\1\1\1$/', $num) ||
		preg_match('/^(\d)\1\1\1\1\d\d$/', $num) ||
		preg_match('/^\d(\d)\1\1\1\1\d$/', $num) ||
	// XYX-YX-YX	три одинаковые пары цифр	242-42-42
	// XYZ-YZ-YZ		246-46-46
	// XY-XY-XYZ		24-24-246
		preg_match('/^(\d)(\d)\1\2\1\2\1$/', $num) ||
		preg_match('/^\d(\d)(\d)\1\2\1\2$/', $num) ||
		preg_match('/^(\d)(\d)\1\2\1\2\d$/', $num) ||
	// X-YYY-XXX	две группы по три одинаковых цифры	2-555-222
	// X-YYY-ZZZ		2-555-666
	// XXX-YYY-X		222-111-2
	// XXX-YYY-Z		555-666-7
	// XXX-Y-ZZZ 		444-1-777
		preg_match('/^\d(\d)\1\1(\d)\2\2$/', $num) ||
		preg_match('/^(\d)\1\1(\d)\2\2\d$/', $num) ||
		preg_match('/^(\d)\1\1\d(\d)\2\2$/', $num) ||
	// XXXX-YXY	пять одинаковых цифр (из них 4 подряд) + две одинаковые	4444-242
	// XXXX-YYX		4444-224
	// XYY-XXXX		422-4444
	// YXY-XXXX		242-4444
		preg_match('/^(\d)\1\1\1(\d)\1\2$/', $num) ||
		preg_match('/^(\d)\1\1\1(\d)\2\1$/', $num) ||
		preg_match('/^(\d)(\d)\2\1\1\1\1$/', $num) ||
		preg_match('/^(\d)(\d)\1\2\2\2\2$/', $num) ||
	// NUM-ZZ-ZZ	четыре одинаковые цифры подряд в начале или конце номера	245-88-88
	// ZZ-ZZ-NUM		88-88-245
		preg_match('/^\d\d\d(\d)\1\1\1$/', $num) ||
		preg_match('/^(\d)\1\1\1\d\d\d$/', $num) ||
	// XYYYYXX	Четыре одинаковые цифры в середине номера + 3 одинаковых	244-44-22
	// XXYYYYX		22-444-42
		preg_match('/^(\d)(\d)\2\2\2\1\1$/', $num) ||
		preg_match('/^(\d)\1(\d)\2\2\2\1$/', $num) ||
	// XXX-YZ-YZ	три одинаковые цифры подряд + две пары цифр	222-45-45
	// XXX-YX-YX		222-42-42
	// XY-ZZZ-XY		71-999-71
	// XY-XY-ZZZ		45-45-222
		preg_match('/^(\d)\1\1(\d)(\d)\2\3$/', $num) ||
		preg_match('/^(\d)\1\1(\d)\1\2\1$/', $num) ||
		preg_match('/^(\d)(\d)(\d)\3\3\1\2$/', $num) ||
		preg_match('/^(\d)(\d)\1\2(\d)\3\3$/', $num) ||
	// XXY-Z-XXY	две группы по три цифры, зеркальные относительно центральной цифры номера	556-7-556
	// XYY-Z-XYY		566-7-566
	// XYX-Z-XYX		565-7-565
	// XYZ-A-XYZ		576-9-576
		preg_match('/^(\d)\1(\d)\d\1\1\2$/', $num) ||
		preg_match('/^(\d)(\d)\2\d\1\2\2$/', $num) ||
		preg_match('/^(\d)(\d)\1\d\1\2\1$/', $num) ||
		preg_match('/^(\d)(\d)(\d)\d\1\2\3$/', $num) ||
		0
	) $cat = 1;	
	elseif(
	// XX-ZY-XXX	5 одинаквых цифр в номере	22-40-222
	// XXX-ZY-XX		222-40-22
		preg_match('/^(\d)\1\d\d\1\1\1$/', $num) ||
		preg_match('/^(\d)\1\1\d\d\1\1$/', $num) ||
	// XXY-XY-YY	3 одинаковых + 4 одинаковых (из них 3 подряд)	225-25-55
		preg_match('/^(\d)\1(\d)\1\2\2\2$/', $num) ||
	// XYZ-XYZ-A	две группы по три цифры	283-283-0
	// XYY-XYY-X		288-288-2
	// XYX-XYX-Z		282-282-9
	// XYY-XYY-Z		288-288-9
		preg_match('/^(\d)(\d)(\d)\1\2\3\d$/', $num) ||
		preg_match('/^(\d)(\d)\2\1\2\2\1$/', $num) ||
		preg_match('/^(\d)(\d)\1\1\2\1\d$/', $num) ||
		preg_match('/^(\d)(\d)\2\1\2\2\d$/', $num) ||
	// A-XYZ-XYZ	две группы по три цифры	7-235-235
	// A-XXY-XXY		7-225-225
	// XXY-XXY-A		228-228-5
	// A-XYY-XYY		7-455-455
	// A-XYX-XYX		7-454-454
		preg_match('/^\d(\d)(\d)(\d)\1\2\3$/', $num) ||
		preg_match('/^\d(\d)\1(\d)\1\1\2$/', $num) ||
		preg_match('/^(\d)\1(\d)\1\1\2\d$/', $num) ||
		preg_match('/^\d(\d)(\d)\2\1\2\2$/', $num) ||
		preg_match('/^\d(\d)(\d)\1\1\2\1$/', $num) ||
	// AXX-XY-XY	две одинаковые пары подряд в конце номера + 2 одинаковые в начале	233-34-34
	// AYY-XY-XY		233-43-43
		preg_match('/^\d(\d)\1\1(\d)\1\2$/', $num) ||
		preg_match('/^\d(\d)\1(\d)\1\2\1$/', $num) ||
	// XYY-XX-YY	3+4 одинаковые цифры в номере из них 3 и 2 одинаковых подряд	655-66-55
	// XXY-XX-YY		665-66-55
	// XYX-YY-XX		656-55-66
	// XYX-XX-YY		656-66-55
		preg_match('/^(\d)(\d)\2\1\1\2\2$/', $num) ||
		preg_match('/^(\d)\1(\d)\1\1\2\2$/', $num) ||
		preg_match('/^(\d)(\d)\1\2\2\1\1$/', $num) ||
		preg_match('/^(\d)(\d)\1\1\1\2\2$/', $num) ||
	// ABX-XY-XY	две одинаковые пары подряд в конце номера	247-75-75
	// ABY-XY-XY	две одинаковые пары подряд в конце номера	375-25-25
	// NUM-XY-XY 	две одинаковые пары подряд в конце номера	245-16-16
		preg_match('/^\d\d\d(\d)(\d)\1\2$/', $num) ||
	// XYY-ZZ-BB	три пары одинаковых цифр	244-66-77
	// XYY-ZZ-XX		233-55-22
	// XYY-ZZ-YY 		455-66-55
	// XXA-YY-ZZ		663-55-77
	// XX-YYY-ZZ	две пары одинаковых цифр в начале и конце номера + 3 одинаковых в середине	22-555-33
	// A-XXXX-YY	четыре одинаковых цифры в середине + две одинаковых в конце	2-5555-33
		preg_match('/^\d(\d)\1(\d)\2(\d)\3$/', $num) ||
		preg_match('/^(\d)\1\d(\d)\2(\d)\3$/', $num) ||
	// XY-ZZZ-YX	3 одинаковые цифры в середине номера + 2 пары зеркальный цифр 	24-555-42
		preg_match('/^(\d)(\d)(\d)\3\3\2\1$/', $num) ||
	// XYZ-BB-YZ	Две одинаковые пары + пара одинаковых цифр в середине	245-33-45
		preg_match('/^\d(\d)(\d)(\d)\3\1\2$/', $num) ||
	// XY-ZAZ-XY	две одинаковые пары в начале и концеsrtномера, зеркальные относительно центральной	25-303-25
		preg_match('/^(\d)(\d)(\d)\d\3\1\2$/', $num) ||
	// A-XXX-YYB	2 одинаковые + 3 одинаковые цифры в номере	7-999-552
	// AXX-YYY-B		799-555-2
	// AB-XX-YYY		79-22-555
	// AB-XXX-YY		79-222-55
		preg_match('/^\d(\d)\1\1(\d)\2\d$/', $num) ||
		preg_match('/^\d(\d)\1(\d)\2\2\d$/', $num) ||
		preg_match('/^\d\d(\d)\1(\d)\2\2$/', $num) ||
		preg_match('/^\d\d(\d)\1\1(\d)\2$/', $num) ||
	// XXX-YY-ZX	3 подряд + 3 не подряд одинаковых цифр	222-44-02
	// XXX-YY-ZY		222-44-04
		preg_match('/^(\d)\1\1(\d)\2\d\1$/', $num) ||
		preg_match('/^(\d)\1\1(\d)\2\d\2$/', $num) ||
	// NUM-XX-YY	две пары одинаковых цифр подряд в конце номера	283-22-00
		preg_match('/^\d\d\d(\d)\1(\d)\2$/', $num) ||
	// XYY-Z-YYY	пять одинаковых цифр (из них 3 подряд)	277-5-777
	// XXX-YX-ZX		222-42-52
		preg_match('/^\d(\d)\1\d\1\1\1$/', $num) ||
		preg_match('/^(\d)\1\1\d\1\d\1$/', $num) ||
	// XXX-AZ-BZ	три одинаковые цифры в начале + две одинаковые в парах	222-31-51
		preg_match('/^(\d)\1\1\d(\d)\d\2$/', $num) ||
	// AB-XXXX-C	четыре одинаковые цифры в номере	246-66-67
	// A-XXXX-BC		244-44-71
	// AXX-BC-XX		244-58-44
	// XYZZ-A-ZZ		245-57-55
	// XY-ZZZ-AZ		245-55-75
		preg_match('/^\d\d(\d)\1\1\1\d$/', $num) ||
		preg_match('/^\d(\d)\1\1\1\d\d$/', $num) ||
		preg_match('/^\d(\d)\1\d\d\1\1$/', $num) ||
		preg_match('/^\d\d(\d)\1\d\1\1$/', $num) ||
		preg_match('/^\d\d(\d)\1\1\d\1$/', $num) ||
    // NUM-XY-YX	две зеркальные пары в нмере	792-34-43
    preg_match('/^\d\d\d(\d)(\d)\2\1$/', $num) ||
    0
	) $cat = 2;	
	elseif(		
	// XYZ-A-ZZZ	четыре одинаковые цифры не подряд 	245-3-555
		preg_match('/^\d\d(\d)\d\1\1\1$/', $num) ||
	// XYZ-AZ-YZ	две одинаковые пары в начале и конце номера, 3,5 и 6 цифры одинаковые	245-35-45
		preg_match('/^\d(\d)(\d)\d\2\1\2$/', $num) ||
	// NUM-Y-XXX	три одинаковые цифры в номере подряд	275-8-000
	// AB-XXX-CD		28-000-34
	// NUM-XXX-Y		245-333-9
	// Y-XXX-NUM		2-333-845
		preg_match('/^\d\d\d\d(\d)\1\1$/', $num) ||
		preg_match('/^\d\d(\d)\1\1\d\d$/', $num) ||
		preg_match('/^\d\d\d(\d)\1\1\d$/', $num) ||
		preg_match('/^\d(\d)\1\1\d\d\d$/', $num) ||
	// XY-XY-NUM	две одинаковые пары подряд в начале  номера	64-64-875
		preg_match('/^(\d)(\d)\1\2\d\d\d$/', $num) ||
	// XY-NUM-XY	две одинаковые пары в начале и конце номера	24-351-24
		preg_match('/^(\d)(\d)\d\d\d\1\2$/', $num) ||
	// XYZ-AB-YZ		243-75-43
		preg_match('/^\d(\d)(\d)\d\d\1\2$/', $num) ||
	// XX-YY-NUM	две пары одинаковых цифр в номере	22-33-875
	// XXX-A-YYB		222-6-778
	// XYY-A-ZZB		266-8-449
	// AXX-BC-YY		266-39-55
	// XX-NUM-YY		33-521-88
		preg_match('/^(\d)\1(\d)\2\d\d\d$/', $num) ||
		preg_match('/^(\d)\1\1\d(\d)\2\d$/', $num) ||
		preg_match('/^\d(\d)\1\d(\d)\2\d$/', $num) ||
		preg_match('/^\d(\d)\1\d\d(\d)\2$/', $num) ||
		preg_match('/^(\d)\1\d\d\d(\d)\2$/', $num) ||

		preg_match('/^(\d)(\d)(\d)\d\3\2\1$/', $num) ||
		preg_match('/^\d\d(\d)(\d)\2\1\d$/', $num) ||
		preg_match('/^\d(\d)(\d)\1\2\d\d$/', $num) ||
		0
  	) $cat = 3;	
	elseif(		
		preg_match('/^\d\d(\d)(\d)\1\2\d$/', $num) ||
		preg_match('/^\d\d(\d)\1(\d)\2\d$/', $num) ||
		preg_match('/^\d(\d)(\d)\2\1\d\d$/', $num) ||
		preg_match('/^\d(\d)(\d)\1\2\d\d$/', $num) ||
		preg_match('/^\d\d\d(\d)\1\d\1$/', $num) ||
		preg_match('/^\d\d\d\d\d(\d)\1$/', $num) ||		
		0
  	) $cat = 4;	


  return $cat;
}

function test()
{
	$nums = array(	
				'222-22-22',
				'288-88-88',
				'7-777-770',
				'666-2-666',
				'939-99-99',
				'44-3-4444',
				'2222-3-22',
				'55555-6-5',
				'225-55-55',
				'333-33-44',
				'7-00000-7',
				'444-22-22',
				'22-22-444',
				'22-555-22',

				'222-23-32',
				'2-33-2222',
				'22-33-222',
				'222-33-22',
				'245-55-55',
				'555-55-32',
				'3-55555-2',
				'242-42-42',
				'246-46-46',
				'24-24-246',
				'2-555-222',
				'2-555-666',
				'222-111-2',
				'555-666-7',
				'444-1-777',
				'4444-242',
				'4444-224',
				'242-4444',
				'242-4444',
				'245-88-88',
				'88-88-245',
				'244-44-22',
				'22-444-42',
				'222-45-45',
				'222-42-42',
				'71-999-71',
				'45-45-222',

				'556-7-556',
				'566-7-566',
				'565-7-565',
				'576-9-576',
				'22-40-222',
				'222-40-22',
				'225-25-55',
				'283-283-0',
				'288-288-2',
				'282-282-9',
				'288-288-9',

				'7-235-235',
				'7-225-225',
				'228-228-5',
				'7-455-455',
				'7-454-454',
				'233-34-34',
				'233-43-43',
				'655-66-55',
				'665-66-55',
				'656-55-66',
				'656-66-55',
				'247-75-75',
				'375-25-25',
				'245-16-16',
				'244-66-77',
				'233-55-22',
				'455-66-55',
				'663-55-77',
				'22-555-33',
				'2-5555-33',
				'24-555-42',
				'245-33-45',
				'25-303-25',
				'7-999-552',
				'799-555-2',
				'79-22-555',
				'79-222-55',
				'222-44-02',
				'222-44-04',
				'283-22-00',
				'277-5-777',
				'222-42-52',
				'222-31-51',
				'246-66-67',
				'244-44-71',
				'244-58-44',
				'245-57-55',
				'245-55-75',
				'245-3-555',
				'245-35-45',
				'275-8-000',
				'28-000-34',
				'245-333-9',
				'2-333-845',
				'64-64-875',
				'24-351-24',
				'243-75-43',
				'22-33-875',
				'222-6-778',
				'266-8-449',
				'266-39-55',
				'33-521-88',
				'792-34-43',
				);
	foreach($nums as $num){
		$num = str_replace('-', '', $num);
		$cat = getNumCat($num);
		echo $num.' - '.$cat."<br/>\n";
	}
}

function work()
{
	global $db, $region;
	$nums = $db->AllRecords('select number from voip_numbers where region = '.$region);
	foreach($nums as $num){
		$nnn = substr($num['number'], -7);
		$cat = getNumCat($nnn);
		$db->Query("UPDATE voip_numbers SET beauty_level='".$cat."' WHERE number='".$num['number']."'");
		echo $num['number'].' - '.$cat."<br/>\n";
	}
}

