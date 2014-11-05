<?
class DbView {
	public $table;
	protected $filers = array();
	public $fieldset = '';
	public $Headers = array();
	public $FieldSets=array();
	public $SQLFilterNames = array();
	public $SQLFilters = array();
	public $SQLFilterGroups = array();
	public $order = array('id'=>'asc');
	protected $SQLFieldsReplacement = array();
	protected $SQLQuery = '';
	private $rawQuery = '';
	
	public function __construct() {
    }

	public function SetFilters($n) {
		if (is_array($n) && count($n)) $this->filters=$n;
        self::__construct();
	}
	public function SetFieldSet($n) {
		$this->fieldset=$n;
    }

    private function makeSQLString() {
        $P=array('AND');
        $order='';
        foreach($this->order as $k=>$v)
                $order .= ($order?',':' order by ').$k.' '.$v;

        foreach($this->filters as $f)
                if(isset($this->SQLFilters[$f]))
                        $P[]=$this->SQLFilters[$f];

        if(isset($this->SQLFilters[$this->fieldset]))
                $P[] = $this->SQLFilters[$this->fieldset];

        $this->rawQuery = $this->SQLQuery;

        $query_start = ($this->rawQuery) ? $this->rawQuery : 'select * from '.$this->table;
        $this->rawQuery = $query_start . ' where '.MySQLDatabase::Generate($P).$order;
    }

	public function Display($link_read,$link_edit,$add = 1) {
		global $db,$design;
        $design->assign('dbview_headers',$this->Headers[$this->fieldset]);

        $this->makeSQLString();

		$dbview_data = array();
		$db->Query($this->rawQuery);
		while($row=$db->NextRecord(MYSQL_ASSOC)){
			foreach($this->SQLFieldsReplacement as $key=>&$field){
				if(isset($row[$key]) && is_array($field) && array_key_exists($row[$key],$field)){
					$row[$key] = $field[$row[$key]];
				}
			}
			$dbview_data[] = $row;
		}
		unset($row);
		$design->assign_by_ref('dbview_data',$dbview_data);

		$P=array();
		foreach($this->SQLFilterGroups as $k=>$v){
			$P[$k]=array();
			foreach($v as $vi){
				$P[$k][]=array(
					'title'=>$this->SQLFilterNames[$vi],
					'value'=>$vi,
					'selected'=>(array_search($vi,$this->filters)!==false));
			}
		}
		$design->assign('dbview_filters',$P);

		$P=array();
		$Q=array();
		$qc=0;
		$qb = false;
		foreach($this->FieldSets[$this->fieldset] as $k=>$v){
			if(!is_array($v)){
				$P[$k]=$v;
				$qc++;
			}else{
				$qb=true;
				if($qc)
					$Q[]=array($qc,'');
				$qc=0;
				foreach($v as $ki=>$vi){
					$P[$ki] = $vi;
					$qc++;
				}
				$Q[] = array($qc,$k);
				$qc=0;
			}
		}
		if($qc && $qb)
			$Q[] = array($qc,'');
		$design->assign('dbview_fields',$P);
		$design->assign('dbview_fieldgroups',$Q);
		$design->assign('dbview_link_read',$link_read);
		$design->assign('dbview_link_edit',$link_edit);
		if($add)
			$design->AddMain('dbview.tpl');
	}
}
class DbFormSimpleLog extends DbForm {
	public function __construct() {
		$this->constructChild();
		$this->fields['edit_user']=array('type'=>'label');
		$this->fields['edit_time']=array('type'=>'label');
	}
	public function Display($form_params = array(),$h2='',$h3='') {
		global $db;
		if(
			$this->isData('edit_user')
		&&
			$this->data['edit_user']
		&&
			(
				$t=$db->GetRow('
					select
						concat(user," (",name,")") as name
					from
						user_users
					where
						id='.$this->data['edit_user']
				)
			)
		) $this->data['edit_user'] = $t['name'];

		return parent::Display($form_params,$h2,$h3);
	}
	public function Process($no_real_update = 0) {
		global $db,$user;
		$this->Get();
		if(!isset($this->dbform['id']))
			return '';
		$this->dbform['edit_user']=$user->Get('id');
		$this->dbform['edit_time']=array('NOW()');
		return DbForm::Process();
	}
}
class DbViewCommonTarif extends DbView {
	public function __construct() {
		$this->SQLFilters['i']='type="I"';
		$this->SQLFilters['v']='type="V"';
		$this->SQLFilters['c']='type="C"';
		$this->SQLFilters['p']='status="public"';
		$this->SQLFilters['a']='status="archive"';
		$this->SQLFilters['s']='status="special"';
		$this->SQLFilters['su']='status="adsl_su"';
		$this->SQLFilters['USD']='currency="USD"';
		$this->SQLFilters['RUR']='currency="RUR"';
		$this->SQLFilterNames['p']='публичный';
		$this->SQLFilterNames['a']='архивный';
		$this->SQLFilterNames['s']='специальный';
		$this->SQLFilterNames['su']='adsl.su';
		$this->SQLFilterNames['USD']='USD';
		$this->SQLFilterNames['RUR']='RUR';
		$this->SQLFilterGroups=array('Тип тарифа'=>array('p','a','s','su'),'Валюта тарифа'=>array('USD','RUR'));
		$this->filters=array('p','USD');
		$this->constructChild();
		parent::__construct();
	}
}

