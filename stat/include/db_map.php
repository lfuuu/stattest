<?
class Db_map {
	var $linksF,$linksR;
	var $keys;
	var $types;
	var $hidden;
	var $visible;
	var $dblecho;
	var $postactions;
	var $readonly;
	var $readonly2=array();
	var $error_mode_echo=1;
	var $error_mode_quit=0;
	var $_currow,$_curignore;
	var $translate;
	
	function SetErrorMode($echo,$quit){
		//echo 0 - не выводить, 1 - вывод через echo, 2 - trigger_error
		//quit 0 - только return, 1 - halt
		$this->error_mode_echo=$echo;
		$this->error_mode_quit=$quit;
		//вид обработки ошибок
	}
	
	//$keys - ассоциированный массив всех ключевых полей/наборов полей
	//$links - массив всех связей один-ко-многим. циклов быть не должно! дополнительно - 3е поле - действия при добавлении
	//$types - ассоциированный массив полей к типам и действиям по их контролю: array(type, action_control, params_for_checker..)
	//visible - какие поля показывать в раскрывающемся списке
	//dblecho - поля с двойным выбором (список+"использовать поле")
	//variants - варианты для списков без связей
	//hidden - скрывать
	//translate - отображение из "таблица.поле" в человеческое название и комментарий
	function Db_map($keys,$links,$types,$visible,$dblecho,$variants,$hidden,$translate,$postactions,$readonly,$order) {
		$this->links=array();
		foreach ($links as $v){
			if (!preg_match('/([^.]+)\.([^.]+)/',$v[0],$m1) ||
				!preg_match('/([^.]+)\.([^.]+)/',$v[1],$m2)) {echo 'Link parse error'; exit;}
			if (!isset($this->linksF[$m1[1]][$m1[2]])) $this->linksF[$m1[1]][$m1[2]]=array();
			$this->linksF[$m1[1]][$m1[2]][]=array($m2[1],$m2[2],isset($v[2])?$v[2]:'',isset($v[3])?$v[3]:'');
			if (!isset($this->linksR[$m2[1]][$m2[2]])) $this->linksR[$m2[1]][$m2[2]]=array();
			$this->linksR[$m2[1]][$m2[2]][]=array($m1[1],$m1[2]);
		}
		foreach ($keys as $t=>$k){
			if (!is_array($keys[$t])) $keys[$t]=array($keys[$t]);
		}
		//из формата A[$table.'.'.$field] делаем A[$table][$field]
		$this->variants=array();
		foreach ($variants as $k=>$v){
			if (!preg_match('/([^.]+)\.([^.]+)/',$k,$m)) {echo 'Variants parse error'; exit;}
			$this->variants[$m[1]][$m[2]]=$v;
		}
		$this->translate=array();
		foreach ($translate as $k=>$v){
			if (!preg_match('/([^.]+)\.([^.]+)/',$k,$m)) {echo 'Translate parse error'; exit;}
			if (!is_array($v)) $v=array($v,'');
			$this->translate[$m[1]][$m[2]]=$v;
		}

		$this->keys=$keys;
		$this->types=$types;
		$this->visible=$visible;
		$this->dblecho=$dblecho;
		$this->hidden=$hidden;
		$this->readonly=$readonly;
		$this->postactions=$postactions;
		$this->order=$order;
	}
	function rset($k,$val){
		if (!isset($this->_curignore[$k])) $this->_currow[$k]=$val;
	}
	//эта функция выполняет проверку значения
	function CheckValue($table,$field,$value,&$row,&$ignore,&$old){
		global $db;
		if (!isset($this->types[$table.'.'.$field])) return;
		$t=($this->types[$table.'.'.$field]);
		$P=array();
		for ($i=2;isset($t[$i]);$i++) $P[]=$t[$i];
		if (isset($t[1]) && $t[1]){
			$v=eval($t[1]);
			if ($v) if ($e=$this->_error('Ошибка в поле '.$field.': '.$v.'<br>')) return $e;
		}
		if (!($this->CheckType($value,$t[0],$P))) if ($e=$this->_error('Ошибка в поле '.$field."<br>")) return $e;
	}
	
