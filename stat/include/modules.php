<?php
use yii\helpers\Url;

abstract class IModuleHead {
	public $module_name = '';
	public $module_title = '';
	public $actions = array();
	public $menu = array();
	private $obj = null;
	private function load() {
		$c = 'm_'.$this->module_name;
		include_once (MODULES_PATH.$this->module_name."/module.php");
		$this->obj = new $c();
		$this->obj->module_name = & $this->module_name;
		$this->obj->module_title = & $this->module_title;
		$this->obj->actions = & $this->actions;
		$this->obj->menu = & $this->menu;
	}
	public function __construct() {
		if (count($this->actions)) {
			foreach ($this->actions as $k=>&$v) {
				if (!is_array($v)) $v = array($this->module_name,$v);
			} unset($v);
		}
	}
	public function GetPanel($fixclient){
		$R=array(); $p=0;
		foreach($this->menu as $val){
			if ($val=='') {
                $p++;
                $R[] = '';
            }elseif(is_callable($val[1])) {
                $getRoute = $val[1];
                if ($route = $getRoute()) {
                    $R[] = [$val[0], Url::to($route), '', ''];
                }
			} else {
				$act=$this->actions[$val[1]];
				if (access($act[0],$act[1])) $R[]=array($val[0],'module='.$this->module_name.'&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
			}
		}
		if (count($R)>$p){
            return array($this->module_title,$R);
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
        return call_user_func_array(array($this->obj,$k),$param);
	}
}
abstract class IModule {
	public $callStyle = 'old';
	public $module_name = null;
	public $module_title = null;
	public $actions = null;
	public $menu = null;
	public function GetMain($action,$fixclient) {
		if (!$action) $action='default';
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if ($act!=='' && !access($act[0],$act[1])) return;

		if ($this->callStyle=='old') {
			call_user_func(array($this,$this->module_name.'_'.$action),$fixclient);
		} else call_user_func(array($this,'action_'.$action),$fixclient);
	}
}