class DbViewTarifsInternet extends DbViewCommonTarif {	
	public function __construct() {
		parent::__construct();
	}

	public function constructChild() {
		$this->table='tarifs_internet';
		$this->Headers['i']='Тарифы на интернет';
		$this->Headers['v']='Тарифы на VPN';
		$this->Headers['c']='Тарифы на Collocation';
		$this->FieldSets['i']=array('name'=>'Название','pay_once'=>'Подключение','Ежемесячно'=>array('pay_month'=>'сумма','mb_month'=>'Мб'),'pay_mb'=>'сумма/мб','comment'=>'комментарий');
		$this->FieldSets['v']=array('name'=>'Название','pay_once'=>'Подключение','Ежемесячно'=>array('pay_month'=>'сумма','mb_month'=>'Мб'),'pay_mb'=>'сумма/мб','comment'=>'комментарий');
		$this->FieldSets['c']=array('name'=>'Название',
							'pay_once'=>'Подключение',
							'pay_month'=>'Аб. плата',
							'Мб в месяц'=>array('month_r'=>'R','month_r2'=>'R2','month_f'=>'F'),
							'Плата за Мб'=>array('pay_r'=>'R','pay_r2'=>'R2','pay_f'=>'F'),
							'pay_once'=>'Подключение',
							'comment'=>'комментарий'
							);
		$this->filters[]='std';
		$this->SetFieldSet('i');
	}
	public function SetFieldSet($n) {
		if ($n=='i' && $this->fieldset!='i') {
			$this->SQLFilters['std']='type_internet="standard"';
			$this->SQLFilters['wm']='type_internet="wimax"';
			$this->SQLFilters['col']='type_internet="collective"';
			$this->SQLFilterNames['std']='обычный';
			$this->SQLFilterNames['wm']='WiMAX';
			$this->SQLFilterNames['col']='коллективный';
			$this->SQLFilterGroups['Тип интернет-тарифа']=array('std','wm','col');
		} else if ($n!='i' && $this->fieldset=='i') {
			unset($this->SQLFilters['std']);
			unset($this->SQLFilters['wm']);
			unset($this->SQLFilters['col']);
			unset($this->SQLFilterNames['std']);
			unset($this->SQLFilterNames['wm']);
			unset($this->SQLFilterNames['col']);
			unset($this->SQLFilterGroups['Тип интернет-тарифа']);
		}
		$this->fieldset=$n;
	}
}
class DbFormTarifsInternet extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_internet');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','special'=>'специальный','archive'=>'архивный','adsl_su'=>'adsl.su'));
		$this->fields['type']=array('assoc_enum'=>array('I'=>'Интернет (I)','V'=>'VPN (V)','C'=>'Collocation (C)'));
		$this->fields['type_internet']=array('assoc_enum'=>array('standard'=>'Обычный','wimax'=>'WiMAX','collective'=>'Коллективный'));
        global $db;
        $a = array();
        $R = $db->AllRecords("select name from adsl_speed");
        foreach($R as $r){$a[$r["name"]] = $r["name"];}

		$this->fields['adsl_speed']=array('assoc_enum'=>$a);
		$this->fields['name']=array();
		$this->fields['pay_once']=array('default'=>'0.00');
		$this->fields['pay_month']=array('default'=>'0.00');
		$this->fields['mb_month']=array('default'=>'0');
		$this->fields['pay_mb']=array('default'=>'0.00');
		$this->fields['sum_deposit']=array();
		$this->fields['type_count']=array('assoc_enum'=>array('all'=>'всё раздельно', 'r2_f'=>'Считать Россию-2 как иностранный', 'all_f'=>'Считать всё как иностранный'));
		$this->fields['comment']=array();
	}
}
class DbFormTarifsCollocation extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_internet');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','special'=>'специальный','archive'=>'архивный'));
		$this->fields['type']=array('assoc_enum'=>array('I'=>'Интернет (I)','V'=>'VPN (V)','C'=>'Collocation (C)'),'default'=>'C');
		$this->fields['name']=array();
		$this->fields['pay_once']=array('default'=>'0.00');
		$this->fields['pay_month']=array('default'=>'0.00');
		$this->fields['month_r']=array('default'=>0);
		$this->fields['month_r2']=array('default'=>0);
		$this->fields['month_f']=array('default'=>0);
		$this->fields['pay_r']=array('default'=>'0.00');
		$this->fields['pay_r2']=array('default'=>'0.00');
		$this->fields['pay_f']=array('default'=>'0.00');
		$this->fields['type_count']=array('assoc_enum'=>array('sep'=>'всё раздельно', 'r2_f'=>'Считать Россию-2 как иностранный', 'all_f'=>'Считать всё как иностранный'));
		$this->fields['comment']=array();
	}
}