	//добавляет запись в таблицу
	function AddRow($table,$row){
		global $db;
		$ignore=array();
		$this->_currow=&$row;
		$this->_curignore=&$ignore;
		foreach ($row as $k=>$v) {
			if ($e=$this->CheckValue($table,$k,$v,$row,$ignore,$row)) return $e;
		}

		if (isset($this->linksF[$table])) foreach ($this->linksF[$table] as $f=>$vals) if (!isset($ignore[$f])) foreach ($vals as $v) {
			$db->Query('select count(*) from '.$v[0].' where `'.$v[1].'`="'.$row[$f].'"');
			if (!($r=$db->NextRecord())) if ($e=$this->_error('AddRow strange error')) return $e;
			$r=$r[0];
			if ($r!=1) if ($e=$this->_error('Ошибка при добавлении записи - нет соответствующей записи в связаной таблице (AddRow: '.$table.'.'.$f.'="'.$row[$f].'" maps with '.$r.' rows on '.$v[0].'.'.$v[1].')<br>')) return $e;
			if (isset($v[2]) && $v[2]){
				$db->Query('select * from '.$v[0].' where `'.$v[1].'`="'.$row[$f].'"');
				$r=$db->NextRecord();
				eval($v[2]);
			}
		}
		$list='';
		$values='';
		foreach ($row as $k=>$v) {
			if ($list) $list.=',';
			if ($values) $values.=',';
			$list.='`'.$k.'`';
			$values.='"'.$v.'"';
		}
		$db->Query('insert into '.$table.' ('.$list.')'.' values ('.$values.')');
	}
	//изменяет запись
	function UpdateRow($table,$row,$old){
		global $db;
		$ignore=array();
		$this->_currow=&$row;
		$this->_curignore=&$ignore;
		if (isset($this->linksB[$table])) foreach ($this->linksB[$table] as $f=>$vals) {
			if ($row[$f]!=$old[$f]) return;
		}

		foreach ($row as $k=>$v) {
			if ($e=$this->CheckValue($table,$k,$v,$row,$ignore,$old)) return $e;
		}
		
		if (isset($this->linksF[$table])) foreach ($this->linksF[$table] as $f=>$vals) if (!isset($ignore[$f])) foreach ($vals as $v) {
			$db->Query('select count(*) from '.$v[0].' where `'.$v[1].'`="'.$row[$f].'"');
			if (!($r=$db->NextRecord())) if ($e=$this->_error('UpdateRow strange error')) return $e;
			$r=$r[0];
//			if ($r!=1) if ($e=$this->_error('UpdateRow: '.$table.'.'.$f.'="'.$row[$f].'" maps with '.$r.' rows on '.$v[0].'.'.$v[1])) return $e;
			if ($r!=1) if ($e=$this->_error('Ошибка при изменении записи - нет соответствующей записи в связаной таблице (UpdateRow: '.$table.'.'.$f.'="'.$row[$f].'" maps with '.$r.' rows on '.$v[0].'.'.$v[1].')<br>')) return $e;
			if (isset($v[2]) && $v[2]){
				$db->Query('select * from '.$v[0].' where `'.$v[1].'`="'.$row[$f].'"');
				$r=$db->NextRecord();
				eval($v[2]);
			}
		}

		$list='';
		foreach ($row as $k=>$v) {
			if ($list) $list.=',';
			$list.='`'.$k.'`="'.$v.'"';
		}
		$db->Query('update '.$table.' set '.$list.' where '.$this->GetWhere($table,$old));
	}
	
	function _GenQuery($table,$stradd){
		global $db;
		$j=''; $s='';
		if (isset($this->linksF[$table])) foreach ($this->linksF[$table] as $f=>$vals) foreach ($vals as $v) {
			$j.=' LEFT JOIN '.$v[0].' as '.$stradd.$f.'_ ON '.$stradd.$f.'_.'.$v[1].'='.$table.'.'.$f;
			$L=$db->ListFields($v[0]);
			foreach($L as $fld){
				$s.=','.$stradd.$f.'_.'.$fld.' as '.$stradd.$f.'_'.$fld;
			}
		}
		return array($s,$j);		
	}
	
	function NextRow(){
		global $db;
		return $db->NextRecord();
	}
	
	function SelectRows($table,$query){
		global $db;
		$db->Query('SELECT '.$table.'.* from '.$table.' '.$query);
	}
	
	function SelectRow($table,$query,$err=0){
		global $db;
		$db->Query('select * from '.$table.($query?' where '.$query:'').' limit 2');
		if (!($r=$db->NextRecord())) if ($err) {if ($e=$this->_error('SelectRow: no such record')) return $e;} else return;
		if ($r2=$db->NextRecord()) if ($err) {if ($e=$this->_error('SelectRow: too many records')) return $e;} else return;
		return $r;
	}
	
	function DeleteRows($table,$query, $delete_linked = 1) {
		global $db;
		$R=array();
		$db->Query('select * from '.$table.' where '.$query);
		while ($r=$db->NextRecord()) $R[]=$r;
		
		foreach ($R as $r) {
			$this->DeleteRow($table,$r,$delete_linked,0);		
		}
		$db->Query('delete from '.$table.' where '.$query);
	}
	
