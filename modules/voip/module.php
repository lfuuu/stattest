<?php
class _voip_prices_parser{
	public static function &xls_read($fname){
		require_once INCLUDE_PATH.'exel/excel_reader2.php';
		@$xlsreader = new Spreadsheet_Excel_Reader($fname,false,'koi8r');
		return $xlsreader;
	}
	public static function &csv_read($fname){
		$f = fopen($fname, 'r');
		$csv = array();
		while(($row=fgetcsv($f, 1*1024*1024, "\t", '"'))){
			$csv[] = $row;
		}
		fclose($f);
		return $csv;
	}

	public static function &beeline_read_price(Spreadsheet_Excel_Reader &$xlsreader){
		$defs = array();
		$len = $xlsreader->rowcount();
		for($i=2;$i<=$len;$i++){
			if(!$xlsreader->val($i,1))
				continue;
			$defs[] = array(
				'def'=>$xlsreader->val($i,1),
				'type'=>$xlsreader->val($i,2),
				'dest'=>$xlsreader->val($i,3),
				'activation'=>$xlsreader->val($i,4),
				'active'=>'Y',
				'currency'=>$xlsreader->val($i,6),
				'price'=>$xlsreader->val($i,7)
			);
		}
		return $defs;
	}
	public static function beeline_price_find_dgroups(&$defs,&$undefined_groups){
		global $db;
		$query = "select `pk`,`dest_name`,`dest_fo_pk` from `voip_dest_zones`";
		$zones = $db->AllRecords($query,null,MYSQL_ASSOC);
		$len = count($defs);
		$zlen = count($zones);
		for($i=0;$i<$len;$i++){
			$d =& $defs[$i];
			if(strpos($d['dest'],'(mob)')!==false){
				$d['fixormob'] = 'mob';
			}else
				$d['fixormob'] = 'fix';
			if($d['type'] == 'INT'){
				$d['dgroup'] = 2;
				$d['dsubgroup'] = 0;
			}elseif($d['type'] == 'CIS'){
				$d['dgroup'] = 3;
				$d['dsubgroup'] = 0;
			}elseif($d['type'] == 'DOM'){
				$d['dgroup'] = 1;
				$buf = array();
				$n = mb_strtolower(str_replace(' (mob)','',str_replace(' (fix)','',$d['dest'])),'koi8r');
				if(strpos($n, 'бесплатны')!==false)
					$d['dsubgroup'] = 9;
				else{
					for($j=0;$j<$zlen;$j++){
						if($n === $zones[$j]['dest_name']){
							$buf = array(&$zones[$j]);
							break;
						}elseif(strpos($n, $zones[$j]['dest_name'])!==false){
							$buf[] =& $zones[$j];
						}
					}
					$cbuf = count($buf);
					if($cbuf==1){
						$d['dsubgroup'] = $buf[0]['dest_fo_pk'];
					}elseif($cbuf>1){
						$flag = true;
						$zone = null;
						for($j=0;$j<$cbuf;$j++){
							if(is_null($zone))
								$zone = $buf[$j]['dest_fo_pk'];
							elseif($zone<>$buf[$j]['dest_fo_pk']){
								$flag = false;
								break;
							}
						}
						if($flag)
							$d['dsubgroup'] = $zone;
						else{
							$sbuf = array();
							$sblen = count($buf);
							for($j=0;$j<$sblen;$j++){
								if($buf[$j]['dest_name'] === substr($n, 0, strlen($buf[$j]['dest_name'])))
									$sbuf[] =& $buf[$j];
							}
							if(count($sbuf)==1)
								$d['dsubgroup'] = $sbuf[0]['dest_fo_pk'];
							else
								$d['dsubgroup'] = -1;
						}
					}else{
						$d['dsubgroup'] = -1;
					}
				}
			}elseif($d['type'] == 'ZON'){
				$d['dgroup'] = 0;
				$d['dsubgroup'] = 0;
			}elseif($d['type'] == 'LOC'){
				$d['dgroup'] = 0;
				$d['dsubgroup'] = 1;
			}else{
				$d['dgroup'] = -1;
				$d['dsubgroup'] = -1;
			}
			if($d['dgroup'] == -1)
				array_unshift($undefined_groups, $i);
			elseif($d['dsubgroup'] == -1)
				array_push($undefined_groups, $i);
		}
	}

	public static function &beeline_read_changes(Spreadsheet_Excel_Reader &$xlsreader){
		$defs = array();
		for($i=2;;$i++){
			if(!$xlsreader->val($i,1))
				break;
			if(trim($xlsreader->val($i,5)) == 'No Change')
				continue;
			$l = count($defs);
			$defs[$l] = array(
				'def'=>trim($xlsreader->val($i,2)).' '.trim($xlsreader->val($i,3)),
				'defs'=>trim($xlsreader->val($i,2)),
				'dest'=>$xlsreader->val($i,1),
				'price'=>$xlsreader->val($i,4),
				'active'=>'Y',
				'currency'=>'RUB'
			);
			preg_match('/(\d+)/',$xlsreader->val($i,6),$m);
			$d = cal_from_jd(gregoriantojd(12, 30, 1899)+$m[1],CAL_GREGORIAN);
			$defs[$l]['activation'] = $d['year'].'-'.$d['month'].'-'.$d['day'];

			if(preg_match('/Del:([\d\s,\-]+)/',$xlsreader->val($i,5),$m))
				$defs[$l]['drop'] = trim($m[1]);

			if(strpos($defs[$l]['dest'],'(mob)')!==false){
				$defs[$l]['fixormob'] = 'mob';
			}else
				$defs[$l]['fixormob'] = 'fix';
		}
		$_defs = self::beeline_parse_ranges_in_change_pl($defs);
		return $_defs;
	}
	private static function beeline_parse_ranges_in_change_pl($defs){
		$_defs = array();
		$_sche = array();
		$len = count($defs);
		for($i=0;$i<$len;$i++){
			$defs_ = array_map('trim',explode(' ', $defs[$i]['def'],2));
			if(count($defs_)==1){
				$_defs[] = $defs[$i];
				continue;
			}else{
				$defs_sections = array_map('trim', explode(',',$defs_[1]));
				$l = count($defs_sections);
				for($j=0;$j<$l;$j++){
					if(strpos($defs_sections[$j], '-')===false){
						$_defs[] = array(
							'def'=>$defs_[0].$defs_sections[$j],
							'defs'=>$defs_[0],
							'dest'=>$defs[$i]['dest'],
							'price'=>$defs[$i]['price'],
							'activation'=>$defs[$i]['activation'],
							'active'=>'Y',
							'currency'=>'RUB',
							'fixormob'=>$defs[$i]['fixormob']
						);
						$_sche[$defs_[0].$defs_sections[$j]] = true;
					}else{
						$defs_section_range = array_map('trim',explode('-',$defs_sections[$j]));
						for($n=$defs_section_range[0];$n<=$defs_section_range[1];$n++){
							$_defs[] = array(
								'def'=>$defs_[0].$n,
								'defs'=>$defs_[0],
								'dest'=>$defs[$i]['dest'],
								'price'=>$defs[$i]['price'],
								'activation'=>$defs[$i]['activation'],
								'active'=>'Y',
								'currency'=>'RUB',
								'fixormob'=>$defs[$i]['fixormob']
							);
							$_sche[$defs_[0].$n] = true;
						}
					}
				}
			}
		}
		for($i=0;$i<$len;$i++){
			if(!isset($defs[$i]['drop']))
				continue;
			$defs_ = array_map('trim',explode(' ', $defs[$i]['drop'],2));
			if(count($defs_)==1){
				continue;
			}else{
				$defs_sections = array_map('trim', explode(',',$defs_[1]));
				$l = count($defs_sections);
				for($j=0;$j<$l;$j++){
					if(strpos($defs_sections[$j], '-')===false){
						if(array_key_exists($defs_[0].$defs_sections[$j], $_sche))
							continue;
						$_defs[] = array(
							'def'=>$defs_[0].$defs_sections[$j],
							'defs'=>$defs_[0],
							'dest'=>$defs[$i]['dest'],
							'price'=>$defs[$i]['price'],
							'activation'=>$defs[$i]['activation'],
							'active'=>'N',
							'currency'=>'RUB',
							'fixormob'=>$defs[$i]['fixormob']
						);
					}else{
						$defs_section_range = array_map('trim',explode('-',$defs_sections[$j]));
						for($n=$defs_section_range[0];$n<=$defs_section_range[1];$n++){
							if(array_key_exists($defs_[0].$n, $_sche))
								continue;
							$_defs[] = array(
								'def'=>$defs_[0].$n,
								'defs'=>$defs_[0],
								'dest'=>$defs[$i]['dest'],
								'price'=>$defs[$i]['price'],
								'activation'=>$defs[$i]['activation'],
								'active'=>'N',
								'currency'=>'RUB',
								'fixormob'=>$defs[$i]['fixormob']
							);
						}
					}
				}
			}
		}
		return $_defs;
	}
	public static function beeline_changes_find_dgroups(&$defs,&$undefined_groups){
		global $db;
		$dlist = array();
		$len = count($defs);
		for($i=0;$i<$len;$i++){
			if(!in_array((int)$defs[$i]['def'],$dlist))
				$dlist[] = (int)$defs[$i]['def'];
		}
		$query = 'select `def`,`dgroup`,`dsubgroup` from `voip_defs` where `def` in ('.implode(',',$dlist).')';
		$mask = $db->AllRecords($query,'def',MYSQL_ASSOC);
		$fd = array();
		for($i=0;$i<$len;$i++){
			if(array_key_exists($defs[$i]['def'], $mask)){
				$defs[$i]['dgroup'] = $mask[$defs[$i]['def']]['dgroup'];
				$defs[$i]['dsubgroup'] = $mask[$defs[$i]['def']]['dsubgroup'];
			}else{
				if(in_array($defs[$i]['defs'], $fd)){
					$defs[$i]['dgroup'] = $mask[$defs[$i]['defs']]['dgroup'];
					$defs[$i]['dsubgroup'] = $mask[$defs[$i]['defs']]['dsubgroup'];
				}else{
					$query = "select * from voip_defs where substring(def from 1 for ".strlen($defs[$i]['defs']).")=".$defs[$i]['defs']." limit 1";
					$fetch = $db->AllRecords($query,null,MYSQL_ASSOC);
					if(count($fetch)){
						$mask[$defs[$i]['defs']] = $fetch[0];
						$defs[$i]['dgroup'] = $mask[$defs[$i]['defs']]['dgroup'];
						$defs[$i]['dsubgroup'] = $mask[$defs[$i]['defs']]['dsubgroup'];
					}else{
						$defs[$i]['dgroup'] = -1;
						$defs[$i]['dsubgroup'] = -1;
						array_unshift($undefined_groups, $i);
					}
				}
			}
		}
	}

