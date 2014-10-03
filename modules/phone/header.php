<?
class m_phone_head extends IModuleHead{
	public $module_name = 'phone';
	public $module_title = 'Виртуальная АТС';
	public $rights = array('readdr,tc,callback,short,report,mail,voip_view,voip_edit,asterisk','настройка переадресации,редактирование Time Conditions,настройка Call-back,настройка коротких номеров,пропущенные звонки,настройка голосовой почты,просмотр VoIP,настройка VoIP,обновление конфигурации Asteriskа');
	public $actions=array(
					'default'			=> array('',''),
					'redir'				=> 'readdr',
					'redir_save'		=> 'readdr',
					'redir_del'			=> 'readdr',
					'tc'				=> 'tc',
					'tc_edit'			=> 'tc',
					'tc_edit2'			=> 'tc',

					'callback'			=> 'callback',
					'callback_add'		=> 'callback',
					'callback_del'		=> 'callback',
					'callback_change'	=> 'callback',
					'short'				=> 'short',
					'short_add'			=> 'short',
					'short_del'			=> 'short',
					'report'			=> 'report',
					'mail'				=> 'mail',
					'mail_save'			=> 'mail',
					'mail_file'			=> 'mail',
					'voip'				=> 'voip_view',
					'voip_edit'			=> 'voip_edit',
						
					'asterisk'			=> 'asterisk',
					'asterisk_reload'	=> 'asterisk',
				);
	public $menu=array(
					array('Asterisk',					'asterisk',''),
					array('Переадресация',				'redir',	''),
					array('TimeConditions',				'tc',		''),
					array('Callback',					'callback',	''),
					array('Быстрый вызов',				'short',	''),
					array('Пропущенные звонки',			'report',	''),
					array('IP-Телефония',				'voip',	''),
//					array('Голосовая почта',			'mail',		''),
				);
}
?>