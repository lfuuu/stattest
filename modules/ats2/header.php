<?php
class m_ats2_head extends IModuleHead{
	public $module_name = 'ats2';
	public $module_title = 'Управление телефонией';

	public $rights=array(
		'ats2'=>array('Учетные записи SIP','access','доступ')
	);

    public $actions=array(
            'default'=>array('ats2','access'),
            'accounts'=>array('ats2','access'),
            'account'=>array('ats2','access'),
            'account_add'=>array('ats2','access'),
            'account_del'=>array('ats2','access'),
            'view_pass'=>array('ats2','access'),
            'log_view'=>array('ats2','access'),
            'number'=>array('ats2','access'),
            'number_del'=>array('ats2','access'),
            'mt' => array("ats2", "access"),
            'mt_add' => array("ats2", "access"),
            'mt_edit' => array("ats2", "access"),
            'mt_link' => array("ats2", "access"),
            'set_update' => array("ats2", "access"),
            'virtpbx' => array("ats2", "access"),
            'virtpbx_start' => array("ats2", "access"),
            'account_bulk_del' => array("ats2", "access"),

            );

	public $menu=array(
		array('Учетные записи','accounts'),
		array('Мультитранки','mt')
	);
}
?>
