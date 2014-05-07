<?
class m_services_head extends IModuleHead {
	public $module_name	= 'services';
	public $module_title = 'Услуги';
    public $rights=array(
            'services_internet'   => array(
                "Интернет", 
                'r,edit,addnew,activate,close,full,edit_off,tarif',
                'просмотр,изменение,добавление,активирование,отключение,полная информация по сетям (общее с collocation),редактирование отключенных сетей (общее с collocation),изменение тарифа (общее с collocation)'),
            'services_collocation'=>array("Collocation", 'r,edit,addnew,activate,close','просмотр,редактирование,добавление,активирование,отключение'),
            'services_voip'=>array("IP Телефония", 'r,edit,addnew,full,activate,close,view_reg,view_regpass,send_settings,e164,del2029','просмотр,редактирование,добавление,доступ ко всем полям,активирование,отключение,просмотр регистрации,отображение пароля,выслать настройки,номерные емкости,удалять невключенные номера'),
            'services_domains'=>array("Доменные имена", 'r,edit,addnew,close','просмотр,редактирование,добавление,отключение'),
            'services_mail'=>array("E-mail", 'r,edit,addnew,full,activate,chpass,whitelist','просмотр,редактирование,добавление,доступ ко всем полям,активирование,смена пароля,белый список'),
            'services_ppp'=>array("PPP-логины", 'r,edit,addnew,full,activate,chpass,close','просмотр,редактирование,добавление,доступ ко всем полям,активирование,смена пароля,отключение'),
            'services_additional'=>array("Дополнительные услуги", 'r,r_old,edit,addnew,full,activate,close','просмотр,просмотр старых,редактирование,добавление,доступ ко всем полям,активирование,отключение'),
            'services_welltime'=>array('WellTime','full,docs','полный доступ,документы'),
            'services_wellsystem'=>array('WellSystem','full','полный доступ'),
            'services_itpark'=>array('Услуги IT Park\'а','full','полный доступ')
            );
	public $actions=array(
					'default'			=> array('',''),

//INternet + vpn
					'in_view'			=> array('services_internet','r'),
					'in_report'			=> array('services_internet','r'),
					'in_view_ind'		=> array('services_internet','r'),
					'in_view_routed'	=> array('services_internet','r'),
					'in_act'			=> array('services_internet','r'),
					'in_act_pon'		=> array('services_internet','r'),
	                'in_async'			=> array('services_internet','r'),
					'in_edit'			=> array('services_internet','edit'),
					'in_apply'			=> array('services_internet','edit'),
					'in_apply2'			=> array('services_internet','edit'),
					'in_add'			=> array('services_internet','addnew'),
					'in_add2'			=> array('services_internet','addnew'),
					'in_close'			=> array('services_internet','close'),
					'in_dev_act'		=> array('services_internet','r'),

//COllocation
					'co_view'			=> array('services_collocation','r'),
					'co_act'			=> array('services_collocation','r'),
					'co_edit'			=> array('services_collocation','edit'),
					'co_apply'			=> array('services_collocation','edit'),
					'co_apply2'			=> array('services_collocation','edit'),
					'co_add'			=> array('services_collocation','addnew'),
					'co_add2'			=> array('services_collocation','addnew'),
					'co_close'			=> array('services_collocation','close'),
						
//VOip
					'vo_view'			=> array('services_voip','r'),
					'vo_act'			=> array('services_voip','r'),
	                'vo_act_trunk'		=> array('services_voip','r'),
					'vo_add'			=> array('services_voip','addnew'),
					'vo_apply'			=> array('services_voip','edit'),
					'vo_close'			=> array('services_voip','close'),
					'e164'				=> array('services_voip','e164'),
                    'e164_edit'			=> array('services_voip','e164'),
                    'vo_settings_send'	=> array('services_voip','send_settings'),
					'vo_delete'			=> array('services_voip','del2029'),
	                'get_tarifs'        =>array('services_voip','r'),
					
//Domain Names
					'dn_view'			=> array('services_domains','r'),
					'dn_add'			=> array('services_domains','addnew'),
					'dn_apply'			=> array('services_domains','edit'),
					'dn_close'			=> array('services_domains','close'),

//E-Mails
					'em_view'				=> array('services_mail','r'),
					'em_add'				=> array('services_mail','addnew'),
					'em_apply'				=> array('services_mail','r'),		//проверка внутри
					'em_toggle'				=> array('services_mail','edit'),
					'em_activate'			=> array('services_mail','activate'),
					'em_chpass'				=> array('services_mail','chpass'),
					'em_chreal'				=> array('services_mail','chpass'),
					'em_whitelist'			=> array('services_mail','whitelist'),
					'em_whitelist_add'		=> array('services_mail','whitelist'),
					'em_whitelist_delete'	=> array('services_mail','whitelist'),
					'em_whitelist_toggle'	=> array('services_mail','whitelist'),

//EXtra - дополнительные услуги (новые)
					'ex_view'			=> array('services_additional','r'),
					'ex_act'			=> array('services_additional','edit'),
					'ex_add'			=> array('services_additional','addnew'),
					'ex_apply'			=> array('services_additional','edit'),
					'ex_close'			=> array('services_additional','close'),
					'ex_async'			=> array('services_additional','addnew'),

//IT Park
					'it_view'			=> array('services_itpark','full'),
					'it_add'			=> array('services_itpark','full'),

//Welltime
					'welltime_act'			=> array('services_welltime','docs'),
					'welltime_view'			=> array('services_welltime','full'),
					'welltime_add'			=> array('services_welltime','full'),
					'welltime_apply'			=> array('services_welltime','full'),
//Виртуальная АТС
					'virtpbx_view'			=> array('services_welltime','full'),
					'virtpbx_add'			=> array('services_welltime','full'),
					'virtpbx_apply'			=> array('services_welltime','full'),
					'virtpbx_act'			=> array('services_welltime','docs'),
					'virtpbx_delete'		=> array('services_voip','del2029'),
//8800
					'8800_view'			=> array('services_welltime','full'),
					'8800_add'			=> array('services_welltime','full'),
					'8800_apply'			=> array('services_welltime','full'),
//sms
					'sms_view'			=> array('services_welltime','full'),
					'sms_add'			=> array('services_welltime','full'),
					'sms_apply'			=> array('services_welltime','full'),

//WellSystem
					'wellsystem_view'			=> array('services_wellsystem','full'),
					'wellsystem_add'			=> array('services_wellsystem','full'),

//PPP-логины
					'ppp_view'			=> array('services_ppp','r'),
					'ppp_add'			=> array('services_ppp','addnew'),
					'ppp_apply'			=> array('services_ppp','r'),		//проверка внутри
					'ppp_append'		=> array('services_ppp','full'),
					'ppp_activate'		=> array('services_ppp','activate'),
					'ppp_activateall'	=> array('services_ppp','activate'),
					'ppp_chpass'		=> array('services_ppp','chpass'),
					'ppp_chreal'		=> array('services_ppp','chpass'),

//дополнительные услуги (старые)
					'ad_view'			=> array('services_additional','r_old'),
					'ad_act'			=> array('services_additional','r_old'),
					'ad_add'			=> array('services_additional','addnew'),
					'ad_apply'			=> array('services_additional','edit'),
					'ad_close'			=> array('services_additional','close'),
				);
	public $menu=array(
		array('IP Телефония',			'vo_view'),
		array('Welltime',				'welltime_view'),
        array('Виртуальная АТС',		'virtpbx_view'),
        array('8800',	                '8800_view'),
        array('СМС',	                'sms_view'),
		array('Интернет',				'in_view'),
		array('Collocation',			'co_view'),
		array('WellSystem',				'wellsystem_view'),
		array('Номерные ёмкости',	    'e164'),
		array('Доменные имена',			'dn_view'),
		array('E-Mail',					'em_view'),
		array('PPP-логины',				'ppp_view'),
		array('Доп. услуги',			'ex_view'),
		array('IT Park',				'it_view'),
	);
}
?>