	public static function &arktel_read_price_russia(&$csv){
		$defs = array();
		$len = count($csv);
		for($i=1;$i<$len;$i++){
			$defs_ = self::arktel_parse_ranges_in_price($csv[$i][1]);
			$len_ = count($defs_);
			for($j=0;$j<$len_;$j++){
				if(!is_numeric($defs_[$j]))
					continue;
				$defs[] = array(
					'def'=>$defs_[$j],
					'dest'=>$csv[$i][0],
					'activation'=>date('d.m.Y'),
					'type'=>'DOM',
					'active'=>'Y',
					'currency'=>'RUB',
					'price'=>(float)str_replace(',','.',$csv[$i][2]),
					'dgroup'=>-1,
					'dsubgroup'=>-1
				);
			}
		}
		return $defs;
	}
	public static function &arktel_read_price_sng(&$csv){
		$defs = array();
		$len = count($csv);
		for($i=1;$i<$len;$i++){
			$defs_ = self::arktel_parse_ranges_in_price($csv[$i][1]);
			$len_ = count($defs_);
			for($j=0;$j<$len_;$j++){
				if(!is_numeric($defs_[$j]))
					continue;
				$defs[] = array(
					'def'=>$defs_[$j],
					'dest'=>$csv[$i][0],
					'activation'=>date('d.m.Y'),
					'type'=>'CIS',
					'active'=>'Y',
					'currency'=>'RUB',
					'price'=>(float)str_replace(',','.',$csv[$i][2]),
					'dgroup'=>3,
					'dsubgroup'=>0
				);
			}
		}
		return $defs;
	}
	public static function &arktel_read_price_world(&$csv){
		$defs = array();
		$len = count($csv);
		for($i=1;$i<$len;$i++){
			$defs_ = self::arktel_parse_ranges_in_price($csv[$i][1]);
			$len_ = count($defs_);
			for($j=0;$j<$len_;$j++){
				if(!is_numeric($defs_[$j]) && 1>(int)$defs_[$j])
					continue;
				$defs[] = array(
					'def'=>$defs_[$j],
					'dest'=>$csv[$i][0],
					'activation'=>date('d.m.Y'),
					'type'=>'INT',
					'active'=>'Y',
					'currency'=>'RUB',
					'price'=>(float)str_replace(',','.',$csv[$i][2]),
					'dgroup'=>2,
					'dsubgroup'=>0
				);
			}
		}
		return $defs;
	}
	public static function &arktel_read_price_usa(&$csv){
		global $db;
		$query = "select price_operator from voip_defs where def=1 and operator_pk=4 order by activation_date desc limit 1";
		$db->Query($query);
		$p = $db->NextRecord(MYSQL_ASSOC);
		$defs = array();
		$len = count($csv);
		for($i=1;$i<$len;$i++){
			$defs_ = self::arktel_parse_ranges_in_price("1 ".$csv[$i][2]);
			$len_ = count($defs_);
			for($j=0;$j<$len_;$j++){
				if(!is_numeric($defs_[$j]) && 1>(int)$defs_[$j])
					continue;
				$defs[] = array(
					'def'=>$defs_[$j],
					'dest'=>'США',
					'activation'=>date('d.m.Y'),
					'type'=>'INT',
					'active'=>'Y',
					'currency'=>'RUB',
					'price'=>$p['price_operator'],
					'dgroup'=>2,
					'dsubgroup'=>0
				);
			}
		}
		return $defs;
	}
	public static function &arktel_read_price_mosobl(&$csv){
		$p = 0.50;
		$defs = array();
		$len = count($csv);
		for($i=0;$i<$len;$i++){
			$defs_ = self::arktel_parse_ranges_in_price("7".$csv[$i][0].' '.$csv[$i][1]);
			$len_ = count($defs_);
			for($j=0;$j<$len_;$j++){
				if(!is_numeric($defs_[$j]) && 1>(int)$defs_[$j])
					continue;
				$defs[] = array(
					'def'=>$defs_[$j],
					'dest'=>$csv[$i][2],
					'activation'=>date('d.m.Y'),
					'type'=>'LOC',
					'active'=>'Y',
					'currency'=>'RUB',
					'price'=>$p,
					'dgroup'=>-1,
					'dsubgroup'=>-1
				);
			}
		}
		return $defs;
	}
	private static function arktel_parse_ranges_in_price($defs){
		if(!strlen($defs))
			return;
		if(!is_numeric($defs{0})){
			return array($defs);
		}

		$defs_ = array();
		preg_match_all('/([\d\-]+)(\s|,|\/|;)?/',$defs,$m,PREG_SET_ORDER);
		$ft = $m[0][1];
		$len = count($m);
		if($len==1)
			$defs_[] = $ft;
		if(strpos($ft,'-')!==false){
			$i=0;
			$ft = '';
		}else{
			$i=1;
		}
		for(;$i<$len;$i++){
			if(isset($m[$i][2]) && !trim($m[$i][2])){
				$ft .= $m[$i][1];
				continue;
			}
			if(strpos($m[$i][1],'-')!==false){
				$_parts = explode('-',$m[$i][1]);
				for($j=$_parts[0];$j<=$_parts[1];$j++){
					$defs_[] = $ft.$j;
				}
			}else
				$defs_[] = $ft.$m[$i][1];
			if(isset($m[$i][2]) && trim($m[$i][2])==';')
				$ft = '';
		}
		return $defs_;
	}
	public static function arktel_price_find_dgroups(&$defs,&$undefined_groups){
		global $db;
		$query = "select `pk`,`dest_name`,`dest_fo_pk` from `voip_dest_zones`";
		$zones = $db->AllRecords($query,null,MYSQL_ASSOC);
		$len = count($defs);
		$zlen = count($zones);
		for($i=0;$i<$len;$i++){
			$d =& $defs[$i];
			if(strpos($d['dest'],'моб.')!==false){
				$d['fixormob'] = 'mob';
			}else
				$d['fixormob'] = 'fix';
			if($d['type'] == 'INT'){
				$d['dgroup'] = 2;
				$d['dsubgroup'] = 0;
			}elseif($d['type'] == 'CIS'){
				$d['dgroup'] = 3;
				$d['dsubgroup'] = 0;
			}elseif($d['type'] == 'DOM'){
				$d['dgroup'] = 1;
				$buf = array();
				$n = mb_strtolower(str_replace(' моб.','',str_replace(' (fix)','',$d['dest'])),'koi8r');
				if(strpos($n, 'бесплатны')!==false)
					$d['dsubgroup'] = 9;
				else{
					for($j=0;$j<$zlen;$j++){
						if($n === $zones[$j]['dest_name']){
							$buf = array(&$zones[$j]);
							break;
						}elseif(strpos($n, $zones[$j]['dest_name'])!==false){
							$buf[] =& $zones[$j];
						}
					}
					$cbuf = count($buf);
					if($cbuf==1){
						$d['dsubgroup'] = $buf[0]['dest_fo_pk'];
					}elseif($cbuf>1){
						$flag = true;
						$zone = null;
						for($j=0;$j<$cbuf;$j++){
							if(is_null($zone))
								$zone = $buf[$j]['dest_fo_pk'];
							elseif($zone<>$buf[$j]['dest_fo_pk']){
								$flag = false;
								break;
							}
						}
						if($flag)
							$d['dsubgroup'] = $zone;
						else{
							$sbuf = array();
							$sblen = count($buf);
							for($j=0;$j<$sblen;$j++){
								if($buf[$j]['dest_name'] === substr($n, 0, strlen($buf[$j]['dest_name'])))
									$sbuf[] =& $buf[$j];
							}
							if(count($sbuf)==1)
								$d['dsubgroup'] = $sbuf[0]['dest_fo_pk'];
							else
								$d['dsubgroup'] = -1;
						}
					}else{
						$d['dsubgroup'] = -1;
					}
				}
			}elseif($d['type'] == 'ZON'){
				$d['dgroup'] = 0;
				$d['dsubgroup'] = 0;
			}elseif($d['type'] == 'LOC'){
				$d['dgroup'] = 0;
				$d['dsubgroup'] = 1;
			}else{
				$d['dgroup'] = -1;
				$d['dsubgroup'] = -1;
			}
			if($d['dgroup'] == -1)
				array_unshift($undefined_groups, $i);
			elseif($d['dsubgroup'] == -1)
				array_push($undefined_groups, $i);
		}
	}
	public static function &arktel_read_changes(Spreadsheet_Excel_Reader &$xlsreader){
		$i = 0;
		while($i<=$xlsreader->sheets[0]['numRows']){
			if(preg_match('/^[\s0-9,\-;]+$/',$xlsreader->val($i,1)))
				break;
			$i++;
		}

		$defs = array();
		for(;;$i++){
			if(!$xlsreader->val($i,1))
				break;
			if(!preg_match('/^[\s0-9,\-;]+$/',$xlsreader->val($i,1)))
				break;
			$defs_ = self::arktel_parse_ranges_in_price($xlsreader->val($i,1));
			$date_ = explode('/',$xlsreader->val($i,6));
			$date_ = $date_[2].'-'.$date_[1].'-'.$date_[0];
			$len_ = count($defs_);
			$price = (is_numeric($xlsreader->val($i,3)))?$xlsreader->val($i,3):round($xlsreader->val($i,5)/$xlsreader->val($i,4),2);
			for($j=0;$j<$len_;$j++){
				if(!is_numeric($defs_[$j]))
					continue;
				$defs[] = array(
					'def'=>$defs_[$j],
					'defs'=>$defs_[$j],
					'dest'=>$xlsreader->val($i,2),
					'activation'=>$date_,
					'type'=>'',
					'active'=>'Y',
					'currency'=>'RUB',
					'price'=>(float)$price,
					'dgroup'=>-1,
					'dsubgroup'=>-1
				);
			}
		}

		return $defs;
	}