	function DeleteRow($table,$row, $delete_linked = 0, $delete_really = 1){
		global $db;
		if (isset($this->linksR[$table]))foreach ($this->linksR[$table] as $f=>$vals) foreach ($vals as $v) {
			$db->Query('select count(*) from '.$v[0].' where `'.$v[1].'`="'.$row[$f].'"');
			if (!($r=$db->NextRecord())) if ($e=$this->_error('AddRow strange error')) return $e;
			$r=$r[0];
			if (($r!=0) && (!$delete_linked)) if ($e=$this->_error('Ошибка при удалении записи - некоторые записи в других таблицах ссылаются на удаляемую. DeleteRow: '.$table.'.'.$f.'="'.$row[$f].'" is mapped with '.$r.' rows on '.$v[0].'.'.$v[1])) return $e;
			if ($r!=0) {
				if (isset($this->linksF[$v[0]])) {
					$this->DeleteRows($v[0],$v[1].'="'.$row[$f].'"',1);
				} else {
					$db->Query('delete from '.$v[0].' where `'.$v[1].'`="'.$v.'"');
				}
			}
		}
		if ($delete_really) $db->Query('delete from '.$table.' where '.$this->GetWhere($table,$row));
	}
	
	//типы полей для проверки типов
	function CheckType($value,$type,$params){
		switch ($type) {
		case 'ip':
			return preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$value);

		case 'ip_net':
			if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/(\d{1,2})$/',$value,$m)) return false;
			if ($m[1]>32) return false;
			return true;

		case 'ip_net_z':
			if ($value=="") return true;
			if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/(\d{1,2})$/',$value,$m)) return false;
			if ($m[1]>32) return false;
			return true;

		case 'integer':
			return $value=((int)$value);
		
		case 'no':
			return true;
		}
		return false;
	}
	
	function GetWhere($table, $row) {
		$w='';
		foreach ($this->keys[$table] as $p){
			if ($w) $w.=' AND ';
			$w.='(`'.$p.'`="'.$row[$p].'")';
		}
		return $w;
	}
	function _error($text){
		if ($this->error_mode_echo==1) echo $text;
			else if ($this->error_mode_echo==2) trigger_error2($text); //else do nothing;
		if ($this->error_mode_quit==0) return $text;
			else exit;
	}
	
	//отображение
	function ShowEditForm($table, $query, $row=array(),$apply_hide=0,$apply_readonly=1,$apply_readonly2=0) {
		global $db,$design;
		if ($query) $data=$this->SelectRow($table,$query,0); else $data=array();
		if (!is_array($data) || (count($data)==0)){
			$data=array();
			$list=$db->ListFields($table);
			foreach ($list as $k) $data[$k]=array('value'=>(isset($row[$k])?$row[$k]:''),'show'=>1,'translate'=>array($k,''));
		} else {
			foreach ($data as $k=>$v) if (is_numeric($k)) unset($data[$k]);
			foreach ($data as $k=>$v) $data[$k]=array('value'=>$v,'show'=>1,'translate'=>array($k,''));
		}
		if (isset($this->linksF[$table])) foreach ($this->linksF[$table] as $f=>$vals) if (!isset($this->hidden[$table]) || !in_array ($f,$this->hidden[$table])) foreach ($vals as $v) {
			$R=array();
			foreach ($this->keys[$v[0]] as $k){
				$kf=$k;
			}
			eval ($v[3]);
			$this->SelectRows($v[0],(isset($wh_add)?'where '.$wh_add:'').' order by '.$kf);
			while ($r=$db->NextRecord()) {
				$p=array('show'	=> array(), 'key'=> $r[$kf]);
				foreach ($this->visible[$v[0]] as $sf) $p['show'][]=$r[$sf];
				$p['show']=implode(' | ',$p['show']);
				$R[]=$p;
			}
			if (!count($R)) $R[]=array('key'=>'','show'=>'');
			
			$data[$f]['variants']=$R; //над variants нужно ещё поработать. ключевые поля есть. список полей для вывода - в одной таблице при инициализации.
			if (isset($this->dblecho[$table]) && in_array($f,$this->dblecho[$table])) {
				$data[$f]['show']|=2;
			} else $data[$f]['show']=2;
		}
		if (isset($this->variants[$table])) foreach($this->variants[$table] as $f=>$vals) {
			$R=array();
			foreach ($vals as $v) {
				if (is_array($v)){
					$R[]=array('show'=> $v[1], 'key'=> $v[0]);
				} else {
					$R[]=array('show'=> $v, 'key'=> $v);
				}
			}
			if (isset($data[$f]['variants']) && is_array($data[$f]['variants']) && count($data[$f]['variants'])){
				$data[$f]['variants']=array_merge($R,$data[$f]['variants']);
			} else $data[$f]['variants']=$R;
			$data[$f]['show']=2;
		}
		if ($apply_hide && isset($this->hidden[$table])) foreach ($this->hidden[$table] as $f){
			$data[$f]['show']=0;
		}
		if (isset($this->translate['*'])) foreach($this->translate['*'] as $f=>$vals) {
			if (isset($data[$f])) $data[$f]['translate']=$vals;
		}
		if (isset($this->translate[$table])) foreach($this->translate[$table] as $f=>$vals) {
			if (isset($data[$f])) $data[$f]['translate']=$vals;
		}
		if ($apply_readonly && isset($this->readonly[$table])) foreach($this->readonly[$table] as $f) {
			//$data[$f]['show']=4;
			$data[$f]['show']=0;
		}
		if ($apply_readonly2 && isset($this->readonly2[$table])) foreach($this->readonly2[$table] as $f) {
			//$data[$f]['show']=4;
			$data[$f]['show']=0;
		}
		
		if (isset($this->order[$table])){
			for($i=0;$i<count($this->order[$table]);$i++){
				$data[$this->order[$table][$i]]['order']=$i;
			}
			$i=count($this->order[$table]);
			foreach	($data as $k=>$v) if (!isset($data[$k]['order'])){
				$data[$k]['order']=$i;
				$i++;
			}
			uasort($data,create_function('$a,$b','if ($a["order"]==$b["order"]) return 0; if ($a["order"]<$b["order"]) return -1; return 1;'));
		}

		$design->assign('row',$data);
		$design->assign('query_table',$table);
		$design->assign('query_query',$query);
	}
	function ShowQuery($table, $query='') {
		global $db,$design;
		$this->SelectRows($table,$query);
		$R=array();
		while ($r=$db->NextRecord()){
			$w="";
			foreach ($this->keys[$table] as $p){
				$w.='&keys['.$p.']='.$r[$p];
			}
			$r['_query']=$w;
			$R[]=$r;
		}
		$T=array();
		if (count($R)>0){
			$r=$R[0];
			foreach ($r as $k=>$v) if (is_numeric($k)) unset($r[$k]);
			unset($r['_query']);
			foreach ($r as $ri=>$rv) {
				$p=array();
				if (in_array($ri,$this->keys[$table])) $p['key']=1; else $p['key']=0;
				if (isset($this->linksF[$table][$ri])){
					$v=$this->linksF[$table][$ri][0];	//вывод только первой связанной таблицы
					$p['link_table']=$v[0];
					$p['link_field']=$v[1];
					
				} else $p['link_table']='';
				$T[$ri]=$p;
			}
		}
		$design->assign('query_rows',$T);
		$design->assign('query_table',$table);
		$design->assign('query_query',$query);
		$design->assign('query_data',$R);
	}
	//разбор параметров, выполнение операций
	function ApplyChanges($def_table='') {
		global $db,$design;
		$action=get_param_protected('dbaction','');
		if (!$action) $action=get_param_protected('action','');
		if (!$action) return;
		$table=get_param_raw('table',$def_table);
		if (!$table) return;
		if ($action=='apply' || $action=='add'){
			$row=get_param_raw('row','');
			$old=get_param_raw('old','');

			if (!is_array($row) || !is_array($old)) return;
			
			$R=array();
			$new=0;
			foreach ($row as $k=>$v){
				$row[$k]=str_protect($row[$k]);
				if (isset($old[$k])) $old[$k]=str_protect($old[$k]);

				$t=substr($k,0,strlen($k)-1);
				if ((substr($k,strlen($k)-1,1)=='_') && isset($row[$t])){
					if ($row[$k]!='nouse'){
						$row[$t]=$row[$k];
					}
					unset($row[$k]);
				}
			}
			foreach ($this->keys[$table] as $k){
				if (!$old[$k]) $new=1;
			}
			if ($new) {
				if (!($e=$this->AddRow($table,$row))) trigger_error2('Удалено'); else return $e;
			} else {
				if (!($e=$this->UpdateRow($table,$row,$old))) trigger_error2('Изменено'); else return $e;
			}
		} else if ($action=='delete'){
			$linked=get_param_integer('linked','');
			$keys=get_param_raw('keys',array());
			$R=array();
			foreach ($keys as $k=>$v){
				if (str_protect($k)!=$k) return;
				if (!strchr($k,'.')) $k=$table.'.'.$k;
				$R[]='('.$k.'="'.$v.'")';	
			}

			$row=$this->SelectRow($table,implode(' AND ',$R),1);
			if ($row) {
				if ($e=$this->DeleteRow($table,$row,$linked,1)) return $e;
			} else if ($e=$this->_error('ApplyChanges error: no such record')) return $e;
		}
		
		if (isset($this->postactions[$table])) eval($this->postactions[$table]);
		return "ok";
	}
};