class DbViewTarifsHosting extends DbViewCommonTarif {	
	public function constructChild() {
		$this->table='tarifs_hosting';
		$this->Headers['z']='Тарифы на хостинг';
		$this->FieldSets['z']=array('name'=>'Название',
							'pay_once'=>'Подключение',
							'pay_month'=>'Аб. плата',
							'mb_disk'=>'Диск, мб',
							'Наличие'=>array('has_dns'=>'DNS','has_ftp'=>'FTP','has_ssh'=>'SSH','has_ssi'=>'SSI','has_php'=>'PHP','has_perl'=>'Perl','has_mysql'=>'MySQL'),
							);
		$this->fieldset='z';
	}
}
class DbFormTarifsHosting extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_hosting');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','special'=>'специальный','archive'=>'архивный'));
		$this->fields['name']=array();
		$this->fields['pay_once']=array('default'=>'0');
		$this->fields['pay_month']=array();
		$this->fields['mb_disk']=array();
		$V=array('assoc_enum'=>array('0'=>'&ndash;','1'=>'+'));
		$this->fields['has_dns']=$V;
		$this->fields['has_ftp']=$V;
		$this->fields['has_ssh']=$V;
		$this->fields['has_ssi']=$V;
		$this->fields['has_php']=$V;
		$this->fields['has_perl']=$V;
		$this->fields['has_mysql']=$V;
	}
}

