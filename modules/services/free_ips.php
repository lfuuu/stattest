<?php


function get_free_nets($type,$node){
 
 global $db;
 $db->Connect();
 $IPs=array();
 
 
 //printdbg($type,'type');
 //printdbg($node,'port');
 
 if ($type==='K')
 {
 
   // printdbg("Ищем IP");
    $query="SELECT net, type, client 
	    FROM `routes`
            WHERE node='$node'
            AND  actual_to>NOW()
	    ORDER BY INET_ATON(LEFT(net, LENGTH(net)-3)), RIGHT(net,2)";
	    
    $res=mysql_query($query) or die("Нет коннетка с базой");

    $count=mysql_num_rows($res)-1;
   // printdbg($count);
    $r=mysql_fetch_array($res);
    $i=0;
    $nets=array();
    while ($count>0)
    {
    	$net=array();
    	//printdbg($r);
    	if($r['type']=='aggregate'){
    		$net[]=$r;
    //		printdbg($r);
    //		printdbg($count,'count');
    		$ip_=explode("/",$r['net']);
    //		printdbg($ip_,'ip_');
    		$num_ips=pow(2,(32-$ip_[1]));
    //		printdbg($num_ips,'num_ips');
    		$ip_long_start=ip2long($ip_[0]);
    		$ip_long_finish=$ip_long_start+$num_ips;
    		$ip_finish=long2ip($ip_long_finish);
    //		printdbg($ip_finish,'ip_finish');
    		
    		while (($r=mysql_fetch_array($res)) and (ip2long(substr($r['net'],0,strlen($r['net'])-3))<=$ip_long_finish)){
    			$net[]=$r;
    //			printdbg($r);
    			$count--;	
    //			printdbg($count,'count');
    		}
    		$nets[$i]=$net;
    //		printdbg($net);
    		$i++;
    	}else {
    	 	$r=mysql_fetch_array($res);
    	 	$count--;
    //	 	printdbg($r);
    //	 	printdbg($count,'count');
    	}
    	
    };
  //printdbg(count($nets),"кол-во подсетей");
   
   foreach($nets as $net)
   {
   	
   	$ip_=explode("/",$net[0]['net']);
    
    	$num_ips=pow(2,(32-$ip_[1]));
       // printdbg($num_ips,'num_ips');
    	$ip_long_start=ip2long($ip_[0]);
    	$ip_long_finish=$ip_long_start+$num_ips;
    	
   	$count=count($net);
   	if ($count >1 ){
           	for ($i=1;$i<$count-1;$i++)
           	{
           		$ip_=explode("/",$net[$i]['net']);
            	    	$num_ips=pow(2,(32-$ip_[1]));
                	//printdbg($num_ips,'num_ips');
            		$ip_long_start_i=ip2long($ip_[0]);
            		$ip_long_finish_i=$ip_long_start_i+$num_ips;
        		
        		$ip_=explode("/",$net[$i+1]['net']);
            	    	$num_ips=pow(2,(32-$ip_[1]));
                	//printdbg($num_ips,'num_ips');
            		$ip_long_start_n=ip2long($ip_[0]);
            		$ip_long_finish_n=$ip_long_start_i+$num_ips;
            		
            		if ($ip_long_finish_i+1<$ip_long_start_n)
            		{
            			//echo "<br>Свободные IP <br>";
            			for($k=$ip_long_finish_i+1;$k<$ip_long_start_n;$k++)
            			{
            				$IPs[]=long2ip($k);
            			};
            		}
        		
        		   			
           	}
           	
           	$ip_=explode("/",$net[$count-1]['net']);
            	    	$num_ips=pow(2,(32-$ip_[1]));
                	//printdbg($num_ips,'num_ips');
            		$ip_long_start_i=ip2long($ip_[0]);
            		$ip_long_finish_i=$ip_long_start_i+$num_ips;
        		
           	if ($ip_long_finish_i+1<$ip_long_finish)
            		{
            			//echo "<br>Свободные IP <br>";
            			for($k=$ip_long_finish_i+1;$k<$ip_long_finish;$k++)
            			{
            				$IPs[]=long2ip($k);
            			};
            		}
           	
           	
 }else{
        $ip_=explode("/",$net[0]['net']);
    
    	$num_ips=pow(2,(32-$ip_[1]));
       // printdbg($num_ips,'num_ips');
    	$ip_long_start=ip2long($ip_[0]);
    	$ip_long_finish=$ip_long_start+$num_ips;
    	//echo "<br>Свободные IP <br>";
    	for($k=$ip_long_start;$k<=$ip_long_finish;$k++)
    	{
    		$IPs[]=long2ip($k);	
    	};
 	
 };  
   };
    



};
return $IPs;
};


?>