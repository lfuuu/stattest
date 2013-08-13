<?php
class m_incomegoods_head extends IModuleHead{
	public $module_name = 'incomegoods';
	public $module_title = 'Закупки';

	public $rights=array(
		'incomegoods'=>array('Закупки','access,admin','доступ,администрирование')
	);

	public $actions=array(
		'order_list' => array('voip','access'),
		'order_view' => array('voip','access'),
		'order_edit' => array('voip','admin'),
		'order_save' => array('voip','admin'),
		'document_view' => array('voip','access'),
		'document_edit' => array('voip','admin'),
		'document_save' => array('voip','admin'),
		'store_view' => array('voip','access'),
		'add_gtd' => array('voip','admin'),
	);

	public $menu=array(
		array('Заказы поставщику','order_list'),
	);
}
?>