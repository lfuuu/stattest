<?php
class m_data_head extends IModuleHead{
	public $module_name = 'data';
	public $module_title = 'Данные справочников';

	public $actions=array(
		'get_gtd' => 'access',
		'search_goods' => 'access',
	);

	public $menu=array();
}
?>