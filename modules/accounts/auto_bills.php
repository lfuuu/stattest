<?php

error_reporting(E_ALL);
	define ("PATH_TO_ROOT",$_SERVER['DOCUMENT_ROOT'].'/operator/');
		require_once("../../conf.php");
		include  "../../include_archaic/lib.php";
    		include  "bill_make_lib.php";
    $db->Connect();
		$date_d=get_param_protected('date_d');
		$date_m=get_param_protected('date_m');
		$date_y=get_param_protected('date_y');
		$bill_date="$date_y-$date_m-$date_d";
		
		$period_f_m=get_param_protected('period_f_m');
		$period_f_y=get_param_protected('period_f_y');
		$period_f="$period_f_y-$period_f_m";

		$period_pre_m=get_param_protected('period_pre_m');
		$period_pre_y=get_param_protected('period_pre_y');
		$period_pre="$period_pre_y-$period_pre_m";
		
		$comp=get_param_protected('comp');

		trigger_error("period_f=$period_f<br>period_pre=$period_pre<br> bill_date=$bill_date<br>comp=$comp");
		$design->AddMain('accounts/auto_bills.tpl');
		

		$where=" where bill_no='w' "; 
       
		$bill_no=false;
        	$req="select client FROM clients 
        		WHERE (type='org' OR type='priv') AND (status='work')";
        	
		$db->Query($req);
            
        	while($row = $db->NextRecord()){
	    		$client=$row['client'];
	    		trigger_error( "$client: ");
	    		$req2="select client from bill_bills where client='$client' and bill_no like '$period_f%'";
	    		if (!($result2 = mysql_query($req2)))
        		{trigger_error("can't read from database!<br>$req2"); break;}
	    		if(($row2 = mysql_fetch_assoc($result2))&&$client==$row2['client']){
			trigger_error("счет за $period_f уже есть - пропущено<br>");
	    		}else{
	        		$bill_no=do_make_bill($client,$bill_date,$period_f,$period_pre,$comp);
				trigger_error("счет $bill_no выставлен<br>");
				if (!$bill_no) {
					trigger_error("проблема с выставлением счета<br>");
					break;
				};
				$where.="or bill_no='$bill_no'";
				$bill_no=false;
	    		}
        	}
		
		$design->assign("where",$where);
		$design->AddMain('accounts/auto_bills.tpl');

		


		
		
		

		
	
?>