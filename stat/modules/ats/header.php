<?php
class m_ats_head extends IModuleHead{
	public $module_name = 'ats';
	public $module_title = 'Управление ATC';

    public $actions=array(
            'default'=>array('ats','access'),
            'sip_users'=>array('ats','access'),
            'sip_add'=>array('ats','access'),
            'sip_modify'=>array('ats','access'),
            'sip_action'=>array('ats','access'),
            'nums'=>array('ats','access'),
            'numbers'=>array('ats','access'),
            'pickup'=>array('ats','access'),
            'callgroup'=>array('ats','access'),
            'test1'=>array('ats','access'),
            'timecond'=>array('ats','access'),
            'anonses'=>array('ats','access'),
            'schema'=>array('ats','access'),
            'mt'=>array('ats','access'),
            'mt_add'=>array('ats','access'),
            'mt_edit'=>array('ats','access'),
            'to_lk'=>array('ats','access'),
            'view_pass'=>array('ats','access'),
            'log_view'=>array('ats','access'),
            );

	public $menu=array(
		array('Учетные записи и номера','sip_users'),
		array('Мультитранки','mt'),
		array('Клиенты','test1'),
		array('Приветствия','anonses'),
		array('Схемы','schema'),
		array('Переход в Личный Кабинет','to_lk'),
	);
}
?>
