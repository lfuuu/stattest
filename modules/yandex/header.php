<?
class m_yandex_head extends IModuleHead{
	public $module_name = 'yandex';
	public $module_title = 'Платежи Яндекс';

	public $rights=array(
					'yandex'			=>array("Яндекс",'history,payment','история платежей,проведение платежей')
				);
	public $actions=array(
					'authorize'	=> array('yandex','payment'),
					'authorize_callbackstat'	=> array('yandex','payment'),
					'authorize_callbackcompapa'	=> array('yandex','payment'),
					'history'			=> array('yandex','history'),
					'pay_stat'			=> array('yandex','payment'),
					'pay_compapa'		=> array('yandex','payment'),
				);
	public $menu=array(
					array('История платежей',	'history'),
				);
}
?>
