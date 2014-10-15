<?
//установка/удаление других модулей
class m_modules {

	function m_modules(){
	}
	function Install($p){
		global $db;
		return array('modules'=>array('Работа с модулями','r,w','чтение,изменение'));
	}

	function GetPanel($fixclient){
		global $design,$user,$db;
		if (!access('modules','r')) return;
        return array('Модули',array(
					array('Список модулей','module=modules'),
				));
	}
	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!access('modules','r')) return;

		//получаем список установленных модулей
		$modules=array();
		$db->Query('select * from modules order by load_order');
		while ($r=$db->NextRecord()) $modules[$r['module']]=$r;

		//получаем список не установленных модулей
		$R = $db->GetRow('select max(load_order) as M from modules where is_installed IN (0,1)');
		$d=dir(MODULES_PATH);
		while ($r=$d->read()){
			if ($r!='.' && $r!='..'){
				if (!isset($modules[$r])){
					$modules[$r]=array('module'=>$r,'is_installed'=>0);
					$R['M']++;
					$db->Query('insert into modules (module,is_installed,load_order) values ("'.$r.'",0,'.$R['M'].')');
				}
			}
		}
		$d->close();

		//защита от ../ и других возможных фокусов
		$id = get_param_raw('id' , '');
		$id=str_replace('"','',$id);
		$id=str_replace('.','',$id);
		$id=str_replace('/','',$id);
		$id=str_replace("\\",'',$id);
		$id=str_replace("\n",'',$id);
		$id=str_replace("\r",'',$id);
		$id=str_replace("\0",'',$id);
		
		if ($id && preg_match('/^([\d\w_\-]+)$/',$id)){
			if (!isset($modules[$id])) {trigger_error('Такого модуля не существует'); return; }
			if (!access('modules','w')) return;
			
			$inst=2; 	//не делать действий по установке
			
			if ($action=='install'){
				if ($modules[$id]['is_installed']==1){
					trigger_error('Модуль уже установлен');
					return;	
				}
				$inst=1;
				$instW1='Модуль установлен';
				$instW2='Модуль не установился';
			} else if ($action=='uninstall'){
				if (!isset($modules[$id]) || ($modules[$id]['is_installed']==0)){
					trigger_error('Модуль уже удалён');
					return;	
				}
				$inst=0;
				$instW1='Модуль удалён';
				$instW2='Модуль не удалился';
			} else if ($action=='up'){
				if (!isset($modules[$id])){
					trigger_error('Модуль не существует');
					return;	
				}
				$db->Query('select module,load_order from modules where load_order<'.$modules[$id]['load_order'].' order by load_order desc');
				$r=$db->NextRecord();
				$db->Query('update modules set load_order='.$r[1].' where module="'.$id.'"');
				$db->Query('update modules set load_order='.$modules[$id]['load_order'].' where module="'.$r[0].'"');

				$modules=array();
				$db->Query('select * from modules order by load_order');
				while ($r=$db->NextRecord()) $modules[$r['module']]=$r;
			} else if ($action=='down'){
				if (!isset($modules[$id])){
					trigger_error('Модуль не существует');
					return;	
				}
				$db->Query('select module,load_order from modules where load_order>'.$modules[$id]['load_order'].' order by load_order asc');
				$r=$db->NextRecord();
//				$db->Lock('modules');
				$db->Query('update modules set load_order='.$r[1].' where module="'.$id.'"');
				$db->Query('update modules set load_order='.$modules[$id]['load_order'].' where module="'.$r[0].'"');
//s				$db->Unlock();

				$modules=array();
				$db->Query('select * from modules order by load_order');
				while ($r=$db->NextRecord()) $modules[$r['module']]=$r;
			}
			
			if ($inst!=2){		//просто, чтобы не копировать код кусками. на самом деле - объединение install & uninstall
				$modules[$id]['is_installed']=$inst;
				$classname = Modules::IncludeFile($id);
				if ($classname) {
					eval('global $module_'.$id.';');
					eval('if (!isset($module_'.$id.')) $module_'.$id.' = new '.$classname.'();');
					eval('$v=$module_'.$id.'->Install('.$inst.');');
				}
				if (isset($v) && is_array($v)) {
					$db->Query('update modules set is_installed='.$inst.' where module="'.$id.'"');
					if ($inst==1) {
						foreach ($v as $k=>$a){
							$db->Query('insert into user_rights (resource,comment,`values`,values_desc) values ("'.$k.'","'.addslashes($a[0]).'","'.addslashes($a[1]).'","'.($a[2]?addslashes($a[2]):'').'")');
						}
					} else {
						foreach ($v as $k=>$a){
							$db->Query('delete from user_rights where resource="'.$k.'"');

/*							if (DEBUG_LEVEL==0) {
								$db->Query('delete from user_grant_users where resource="'.$k.'"');
								$db->Query('delete from user_grant_groups where resource="'.$k.'"');
							}*/
						}
					}
					trigger_error($instW1);
				} else trigger_error($instW2);
			}
		}
		
		$design->assign('modules',$modules);
		$design->AddMain('modules/main.tpl');
	}
}
	
	

	
?>