class DbViewPriceVoip extends DbViewCommonTarif {
	public function constructChild() {
		global $db;
		$this->table='price_voip';
		$this->Headers['r']='Тарифы на междугородние звонки';
		$this->Headers['w']='Тарифы на международные звонки';
		$this->FieldSets['r'] = array(
			'destination_name'=>'Пункт',
			'destination_prefix'=>'Префикс',
			'Стоимость минуты'=>array(
				'rate_USD'=>'В долларах',
				'rate_RUR'=>'В рублях'
			),
			'Направление'=>array(
				'dgroup'=>'Группа',
				'dsubgroup'=>'Подгруппа'
			),
		);
		$this->FieldSets['w']=$this->FieldSets['r'];
		$this->SQLFilters['r']='dgroup IN (0,1)';
		$this->SQLFilters['w']='dgroup IN (2)';
		$this->SQLFieldsReplacement = array(
			'dgroup'=>array(0=>'Москва',1=>'Россия',2=>'Международное'),
			'dsubgroup'=>array(0=>'Мобильные',1=>'1 Зона/Стационарные',2=>'2 Зона',3=>'3 Зона',4=>'4 Зона',5=>'5 Зона',6=>'6 Зона',97=>'Международное Фрифон',98=>'Россия Фрифон',99=>'Другое')
		);
		$this->order = array('dgroup'=>'asc','dsubgroup'=>'asc','destination_name'=>'asc');
		$r=$db->GetRow('select max(priceid) as A from price_voip');
		$R=array();
		for($i=0;$i<=$r['A'];$i++){
			$this->SQLFilters['g'.$i]='priceid='.$i;
			$this->SQLFilterNames['g'.$i]=$i;
			$R[]='g'.$i;
		}
		$this->SQLFilterGroups=array('Группа'=>$R);
		$this->filters=array('g0');
		$this->fieldset='r';
	}
}
class DbFormPriceVoip extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('price_voip');
		$this->fields['destination_name']=array();
		$this->fields['destination_prefix']=array();
		$this->fields['operator']=array();
		global $user;
		if(in_array($user->Get('user'),array('sfilatov','dns','bnv','mak','pma'))){
			$this->fields['rate_USD']=array('default'=>'0.00');
			$this->fields['rate_RUR']=array('default'=>'0.00');
			$this->fields['priceid']=array('default'=>'0');
		}
		$this->fields['dgroup']=array('default'=>0,'assoc_enum'=>array(0=>'Москва',1=>'Россия',2=>'Международное'));
		$this->fields['dsubgroup']=array('default'=>0,'assoc_enum'=>array(0=>'Мобильные',1=>'1 Зона/Стационарные',2=>'2 Зона',3=>'3 Зона',4=>'4 Зона',5=>'5 Зона',6=>'6 Зона',97=>'Международное Фрифон',98=>'Россия Фрифон',99=>'Другое'));
	}
}
class DbViewTarifsExtra extends DbViewCommonTarif {
	public function constructChild() {
		$this->table='tarifs_extra';
		$this->Headers['z']='Тарифы на дополнительные услуги';
		$this->FieldSets['z']=array(
							'description'=>'Описание',
							'code' => 'Код',
							'price'=>'Стоимость',
							'period' => 'Период',
							);
		$this->fieldset='z';

		$this->SQLFilters['pa']='1 and code not in ("welltime","wellsystem")';
		$this->SQLFilters['pm']='period="month" and code not in ("welltime","wellsystem")';
		$this->SQLFilters['py']='period="year" and code not in ("welltime","wellsystem")';
		$this->SQLFilters['po']='period="once" and code not in ("welltime","wellsystem")';
		$this->SQLFilters['p3']='period="3mon" and code not in ("welltime","wellsystem")';
		$this->SQLFilters['p6']='period="6mon" and code not in ("welltime","wellsystem")';
		$this->SQLFilterNames['pa']='любой';
		$this->SQLFilterNames['pm']='ежемесячный';
		$this->SQLFilterNames['py']='ежегодный';
		$this->SQLFilterNames['po']='разовый';
		$this->SQLFilterNames['p3']='3 месяца';
		$this->SQLFilterNames['p6']='6 месяцев';
		$this->SQLFilterGroups['Период тарифа']=array('pa','pm','py','po','p3','p6');
		$this->filters[]='pa';
	}
}
class DbFormTarifsExtra extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_extra');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','special'=>'специальный','archive'=>'архивный'));
		$this->fields['description']=array();
		$this->fields['code']=array('assoc_enum'=>array(
                    ''=>'',

                    //'confroom' => 'Конференц-зал',
                    'domain'=>'Домен',
                    'ip'=>'IP',
                    'mailserver'=>'Почтовый сервер',
                    'phone_ats'=>'АТС',
                    'site'=>'Сайт',
                    'sms_gate'=>'SMS Gate', 
                    'uspd' => "УСПД",
                    //'welltime'=>'WellTime',
                    //'wellsystems'=>'WellSystems'
                    ));
		$this->fields['param_name']=array();
		$this->fields['is_countable']=array('assoc_enum'=>array('1'=>'любое', 0=>'всегда 1'));
		$this->fields['price']=array();
		$this->fields['period']=array('assoc_enum'=>array('month'=>'ежемесячно', 'year'=>'ежегодно','once'=>'разово', '3mon'=>'раз в 3 месяца','6mon'=>'раз в 6 месяцев'));
	}
}

class DbViewTarifsITPark extends DbView{
	public function __construct(){
		$this->table = 'tarifs_extra';
		$this->Headers['z'] = 'Тарифы ITPark';
		$this->FieldSets['z']=array(
			'description'=>'Описание',
			'price'=>'Стоимость',
			'period' => 'Период',
		);
		$this->fieldset = 'z';

		$this->SQLFilterGroups = array('Группа тарифов'=>array('itpark'));
		$this->SQLFilterGroups['Тип тарифа'] = array('once','periodical');
		$this->SQLFilterNames['itpark'] = 'IT Park';
		$this->SQLFilterNames['once'] = 'разовый';
		$this->SQLFilterNames['periodical'] = 'периодический';
		$this->SQLFilters['once'] = 'period="once"';
		$this->SQLFilters['periodical'] = 'period<>"once"';
		$this->SQLFilters['itpark'] = 'status="itpark"';
		$this->filters = array('itpark','once');
	}
}

