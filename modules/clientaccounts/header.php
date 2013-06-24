<?
class m_clientaccounts_head extends IModuleHead{
	public $module_name = 'clientaccounts';
	public $module_title = 'Счета';

	public $rights=array(
					'clientaccounts_bills'			=>array("Счета",'read','просмотр'),
				);
	public $actions=array(
					'bill_list'			=> array('clientaccounts_bills','read'),
					'bill_view'			=> array('clientaccounts_bills','read'),
					'pay'				=> array('clientaccounts_bills','read'),
					'rawpayments'		=> array('clientaccounts_bills','read'),
					'update_status'		=> array('clientaccounts_bills','read'),
					'cancel'		=> array('clientaccounts_bills','read'),
					'details'		=> array('clientaccounts_bills','read'),
	);
	public $menu=array(
					array('Счета',				'bill_list'),
					array('Пополнить баланс',	'pay'),
					array('Оплаты по картам',	'rawpayments'),
					);
}
?>
