<?php
class m_voip_head extends IModuleHead{
	public $module_name = 'voip';
	public $module_title = 'IP Телефония';

	public $rights=array(
		'voip'=>array('Список операторов','access','доступ')
	);

	public $actions=array(
		'operators_list'=>array('voip','access'),
		'defcodes'=>array('voip','access'),
		'upload'=>array('voip','access'),
		'stats'=>array('voip','access'),
		'tgroups_tariffication'=>array('voip','access'),
		'export_csv'=>array('voip','access'),
		'tgroups'=>array('voip','access'),
		'tdiffs'=>array('voip','access')
	);

	public $menu=array(
		array('Операторы','operators_list'),
		array('DEF-коды и цены','defcodes'),
		array('Статистика','stats'),
		array('Экспорт в exel','export_csv'),
		array('Тарифные группы','tgroups'),
		array('Отчет по тарификации','tgroups_tariffication'),
		array('Сравнение тарифов','tdiffs')
	);
}
?>