class DbViewTarifsWelltime extends DbView{
	public function __construct(){
		$this->table = 'tarifs_extra';
		$this->Headers['z'] = 'Тарифы Welltime';
		$this->FieldSets['z']=array(
			'description'=>'Описание',
			'price'=>'Стоимость',
			'period' => 'Период',
		);
		$this->fieldset = 'z';

		//$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','special'=>'специальный','archive'=>'архивный','adsl_su'=>'adsl.su'));

		$this->SQLFilterGroups = array('Группа тарифов'=>array('welltime'));
		$this->SQLFilterGroups['Состояние'] = array('public', 'archive');
		$this->SQLFilterGroups['Тип тарифа'] = array('once','periodical');


		$this->SQLFilterNames['welltime'] = 'Welltime';
		$this->SQLFilterNames['once'] = 'разовый';
		$this->SQLFilterNames['periodical'] = 'периодический';
		$this->SQLFilters['once'] = 'period="once"';
		$this->SQLFilters['periodical'] = 'period<>"once" and period <>"archive"';
		$this->SQLFilters['welltime'] = 'code="welltime"';

		$this->SQLFilters['public']='status="public"';
		$this->SQLFilters['archive']='status="archive"';
		$this->SQLFilterNames['public']='публичный';
		$this->SQLFilterNames['archive']='архивный';



		$this->filters = array('welltime','public', 'once');
	}
}

class DbViewTarifsVirtpbx extends DbView{
	public function __construct(){
		$this->table = 'tarifs_virtpbx';
		$this->Headers['z'] = 'Тарифы на виртуальную АТС';
		$this->FieldSets['z']=array(
			'description'=>'Описание',
			'price'=>'Стоимость',
			'period' => 'Период',
		);
		$this->fieldset = 'z';

		$this->SQLFilterGroups['Состояние'] = array('public', 'archive');


		$this->SQLFilters['public']='status="public"';
		$this->SQLFilters['archive']='status="archive"';
		$this->SQLFilterNames['public']='публичный';
		$this->SQLFilterNames['archive']='архивный';



		$this->filters = array('virtpbx','public');
	}
}

class DbViewTarifs8800 extends DbView{
	public function __construct(){
		$this->table = 'tarifs_8800';
		$this->Headers['z'] = 'Тарифы на номера 8800';
		$this->FieldSets['z']=array(
			'description'=>'Описание',
			'price'=>'Стоимость',
			'period' => 'Период',
		);
		$this->fieldset = 'z';

		$this->SQLFilterGroups['Состояние'] = array('public', 'archive');


		$this->SQLFilters['public']='status="public"';
		$this->SQLFilters['archive']='status="archive"';
		$this->SQLFilterNames['public']='публичный';
		$this->SQLFilterNames['archive']='архивный';



		$this->filters = array('virtpbx','public');
	}
}

class DbViewTarifsSms extends DbView{
	public function __construct(){
		$this->table = 'tarifs_sms';
		$this->Headers['z'] = 'Тарифы на SMS';
		$this->FieldSets['z']=array(
			'description'=>'Описание',
			'per_month_price'=>'Абонентская плата, руб. с НДС',
			'per_sms_price'=>'за 1 СМС, руб с НДС',
		);
		$this->fieldset = 'z';

		$this->SQLFilterGroups['Состояние'] = array('public', 'archive');


		$this->SQLFilters['public']='status="public"';
		$this->SQLFilters['archive']='status="archive"';
		$this->SQLFilterNames['public']='публичный';
		$this->SQLFilterNames['archive']='архивный';



		$this->filters = array('sms','public');
	}
}

