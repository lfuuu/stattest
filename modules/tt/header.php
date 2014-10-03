<?
class m_tt_head extends IModuleHead{
	public $module_name = 'tt';
	public $module_title = 'Заявки';
	var $rights=array(
		'tt'=>array(
			'Работа с заявками',
			'view,view_cl,use,time,admin,states,report,doers_edit,shop_orders,comment,rating',
			'просмотр,показывать "Запросы клиентов",использование,управление временем,администраторский доступ,редактирование состояний,отчёт,редактирование исполнителей,заказы магазина,коментарии для не своих заявок,оценка заявки'
		),
	);
	var $actions=array(
					'default'		=> array('tt','view'),
					'list'			=> array('tt','view'),
					'list_cl'		=> array('tt','view_cl'),
					'list2'			=> array('tt','use'),
					'view'			=> array('tt','view'),
					'add'			=> array('tt','use'),		//новый трабл
					'move'			=> array('tt','use'),		//новый этап
					'refix_doers'	=> array('tt','use'),
					'list_types'	=> array('tt','use'),
					'view_type'		=> array('tt','use'),
					'time'			=> array('tt','time'),

					'sadd'			=> array('tt','states'),
					'sapply'		=> array('tt','states'),
					'sedit'			=> array('tt','states'),
					'slist'			=> array('tt','states'),
					'report'		=> array('tt','report'),
					'timetable'		=> array('tt','view'),
					'doers_list'	=> array('tt','view'),
					'courier_report'	=> array('tt','view'),
					'courier_report2'	=> array('tt','view'),
					'doers'			=> array('tt','doers_edit'),
					'rpc_setState1c'=> array('tt','use')
				);
	var $menu=array(
		array('Тех поддержка MCN','view_type', '&type_pk=1'),
		array('Задания MCN','view_type','&type_pk=2'),
		array('Тех поддержка WellTime', 'view_type', '&type_pk=3'),
		array('Заказы Магазина', 'view_type', '&type_pk=4'),
		array('Установка и Монтаж', 'view_type', '&type_pk=5'),
		array('Заказы Welltime', 'view_type', '&type_pk=6'),
		array('Заказы поставщику', 'view_type', '&type_pk=7'),
		array('','view_type'),
		#array('Список типов', 'list_types'),
		array('Расписание',				'timetable'),
		array('Список открытых заявок', 'list','&mode=1'),
		array('Список всех заявок', 	'list','&mode=0'),
		array(
			'Откр. Тех.Поддержка',
			'list',
			"&mode=0&state_filter=1&resp=SUPPORT&date_from=prev_mon"
		),
		array('Мне поручили',	 		'list2','&mode=2'),
		array('Я поручил',	 			'list2','&mode=3'),
		array('Запросы моих клиентов', 	'list_cl','&mode=4'),			//mode=4
		array('Под контролем',			'list','&mode=5'),
		#array('Отчёт', 					'report'),
		#array('Состояния',				'slist'),
		array('','view_type'),
		array('Исполнители',			'doers'),
		array('Отчет Абон. отдела',		'doers_list'),
		array('Загрузка логистики',		'courier_report'),
		array('Загрузка логистики2',		'courier_report2'),
	);
	function GetPanel($fixclient){
		global $design,$user,$db,$module;
		if (!access('tt','view')) return;
		if ($this->is_active==0 && !($fixclient && $module=='clients')) {
			$this->showTroubleList(2,'top',$fixclient);
		}
		parent::GetPanel($fixclient);
	}
}
?>
