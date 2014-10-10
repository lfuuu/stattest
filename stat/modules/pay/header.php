<?
class m_pay_head extends IModuleHead{
	public $module_name = 'pay';
	public $module_title = 'Платежи';
	public $rights = array('info,yandex,demoyandex,webmoney','Информация,Яндекс-деньги,Яндекс-демоденьги,Webmoney');
	public $actions=array(
					'default'			=> 'info',
					'yandex'			=> 'yandex',
					'demoyandex'		=> 'demoyandex',
					'webmoney'			=> 'webmoney',
				);
	public $menu=array(
					array('Яндекс.деньги',			'yandex'),
					array('Яндекс.деньги - демо',	'demoyandex'),
					array('Webmoney',				'webmoney'),
				);
}
?>