class DbViewTarifsWellSystem extends DbView{
	public function __construct(){
		$this->table = 'tarifs_extra';
		$this->Headers['z'] = 'Тарифы WellSystem';
		$this->FieldSets['z']=array(
			'description'=>'Описание',
			'price'=>'Стоимость',
			'period' => 'Период',
		);
		$this->fieldset = 'z';

		$this->SQLFilterGroups = array('Группа тарифов'=>array('wellsystem'));
		$this->SQLFilterGroups['Тип тарифа'] = array('once','periodical');
		$this->SQLFilterNames['wellsystem'] = 'WellSystem';
		$this->SQLFilterNames['once'] = 'разовый';
		$this->SQLFilterNames['periodical'] = 'периодический';
		$this->SQLFilters['once'] = 'period="once"';
		$this->SQLFilters['periodical'] = 'period<>"once"';
		$this->SQLFilters['wellsystem'] = 'code="wellsystem"';
		$this->filters = array('wellsystem','once');

	}
}
class DbFormTarifsITPark extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_extra');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('enum'=>array('itpark'),'type'=>'hidden','default'=>'itpark');
		$this->fields['description']=array();
		$this->fields['code']=array('assoc_enum'=>array(''=>'','confroom'=>'Конференц-зал','workingtable'=>'Рабочее место'));
		$this->fields['param_name']=array();
		$this->fields['is_countable']=array('assoc_enum'=>array('1'=>'любое', 0=>'всегда один'));
		$this->fields['price']=array();

        global $db;
        $okvd = array("0" => "-");
        foreach($db->AllRecords("select distinct code, name from okvd order by name") as $o)
        {
            $okvd[$o["code"]] = $o["name"];
        }

		$this->fields['okvd_code']=array('assoc_enum' => $okvd);
		$this->fields['period']=array('assoc_enum'=>array('month'=>'ежемесячно', 'year'=>'ежегодно','once'=>'разово', '3mon'=>'раз в 3 месяца','6mon'=>'раз в 6 месяцев'));
	}
}

class DbFormTarifsWelltime extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_extra');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		//$this->fields['status']=array('enum'=>array('public'),'type'=>'hidden','default'=>'public');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','archive'=>'архивный'));
		//$this->fields['status']=array('enum'=>array('itpark'),'type'=>'hidden','default'=>'itpark');
		$this->fields['description']=array();
		$this->fields['code']=array('enum'=>array('welltime'),'type'=>'hidden','default'=>'welltime');
		//$this->fields['code']=array('assoc_enum'=>array(''=>'','confroom'=>'Конференц-зал','workingtable'=>'Рабочее место'));
		$this->fields['param_name']=array();
		$this->fields['is_countable']=array('assoc_enum'=>array('1'=>'любое', 0=>'всегда один'));
		$this->fields['price']=array();
		$this->fields['period']=array('assoc_enum'=>array('month'=>'ежемесячно', 'year'=>'ежегодно','once'=>'разово', '3mon'=>'раз в 3 месяца','6mon'=>'раз в 6 месяцев'));
	}
}

class DbFormTarifsVirtpbx extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_virtpbx');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','archive'=>'архивный'));
		$this->fields['description']=array();
		$this->fields['price']=array('default'=>0);
		$this->fields['period']=array('assoc_enum'=>array('month'=>'ежемесячно'));
        $this->fields['num_ports']=array('default'=>50);
        $this->fields['overrun_per_port']=array('default'=>1);
        $this->fields['space']=array('default'=>100);
        $this->fields['overrun_per_gb']=array('default'=>1);
        $this->fields['is_record']=array('assoc_enum' => array('1' => 'Да', '0' => 'Нет'), 'default'=>1);
        $this->fields['is_fax']=array('assoc_enum' => array('1' => 'Да', '0' => 'Нет'), 'default'=>1);
	}
}

class DbFormTarifs8800 extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_8800');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','archive'=>'архивный'));
		$this->fields['description']=array();
		$this->fields['price']=array('default'=>0);
		$this->fields['period']=array('assoc_enum'=>array('month'=>'ежемесячно'));
	}
}

class DbFormTarifsSms extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_sms');
		$this->fields['currency']=array('type' => 'hidden', 'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','archive'=>'архивный'));
		$this->fields['description']=array();
		$this->fields['per_month_price']=array('default'=>0);
		$this->fields['per_sms_price']=array('default'=>0);
		$this->fields['period']=array('assoc_enum'=>array('month'=>'ежемесячно'));
	}
}