	public static function mcn_read_price($str){
		$l = explode("\n",$str);
		$len = count($l);
		$defs = array();
		for($i=0;$i<$len;$i++){
			$d = array_map('trim',explode("\t",$l[$i]));
			if(!is_numeric($d[0]))
				continue;
			$defs[$d[0]] = $d[1];
		}
		return $defs;
	}
}

class _voip_export_csv{
	public static function put($str, $name){
		$str = iconv('koi8r', 'cp1251', $str);
		header("Content-Type: application/force-download");
		header("Content-Length: ".strlen($str));
		header('Content-Disposition: attachment; filename="'.$name.'.xls"');
		header("Cache-Control: public, must-revalidate");
		header("Pragma: hack");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Content-Transfer-Encoding: binary");
		echo $str;
		exit();
	}
}

class m_voip extends IModule{
	public function voip_operators_list(){
		global $db,$design;

		$query = 'select * from `voip_operators` order by `pk`';
		$db->Query($query);
		$ops = array();
		while($row = $db->NextRecord(MYSQL_ASSOC)){
			$ops[$row['pk']] = $row;
		}

		$design->assign('operators_list',$ops);
		$design->AddMain('voip/operators_list.html');
	}

	public function voip_defcodes(){
		global $db,$design;
		$design->AddMain('voip/defcodes.html');
	}

