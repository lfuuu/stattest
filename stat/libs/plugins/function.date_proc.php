<?php
	function smarty_function_date_proc($param,&$smarty){
		$val = $param['in'];
		$parse_date = explode("-",$val);

		if(!isset($param['mode']))
			return;
		switch($param['mode']){
			case 'year_filter':{
				if($parse_date[0] == $param['year'])
					return '';
				else
					return $val;
				break;
			}
		}
	}
?>