class Db_map_nispd extends Db_map {
	function Db_map_nispd(){
		//ключевые поля. для каждой используемой таблицы обязательно указывать
		$keys=array(
				'usage_ip_ports'		=> 'id',
				'routes'				=> 'id',
				'usage_tech_cpe'		=> 'id',
				'tech_routers'			=> 'router',
				'clients_vip'			=> 'id',
				'clients'				=> 'client',
				'tarifs_internet'		=> 'id',
				'Phones'				=> 'id',
				'phone_tarif'			=> 'id',
				'usage_voip'			=> 'id',
				'domains'				=> 'id',
				'emails'				=> 'id',
				'tt_states'				=> 'id',
				'usage_ip_ppp'			=> 'id',
			);
		
		//связи между таблицами. например, array('routes.client','clients.client') - связь по клиенту
		//3ий параметр - операции, выполняемые при переходе от 1го (связывающего) поля ко 2му (в связанной таблице). автозаполнение полей делается именно здесь
		//4ый - фильтр для вариантов связаной таблицы - часть запроса, которую нужно положить в $wh_add
		$links=array(
				array('routes.port_id',					'usage_ip_ports.id',	'$this->rset("node",$r["node"]); if (!$row["address"]) $this->rset("address",$r["address"]);'),
				array('routes.node',					'tech_routers.router'),					//это поле мы будем заполнять автоматически
				array('usage_ip_ports.node',			'tech_routers.router'),
				array('routes.client',					'clients.client'),
				array('usage_ip_ports.client',			'clients.client',		'if (!$row["address"]) $this->rset("address",$r["address_post_real"]);'),
				array('usage_tech_cpe.client',			'clients.client'),

				array('usage_ip_ports.tarif_id',		'tarifs_internet.id',	'
							if (access("services_internet","tarif")){
								if (isset($old["tarif_id"]) && ($row["tarif_id"]!=$old["tarif_id"])) {
									$db->Query("select NOW()");
									$_tmp_=$db->NextRecord();
									if ($row["tarif_change"]==$old["tarif_change"]) $row["tarif_change"]=$_tmp_[0];
									$row["tarif_old_id"]=$old["tarif_id"];
									$ignore["tarif_old_id"]=true;
								}
							} else {
								if (isset($old["tarif_id"]) && ($row["tarif_id"]!=$old["tarif_id"])) {
									echo "Нет доступа к изменению тарифа<BR>";
								}
								$row["tarif_id"]=$old["tarif_id"];
								$row["tarif_old_id"]=$old["tarif_old_id"];
								$row["tarif_change"]=$old["tarif_change"];
								$ignore["tarif_id"]=true;
								$ignore["tarif_old_id"]=true;
								$ignore["tarif_change"]=true;
							}
										'),
				array('usage_ip_ports.tarif_old_id',	'tarifs_internet.id'),
				
				array('usage_voip.tech_voip_device_id',	'usage_tech_cpe.id',		'',	'$wh_add="(client=\"".$data["client"]["value"]."\")";'),

				array('usage_ip_ppp.port_id',			'usage_ip_ports.id'),
			);
		//типы полей и проверка правильности значений
		$types=array(
				'routes.net'						=> array('ip_net',		'return; if (CheckNet($value)) return; else return "Invalid network ".$value;'),
				'routes.type'						=> array('no',			'if (in_array($value,array("aggregate","reserved","uplink"))) {if ($row["client"]) return "Client must be empty"; else $ignore["client"]=true;}'),
				'routes.port'						=> array('no',			'if ($value=="mgts"){$ignore["node"]=true;if (preg_match("/^\d+$/",$row["node"])) return; else return "В node должен быть телефонный номер";}'),
				'routes.tarif_type'					=> array('no',			'if ($value=="c" && $row["type"]!="separate") return "Type must be \"separate\"";'),

				'usage_voip.tech_voip_device_id'	=> array('no',			'if (!$value) $ignore["tech_voip_device_id"]=true;'),

				'usage_ip_ports.type'				=> array('no',			'if (in_array($value,array("aggregate","reserved","uplink"))) {if ($row["client"]) return "Client must be empty"; else $ignore["client"]=true;}'),
				'usage_ip_ports.port'				=> array('no',			'if ($value=="mgts"){$ignore["node"]=true;if (preg_match("/^\d+$/",$row["node"])) return; else return "Node must be phone number";}'),
				'usage_ip_ports.tarif_type'			=> array('no',			'if ($value=="c" && $row["type"]!="separate") return "Type must be \"separate\"";'),
				'usage_ip_ports.tarif_id'			=> array('no',			'if (!$value) {$ignore["tarif_id"]=true; $row["tarif_id"]=0;}'),
				'usage_ip_ports.tarif_old_id'		=> array('no',			'if (!$value) $ignore["tarif_old_id"]=true;'),
				'usage_ip_ports.port_type'			=> array('no',			'if (!$old["port_type"]) $ignore["port_type"]=true;'),

					
				'usage_tech_cpe.ip'					=> array('ip'),
				'tech_routers.net'					=> array('ip_net_z'),
					
				'usage_ip_ppp.ip'					=> array('ip'),
				'usage_ip_ppp.port_id'				=> array('no',			'if (!$value) $ignore["port_id"]=true;'), 
			);
		//какие поля показывать в раскрывающихся списках
		$visible_in_list=array(
				'usage_ip_ports'		=> array('client','address','type','node'),
				'routes'				=> array('node','net','client'),
				'clients'				=> array('client','company','address_jur'),
				'usage_tech_cpe'		=> array('client','ip','manufacturer','model'),
				'tech_routers'			=> array('router','net'),
				'tarifs_internet'		=> array('mb_month','pay_month','pay_mb','name'),
				'domains'				=> array('domain'),
				);
		
		//"двойной" вывод - текстовое поле и список
		$dblecho=array(
				'routes'				=> array('node'),
				'usage_ip_ports'		=> array('node'),
				);
		//варианты значений, поле будет выведено в виде списка.
		$variants=array(
				'routes.type'						=> array('client', 'unused', 'uplink', 'uplink+pool',  'client-nat', 'pool', 'aggregate'),
				'routes.port_type'					=> array('dedicated', 'unused',  'pppoe', 'pptp', 'hub', 'mgts'),
				'routes.trafcounttype'				=> array('aggregate', 'separate'),
				'routes.tarif_type'					=> array('K', 'I', 'C'),
	
				'usage_ip_ports.type'				=> array('client', 'unused', 'uplink', 'uplink+pool',  'client-nat', 'pool', 'aggregate'),
				'usage_ip_ports.port_type'			=> array('dedicated', 'unused', 'pppoe', 'pptp', 'hub', 'mgts'),
				'usage_ip_ports.trafcounttype'		=> array('aggregate', 'separate'),
				'usage_ip_ports.tarif_type'			=> array('K', 'I', 'C',),
				
				'domains.registrator'				=> array('-', 'RIPN-REG-RIPN', 'RUCENTER-REG-RIPN'),
				'domains.dns'						=> array('-', 'mcn'),
				
				'usage_voip.DialPlan'				=> array('E164','city'),
				'usage_voip.tech_voip_device_id'	=> array(array('0','')),
				
				'emails.spam_act'					=> array('pass','mask','discard'),
					
				'usage_ip_ports.period'				=> array(array('immediately','мгновенно'),array('day','раз в день'),array('week','раз в неделю'),array('month','раз в месяц'),array('6months','раз в полгода'),array('year','раз в год')),
				'usage_voip.period'					=> array(array('immediately','мгновенно'),array('day','раз в день'),array('week','раз в неделю'),array('month','раз в месяц'),array('6months','раз в полгода'),array('year','раз в год')),
				'domains.period'					=> array(array('immediately','мгновенно'),array('day','раз в день'),array('week','раз в неделю'),array('month','раз в месяц'),array('6months','раз в полгода'),array('year','раз в год')),
				'emails.period'						=> array(array('immediately','мгновенно'),array('day','раз в день'),array('week','раз в неделю'),array('month','раз в месяц'),array('6months','раз в полгода'),array('year','раз в год')),
				'usage_phone_callback.period'		=> array(array('immediately','мгновенно'),array('day','раз в день'),array('week','раз в неделю'),array('month','раз в месяц'),array('6months','раз в полгода'),array('year','раз в год')),

				'usage_ip_ports.status'				=> array(array('working','в работе'),array('connecting','на стадии подключения, до выписки счёта')),
				'usage_voip.status'					=> array(array('working','в работе'),array('connecting','на стадии подключения, до выписки счёта')),
				'domains.status'					=> array(array('working','в работе'),array('connecting','на стадии подключения, до выписки счёта')),
				'emails.status'						=> array(array('working','в работе'),array('connecting','на стадии подключения, до выписки счёта')),
				'usage_phone_callback.status'		=> array(array('working','в работе'),array('connecting','на стадии подключения, до выписки счёта')),

				);
		//скрытые поля
		$hidden=array(
				'usage_ip_ports'		=> array ('client','id','tarif'),
				'routes'				=> array ('id','client','port_id','tarif','tarif_lastmonth','tarif_type','test_lines','test_req_no','adsl_modem_serial'),
				'usage_voip'			=> array ('client','id'),
				'domains'				=> array ('client','id'),
				'emails'				=> array ('client','id','domain'),
				'tech_routers'			=> array ('router'),
				'clients_vip'			=> array ('id','last_time5'),
				'usage_tech_cpe'		=> array ('id'),
				'tech_routers'			=> array ('id'),
				'usage_ip_ppp'			=> array ('client','port_id','user_editable','enabled','mtu','send_nispd_vsa','enabled_local_ports','enabled_remote_ports','limit_cps_in','limit_cps_out','day_quota_in','day_quota_in_used','day_quota_out','day_quota_out_used','month_quota_in','month_quota_in_used','month_quota_out','month_quota_out_used'),
				);
		//перевод
		$translate=array(
				'*.actual_from'			=> 'активна с',
				'*.actual_to'			=> 'активна до',
				'*.net'					=> 'IP-адрес сети',
				'*.client'				=> 'клиент',
				'*.address'				=> 'адрес',
				'*.node'				=> 'роутер или телефон клиента ',
				'*.port'				=> 'порт если индивидуал то ставится mgts',
				'*.type'				=> 'тип обычно надо ставить client',
				'*.port_type'			=> array('тип порта','обычно dedicated'),
				'*.tarif'				=> 'тариф',
				'*.trafcounttype'		=> 'тип учёта траффика всегда agrigate',
				'*.description'			=> 'описание',
				'*.price'				=> 'стоимость',
				'*.amount'				=> 'количество',
				'*.period'				=> 'период',
				'*.domain'				=> 'домен',
				'*.password'			=> 'пароль',
				'*.last_modified'		=> 'дата последней модификации',
				'*.router'				=> 'роутер',
				'*.phone'				=> 'телефон',
				'*.location'			=> 'местоположение',
				'*.reboot_contact'		=> 'данные ответственного за перезагрузку',
				'*.adsl_modem_serial'	=> 'серийный номер модема<a name="serial" style="display:none"></a>',
				'*.manufacturer'		=> 'производитель',
				'*.model'				=> 'модель',
				'*.serial'				=> 'серийный номер',
				'*.location'			=> 'местоположение',
				'*.E164'				=> 'номер телефона',
				'*.ClientIPAddress'		=> 'IP-адрес',
				'*.enabled'				=> 'включено',
				'*.status'				=> 'состояние',
				'usage_tech_cpe.numbers'	=> 'телефонные номера',
				'usage_tech_cpe.logins'	=> 'логины',
				'usage_tech_cpe.owner'	=> 'владелец',
					
				'emails.local_part'		=> 'локальная часть e-mail-адреса',
				'emails.box_size'		=> 'занято',
				'emails.box_quota'		=> 'размер ящика',
			
				'*.nat_net'							=> 'внутренняя сеть через NAT',
				'*.dnat'							=> 'dnat поле редактируется только администратором',
				'*.up_node'							=> 'up_node поле редактируется только администратором',
				'*.flows_node'						=> 'flows_node поле редактируется только администратором',
				'*.secondary_to_net'				=> 'вторична к сети',
				'*.tarif_type'						=> array('тип тарифа',' K-коллективный,I - индивидуал С - CoLocation'),
				'routes.port_id'					=> 'подключение',
				'routes.comment'					=> 'комментарий',
				'routes.tarif_lastmonth'			=> 'прошлый тариф',

				'usage_ip_ports.tarif_id'			=> 'тариф<a name="tarif" style="display:none"></a>',
				'usage_ip_ports.tarif_old_id'		=> array('старый тариф','Устанавливается автоматически'),
				'usage_ip_ports.tarif_change'		=> 'дата изменения тарифа',
				'usage_ip_ports.tarif_type'			=> array('тип тарифа',"K-коллективный <br>,I - индивидуал С - CoLocation"),
				'usage_ip_ports.adsl_modem_serial'	=> 'серийный номер модема<a name="serial" style="display:none"></a>',
				'usage_ip_ports.test_lines'			=> 'test_lines Тестируемые линии',
				'usage_ip_ports.test_req_no'		=> 'test_req_no Заявка в МГТС',
				
				'usage_voip.no_of_lines'			=> 'число линий',
				'usage_voip.tech_voip_device_id'	=> array('устройство','<a href="index.php?module=routers&action=d_add" target=_blank>Добавить устройство</a> (после добавления придётся вручную обновить страницу)'),
				'usage_voip.tarif'					=> array('тариф','Взять из окошка: <select onchange="javscript:form.row_tarif.value=this.value;"><option value="V095-29-29-A">V095-29-29-A</option><option value="V-0-0-0">V-0-0-0</option></select>'),
					
				'clients_vip.num_unsucc'			=> array('Текущее число неудачных попыток','вряд ли в нормальной ситуации потребуется изменять это поле'),
				'clients_vip.email'					=> 'Адрес e-mail',
				'clients_vip.phone'					=> 'Номер телефона',
				'clients_vip.important_period'		=> 'В какое время отслеживать',
				'clients_vip.router'				=> array('Роутер','вводите <b>или</b> клиента, или роутер'),
				);
		//действия, которые выполняются после добавления или изменения записи
		$postactions=array(
				'usage_ip_ports'	=>	'
						$db->Query("update routes set tarif_type=\"{$row["tarif_type"]}\",adsl_modem_serial=\"{$row["adsl_modem_serial"]}\",test_lines=\"{$row["test_lines"]}\",test_req_no=\"{$row["test_req_no"]}\" where port_id={$row["id"]}");
						$db->Query("select concat(mb_month,\"-\",pay_month,\"-\",pay_mb) as ot from tarifs_internet where (id=\"{$row["tarif_id"]}\")");
						if ($r=$db->NextRecord()){
							$db->Query("update routes set tarif=\"{$r["ot"]}\" where port_id={$row["id"]}");
						}
						$db->Query("select concat(mb_month,\"-\",pay_month,\"-\",pay_mb) as ot from tarifs_internet where (id=\"{$row["tarif_old_id"]}\")");
						if ($r=$db->NextRecord()){
							$db->Query("update routes set tarif_lastmonth=\"{$r["ot"]}\" where port_id={$row["id"]}");
							$db->Query("insert into usage_ip_ports_history (port_id,tarif_id,date) values ({$row["id"]},{$row["tarif_old_id"]},NOW())");
						}
					',
					'routes'			=> '
						if (($row["actual_from"]!=$old["actual_from"]) || ($row["actual_to"]!=$old["actual_to"])){
							$db->Query("select count(*) from routes where (actual_from<=NOW()) and (actual_to>=NOW()) and (client=\"{$row["client"]}\")");
							$r=$db->NextRecord();
							if (isset($r[0]) && (!$r[0])){
								echo "Клиент отключен<br>";
								$db->Query("update clients set status=\"closed\" where client=\"{$row["client"]}\"");
								$db->Query("select count(*) from usage_voip where (actual_from<=NOW()) and (actual_to>=NOW()) and (client=\"{$row["client"]}\")");
								$r=$db->NextRecord();
								if (isset($r[0]) && ($r[0])) echo "<font color=red>Внимание!</font> У клиента осталась IP-телефония.<br>";
							} else {
								$db->Query("select status from clients where client=\"{$row["client"]}\"");
								$r=$db->NextRecord();
								if ($r[0]!="work") {
									echo "Клиент включен<br>";
									$db->Query("update clients set status=\"work\" where client=\"{$row["client"]}\"");
								}
							}
						}
					',
				);
		//readonly-поля. поля, недоступные менеджерам, прописываются здесь.
		$readonly=array(
				'usage_ip_ports'	=> array('type','port_type','trafcounttype','trafcounttype','tarif'),
				'routes'			=> array('nat_net','dnat','up_node','flows_node','trafcounttype','secondary_to_net','port_type'),
				'usage_voip'		=> array('switch_type','switch_ip','forward_condition','forward_address_type','forward_address','DialPlan'),
				);
		//порядок вывода полей. те поля, которые не указаны в списке, выведутся в конце
		$order=array(
				'usage_ip_ports'	=> array('address','tarif_id','tarif_old_id','tarif_change','tarif_type','node','port','test_lines','test_req_no','adsl_modem_serial'),
				'routes'			=> array('actual_from','actual_to','net','address','comment','node','port','type'),
				'clients_vip'		=> array('client','router','email','phone','important_period'),
				'usage_voip'		=> array('client','actual_from','actual_to','tech_voip_device_id','E164','no_of_lines','tarif'),
				);
		$this->Db_map($keys,$links,$types,$visible_in_list,$dblecho,$variants,$hidden,$translate,$postactions,$readonly,$order);
		$this->SetErrorMode(1,0);
	}
}
?>
