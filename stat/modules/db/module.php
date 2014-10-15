<?php
class m_db{
	var $rights=array(
					'db'			=>array('Работа с базой','all','Полный доступ')
				);
	var $actions=array(
					'default'		=> array('db','all'),
					'edit'			=> array('db','all'),
					'apply'			=> array('db','all'),
					'delete'		=> array('db','all'),
				);
	var $tables=array('routes','usage_ip_ports','tech_routers','tech_devices','clients');
				   
	function m_db(){
    }


	function Install($p){
		return $this->rights;
	}
	
	function GetPanel(){
		$R=array(); $p=0;
		if (!access('db','all')) return;
		foreach ($this->tables as $t){
			$R[]=array($t,'module=db&table='.$t,'','','');
		}
        return array('База данных',$R);
	}

	function GetMain($action,$fixclient){
		global $design,$db,$user,$dbmap;
		if (!isset($this->actions[$action])) return;
        require_once INCLUDE_PATH.'db_map.php';
		$dbmap=new Db_map_nispd();
		$dbmap->SetErrorMode(2,0);
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'db_'.$action),$fixclient);
	}
		
	function db_default(){	
		global $design,$user,$dbmap;
		$table=get_param_raw('table','routes');
		if (!in_array($table,$this->tables)) return;
		$keys=get_param_raw('keys',array());
		$R=array();
		foreach ($keys as $k=>$v){
			if (str_protect($k)!=$k) return;
			if (!strchr($k,'.')) $k=$table.'.'.$k;
			$R[]='('.$k.'="'.$v.'")';	
		}
		if (count($R)>0) $w='where '.implode(' AND ',$R); else $w='';
		$dbmap->ShowQuery($table,$w.' limit 100');
		$design->AddMain('db/query.tpl');
	}
	function db_apply(){
		global $design,$user,$dbmap;
		$table=get_param_raw('table','');
		if (!in_array($table,$this->tables)) return;
		$row=get_param_raw('row','');
		$old=get_param_raw('old','');
		if (!is_array($row) || !is_array($old)) return;
		if (count($row)!=count($old)) return;
		
		$R=array();
		$new=0;
		foreach ($row as $k=>$v){
			$row[$k]=str_protect($row[$k]);
			$old[$k]=str_protect($old[$k]);
		}
		foreach ($dbmap->keys[$table] as $k){
			if (!$old[$k]) $new=1;
		}
		if ($new) {
			if (!$dbmap->AddRow($table,$row)) trigger_error('added');
		} else {
			if (!$dbmap->UpdateRow($table,$row,$old)) trigger_error('altered');
		}
		$dbmap->ShowEditForm($table,$dbmap->GetWhere($table,$row),$row);
		$design->AddMain('db/edit.tpl');
	}
	function db_delete(){
		global $design,$user,$dbmap;
		if (!in_array($table,$this->tables)) return;
		$keys=get_param_raw('keys',array());
		$linked=get_param_integer('linked',0);
		$R=array();
		foreach ($keys as $k=>$v){
			if (str_protect($k)!=$k) return;
			if (!strchr($k,'.')) $k=$table.'.'.$k;
			$R[]='('.$k.'="'.$v.'")';	
		}
		$row=$dbmap->SelectRow($table,implode(' AND ',$R),1);
		if ($row) $dbmap->DeleteRow($table,$row,$linked,1);
	}	
	function db_edit(){	
		global $design,$user,$dbmap;
		$table=get_param_raw('table','routes');
		if (!in_array($table,$this->tables)) return;
		$keys=get_param_raw('keys',array());
		$R=array();
		foreach ($keys as $k=>$v){
			if (str_protect($k)!=$k) return;
			if (!strchr($k,'.')) $k=$table.'.'.$k;
			$R[]='('.$k.'="'.$v.'")';	
		}
		$dbmap->ShowEditForm($table,implode(' AND ',$R));
		$design->AddMain('db/edit.tpl');
	}
	
	

};


?>