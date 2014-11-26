<?
class m_logs_head extends IModuleHead{
	public $module_name = 'logs';
	public $module_title = 'Логи';
	var $actions=array(
		'default'			=> array('logs','read'),
		'alerts'			=> array('logs','read'),
	);
	var $menu=array(
		array('Оповещения',		'alerts'),
	);
}
?>
