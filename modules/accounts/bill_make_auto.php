<?php
    error_reporting(E_ALL);
    set_magic_quotes_runtime(0);
    include  "../../include_archaic/lib.php";
    include  "bill_make_lib.php";
 //   define ("PATH_TO_ROOT",$_SERVER['DOCUMENT_ROOT'].'/operator/');
//   require_once("../../include/util.php");

function str_protect($str){
	$str=str_replace("\\","\\\\",$str);
	$str=str_replace("\"","\\\"",$str);
	return $str;
};

function get_param_protected($name,$default = '') {
	if (isset($_GET[$name])){
		$t=$_GET[$name];
	} else if (isset($_POST[$name])){
		$t=$_POST[$name];
	} else if (isset($_COOKIES[$name])){
		$t=$_COOKIES[$name];	
	} else {
		return $default;
	}
	
	return str_protect($t);
};
    
    		$date_d=get_param_protected('date_d');
		$date_m=get_param_protected('date_m');
		$date_y=get_param_protected('date_y');
		$date="$date_y-$date_m-$date_d";
		
		$period_f_m=get_param_protected('period_f_m');
		$period_f_y=get_param_protected('period_f_y');
		$period_f="$period_f_y-$period_f_m";

		$period_pre_m=get_param_protected('period_pre_m');
		$period_pre_y=get_param_protected('period_pre_y');
		$period_pre="$period_pre_y-$period_pre_m";
		
		$comp=get_param_protected('comp');
		$must_pay=get_param_protected('must_pay');
		$limit=get_param_protected('limit');
		if (0<$limit) $limit=" LIMIT $limit";
		
    db_open();
	$where=" where bill_no='w' "; 
       
	$bill_no=false;
        $req="select client, manager FROM clients 
        WHERE (type='org' OR type='priv') AND (status='work') ";
        if (!($result = mysql_query($req,$GLOBALS['dbh'])))
            {echo "can't read from database!<br>$req"; exit;}
            
        $bills_array=array();
	$list_bills='';
	$list_pma='';
	$list_bnv='';
	$max=0;
	$maxpma=1;
	$maxbnv=1;
        while($row = mysql_fetch_assoc($result)){
	    $client=$row['client'];
	    $manager=$row['manager'];
	    print "$client: ";
	    $req2="select client from bill_bills where client='$client' and bill_no like '$period_f%'";
	    if (!($result2 = mysql_query($req2,$GLOBALS['dbh'])))
        	{echo "can't read from database!<br>$req2"; exit;}
	    if(($row2 = mysql_fetch_assoc($result2))&&$client==$row2['client']){
		echo "счет за $period_f уже есть - пропущено\n";
	    }else{
	        $bill_no=do_make_bill($client,$date,$period_f,$period_pre,$comp,'default',$must_pay);
		echo "счет $bill_no выставлен\n";
		if (!$bill_no) {
			echo "проблема с выставлением счета";
			exit;
		};
		$where.="or bill_no='$bill_no'";
		$list_bills.="$bill_no,";
		$max++;
		$bills_array[]=array('bill_no'=>$bill_no,'client'=>$client);
		if ($manager=='pma'){
			$list_pma.="$bill_no,";
			$maxpma++;
		}else{
			$list_bnv.="$bill_no,";
			$maxbnv++;
		};
		$bill_no=false;
		
	    }
       } 
        echo "<br>".$where."<br>";
	$list_bills=substr($list_bills,0,strlen($list_bills)-1);
	$list_pma=substr($list_pma,0,strlen($list_pma)-1);
	$list_bnv=substr($list_bnv,0,strlen($list_bnv)-1);
	

        $bills_zerro=array();
        $bills_voip=array();
        $list_zerro="";
	$list_voip='';
        
        foreach($bills_array as $bill){
        	print_r($bill);
        	$tested_bill=test($bill['bill_no']);
        	if ($tested_bill['sum']==0) {$bills_zerro[]=$bill; $list_zerro.=$bill['bill_no'].",";}
        	if ($tested_bill['voip']==true) {$bills_voip[]=$bill; $list_voip.=$bill['bill_no'].",";}
        	
        };
	$list_zerro=substr($list_zerro,0,strlen($list_pma)-1);
	$list_voip=substr($list_voip,0,strlen($list_bnv)-1);

$query="INSERT INTO bill_log_auto VALUES (NULL,NOW(),'$list_bills','$list_pma','$list_bnv','$list_zerro','$list_voip',0)";
$res=mysql_query($query) or die ("<br>cannot do request $query <br>".mysql_error());


        
        echo "<h1>Нулевые Счета</h1>";
        foreach ($bills_zerro as $bill){
        	?>
        	<a href="../../index.php?module=clients&id=<?=$bill['client'];?>&clients_client=<?=$bill['client'];?>" target="_blank">
        	<?=$bill['client'];?>
        	</a><br>
        	<?php
        };
        
        echo "<h1>Счета c телефонией</h1>";
        foreach ($bills_voip as $bill){
        	?>
        	<a href="../../index.php?module=clients&id=<?=$bill['client'];?>&clients_client=<?=$bill['client'];?>" target="_blank">
        	<?=$bill['client'];?>
        	</a><br>
        	<?php
        };
        
        
        
        
        
        
function test($bill_no){
db_open();
	$query="SELECT * from bill_bills where bill_no='$bill_no'";
	$res=mysql_query($query) or die("test: $query <br>".mysql_error());
	$r=mysql_fetch_assoc($res) or die("test: fetch <br>".mysql_error());
	$ret['sum']=$r['sum'];
	
	$query="SELECT count(*) as c  from bill_bill_lines where bill_no='$bill_no' and item like 'Услуги IP%'";
	$res=mysql_query($query) or die("test: $query <br>".mysql_error());
	$r=mysql_fetch_assoc($res) or die("test: fetch <br>".mysql_error());
	$ret['voip']=false;
	if ($r['c']>0){$ret['voip']=true;}
	return $ret;

}

?>
<a href="../../index.php?module=accounts&action=accounts_bills&todo=auto_bills_print">
печать счетов
</a>
