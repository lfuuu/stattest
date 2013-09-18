<?php
class m_incomegoods_head extends IModuleHead{
	public $module_name = 'incomegoods';
	public $module_title = 'Закупки';

	public $rights=array(
		'incomegoods'=>array('Закупки','access,admin','доступ,администрирование')
	);

	public $actions=array(
        'default'=> array('incomegoods','access'),
		'order_list' => array('incomegoods','access'),
		'order_view' => array('incomegoods','access'),
		'order_edit' => array('incomegoods','admin'),
		'order_save' => array('incomegoods','admin'),
		'document_view' => array('incomegoods','access'),
		'document_edit' => array('incomegoods','admin'),
		'document_save' => array('incomegoods','admin'),
		'store_view' => array('incomegoods','access'),
		'add_gtd' => array('incomegoods','admin'),
	);

	public $menu=array(
		array('Заказы поставщику','order_list'),
	);
}
?>