class DbFormTarifsWellSystem extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('tarifs_extra');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('enum'=>array('public'),'type'=>'hidden','default'=>'public');
		//$this->fields['status']=array('enum'=>array('itpark'),'type'=>'hidden','default'=>'itpark');
		$this->fields['description']=array();
		$this->fields['code']=array('enum'=>array('wellsystem'),'type'=>'hidden','default'=>'wellsystem');
		//$this->fields['code']=array('assoc_enum'=>array(''=>'','confroom'=>'Конференц-зал','workingtable'=>'Рабочее место'));
		$this->fields['param_name']=array();
		$this->fields['is_countable']=array('assoc_enum'=>array('1'=>'любое', 0=>'всегда один'));
		$this->fields['price']=array();
		$this->fields['period']=array('assoc_enum'=>array('month'=>'ежемесячно', 'year'=>'ежегодно','once'=>'разово', '3mon'=>'раз в 3 месяца','6mon'=>'раз в 6 месяцев'));
	}
}
class DbViewBillMonthlyaddReference extends DbViewCommonTarif {
	public function constructChild() {
		$this->table='bill_monthlyadd_reference';
		$this->Headers['z']='Тарифы на дополнительные услуги';
		$this->FieldSets['z']=array(
							'description'=>'Описание',
							'price'=>'Стоимость',
							'period' => 'Период',
							);
		$this->fieldset='z';
	}
}
class DbFormBillMonthlyaddReference extends DbFormSimpleLog {
	public function constructChild() {
		DbForm::__construct('bill_monthlyadd_reference');
		$this->fields['currency']=array('enum'=>array('USD','RUR'),'default'=>'RUR');
		$this->fields['status']=array('assoc_enum'=>array('public'=>'публичный','special'=>'специальный','archive'=>'архивный'));
		$this->fields['description']=array();
		$this->fields['price']=array();
		$this->fields['period']=array('assoc_enum'=>array('day'=>'Ежедневно', 'month'=>'Ежемесячно', 'year'=>'Ежегодно', 'once'=>'Разовая услуга'));
	}
}

class DbViewMonitorClients extends DbView {
	public function __construct() {
		$this->filters=array();
		$this->SQLFilters=array();
		$this->SQLFilterNames=array();
		$this->SQLFilterGroups=array();
		$this->table='monitor_clients';
		$this->Headers['z']='Мониторинг';
		$this->FieldSets['z']=array(
							'client'=>'Клиент',
							'email'=>'E-Mail',
							'allow_bad' => 'Пороговое число плохих пингов',
							'period_mail' => 'Время между письмами, мин',
							'IP-адреса'=>array('ips'=>'IP','bad'=>'плохих пингов'),
							);
		$this->fieldset='z';
		parent::__construct();
	}
	public function Display($link_read,$link_edit,$add = 1) {
		global $db,$design;
		parent::Display($link_read,$link_edit,0);
		$v=&$design->_tpl_vars['dbview_data'];
		foreach ($v as &$k) {
			$R=$db->AllRecords('select INET_NTOA(ip_int),count from monitor_ips where monitor_id='.$k['id']);
			$p=null;
			$s1=''; $s2=''; foreach ($R as $r) {
				$s1.=($s1?'<br>':'').__ipstat(array('net'=>$r[0]),$p);
				$s2.=($s2?'<br>':'').$r[1];
			}
			$k['ips']=$s1; $k['bad']=$s2;
			$k['period_mail']=$k['period_mail']*5+5;
			if ($s1) {
				$k['_tr_class']='comment';
			}
		}
		$design->AddMain('dbview.tpl');
	}
}

/*
class DbFormMonitorClients extends DbForm {
	public function __construct() {
		DbForm::__construct('monitor_clients');
		$this->fields['client']=array();
		$this->fields['email']=array();
		$this->fields['allow_bad']=array();
		$this->fields['period_mail']=array();
		$this->includesPost =array('dbform_monitoring.tpl');
	}
	public function Process() {
		global $db,$user;
		$this->Get();
		if (isset($this->dbform['allow_bad'])) {
			$this->dbform['period_mail']=round($this->dbform['period_mail']/5)-1;
		}
		$v=DbForm::Process();
		return $v;
	}
	public function Display($form_params = array(),$h2='',$h3='') {
		global $db,$design;
		if ($this->isData('id')) {
			$this->data['period_mail']=$this->data['period_mail']*5+5;
			$design->assign('dbform_f_monitoring',$db->AllRecords('select *,INET_NTOA(ip_int) as ip from monitor_ips where monitor_id='.$this->data['id']));
		}
		DbForm::Display($form_params,$h2,$h3);
	}
}
 */

