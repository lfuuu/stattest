<?
abstract class IModuleHead {
	public $module_name = '';
	public $module_title = '';
	public $rights = array();
	public $actions = array();
	public $menu = array();
	private $obj = null;
	private function load() {
		$c = 'm_'.$this->module_name;
		include_once (MODULES_PATH.$this->module_name."/module.php");
		$this->obj = new $c();
		$this->obj->module_name = & $this->module_name;
		$this->obj->module_title = & $this->module_title;
		$this->obj->rights = & $this->rights;
		$this->obj->actions = & $this->actions;
		$this->obj->menu = & $this->menu;
	}
	public function __construct() {
		if (count($this->rights)==2 && isset($this->rights[0]) && isset($this->rights[1]) && is_string($this->rights[0]) && is_string($this->rights[1])) {
			$this->rights = array($this->module_name => array($this->module_title,$this->rights[0],$this->rights[1]));
		}
		if (count($this->actions)) {
			foreach ($this->actions as $k=>&$v) {
				if (!is_array($v)) $v = array($this->module_name,$v);
			} unset($v);
		}
	}
	public function Install(){ return $this->rights; }
	public function GetPanel(){
		global $design,$user;
		$R=array(); $p=0;
		foreach($this->menu as $val){
			if ($val=='') {
				$p++;
				$R[]='';
			} else {
				$act=$this->actions[$val[1]];
				if (access($act[0],$act[1])) $R[]=array($val[0],'module='.$this->module_name.'&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
			}
		}
		if (count($R)>$p){
			$design->AddMenu($this->module_title,$R);
		}
	}
	public final function __get($k) {
		if (!$this->obj) $this->load();
		return $this->obj->$k;
	}
	public final function __set($k,$v) {
		if (!$this->obj) $this->load();
		$this->obj->$k = $v;
	}
	public final function __call($k,$param) {
		if (!$this->obj) $this->load();
		try {
			return call_user_func_array(array($this->obj,$k),$param);
		} catch (Sync1CException $e) {
			echo htmlspecialchars_($e->getMessage());
			exit;
		} catch (Exception $e) {
			echo "<h1>" . htmlspecialchars_($e->getMessage()) . "</h1>\n";
			echo "<pre>\n";
			echo htmlspecialchars_($e->getTraceAsString());
			echo "</pre>\n";
			exit;
		}

	}
}
abstract class IModule {
	public $callStyle = 'old';
	public $module_name = null;
	public $module_title = null;
	public $rights = null;
	public $actions = null;
	public $menu = null;
	public function GetMain($action,$fixclient = null) {
		if (!$action) $action='default';
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if ($act!=='' && !access($act[0],$act[1])) return;
		if ($this->callStyle=='old') {
			call_user_func(array($this,$this->module_name.'_'.$action),$fixclient);
		} else call_user_func(array($this,'action_'.$action),$fixclient);
	}
}

class Modules {
	var $modules;
	public static function IncludeFile($m) {
		$f = MODULES_PATH.$m."/header.php";
		if (file_exists($f) && include_once($f)) return 'm_'.$m.'_head';
		$f = MODULES_PATH.$m."/module.php";
		if (file_exists($f) && include_once($f)) return 'm_'.$m;
		return null;
	}
	function Modules() {
		global $design,$db;
		$this->modules=array();
		$db->Query('select module from modules where (is_installed=1) order by load_order');
		while ($r=$db->NextRecord()){
			$m = trim($r['module']);
			$classname = self::IncludeFile($m);
			if ($classname) {
				$this->modules[$m]=$m;
				$GLOBALS['module_'.$m] = new $classname();
			} else {
				trigger_error("Невозможно подключить модуль ".$m);
			}
		}
	}	
	
	function GetPanels($fixclient) {
		global $design;
		foreach ($this->modules as $m){
			eval('global $module_'.$m.';');
			$design->assign('panel_module',$m);
			eval('$module_'.$m.'->GetPanel($fixclient);');
		}
	}	
	function GetMain($m,$action,$fixclient) {
		global $design;
		if (!$m) return;
		$v='module_'.$m;
		global $$v;
		if (isset($$v)) {
			$$v->GetMain($action,$fixclient);
		} else trigger_error("Модуль не существует");
	}
}
	
	
?>