	public function voip_upload(){
		global $db,$design;

		if(isset($_POST['step']) && $_POST['step']=='upfile'){
			if(!$_FILES['upfile']){
				trigger_error('Пожалуйста, загрузите файл для обработки');
			}elseif($_FILES['upfile']['error']){
				trigger_error('При загрузке файла произошла ошибка. Пожалуйста, попробуйте еще раз');
			}else{
				$f =& $_FILES['upfile'];
				if(
					in_array($_POST['ftype'],array('xls_beeline','xls_beeline_change','xls_arktel','xls_arktel_change'))
				&&
					$f['type']<>'application/vnd.ms-excel'
				){
					trigger_error('Формат файла указан не правильно');
				}else{
					$defs = array();
					if($_POST['ftype']=='xls_beeline'){
						$design->assign('operator_id',2);
						$xlsreader = _voip_prices_parser::xls_read($f['tmp_name']);
						$defs = _voip_prices_parser::beeline_read_price($xlsreader);
						unset($xlsreader);
						$undefined_groups = array();
						_voip_prices_parser::beeline_price_find_dgroups($defs, $undefined_groups);
						//sorting
						$len = count($defs);
						$ulen = count($undefined_groups);
						$sdefs = array();
						for($i=0;$i<$ulen;$i++){
							$sdefs[] =& $defs[$undefined_groups[$i]];
						}
						for($i=0;$i<$len;$i++){
							if(in_array($i, $undefined_groups))
								continue;
							$sdefs[] =& $defs[$i];
						}
					}elseif($_POST['ftype']=='xls_beeline_changes'){
						$design->assign('operator_id',2);
						$xlsreader = _voip_prices_parser::xls_read($f['tmp_name']);
						$defs = _voip_prices_parser::beeline_read_changes($xlsreader);
						unset($xlsreader);
						$undefined_groups = array();
						_voip_prices_parser::beeline_changes_find_dgroups($defs, $undefined_groups);
						//sorting
						$len = count($defs);
						$ulen = count($undefined_groups);
						$sdefs = array();
						for($i=0;$i<$ulen;$i++){
							$sdefs[] =& $defs[$undefined_groups[$i]];
						}
						for($i=0;$i<$len;$i++){
							if(in_array($i, $undefined_groups))
								continue;
							$sdefs[] =& $defs[$i];
						}
					}elseif($_POST['ftype']=='csv_arktel_russia'){
						$design->assign('operator_id',4);
						$csv =& _voip_prices_parser::csv_read($f['tmp_name']);
						$defs = _voip_prices_parser::arktel_read_price_russia($csv);
						$undefined_groups = array();
						_voip_prices_parser::arktel_price_find_dgroups($defs, $undefined_groups);
						//sorting
						$len = count($defs);
						$ulen = count($undefined_groups);
						$sdefs = array();
						for($i=0;$i<$ulen;$i++){
							$sdefs[] =& $defs[$undefined_groups[$i]];
						}
						for($i=0;$i<$len;$i++){
							if(in_array($i, $undefined_groups))
								continue;
							$sdefs[] =& $defs[$i];
						}
					}elseif($_POST['ftype']=='csv_arktel_sng'){
						$design->assign('operator_id',4);
						$csv =& _voip_prices_parser::csv_read($f['tmp_name']);
						$defs = _voip_prices_parser::arktel_read_price_sng($csv);
						$undefined_groups = array();
						_voip_prices_parser::arktel_price_find_dgroups($defs, $undefined_groups);
						$sdefs =& $defs;
					}elseif($_POST['ftype']=='csv_arktel_world'){
						$design->assign('operator_id',4);
						$csv =& _voip_prices_parser::csv_read($f['tmp_name']);
						$defs = _voip_prices_parser::arktel_read_price_world($csv);
						$undefined_groups = array();
						_voip_prices_parser::arktel_price_find_dgroups($defs, $undefined_groups);
						$sdefs =& $defs;
					}elseif($_POST['ftype']=='csv_arktel_usa'){
						$design->assign('operator_id',4);
						$csv =& _voip_prices_parser::csv_read($f['tmp_name']);
						$defs = _voip_prices_parser::arktel_read_price_usa($csv);
						$undefined_groups = array();
						_voip_prices_parser::arktel_price_find_dgroups($defs, $undefined_groups);
						$sdefs =& $defs;
					}elseif($_POST['ftype']=='csv_arktel_mosobl'){
						$design->assign('operator_id',4);
						$csv =& _voip_prices_parser::csv_read($f['tmp_name']);
						$defs = _voip_prices_parser::arktel_read_price_mosobl($csv);
						$undefined_groups = array();
						_voip_prices_parser::arktel_price_find_dgroups($defs, $undefined_groups);
						$sdefs =& $defs;
					}elseif($_POST['ftype']=='xls_arktel_changes'){
						$design->assign('operator_id',4);
						$xlsreader = _voip_prices_parser::xls_read($f['tmp_name']);
						$defs = _voip_prices_parser::arktel_read_changes($xlsreader);
						unset($xlsreader);
						$undefined_groups = array();
						_voip_prices_parser::beeline_changes_find_dgroups($defs, $undefined_groups);
						//sorting
						$len = count($defs);
						$ulen = count($undefined_groups);
						$sdefs = array();
						for($i=0;$i<$ulen;$i++){
							$sdefs[] =& $defs[$undefined_groups[$i]];
						}
						for($i=0;$i<$len;$i++){
							if(in_array($i, $undefined_groups))
								continue;
							$sdefs[] =& $defs[$i];
						}
					}
					$query = "select `pk`,`name` from `voip_dest_fo` order by `pk`";
					$design->assign('subgroups',$db->AllRecords($query,'pk',MYSQL_ASSOC));
					$design->assign_by_ref('defs',$sdefs);
					$design->AddMain('voip/upload_stage1.html');
					return true;
				}
			}
		}elseif(isset($_POST['step']) && $_POST['step'] == 'upprice'){
			$query = "
				insert into `voip_defs`
					(`activation_date`,`active`,`destination`,`def`,`operator_pk`,`dgroup`,`dsubgroup`,`fixormob`,`price_operator`,`price_operator_usd`)
				values
					('%s','%s','%s','%s',%d,%d,%d,'%s',%.2f,%.4f)";
			foreach($_POST as $def=>&$val){
				if(in_array($def, array('module','action','step','submit')))
					continue;
				if(!is_array($val))
					continue;
				if(preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/',$val['activation'],$m))
					$val['activation'] = $m[3].'-'.$m[2].'-'.$m[1];
				$q = sprintf(
					$query,
					addcslashes($val['activation'], "\\'"),
					addcslashes($val['active'],"\\'"),
					addcslashes($val['dest'],"\\'"),
					addcslashes($def,"\\'"),
					$_POST['operator_id'],
					$val['dgroup'],
					$val['dsubgroup'],
					addcslashes($val['fixormob'],"\\'"),
					$val['currency']=='RUB'?$val['price']:0,
					$val['currency']<>'RUB'?$val['price']:0
				);
				$db->Query($q);
			}
			Header("Location: index.php?module=voip&action=upload&step=stage2");
			exit();
		}
		if(isset($_REQUEST['step']) && $_REQUEST['step']=='stage2'){
			$design->AddMain('voip/upload_stage2.html');
			return true;
		}

		$design->AddMain('voip/upload.html');
	}

	public function voip_stats($client){
		global $db,$design;
		if(!$client){
			trigger_error('Клиент не выбран');
			return;
		}

		$currency = $db->AllRecords("select currency from clients where client='".addslashes($client)."'");
		$currency = $currency[0]['currency'];
		$usage_voip=$db->AllRecords('select * from usage_voip where client="'.addslashes($client).'" order by id');
		$design->assign('phone',$phone=get_param_protected('phone',''));
		$phones = array();
		$phones_sel = array();
		foreach ($usage_voip as $r) {
			if (substr($r['E164'],0,4)=='7095') $r['E164']='7495'.substr($r['E164'],4);
			$phones[$r['E164']]=$r['E164'];
			if ($r['E164']==$phone) $phones_sel[]=$r['id'];
		}
		$design->assign('phones',$phones);

		$def=getdate();
		$def['mday']=1; $from=param_load_date('from_',$def);
		$def['mday']=31; $to=param_load_date('to_',$def);

		$def['mday']=1; $cur_from=param_load_date('cur_from_',$def);
		$def['mday']=31; $cur_to=param_load_date('cur_to_',$def);
		$def['mon']--; if ($def['mon']==0) {$def['mon']=12; $def['year']--; }
		$def['mday']=1; $prev_from=param_load_date('prev_from_',$def);
		$def['mday']=31; $prev_to=param_load_date('prev_to_',$def);

		$dgroups = $db->AllRecords("select * from `voip_dests` order by `pk`",'pk',MYSQL_ASSOC);
		$dgroup = get_param_raw('dgroup', 'all');
		if(!array_key_exists($dgroup, $dgroups))
			$dgroup = 'all';
		$dsubgroups = $db->AllRecords("select * from `voip_dest_fo` order by `pk`",'pk',MYSQL_ASSOC);
		$dsubgroup = get_param_raw('dsubgroup','all');
		if(!array_key_exists($dsubgroup, $dsubgroups))
			$dsubgroup = 'all';
		$fixormob = get_param_raw('fixormob','both');
		if(!in_array($fixormob, array('fix','mob','both'),true))
			$fixormob = 'both';
		$direction = get_param_raw('direction','both');
		if(!in_array($direction,array('both','in','out')))
			$direction = 'both';

		$design->assign('dgroups',$dgroups);
		$design->assign('dgroup',$dgroup);
		$design->assign('dsubgroups',$dsubgroups);
		$design->assign('dsubgroup',$dsubgroup);
		$design->assign('fixormob',$fixormob);
		$design->assign('direction',$direction);
		$design->assign('detality',$detality=get_param_protected('detality','day'));
		$design->assign('is_priced',$is_priced=get_param_integer('is_priced','1'));

		if (!($stats=&$this->GetStatsVoIP($from,$to,$detality,$client,$phones_sel,$is_priced,$dgroup,$dsubgroup,$fixormob,$direction,$currency))) return;
		$design->assign_by_ref('stats',$stats);

		if (get_param_raw('xml')){
			header("Content-type: text/xml");
			$design->ProcessEx('voip/stats_xml.tpl');
		} else {
			$design->AddMain('voip/stats.tpl');
			$design->AddMain('voip/stats_form.tpl');
		}
	}
	private function GetStatsVoIP($from,$to,$detality,$client,$usage_arr,$is_priced=0,$dgroup='all',$dsubgroup='all',$fixormob='both',$direction='both',$currency='RUR'){
		global $db;
		$f = date('Y-m-d',$from);
		$t = date('Y-m-d',$to);

		if($currency=='USD')
			$currency='_usd';
		else
			$currency='';

		if(!count($usage_arr)){
			$q = "select `id` from `usage_voip` where `client`='".addcslashes($client,"\\'")."' and ('".$f."' between `actual_from` and `actual_to` or '".$t."' between `actual_from` and `actual_to`)";
			$usage_arr = array_keys($db->AllRecords($q,'id',MYSQL_ASSOC));
			unset($q);
		}
		$lj = '';
		if($detality=='year'){
			$sel = "
				`vcs`.`time` `date`,
				unix_timestamp(`vcs`.`time`) `time`,
				count(*) `cnt`,
				sum(/*if(`len`>5,`len`,0)*/`len`) `len`,
				sum(round(`price_mcn".$currency."`,2)) `sum`,
				sum(`price".$currency."`) `price`
			";
			$group = " group by year(`vcs`.`time`)";
			$format='Y г.';
		}elseif($detality=='month'){
			$sel = "
				`vcs`.`time` `date`,
				unix_timestamp(`vcs`.`time`) `time`,
				count(*) `cnt`,
				sum(/*if(`len`>5,`len`,0)*/`len`) `len`,
				sum(round(`price_mcn".$currency."`,2)) `sum`,
				sum(`price".$currency."`) `price`
			";
			$group=' group by month(`vcs`.`time`)';
			$format='Месяц Y г.';
		}elseif($detality=='day'){
			$sel = "
				`vcs`.`time` `date`,
				unix_timestamp(`vcs`.`time`) `time`,
				count(*) `cnt`,
				sum(/*if(`len`>5,`len`,0)*/`len`) `len`,
				sum(round(`price_mcn".$currency."`,2)) `sum`,
				sum(`price".$currency."`) `price`
			";
			$group=' group by date(`vcs`.`time`)';
			$format='d месяца Y г.';
		}else{
			$sel = "
				`vcs`.`time` `date`,
				unix_timestamp(`vcs`.`time`) `time`,
				1 `cnt`,
				if(`vcs`.`direction`='out',`uv`.`e164`,`unp`.`phone_num`) `caller`,
				if(`vcs`.`direction`='in',`uv`.`e164`,`unp`.`phone_num`) `called`,
				`vcs`.`len`,
				`vcs`.`price_mcn".$currency."` `sum`,
				`vcs`.`price".$currency."` `price`,
				`vo`.`name` `operator`
			";
			$group='';
			$lj = "
				left join
					`usage_nvoip_phone` `unp`
				on
					`unp`.`phone_id`=`vcs`.`phone_id`
				left join
					`usage_voip` `uv`
				on
					`uv`.`id`=`vcs`.`usage_id`
				left join
					`voip_operators` `vo`
				on
					`vo`.`pk`=`vcs`.`operator_pk`";
			$format='d месяца Y г. H:i:s';
		}

		if($is_priced=='1')
			$nonfree = ' and `vcs`.`price_mcn`>0 ';
		elseif($is_priced=='-1')
			$nonfree = ' and `vcs`.`price_mcn`=0 ';
		else
			$nonfree = '';


		if($dgroup<>'all')
			$dgr = ' and `vcs`.`dgroup`='.(int)$dgroup;
		else
			$dgr = '';
		if($dsubgroup<>'all')
			$dsg = ' and `vcs`.`dsubgroup`='.(int)$dsubgroup;
		else
			$dsg = '';
		if($fixormob<>'both')
			$fom = " and `vcs`.`fixormob`='".addslashes($fixormob)."'";
		else
			$fom = '';
		if($direction<>'both')
			$dir = " and `vcs`.`direction`='".addslashes($direction)."'";
		else
			$dir = '';

		$query = "
			select
				".$sel."
			from
				`voip_calls_stats` `vcs`".$lj."
			where
				`vcs`.`time` between '".$f."' and '".$t." 23:59:59'
			and
				`vcs`.`usage_id` in (".implode(',',$usage_arr).")".$nonfree.$dgr.$dsg.$fom.$dir.$group."
			order by
				`vcs`.`time`";

		$stat = array();
		$db->Query($query);
		$total = array(
			'len'=>0,
			'price'=>0,
			'cnt'=>0,
			'hulen'=>0,
			'sum'=>0,
			'date'=>'<b>Итого</b>',
			'caller'=>'&nbsp;',
			'called'=>'&nbsp;',
			'operator'=>'&nbsp;'
		);
		while(($row=$db->NextRecord(MYSQL_ASSOC))){
			$i = count($stat);
			$stat[$i] = $row;
			$stat[$i]['date'] = mdate($format, $stat[$i]['time']);
			$l =& $stat[$i]['len'];
			$h = floor($l/3600);
			$m = floor(($l%3600)/60);
			$s = (($l%3600)%60);
			$stat[$i]['hulen'] = ($h<10?'0'.$h:$h).':'.($m<10?'0'.$m:$m).':'.($s<10?'0'.$s:$s);
			$total['len']+=$stat[$i]['len'];
			$total['cnt']+=$stat[$i]['cnt'];
			$total['sum']+=$stat[$i]['sum'];
			$total['price']+=$stat[$i]['price'];
		}
		$l =& $total['len'];
		$h = floor($l/3600);
		$m = floor(($l%3600)/60);
		$s = (($l%3600)%60);
		$total['hulen'] = ($h<10?'0'.$h:$h).':'.($m<10?'0'.$m:$m).':'.($s<10?'0'.$s:$s);
		return array_merge($stat,array($total));
	}

	public function voip_tgroups_tariffication(){
		global $db,$design;

		$sel = array(
			'date_from'=>param_load_date('from_', array('mday'=>date('d'),'mon'=>date('m'),'year'=>date('Y'))),
			'date_to'=>param_load_date('to_',array('mday'=>date('d'),'mon'=>date('m'),'year'=>date('Y'))),
			'dgroup'=>get_param_raw('dgroup', 'all'),
			'dsubgroup'=>get_param_raw('dsubgroup','all'),
			'fixormob'=>get_param_raw('fixormob','both'),
			'sort_by'=>get_param_raw('sort_by','dest'),
			'sort_type'=>get_param_raw('sort_type','asc')
		);

		if(!in_array($sel['sort_by'], array('dest','dgroup','dsubgroup','fixormob','def','tmin','bmin','amin','omin','mprice','bprice','aprice','oprice','msum','bsum','asum','osum','mrealsum','diff'), true)){
			$sel['sort_by'] = 'dest';
		}
		if(!in_array($sel['sort_type'],array('asc','desc'), 'true')){
			$sel['sort_type'] = 'asc';
		}

		$design->assign('from_y',date('Y',$sel['date_from']));
		$design->assign('from_m',date('m',$sel['date_from']));
		$design->assign('from_d',date('d',$sel['date_from']));
		$design->assign('to_y',date('Y',$sel['date_to']));
		$design->assign('to_m',date('m',$sel['date_to']));
		$design->assign('to_d',date('d',$sel['date_to']));
		$design->assign('dgroup',$sel['dgroup']);
		$design->assign('dsubgroup',$sel['dsubgroup']);
		$design->assign('fixormob',$sel['fixormob']);
		$design->assign('sort_by',$sel['sort_by']);
		$design->assign('sort_type',$sel['sort_type']);

		$dests = $db->AllRecords('select * from `voip_dests` order by `pk`','pk',MYSQL_ASSOC);
		$dest_fo = $db->AllRecords('select * from `voip_dest_fo` order by `pk`','pk',MYSQL_ASSOC);

		$design->assign_by_ref('dests',$dests);
		$design->assign_by_ref('dest_fo',$dest_fo);

		if(in_array($sel['dgroup'], array_map('strval',array_keys($dests)), true))
			$dgroup_filter = " and `dgroup`=".$sel['dgroup'];
		else
			$dgroup_filter = '';
		if(in_array($sel['dsubgroup'], array_map('strval',array_keys($dest_fo)), true))
			$dsubgroup_filter = " and `dsubgroup`=".$sel['dsubgroup'];
		else
			$dsubgroup_filter = "";
		if(in_array($sel['fixormob'],array('fix','mob')))
			$fixormob_filter = " and `fixormob`='".$sel['fixormob']."'";
		else
			$fixormob_filter = "";

		if(in_array($sel['sort_by'],array('dest','dgroup','dsubgroup','fixormob'))){
			$sb = $sel['sort_by'];
			if($sb=='dgroup')
				$sb='vdest.name';
			elseif($sb=='dsubgroup')
				$sb='vdfo.name';
			elseif($sb=='fixormob')
				$sb='vdd.fixormob';
			elseif($sb=='dest')
				$sb='vdd.destination';
		}else
			$sb = 'vdd.dgroup';

		$query = "
			select
				vd.operator_pk,
				vdd.destination,
				vdest.name dgroup,
				vdfo.name dsubgroup,
				vdd.fixormob,
				vdd.def,
				vdca.def adef,
				vdcb.def bdef,
				vdca.price_operator aprice,
				vdcb.price_operator bprice,
				sum(vcs.len) len,
				sum(vcs.price_mcn) tsum,
				vd.price_operator,
				vp.price
			from voip_calls_stats vcs
			left join voip_defs_destinations vdd on vdd.def = vcs.def
			left join voip_calls_odefs vco on vco.call_pk = vcs.call_pk
			left join voip_defs vdca on vdca.pk = vco.adef_pk
			left join voip_defs vdcb on vdcb.pk = vco.bdef_pk
			inner join voip_defs vd on vd.pk = if(vcs.operator_pk=2,vdcb.pk,if(vcs.operator_pk=4,vdca.pk,0))
			left join voip_dests vdest on vdest.pk = vdd.dgroup
			left join voip_dest_fo vdfo on vdfo.pk = vdd.dsubgroup
			left join voip_prices vp on vp.def=vd.def and vp.tarif_group_pk=5
			where vcs.time between from_unixtime(".$sel['date_from'].") and from_unixtime(".($sel['date_to']+86400).")
			and vcs.def_pk > 0
			and vcs.operator_pk > 0
			and vcs.direction = 'out'
			".($sel["dgroup"] != "all" ? "and vdd.dgroup = '".$sel["dgroup"]."'" : "")."
			".($sel["dsubgroup"] != "all" ? "and vdd.dsubgroup = '".$sel["dsubgroup"]."'" : "")."
			".($sel["fixormob"] != "both" ? "and vdd.fixormob = '".$sel["fixormob"]."'" : "")."
			group by
				vd.operator_pk,
				vdd.destination,
				vdest.name,
				vdfo.name,
				vdd.fixormob,
				vdd.def,
				vdca.def,
				vdcb.def,
				vdca.price_operator,
				vdcb.price_operator,
				vd.price_operator,
				vp.price
			order by
				".$sb." ".$sel['sort_type'].",
				vdd.def
		";

        //printdbg($query);

		$defs = array();
		$tts = array(
			'tlen'=>0,
			'blen'=>0,
			'alen'=>0,
			'olen'=>0,
			'msum'=>0,
			'bsum'=>0,
			'asum'=>0,
			'osum'=>0,
			'mrealsum'=>0,
			'diff'=>0
		);
		$db->Query($query);
		while($row=$db->NextRecord(MYSQL_ASSOC)){
			if(!isset($defs[$row['def']])){
				$defs[$row['def']] = array(
					'detailed'=>array(2=>array(),4=>array()),
					'dest'=>$row['destination'],
					'dgroup'=>$row['dgroup'],
					'dsubgroup'=>$row['dsubgroup'],
					'fixormob'=>$row['fixormob'],
					'tlen'=>0,
					'blen'=>0,
					'alen'=>0,
					'olen'=>0,
					'mprice'=>$row['price'],
					/*'bprice'=>0,
					'aprice'=>0,
					'oprice'=>0,*/
					'msum'=>0,
					'bsum'=>0,
					'asum'=>0,
					'osum'=>0,
					'mrealsum'=>0,
					'diff'=>0
				);
			}
			$l =& $defs[$row['def']];

			$l['tlen']+=$row['len'];
			$l['msum']+=round($row['len']*$row['price']/60,2);
			$l['mrealsum']+=$row['tsum'];
			$tts['tlen']+=$row['len'];
			$tts['msum']+=round($row['len']*$row['price']/60,2);
			$tts['mrealsum']+=$row['tsum'];

			if($row['operator_pk']==2){
				$tts['blen']+=$row['len'];
				$tts['bsum']+=round($row['len']*$row['price_operator']/60,2);
				$l['blen']=$row['len'];
				$l['bsum']=round($row['len']*$row['price_operator']/60,2);
			}elseif($row['operator_pk']==4){
				$tts['alen']+=$row['len'];
				$tts['asum']+=round($row['len']*$row['price_operator']/60,2);
				$l['alen']=$row['len'];
				$l['asum']=round($row['len']*$row['price_operator']/60,2);
			}else{
				$l['osum']+=round($row['len']*$row['price_operator']/60,2);
				$tts['olen']+=$row['len'];
				$tts['osum']+=round($row['len']*$row['price_operator']/60,2);
			}

			if(isset($l['detailed'][$row['operator_pk']])){
				$d =& $l['detailed'][$row['operator_pk']];
				$def = ($row['operator_pk']==2)?$row['bdef']:$row['adef'];
				if(!isset($d[$def])){
					$d[$def] = array(
						'len'=>0,
						'sum'=>0,
						'ppm'=>($row['operator_pk']==2)?$row['bprice']:$row['aprice']
					);
				}

				$d[$def]['len'] += $row['len'];
				$d[$def]['sum'] = round($row['len']*$d[$def]['ppm']/60,2);

				unset($d,$def);
			}

			$l['diff'] = round($l['mrealsum']-$l['bsum']-$l['asum'],2);
			$l['mrealsum'] = round($l['mrealsum'],2);
			$tts['mrealsum']=round($tts['mrealsum'],2);
			$l['tmin'] = self::get_mins($l['tlen']);
			$l['bmin'] = self::get_mins($l['blen']);
			$l['amin'] = self::get_mins($l['alen']);
			$l['omin'] = self::get_mins($l['olen']);
		}
		unset($l);

		foreach($defs as $d=>&$v){
			$tts['diff']+=round($v['mrealsum']-$v['bsum']-$v['asum'],2);
			$jb = array();
			foreach($v['detailed'][2] as $def=>$vals){
				$jb[] = "{d:'".$def."',l:'".self::get_mins($vals['len'])."',p:'".$vals['ppm']."',t:'".$vals['sum']."'}";
			}
			$ja = array();
			foreach($v['detailed'][4] as $def=>$vals){
				$ja[] = "{d:'".$def."',l:'".self::get_mins($vals['len'])."',p:'".$vals['ppm']."',t:'".$vals['sum']."'}";
			}
			$json = sprintf("{b:[%s],a:[%s]}",join(',',$jb),join(',',$ja));

			$defs[$d]['detailed_json'] = $json;
			unset($ja,$jb,$json);
		}
		unset($v);
		if(!in_array($sel['sort_by'], array('dest','dgroup','dsubgroup','fixormob'))){
			$s = $sel['sort_by'];
			$m = $sel['sort_type'];
			if($s<>'def'){
				$sorting_buf = array();
				$sb =& $sorting_buf;
				$sf = SORT_NUMERIC;
				if($s<>'def')
					foreach($defs as $def=>&$vals){
						if(!isset($sb[trim($vals[$s])]))
							$sb[trim($vals[$s])] = array();
						$sb[trim($vals[$s])][] = $def;
					}
				if($m=='asc')
					ksort($sb,$sf);
				else
					krsort($sb,$sf);
				$tmp_defs = array();
				foreach($sb as $k=>&$v){
					sort($v,SORT_STRING);
					$l = count($v);
					for($i=0;$i<$l;$i++)
						$tmp_defs[$v[$i]] = $defs[$v[$i]];
				}
				unset($sb,$defs,$sorting_buf);
				$defs =& $tmp_defs;
				unset($tmp_defs);
			}else{
				if($m=='asc')
					ksort($defs,SORT_STRING);
				else
					krsort($defs,SORT_STRING);
			}
			unset($s,$m);
		}
		$tts['tmin'] = self::get_mins($tts['tlen']);
		$tts['amin'] = self::get_mins($tts['alen']);
		$tts['bmin'] = self::get_mins($tts['blen']);

        foreach($defs as &$tt) {
            $diff = $tt["diff"];
            foreach($tt as $tName => &$t)
            if(!is_array($t))
                $t = str_replace(".",",",$t);
            $tt["diff_orig"] = $diff;
        }
        unset($tt, $t);
        foreach($tts as &$t)
            if(!is_array($t))
                $t = str_replace(".",",",$t);
        unset($t);

		$design->assign('tts',$tts);
		$design->assign_by_ref('report',$defs);

		$design->AddMain('voip/tgroups_tariffication.html');
	}
	private static function get_human_tlen($sec){
		$days = floor($sec/86400);
		$hous = floor(($sec%86400)/3600);
		$mins = floor(($sec%86400%3600)/60);
		$secs = floor($sec%60);
		$ret = '';
		if($days)
			$ret .= $days.'д ';
		if($hous)
			$ret .= $hous.'ч ';
		if($mins)
			$ret .= $mins.'м ';
		$ret .= $secs.'с';
		return $sec.' : '.$ret;
	}
	private static function get_mins($sec){
		return round($sec/60,2);
	}

	public function voip_export_csv(){
		global $design, $db;

        $report = get_param_raw("report", "null");

        $l = array("all" => "Все");
        foreach($db->AllRecords("select pk, name from voip_tarif_groups order by name", '', MYSQL_ASSOC) as $pk => $name) {
            $l[$name["pk"]] = $name["name"];
        }
        $design->assign("l_groups", $l);

		if($report != "null" && isset($l[$report]))
        {
			global $db;
					$query = "
						select
							vdb.dgroup,
							vda.destination,
							vda.fixormob,
							vp.def,
							vdb.price_operator price_beeline,
							vda.price_operator price_arktel,
							vp.price price_mcn
                    from voip_prices vp
                    left join voip_defs_current vdcb on vdcb.def = vp.def and vdcb.operator_pk = 2
                    left join voip_defs_current vdca on vdca.def = vp.def and vdca.operator_pk = 4
                    left join voip_defs vdb on vdb.pk = vdcb.def_pk
                    left join voip_defs vda on vda.pk = vdca.def_pk
                    ".($report != "all" ? "where vp.tarif_group_pk = '".$report."'" : "")."
                    order by vdb.dgroup, vda.destination
					";
					$db->Query($query);
					$str = implode("\t",array('"G"','"Направление"','"Стац/Моб"','"Префикс"','"Цена билайн"','"Цена арктел"','"Цена MCN"'));
					while($row=$db->NextRecord(MYSQL_NUM)){
						$str .= "\n".implode("\t",array_map(function($row){if(is_numeric($row))return str_replace('.',',',$row);else return '"'.addcslashes($row,'\\"').'"';},$row));
					}

            $name = "Def коды и цены - ".$l[$report];
            _voip_export_csv::put($str, $name);
		}

		$design->AddMain('voip/export_list.html');
	}

	public function voip_tgroups(){
		global $db,$design;

		$groups = $db->AllRecords("select `pk`,`name` from `voip_tarif_groups`",'pk',MYSQL_ASSOC);

		$design->assign('groups',$groups);

		if(isset($_POST['uploadnew'])){
			$prices = file_get_contents($_FILES['upfile']['tmp_name']);
			if(strlen($prices)){
				$db->Query('start transaction');
				$r=$db->GetRow('select max(pk)+1 pk from voip_tarif_groups');
				$db->Query("insert into `voip_tarif_groups` set pk=".$r['pk'].", name='".addcslashes($_POST['gname'], "\\'")."'");
				$db->Query('commit');
				if(!mysql_errno()){
					$defs = _voip_prices_parser::mcn_read_price($prices);
					$db->Query('start transaction');
					$rate = $db->GetRow('select usd_rate from voip_tarif_groups where pk='.$r['pk'].' limit 1');
					foreach($defs as $def=>$price){
						$db->Query("insert into `voip_prices` set def='".$def."',tarif_group_pk='".$r['pk']."',price=".(float)$price.",price_usd=".round((float)$price/$rate['usd_rate'],4));
					}
					$db->Query('commit');
				}
				Header('Location: ?module=voip&action=tgroups&tgroup='.$r['pk']);
				exit();
			}
		}elseif(isset($_GET['tgroup'])){
			if(isset($_GET['delflag'])){
				$db->Query('start transaction');
				$db->Query('delete from `voip_prices` where `tarif_group_pk` = '.(int)$_GET['tgroup']);
				$db->Query('delete from `voip_tarif_groups` where `pk`='.(int)$_GET['tgroup']);
				if(mysql_error())
					$db->Query('rollback');
				else
					$db->Query('commit');
				Header('Location: ?module=voip&action=tgroups');
				exit();
			}
			$rate = $db->GetRow('select `usd_rate` from `voip_tarif_groups` where pk='.(int)$_GET['tgroup'],MYSQL_ASSOC);
			$rate = (float)$rate['usd_rate'];
			$query = "
				select
					`p`.`pk`,
					if(`d`.`destination` is null,(select `destination` from `voip_defs_destinations` where `def`=substring(`p`.`def` from 1 for length(`def`)) order by length(`def`) limit 1),`d`.`destination`) `destination`,
					`p`.`def`,
					`p`.`price`,
					`p`.`price_usd`
				from
					`voip_prices` `p`
				left join
					`voip_defs_destinations` `d`
				on
					`d`.`def` = `p`.`def`
				where
					`p`.`tarif_group_pk` = '".(int)$_GET['tgroup']."'
				order by
					`destination`,
					`p`.`def`
			";

			if(isset($_POST['ok'])){
				$nrate = (float)str_replace(',','.',$_POST['usd_rate']);
				if($rate<>$nrate){
					$db->Query('start transaction');
					$db->Query('update `voip_tarif_groups` set `usd_rate`='.$nrate.' where `pk`='.(int)$_GET['tgroup']);
					$db->Query('update `voip_prices` set `price_usd`=`price`/'.$nrate.' where `tarif_group_pk`='.(int)$_GET['tgroup']);
					$db->Query('commit');
					$rate = $nrate;
				}
			}

			$prices = $db->AllRecords($query,null,MYSQL_ASSOC);

			if(isset($_POST['ok'])){
				$mod = array();
				$len = count($prices);
				for($i=0;$i<$len;$i++){
					if((float)$prices[$i]['price']<>(float)str_replace(',','.',trim($_POST['price'][$prices[$i]['def']])))
						$mod[] = $i;
				}
				$len = count($mod);
				$ups = array();
				for($i=0;$i<$len;$i++){
					$ups[] = "update `voip_prices` set `price`=".
						(float)str_replace(',','.',trim($_POST['price'][$prices[$mod[$i]]['def']])).
						", `price_usd`=".((float)str_replace(',','.',trim($_POST['price'][$prices[$mod[$i]]['def']]))/$rate)."
						where def=".$prices[$mod[$i]]['def']." and tarif_group_pk=".(int)$_GET['tgroup'];
				}
				$db->Query('start transaction');
				$len = count($ups);
				for($i=0;$i<$len;$i++)
					$db->Query($ups[$i]);
				$db->Query('commit');
				Header('Location: ?module=voip&action=tgroups&tgroup='.(int)$_GET['tgroup']);
				exit();
			}

			$design->assign_by_ref('prices',$prices);
			$design->assign('usd_rate',$rate);
		}

		$design->AddMain('voip/tgroups.html');
	}

	public function voip_tdiffs(){
		global $db,$design;

		if(isset($_GET['go_report'])){
			$actual_from = param_load_date('from_', array('year'=>date('Y'),'mon'=>date('m'),'mday'=>date('d')));
			$actual_to = param_load_date('to_', array('year'=>date('Y'),'mon'=>date('m'),'mday'=>date('d')))+86400;

			/*$query = "
				select
					sum(if(`vc`.`len`<60,60,`vc`.`len`)*`vp`.`price`/60) `price`,
					`vd`.`dgroup`,
					`vtg`.`name` `tname`,
					`uv`.`client`
				from
					`voip_calls` `vc`
				inner join
					`voip_calls_dests` `vcd`
				on
					`vcd`.`call_pk` = `vc`.`pk`
				left join
					`voip_defs` `vd`
				on
					`vd`.`pk` = `vcd`.`def_pk`
				left join
					`voip_prices` `vp`
				on
					`vp`.`def` = `vd`.`def`
				left join
					(select distinct `pk`,`name` from `voip_tarif_groups`) `vtg`
				on
					`vtg`.`pk` = `vp`.`tarif_group_pk`
				left join
					`usage_voip` `uv`
				on
					`uv`.`id` = `vc`.`usage_id`
				where
					`vc`.`time` between from_unixtime(".$actual_from.") and from_unixtime(".$actual_to.")
				and
					`vc`.`dcause` in('10','1F')
				and
					`vc`.`direction`='out'
				group by
					`vd`.`dgroup`,
					`vtg`.`name`,
					`uv`.`client`
			";*/

			$query = "
				select
					sum(round(if(`vcs`.`len`<60,60,`vcs`.`len`)*if(`vcs`.`dgroup`=0 and `vcs`.`fixormob`='fix' and `vcs`.`price_mcn`=0,0,`vp`.`price`)/60,2)) `price`,
					`vcs`.`dgroup`,
					`vcs`.`fixormob`,
					`vtg`.`name` `tname`,
					`uv`.`client`
				from
					`voip_calls_stats` `vcs`
				left join
					`voip_prices` `vp`
				on
					`vp`.`def` = `vcs`.`def`
				left join
					(select distinct `pk`,`name` from `voip_tarif_groups`) `vtg`
				on
					`vtg`.`pk` = `vp`.`tarif_group_pk`
				left join
					`usage_voip` `uv`
				on
					`uv`.`id` = `vcs`.`usage_id`
				where
					`vcs`.`time` between from_unixtime(".$actual_from.") and from_unixtime(".$actual_to.")
				and
					`vcs`.`direction` = 'out'
				group by
					`vcs`.`dgroup`,
					`vcs`.`fixormob`,
					`vtg`.`name`,
					`uv`.`client`
				having
					`vtg`.`name` is not null
			";

			$report = array();
			$tarifs = array();
			$tsum = array();

			$db->Query($query);
			while($row=$db->NextRecord(MYSQL_ASSOC)){
				if(!in_array($row['tname'], $tarifs))
					$tarifs[] = $row['tname'];
				if(!array_key_exists($row['tname'],$tsum))
					$tsum[$row['tname']] = array('0fix'=>0,'0mob'=>0,1=>0,2=>0,3=>0,'sum'=>0);
				if(!isset($report[$row['client']]))
					$report[$row['client']] = array();
				$l =& $report[$row['client']];
				if(!isset($l[$row['tname']]))
					$l[$row['tname']] = array('0fix'=>0,'0mob'=>0,1=>0,2=>0,3=>0,'sum'=>0);
				$l =& $l[$row['tname']];
				if($row['dgroup']==0)
					$row['dgroup'] = $row['dgroup'].$row['fixormob'];
				$l[$row['dgroup']] += $row['price'];
				$l['sum'] += $row['price'];//$l['0fix']+$l['0mob']+$l[1]+$l[2]+$l[3];
				$tsum[$row['tname']][$row['dgroup']] += $row['price'];
				$tsum[$row['tname']]['sum'] += $row['price'];
			}
			unset($l);

			$design->assign_by_ref('report',$report);
			$design->assign('tarifs',$tarifs);
			$design->assign('tsum',$tsum);
		}

		$design->AddMain('voip/tdiffs.html');
	}
}
?>