class DbViewFactory {
	public static function Get($v) {
		if ($v=='internet') return new DbViewTarifsInternet();
		if ($v=='hosting') return new DbViewTarifsHosting();
		if ($v=='price_voip') return new DbViewPriceVoip();
		if ($v=='bill_monthlyadd_reference') return new DbViewBillMonthlyaddReference();
		if ($v=='extra') return new DbViewTarifsExtra();
		if ($v=='itpark') return new DbViewTarifsITPark();
		if ($v=='welltime') return new DbViewTarifsWelltime();
		if ($v=='virtpbx') return new DbViewTarifsVirtpbx();
		if ($v=='wellsystem') return new DbViewTarifsWellSystem();
		if ($v=='8800') return new DbViewTarifs8800();
		if ($v=='sms') return new DbViewTarifsSms();
		return false;
	}
	public static function GetForm($v,$t) {
		if ($v=='internet' && $t=='c') return new DbFormTarifsCollocation();
		if ($v=='internet') return new DbFormTarifsInternet();
		if ($v=='hosting') return new DbFormTarifsHosting();
		if ($v=='price_voip') return new DbFormPriceVoip();
		if ($v=='bill_monthlyadd_reference') return new DbFormBillMonthlyaddReference();
		if ($v=='extra') return new DbFormTarifsExtra();
		if ($v=='itpark') return new DbFormTarifsITPark();
		if ($v=='welltime') return new DbFormTarifsWelltime();
		if ($v=='virtpbx') return new DbFormTarifsVirtpbx();
		if ($v=='wellsystem') return new DbFormTarifsWellSystem();
		if ($v=='8800') return new DbFormTarifs8800();
		if ($v=='sms') return new DbFormTarifsSms();
		return false;
	}
}


class DbViewUsagePhoneRedirConditions extends DbView {
	public function __construct() {
		$this->filters=array();
		$this->SQLFilters=array();
		$this->SQLFilterNames=array();
		$this->SQLFilterGroups=array();
		$this->table='usage_phone_redir_conditions';
		$this->Headers['z']='Time Conditions';
		$this->FieldSets['z']=array(
							'title' => 'Название правила',
							'type' => 'Тип',
							);
		$this->fieldset='z';
		parent::__construct();
	}
}
class DbViewSaleChannels extends DbView {
	public function __construct() {
		$this->filters=array();
		$this->SQLFilters=array();
		$this->SQLFilterNames=array();
		$this->SQLFilterGroups=array();
		$this->table='sale_channels';
		$this->Headers['z']='Каналы продаж';
		$this->FieldSets['z']=array(
							'name' => 'Название',
							'dealer_id' => 'ID дилера',
							'is_agent' => 'Агент',
							'interest' => 'Вознаграждение',
							'courier_name' => 'Курьер',
		);
		$this->fieldset='z';
		$this->SQLQuery = 'select a.*, b.name as courier_name from '.$this->table.' as a LEFT JOIN courier as b ON b.id = a.courier_id';
		parent::__construct();
	}
}
class DbFormSaleChannels extends DbForm{
	public function __construct() {
		DbForm::__construct('sale_channels');
		$this->fields['name']=array();
		$this->fields['dealer_id']=array();
		$this->fields['is_agent']=array('assoc_enum'=>array('0'=>'Нет', '1'=>'Да'));
		$this->fields['interest']=array();
        global $db;
        $couriers = array("0" => "-");
        foreach($db->AllRecords("select id, name from courier where depart='Региональный представитель'") as $o)
        {
            $couriers[$o["id"]] = $o["name"];
        }
		$this->fields['courier_id']=array('assoc_enum' => $couriers);
	}
}
class DbViewTechNets extends DbView {
	public function __construct() {
		$this->filters=array();
		$this->SQLFilters=array();
		$this->SQLFilterNames=array();
		$this->SQLFilterGroups=array();
		$this->table='tech_nets';
		$this->Headers['z']='Сети';
		$this->FieldSets['z']=array(
							'id' => 'ID',
							'net' => 'Сеть',
							);
		$this->fieldset='z';
		parent::__construct();
	}
}
class DbFormTechNets extends DbForm {
	public function __construct() {
		DbForm::__construct('tech_nets');
		$this->fields['id']=array('type'=>'label');
		$this->fields['net']=array();
	}
}
?>
