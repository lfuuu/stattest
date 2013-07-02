<?php
//просмотр списка клиентов с фильтрами и поиском / просмотр информации о конкретном клиенте
class m_clients {
	var $rights=array(
					'clients'		=>array(
						'Работа с клиентами',
							'read,read_filter,read_all,new,edit,restatus,credit,credit_all,edit_tele,sale_channels,file,inn_double,all4net,history_edit',
							'просмотр вообще,просмотр с фильтрами,просмотр всех,создание,редактирование,изменение статуса,установка кредита,установка кредита всем сразу,редактирование для телемаркетинга,редактирование каналов продаж,доступ к файлам,заведение совпадающих ИНН,синхронизация с all4net,редактирование истории'),
				);
	var $actions=array(
					'default'		=> array('clients','read'),
					'search_as'		=> array('clients','read'),
					'edit'			=> array('',''),					//права проверяются потом
					'restatus'		=> array('clients','restatus'),
					'recontact'		=> array('',''),
					'recontact2'	=> array('',''),
					'recontract'	=> array('',''),
					'recontract2'	=> array('',''),
					'edit_pop'		=> array('',''),
					'apply'			=> array('',''),				//собственно редактирование
					'apply_pop'		=> array('',''),
					'mkcontract'	=> array('clients','new'),
					'contract_edit' => array('clients','edit'),
					'chpass'		=> array('clients','edit'),
					'print'			=> array('clients','read'),
					'send'			=> array('clients','read'),
					'new'			=> array('clients','new'),
					'create'		=> array('clients','new'),					//собственно добавление
					'all'			=> array('clients','read_all'),
					'my'			=> array('clients','read_filter'),
					'show'			=> array('clients','read_filter'),
					'credit'		=> array('clients','credit'),
					'sc'			=> array('clients','sale_channels'),
					'sc_edit'		=> array('clients','sale_channels'),
					'files'			=> array('clients','file'),
					'files_report'	=> array('clients','file'),
					'file_put'		=> array('clients','file'),
					'file_get'		=> array('clients','file'),
					'file_send'		=> array('clients','file'),
					'file_del'		=> array('clients','file'),
					'inn'			=> array('clients','edit'),
					'pay_acc'		=> array('clients','edit'),
					'print_yota_contract' => array('clients','file'),
					'rpc_findClient1c'	=> array('clients','new'),
					'rpc_findBank1c'	=> array('clients','new'),
					'view_history'		=> array('clients', 'edit'),

					'p_edit' => array('clients','edit')
				);

	//содержимое левого меню. array(название; действие (для проверки прав доступа); доп. параметры - строкой, начинающейся с & (при необходимости); картиночка ; доп. текст)
	var $menu=array(
//					array('Мои клиенты',			'my'),
//					array('Все клиенты',			'all'),
					array('Новый клиент',			'new'),
					#array('ФизЛицо',				'p_edit'),
					array('Кредит',					'credit'),
					array('',						'show'),	//чтобы пробел не показывался, если read_filter отключен
					array('Телемаркетинг',			'show','&subj=telemarketing'),
					array('Входящие',				'show','&subj=income'),
					array('В стадии переговоров',	'show','&subj=negotiations'),
					array('Тестируемые',			'show','&subj=testing'),
					array('Подключаемые',			'show','&subj=connecting'),
					array('Включенные',				'show','&subj=work'),
					array('Отключенные',			'show','&subj=closed'),
					array('С тех. отказом',			'show','&subj=tech_deny'),
					array('Отказ',					'show','&subj=deny'),
					array('Отключенные за долги',	'show','&subj=debt'),
                    array('Дубликаты',              'show','&subj=double'),
				    array('Мусор',                  'show','&subj=trash'),
				    array('Переезд',                'show','&subj=move'),
				    array('Есть канал',             'show','&subj=already'),
				    array('Отказ/задаток',          'show','&subj=denial'),
					array('Разовые',				'show','&subj=once'),
					array('Резервирование канала',	'show','&subj=reserved'),
					array('Телефония отключена',	'show','&subj=voip_disabled'),
					array('Временно заблокирован',	'show','&subj=blocked'),
					array('',						'sc'),
					array('Каналы продаж',			'sc'),
					array('Отчёт по файлам',		'files_report'),
				);
//###################################################################
	var $statuses;

	function __construct() {
		$this->statuses = ClientCS::$statuses;
	}
	function color_status($status_code){
		if (!isset($this->statuses[$status_code])) return '';
		return $this->statuses[$status_code]['color'];
	}

	function m_clients(){
		global $design;
    /*
		$design->assign('letter','');
    $design->assign('letter_region','any');
		$design->assign('clients_my','');
		$design->assign('search','');
    */
	}

	function Install($p){
		return $this->rights;
	}

	function GetPanel($fixclient){
		global $design,$user;
		$R=array(); $p=0;
		foreach($this->menu as $val){
			if ($val=='') {
				$p++;
				$R[]='';
			} else {
				$act=$this->actions[$val[1]];
				if (access($act[0],$act[1])) $R[]=array($val[0],'module=clients&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
			}
		}
		if (count($R)>$p){
			$design->AddMenu('Клиенты',$R);
		}
	}

	function GetMain($action,$fixclient){

		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'clients_'.$action),$fixclient);
	}

	function clients_search_as($fixclient) {
		global $db,$design,$_RESULT;
		include INCLUDE_PATH."JsHttpRequest.php";
		$JsHttpRequest = new Subsys_JsHttpRequest_Php();
		$JsHttpRequest->setEncoding("KOI8-R");
		if (isset($_POST['query'])) $_POST['search']=$_POST['query'];
		$design->assign('clients',array());
		$this->clients_list(false,5,20);
		$_RESULT=array(
					'data'		=> $design->fetch('clients/as_search.tpl'),
					);
		$design->ProcessEx('errors.tpl');
	}

	function clients_my($fixclient) {
		global $design,$user;
		// запоминаем что дальше всех клиентов надо фильтровать по менеджеру
		session_set('clients_my',$user->_Login);
		$this->clients_headers('my');
		$this->clients_list(true);
		$design->assign('name_of_action', 'Мои клиенты');
		$design->AddMain('clients/main_clients.tpl');
	}

	function client_unfix(){
		// фильтр по менеджеру обнуляем
		// Фильтр по типу клиента обнуляем
		session_set('clients_filter','');
		session_set('clients_my','');
		session_set('clients_client','');

		$GLOBALS['fixclient']="";
        if(get_param_raw("unfix",0)){
            $rf = $_SERVER["HTTP_REFERER"];
            preg_match_all("#(?:(?:\?|&)([^=]+?)=([^&]+?))+#U", $rf, $out, PREG_SET_ORDER);
            $d = array();
            foreach($out as $a)
                $d[$a[1]] = $a[2];

            if(
                    !(
                    ($d["module"] == "clients" && $d["id"]) ||
                    ($d["module"] == "newaccounts" && $d["action"] == "make_1c_bill")
                    )
              ){
                header("Location: ".$rf);
                exit();
            }
        }
	}

	function clients_all($fixclient){
		global $design,$user;
		// запоминаем что дальше надо показывать всех клиентов
		$this->client_unfix();

		$this->clients_headers('all');
		$this->clients_list(true);

		$design->assign('name_of_action','Все клиенты');
		$design->AddMain('clients/main_clients.tpl');
	}

	function get_credit_sum($client) {
		global $db;
		$db->Query("SELECT bill_no,sum FROM bill_bills WHERE client='{$client}' order by bill_date DESC LIMIT 5");
		$s=array();
		$a=array();
		while ($r=$db->NextRecord()) {
			$s[$r['bill_no']]=floatval($r['sum']);
			$v[$r['bill_no']]=floatval($r['sum']);
			$a[]=$r['bill_no'];
		}
		if (!count($a)) return 0;
		$db->Query("select bill_no,sum_usd from bill_payments WHERE client='{$client}' and bill_no IN ('".implode("','",$a)."')");
		$S=0;
		while ($r=$db->NextRecord()) {
			$v[$r['bill_no']]-=$r['sum_usd'];
		}
		foreach ($v as $b=>$val) {
			if ($val>1) {
				$S-=$s[$b];
			} else $S+=$s[$b];
		}
		if ($S<0) $S=0;
		return $S;
	}
	function set_credit($client,$service){
		global $db;
		switch ($service) {
		case 'usage_ip_ports':
			$db->Query("update usage_ip_ports as u LEFT JOIN tarifs_internet as t ON t.id=u.tarif_id set credit_usd=t.pay_month*3 WHERE u.client='{$client}'");
			break;
		case 'usage_voip':
            /*
			$db->Query("select * from usage_voip as u WHERE u.client='{$client}'");
			$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
			foreach ($R as $r) {
				if (preg_match('/V\d+\-\d+\-(\d+)/',$r['tarif'],$m)) $credit=$m[1];
				$db->Query("update usage_voip set credit_usd='{$credit}' where id={$r['id']}");
			}
			*/
            break;
		}
	}
	function clients_credit($fixclient) {
		global $db,$design,$writeoff_services;
		$process=get_param_protected("process",0);
		$sum=get_param_raw("sum");
		$client=get_param_protected("client",'');
		$service=get_param_protected("service",'');
		if ($process) {
	    	if ($client) {
	    		$C=array($client);
	    	} else if (access('clients','credit_all')){
	    		$db->Query("select * from clientsw where client!=''");
	    		$C=array(); while ($c=$db->NextRecord()) $C[]=$c['client'];
			} else {trigger_error("Выберите клиента"); return;}

    		foreach ($C as $client) {
//    			$sum=$this->get_credit_sum($client);
				foreach ($writeoff_services as $w) if (($w==$service) || ($service=="")){
					if ($sum) {
						$db->Query("update $w set credit_usd=".$sum." where client='{$client}'");
					} else {
						$this->set_credit($client,$w);
					}
				}
    		}
			trigger_error("Кредит установлен");
		}
		if ($fixclient) $design->assign('credit_sum',$this->get_credit_sum($fixclient));
		if (!$fixclient && !access('clients','credit_all')) {trigger_error('Выберите клиента'); return; }
		$W=$writeoff_services;
		$W = array_unshift( $W,"");
		$design->assign('services',$W);
		$design->assign('service',$service);
		$design->AddMain('clients/credit.tpl');

	}

	function clients_show($fixclient){
		global $design,$user;
		$subj=get_param_protected("subj");
		if (!$subj) return;
		session_set('clients_filter',$subj);
		$this->clients_headers('show',$subj);

		switch($subj) {
			case 'telemarketing': $design->assign('name_of_action','Телемаркетинг'); break;
			case 'income':		$design->assign('name_of_action','Входящие'); break;
			case 'testing':		$design->assign('name_of_action','Тестируемые клиенты'); break;
			case 'negotiations':	$design->assign('name_of_action','В стадии переговоров'); break;
			case 'connecting':	$design->assign('name_of_action','В стадии подключения'); break;
			case 'work':			$design->assign('name_of_action','Включенные'); break;
			case 'tech_deny':	$design->assign('name_of_action','С тех. отказом'); break;
			case 'closed':		$design->assign('name_of_action','Отключенные'); break;
			case 'deny':			$design->assign('name_of_action','Отказ'); break;
			case 'debt':			$design->assign('name_of_action','Отключен за долги'); break;
            case 'double':   $design->assign('name_of_action','Дубликаты');break;
            case 'trash': $design->assign('name_of_action','Мусор');break;
            case 'move':  $design->assign('name_of_action','Переезд');break;
            case 'already': $design->assign('name_of_action','Есть канал');break;
			case 'denial':  $design->assign('name_of_action','Отказ/задаток');break;
			case 'reserved': $design->assign('name_of_action','Резервирование канала');break;
			case 'voip_disabled': $design->assign('name_of_action', 'Телефония отключена');break;
			case 'blocked': $design->assign('name_of_action', 'Временно заблокирован');break;
			case 'once': $design->assign('name_of_action', 'Разовые');break;
			default: return;
		};

		$this->clients_list(true);
		$design->AddMain('clients/main_clients.tpl');
	}
	function clients_chpass($fixclient) {
		global $db,$design;
		$id=get_param_protected('id','');
		mt_srand();
		$password=substr(md5(md5(md5(mt_rand(0,1000)).mt_rand(0,1000)).mt_rand(0,1000)),0,8);
		if (is_numeric($id)) {
			$db->Query('update clients set password="'.$password.'" where id='.$id);
			trigger_error('Клиенту установлен пароль '.$password);
			return $this->client_view($id);
		}
	}

	function clients_default($fixclient){
		$id=get_param_protected('id','');
		if ($id) {
			$this->client_view($id);
		} else {
			if (access('clients','read_filter')) {
				$this->clients_my($fixclient);
			} else {
				$this->clients_all($fixclient);
			}
		}
	}
	// функция получения заголовка таблицы клиентов
	function clients_headers($action = '',$subj='') {

		global $design,$db,$user;
		$L=array("" => "***нет***");
    $LR = array("any" => "***Любой***");
		$L['1'] = '1';
		for ($i = ord('a');$i<=ord('z');$i++) $L[chr($i)] = chr($i);
		ksort($L);
		$L['*'] = '*';
		$L['@'] = '@';
		$L['!'] = '!';
		$L['+'] = '+';

      foreach($db->AllRecords("select id, name from regions order by if(id = 99, '!!!', name)", "id") as $r => $n)
        $LR[$r] = $n["name"];

        if(($sas = get_param_raw("additional_view", "")) != "") {
            setcookie("stat_addit_search", $sas, time()+60*60*24*30*12*10);
            $_COOKIE["stat_addit_search"]=$sas;
            if(($retPath=get_param_raw("retpath", "")) != ""){
                header("Location: ".$retPath);
                exit();
            }
        }

        if(!isset($_COOKIE["stat_addit_search"])) {
          setcookie("stat_addit_search", "1", time()+60*60*24*30*12*10);
          $_COOKIE["stat_addit_search"]=1;
        }

        $design->assign("view_add_search", $_COOKIE["stat_addit_search"]);

		$design->assign('letters', $L);
    $design->assign('letter_regions', $LR);
		if ($action) $design->assign('action', $action);
		if ($subj) $design->assign('client_subj', $subj);

    $letter=isset($_GET["letter"]) ? $_GET["letter"] : (isset($_SESSION["letter"]) ? $_SESSION["letter"] : "");

    $letter_region=isset($_GET["region"]) ? $_GET["region"] : (isset($_SESSION["letter_region"]) ? $_SESSION["letter_region"] : "any");

    if(!$letter_region){
      $letter_region = "any";
    }else{
      $letter_region1 =  "".((int)$letter_region);
      $letter_region2 =  "".$letter_region;

      if($letter_region1 !=  $letter_region2)
        $letter_region = "any";

    }

    $_SESSION["letter"] = $letter;
    $_SESSION["letter_region"] = $letter_region;

    $design->assign("letter", $letter);
    $design->assign("letter_region", $letter_region);

	}

	function clients_list($move_if_single=false,$smode = '',$limit = ''){
		global $db, $design,$user;

    if(!isset($_SESSION["letter"]))
      $_SESSION["letter"] = "";

    $letter=$_SESSION["letter"];

    if(!isset($_SESSION['letter_region']))
      $_SESSION['letter_region'] = "any";

    $letter_region=$_SESSION['letter_region'];

		$my=get_param_protected('clients_my');
		$filter=get_param_protected('clients_filter');
		$search=get_param_protected('search');
        $search = trim($search);
		$smode=get_param_protected('smode',$smode);

		if(
			array_key_exists('filter_clients_date_from_y', $_POST)
		&& array_key_exists('filter_clients_date_from_m', $_POST)
		&& array_key_exists('filter_clients_date_from_d', $_POST)
		&& array_key_exists('filter_clients_date_to_y', $_POST)
		&& array_key_exists('filter_clients_date_to_m', $_POST)
		&& array_key_exists('filter_clients_date_to_d', $_POST)
		){
			$date_from = param_load_date('filter_clients_date_from_',array('year'=>0,'mon'=>0,'mday'=>0));
			$date_to = param_load_date('filter_clients_date_to_',array('year'=>0,'mon'=>0,'mday'=>0));
			$design->assign('filter_clients_date_from_y',$_POST['filter_clients_date_from_y']);
			$design->assign('filter_clients_date_from_m',$_POST['filter_clients_date_from_m']);
			$design->assign('filter_clients_date_from_d',$_POST['filter_clients_date_from_d']);
			$design->assign('filter_clients_date_to_y',$_POST['filter_clients_date_to_y']);
			$design->assign('filter_clietns_date_to_m',$_POST['filter_clients_date_to_m']);
			$design->assign('filter_clients_date_to_d',$_POST['filter_clients_date_to_d']);
		}else
			$date_from = $date_to = null;

		if ($smode && $smode!=1 && $smode!=5){
			$my = '';
			$letter = '';
		}
		$where="1 ";
		$join="";
		$group='';
		$where_2='';

		if($my!=='')
			$where.="and ((cl.manager='$my') or (cl.support='$my') or (cl.telemarketing='$my')) ";

		if($smode!=5){
			if($filter!=='')
                if($filter == "voip_disabled")
                    $where.="and voip_disabled ";
                else
                    $where.="and cl.status='".$filter."' ";
			else
				$where.="and (cl.status NOT IN ('deny','tech_deny','closed','debt') )";
		}

		if($letter!==''){
            if($letter=='!')
				$where .= " and cl.client in (select client from usage_extra ue inner join tarifs_extra te on ue.tarif_id=te.id and te.status='itpark') ";
            elseif($letter == "+"){
				$where.="and cl.type ='distr' ";
            }else{
				$where.="and cl.client LIKE '{$letter}%' ";
			}
		}

    if($letter_region != "any")
    {
      $region = (int)$letter_region;
      $where .= " and cl.region = '".$region."'";
    }



		if($search!=''){
			$words=explode(' ',$search);
			$where2='';
			$where3='';
			$where4='';
			foreach($words as $word){
				$mask_sum="POW(2,32-SUBSTRING_INDEX(net,'/',-1))";
				$ip_start="INET_ATON(SUBSTRING_INDEX(net,'/',1))";
				$where3.='AND ((INET_ATON("'.$word.'")>='.$ip_start.') AND (INET_ATON("'.$word.'")<'.$ip_start.'+'.$mask_sum.')) ';

				if($smode!=3){
					if (!strstr($word,'*')) $word=$word.'*';
					$word=str_replace('*','%',$word);
				}
				if(substr($word,0,1)=='%'){
					$where2.='AND (node LIKE "'.$word.'") ';
				}else{
					$where2.='AND (node LIKE "(49_) '.$word.'") ';
				}
				//$where3.='or (net LIKE "'.$word.'") ';

				$where4.='AND (address LIKE "%'.$word.'") ';
			}

			if($smode==2){
				$R=array();
				$db->Query($q='
					SELECT
						usage_ip_ports.client
					FROM
						usage_ip_ports
					INNER JOIN
						tech_ports
					ON
						tech_ports.id = usage_ip_ports.port_id
					WHERE
						(port_name="mgts")
					AND
						(1 '.$where2.')
					GROUP BY
						usage_ip_ports.client
				');
				while($r=$db->NextRecord())
					$R[$r[0]]='"'.$r[0].'"';
				$db->Query($q='
					SELECT
						client
					FROM
						usage_ip_ports
					LEFT JOIN
						tech_ports
					ON
						tech_ports.id=usage_ip_ports.port_id
					WHERE
						(tech_ports.port_name="mgts")
					AND
						(1 '.$where2.')
					GROUP BY
						client
				');
				while($r=$db->NextRecord())
					$R[$r[0]]='"'.$r[0].'"';
				$in_c=implode(',',$R);
			}elseif($smode==3){
				$R=array();
				$db->Query($q='
					SELECT
						usage_ip_ports.client
					FROM
						usage_ip_routes
					LEFT JOIN
						usage_ip_ports
					ON
						usage_ip_ports.id = usage_ip_routes.port_id
					WHERE
						INSTR(net,"/")
					AND
						(1 '.$where3.')
					and
						usage_ip_ports.client is not null
					and
						usage_ip_ports.client <> ""
					GROUP BY
						client
				');
				while($r=$db->NextRecord())
					$R[$r[0]]='"'.$r[0].'"';
				$in_c=implode(',',$R);
			}elseif($smode==4){
				$R=array();
				$db->Query('
					SELECT
						client
					FROM
						usage_ip_ports
					WHERE
						(1 '.$where4.')
					GROUP BY
						client
				');
				while($r=$db->NextRecord())
					$R[$r[0]]='"'.$r[0].'"';
				$in_c=implode(',',$R);
			}elseif($smode==6){
				$db->Query($q='
					SELECT
						`cl`.`id`,
						`cl`.`client`
					FROM
						`clients` `cl`
					INNER JOIN
						`client_contacts` `cc`
					ON
						`cc`.`client_id` = `cl`.`id`
					AND
						`cc`.`type`="email"
					AND
						`cc`.`is_active`=1
					AND
						trim(`cc`.`data`) = "'.$search.'"
					AND
						`cl`.`client`<>""
					AND
						`cl`.`client` IS NOT NULL
				');

				$in_c = '';
				$cnt = 0;
				while($row = $db->NextRecord()){
					$in_c .= "'".$row['client']."',";
					$cnt++;
				}
				if(!$cnt)
					return;
				else
				$in_c = substr($in_c, 0, strlen($in_c)-1);
			}elseif($smode==7){
				$cls = $db->AllRecords($q='
					SELECT `client` FROM `usage_voip` `uv` WHERE `uv`.`e164` = "'.($search).'" ORDER BY `actual_from` DESC LIMIT 1
				',null,MYSQL_ASSOC);
				if(count($cls))
					$in_c = "'".$cls[0]['client']."'";
				else
					$in_c = "''";
			}elseif($smode==8){
				$cls = $db->AllRecords(
                        $q=' SELECT `client` FROM `domains` WHERE `domain` = "'.($search).'" AND now() BETWEEN `actual_from` AND `actual_to` ',null,MYSQL_ASSOC);

				$in_c = "'".$cls[0]['client']."'";
			}elseif($smode==9){
                $in_c = "";
				$cls = $db->AllRecords($q='
					SELECT `client` FROM `clients` WHERE `inn` = "'.($search).'"
				',null,MYSQL_ASSOC);

                if($cls)
                    $in_c = "'".$cls[0]['client']."'";
			}
			if($smode==1 || $smode==4 || $smode==5){
				if($smode==4 && $in_c){
					$where='('.$where.' AND cl.client IN ('.$in_c.')) OR (1 ';
				};
				foreach($words as $word){
					if(!strstr($word,'*'))
						$word='*'.$word.'*';
					$word = str_replace('*','%',$word);
					$lword = 'LIKE ("'.$word.'")'; //LCASE
					if($smode==1){
						$where.='and ('.
								'(cl.client '.$lword.') OR '. //LCASE
								'(cl.company '.$lword.') OR '.
								'(cl.company_full '.$lword.') OR '.
//								'(phone '.$lword.') OR '.
//								'(contact '.$lword.') OR '.
								'(cl.support '.$lword.') OR '.
								'(cl.telemarketing '.$lword.') OR '.
								'(cl.site_req_no '.$lword.') OR '.
								'(cl.manager '.$lword.')'.
								') ';
					}elseif($smode==4){
						$where.='and ('.
								'(cl.address_post_real '.$lword.') OR '.
								'(cl.address_jur '.$lword.') OR '.
								'(cl.address_post '.$lword.')'.
								') ';
					}elseif($smode==5){
						$where.='and ('.
								'(cl.client '.$lword.')'.
								' OR (cl.company '.$lword.')'.
								' OR (cl.company_full '.$lword.')'.
								' OR (cl.site_req_no '.$lword.')';
						$where.=' OR (cl.id '.$lword.'))';
					}
				}
				if($smode==4 && $in_c){
					$where.=') ';
				}
			}else{
				if($in_c){
					$where = '(cl.client IN ('.$in_c.')) ';
				} else $where = '0 ';
			}
		}

		$flag_single = true;
		if(!$smode && $filter && $date_from && $date_to){
			$flag_single = false;
			$query = "
				select
					cs.id_client
				from
					client_statuses cs
				inner join
					clients c
				on
					c.id = cs.id_client
				where
					cs.ts between '".date('Y-m-d',$date_from)."' and '".(date('Y-m-d',$date_to).' 23:59:59')."'
				and
					cs.status = c.status
				and
					c.status='".addcslashes($filter,"\\\\'")."'
				group by
					c.client
			";
			$clients = $db->AllRecords($query,null,MYSQL_ASSOC);
			if(count($clients)>0){
				$where .= 'and cl.id in (';
				foreach($clients as $client){
					$where .= $client['id_client'].',';
				}
				$where = substr($where, 0, strlen($where)-1).")";
			}

		}
		/*$query = "
			SELECT
				(select date(ts) from client_statuses where id_client=clients.id order by client_statuses.id asc limit 1) as date_zayavka,
				clients.*,
				clients.client as client
			FROM
				clients
			WHERE ";*/

		$query = "
			select sql_calc_found_rows
				cl.*,
				date(cls.ts) date_zayavka
			from clients cl
			left join client_statuses cls on cl.id = cls.id_client
			and
				( cls.id is null and
					cls.id = (select min(id) from client_statuses where id_client=cl.id)
				)
			where
				";
		// если нет никаких ограничений, то печатаем только список букв

		if($where==="1 and (cl.status NOT IN ('deny','tech_deny','closed','debt') )")
			return;
		$query.=$where;

		// Сортировка результата по указанному полю
		$so = get_param_integer ('so', 1);
		$order = $so ? 'asc' : 'desc';
		switch($sort=get_param_integer('sort',1)){
			case 2: $order='cl.company '.$order; break;
			case 3: $order='cl.currency '.$order; break;
			case 4: $order='cl.sale_channel '.$order; break;
			case 5: $order='cl.manager '.$order; break;
			case 6: $order='cl.support '.$order; break;
			case 7: $order='cl.telemarketing '.$order; break;
			case 8: $order='1 '.$order; break;
			default: $order='cl.client '.$order; break;	//=1
		}
		$design->assign('sort',$sort);
		$design->assign('so',$so);
		$query.="ORDER BY ".$order;

        $page = get_param_integer("page", 1);
        $recPerPage = 50;
        $limit = (($page-1)*$recPerPage).",".$recPerPage;

		if($limit)
			$query.=" LIMIT ".$limit;

		$SC = $db->AllRecords('select * from sale_channels','id');

		$db->Query($query);

		$R=array();
		while($r=$db->NextRecord()){
			if(isset($this->statuses[$r['status']])){
				$r['status_name']=$this->statuses[$r['status']]['name'];
				$r['status_color']=$this->statuses[$r['status']]['color'];
			}
			if(isset($SC[$r['sale_channel']]))
				$r['sale_channel'] = $SC[$r['sale_channel']]['name'];
			$R[]=$r;
		}

        util::pager("cl");

        // posible bill
        if(!count($R) && strlen($search) > 4){
            if($db->GetRow("select bill_no from `newbills` where bill_no = '".mysql_escape_string($search)."'")){
                Header("Location: ./?module=newaccounts&action=search&search=".urlencode($search));
                exit;
            }
        }


		if($move_if_single && (count($R)==1) && $flag_single){
			Header("Location: ?module=clients&id=".($R[0]['client'] ? $R[0]['client'] :$R[0]['id']));
			exit;
		}
		$design->assign('clients',$R);
		//$design->assign('letter',$letter);
    //$design->assign('letter_region',$letter_region);
		$design->assign('clients_my',$my);
		$design->assign('search',$search);
		$design->assign('clients_m_chose',array($my,$letter, $letter_region, $filter));
	}
	function clients_send(){
		global $design,$db,$_SERVER;
		if (!($id=get_param_integer('id'))) return;

		$c = $db->GetRow('select * from client_contracts where id="'.intval($id).'"');
		//if (!($r = $db->GetRow('select * from clients where id='.$c['client_id'].' limit 1'))) {trigger_error('Такого клиента не существует');return;}

        $email = "";
        if (($em = $db->GetRow('SELECT data FROM `client_contacts` where client_id = '.$c["client_id"].' and type = "email" and is_official = 1 order by id desc limit 1')))
        {
            $email = $em["data"];
        }

		$p=data_encode($c['id'].'-'.$c['client_id']);
		$adr="https://lk.mcn.ru/document?code=".str_replace('=','%%3D',$p);

		$body="Уважаемые Господа!" . "<br><br>" . "Отправляем Вам договор:" . "<br>";
		$body.="<a href=\"".$adr."\">".$adr."</a><br><br>";

        //echo "<html><meta http-equiv=\"refresh\" content=\"0;url=http://85.94.32.194/welltime/?module=com_agent_panel&set_action=new_msg&subject=".rawurlencode (iconv("koi8-r", "utf-8", "MCN - договор"))."&new_msg=".rawurlencode (iconv("koi8-r", "utf-8", $body)).(!empty($email) ? "&to=".$email: "" )."\"/><body></body></html>";
        echo "<html><meta http-equiv=\"refresh\" content=\"0;url=http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&subject=".rawurlencode (iconv("koi8-r", "utf-8", "MCN - договор"))."&new_msg=".rawurlencode (iconv("koi8-r", "utf-8", $body)).(!empty($email) ? "&to=".$email: "" )."\"/><body></body></html>";

		$design->ProcessEx('empty.tpl');
	}

	static function fix_contract($clientId, $contractId)
	{
		global $db, $design;
		$file = 'contracts/'.$clientId.'-'.$contractId.'.html';
		$fileTemplate = 'contracts/'.$clientId.'-'.$contractId.'-tpl.html';

		if(file_exists(STORE_PATH.$fileTemplate)) //already
			return true;

		if (!($r = $db->GetRow('select * from clients where (id="'.intval($clientId).'") limit 1'))) {
			trigger_error('Такого клиента не существует');
			return;
		}

		if (!($c = $db->GetRow('select * from client_contracts where id="'.intval($contractId).'"'))) {
			trigger_error('Такого договора не существует');
			return;
		}

		ClientCS::Fetch($r,$c);


		self::contract_fix_static_parts_of_template(file_get_contents(STORE_PATH.$file), $r["client"], $r["id"]);
		self::contract_apply_firma($r["firma"]);
    self::contract_apply_support_phone($r["region"]);

		ob_start();
		$design->ProcessEx(STORE_PATH.$file);
		$c = ob_get_clean();
		//echo $c;
		if(copy(STORE_PATH.$file,STORE_PATH.$fileTemplate))
		{
			file_put_contents(STORE_PATH.$file, $c);
			return true;
		}


		return false;
	}

	function clients_print($fixclient,$default_data=''){
		global $design,$db;

		if (!($id=get_param_integer('id',$fixclient))) return;

		$data=get_param_raw('data',$default_data);
		if ($data=='contract') {
			$c = $db->GetRow('select * from client_contracts where id="'.intval($id).'"');
			$id = $c['client_id'];
		} else
			$c = null;

		if (!($r = $db->GetRow('select * from clients where (id="'.$id.'") limit 1'))) {
			trigger_error('Такого клиента не существует');
			return;
		}

        if ($data=='contract') {

            $file = 'contracts/'.$r['id'].'-'.$c['id'].'.html';
            $fileTemplate = 'contracts/'.$r['id'].'-'.$c['id'].'-tpl.html';

            if(file_exists(STORE_PATH.$fileTemplate)) {
            	echo file_get_contents(STORE_PATH.$file);
            	exit();
            }else{
            	$this->fix_contract($r['id'], $c['id']);
            }

            if (!file_exists(STORE_PATH.$fileTemplate)) {
            	echo "Ошибка";
            	exit();
            }


            echo file_get_contents(STORE_PATH.$file);

        } else {

        	ClientCS::Fetch($r,$c);

#			header('Content-Type: application/ms-word');
#			header('Content-Disposition: attachment; filename="document.doc"');
#			header("Pragma: ");
#			header("Cache-Control: ");
#			$design->ProcessEx('clients/test.tpl.html');
			$c = $design->fetch('../store/acts/envelope.tpl');
            if(stripos($_SERVER["HTTP_USER_AGENT"], "FireFox") !== false)
            {
                $c = str_replace("padding-top: 22cm;", "padding-top: 24cm;", $c);
                if(get_param_raw("alone", "") == "true")
                {
                    //
                }else{
                    $c = str_replace("padding-top: 24cm;", "padding-top: 26cm;", $c);
                }

            }
            echo $c;
			exit();
		}
	}
	function clients_contract_edit() {
		global $design,$db;

		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$contract = $db->GetRow('select * from client_contracts where id="'.intval($id).'"');
		$client = $db->GetRow('select * from clients where (id="'.$contract['client_id'].'") limit 1');
		$design->assign('contract',$contract);
		$design->assign('client',$client);
		$design->assign('content',ClientCS::getContractTemplate($client['id'].'-'.$contract['id']));

		$design->display('pop_header.tpl');
		$design->display('clients/contract_edit.tpl');
		$design->display('pop_footer.tpl');
		$design->ProcessEx('errors.tpl');
	}
	function client_view($id,$show_edit = 0,$design_echo = 1){

		global
			$design,
			$db,
			$module_tt,
			$module_users, $user;

    $voip = new VoipStatus;
    $voip->loadClient($id);
    $voip_counters = $voip->loadVoipCounters();
    $voip->showCountersWarning();
    $design->assign('voip_counters',$voip_counters);

		if(get_param_raw("contract_open", "") !== "")
			$design->assign('contract_open',true);

		if(is_numeric($id)){
			$q='(clients.id="'.$id.'")';
		}else
			$q='(clients.client="'.$id.'")';


		$r = $db->GetRow('
				select
					clients.*,
					uA.name as manager_name,
					uA.color as manager_color,
					uB.name as support_name,
					uB.color as support_color,
					cl.client prev_r_cl
				from
					clients
				LEFT JOIN  user_users as uA  ON uA.user=clients.manager
				LEFT JOIN  user_users as uB  ON uB.user=clients.support
				left join  clients cl on cl.id = clients.previous_reincarnation
				where
					'.$q.'
				limit 1
		');



		if(!$r){
			trigger_error('Такого клиента не существует');
			return;
		}


        if(access("clients", "read_multy"))
                if($r["type"] != "multi"){
                trigger_error('Доступ к клиенту ограничен');
                return;
            }

		if(strrpos($r['client'],'/')!==false){
			$cl_main_card = substr($r['client'],0,-2);
			$design->assign('card_type','addition');
		}else{
			$cl_main_card = $r['client'];
			$design->assign('card_type','main');
		}


		$_cards_sel = "select id,client from clients where client<>'' and client = '".$cl_main_card."' or client like '".$cl_main_card."/%' order by client";
		$_cards = $db->AllRecords($_cards_sel,null,MYSQL_ASSOC);
		$design->assign('_cards',$_cards);
		$design->assign('all_cls',$db->AllRecords("select id,client from clients where client<>'' order by client",null,MYSQL_ASSOC));

		$r['status_name'] = (isset(ClientCS::$statuses[$r['status']]) ? ClientCS::$statuses[$r['status']]['name'] : $r['status']);
		$r['status_color'] = (isset(ClientCS::$statuses[$r['status']]) ? ClientCS::$statuses[$r['status']]['color'] : '');
    $r["price_type"] = $r["price_type"] ? $r["price_type"] : ClientCS::GetIdByName("price_type", "Розница");
    $design->assign('user_flag_statusbox',$user->Flag('statusbox'));

		$design->assign('fixclient',$id);
		$GLOBALS['fixclient'] = $id;
		$design->assign('request_uri',str_replace('&id='.$id,'',$_SERVER['REQUEST_URI']));

		if(isset($module_tt))
			$module_tt->get_counters($id);

		$this->statuses[$r['status']]['selected'] = ' selected';
		$design->assign_by_ref('statuses',$this->statuses);
		$cs = new ClientCS($r['id']);

		$design->assign('templates',ClientCS::contract_listTemplates());


		if(!$show_edit){
			$design->assign('contacts',$cs->GetContacts());
			$d = $cs->GetContracts();
			foreach ($d as $k=>$v){
				$p = data_encode($v['id'].'-'.$v['client_id']);
				$d[$k]['link']=PROTOCOL_STRING.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/view.php?code='.$p;
			}
			$design->assign('contracts',$d);
			$contract_last = 0;
			foreach($d as $k=>$ctr)
				if($ctr['is_active'])
					$contract_last = $k;

			$design->assign('contract_last',$contract_last);
			if(count($d))
				$design->assign('contract',$d[count($d)-1]);
			$design->assign('contact',$cs->GetContact(false));

			$r['comment'] = $cs->GetLastComment();
			$r['data_cs'] = $cs->GetAllStatuses();

			$design->assign('cfiles',count($cs->GetFiles()));

			if($design_echo){
				$design->AddMain('clients/main_client.htm');
			}

			if($r['client']){
				$design->assign('is_secondary_output',1);
				$GLOBALS['module_tt']->showTroubleList(1,'client',$r['client']);
				$GLOBALS['module_services']->services_in_view($r['client']);
				$GLOBALS['module_services']->services_co_view($r['client']);
				$GLOBALS['module_services']->services_ppp_view($r['client']);
				$GLOBALS['module_services']->services_vo_view($r['client']);
				$GLOBALS['module_routers']->routers_d_list($r['client'],1);
				//$GLOBALS['module_routers']->routers_d_list($r['client']);
				$GLOBALS['module_services']->services_em_view($r['client']);
				$GLOBALS['module_services']->services_ex_view($r['client']);
				$GLOBALS['module_services']->services_it_view($r['client']);
				$GLOBALS['module_services']->services_welltime_view($r['client']);
				$GLOBALS['module_services']->services_wellsystem_view($r['client']);
				$GLOBALS['module_services']->services_ad_view($r['client']);
				$design->assign('log_company', ClientCS::getClientLog($r["id"], array("company_name")));
			}

		}else{
			$design->assign('log', ClientCS::getClientLog($r["id"]));

			$design->assign('selected_channel', $r['sale_channel']);
            $design->assign("l_metro", ClientCS::GetMetroList());
            $design->assign("sale_channels", ClientCS::GetSaleChannelsList());

			$R=array();
			$module_users->d_users_get($R,'telemarketing');
			if(isset($R[$r['telemarketing']]))
				$R[$r['telemarketing']]['selected']=' selected';

			$design->assign('users_telemarketing',$R);

			$R=array();
			$module_users->d_users_get($R,'manager');
			if(isset($R[$r['manager']]))
				$R[$r['manager']]['selected']=' selected';
			$design->assign('users_manager',$R);

			$R=array();
			$module_users->d_users_get($R,'support');
			if(isset($R[$r['support']]))
				$R[$r['support']]['selected']=' selected';
			$design->assign('users_support',$R);

			$design->assign(
				'inn',
				$db->AllRecords('
					select
						L.*,
						U.user
					from
						client_inn as L
					left join
						user_users as U
					ON
						U.id = L.user_id
					where
						L.client_id = '.$r['id'].'
					order by
						ts desc
				'));

			$design->assign(
				'pay_acc',
				$db->AllRecords('
					select
						L.*,
						U.user
					from
						client_pay_acc as L
					left join
						user_users as U
					ON
						U.id = L.who
					where
						L.client_id = '.$r['id'].'
					order by
						date desc
				'));

            $design->assign("l_price_type", ClientCS::GetPriceTypeList($r["price_type"]));

			if($design_echo)
			{
				$design->assign("history_flags", $this->get_history_flags($r['id']));
				$design->AddMain('clients/main_edit.tpl');
			}
		}

		$design->assign('client',$r);
        $design->assign('region_name', $db->GetValue('select `name` from regions where id='.intval($r['region'])) );
		session_set('clients_client',$r['client']);
	}
	function clients_new() {
		global $design, $db,$user;
		$design->assign('mode_new',1);

		$R=array();
		$GLOBALS['module_users']->d_users_get($R,'manager');
		if(isset($R[$user->Get('user')]))
			$R[$user->Get('user')]['selected']=' selected';
		$design->assign('users_manager',$R);

		$R=array();
		$GLOBALS['module_users']->d_users_get($R,'telemarketing');
		if(isset($R[$user->Get('user')]))
			$R[$user->Get('user')]['selected']=' selected';
		$design->assign('users_telemarketing',$R);

		$R=array();
		$GLOBALS['module_users']->d_users_get($R,'support');
		if(isset($R[$user->Get('user')]))
			$R[$user->Get('user')]['selected']=' selected';
		$design->assign('users_support',$R);
        $design->assign("client", array(
                    "client"=>"idNNNN",
                    "credit"=>-1,
                    "firma" => "mcn_telekom",
                    "price_type" => ClientCS::GetIdByName("price_type", "Розница"),
                    "password" => substr(md5(time()+rand(1,1000)*rand(10000,10000)), 3, 8),
                    "voip_credit_limit_day" => 1000
                    ));

        $design->assign("l_price_type", ClientCS::GetPriceTypeList());
        $design->assign("l_metro", ClientCS::GetMetroList());
        $design->assign("sale_channels", ClientCS::GetSaleChannelsList());
        $design->assign('regions',$db->AllRecords('select * from regions order by id desc', 'id'));

        $design->assign("history_flags", $this->get_history_flags(0));

		$design->AddMain('clients/main_edit.tpl');
	}
	function clients_edit_pop($v){ $this->clients_edit($v,true); exit; }
	function clients_edit($v,$pop = false) {
		global $design, $db;


        /*
        $s = file_get_contents("/tmp/statSaveOrder2010-11-12_18:53:50.5659");
        $s = unserialize($s);

		require_once INCLUDE_PATH.'1c_integration.php';
        printdbgu($s);
        $r = _1c\SoapHandler::statSaveOrder($s);
        printdbgu($r);

        exit();
        */

		if(!($id=get_param_protected('id')))
			return;
		if($this->check_tele($id)==0)
			return;

		$cli = $db->GetRow("select client from clients where id=".(int)$id);
		if($cli['client'] == 'pid'.$id){
			header('Location: ?module=clients&action=p_edit&pid='.$id);
			exit();
		}

		$design->assign('hl',get_param_protected('hl'));
        $design->assign('regions',$db->AllRecords('select * from regions', 'id'));
		$design->assign("history_flags", $this->get_history_flags($id));

		if($pop){
			$design->assign('form_action','apply_pop');
			$design->display('pop_header.tpl');
			$this->client_view($id,1,0);
			$design->display('clients/main_edit.tpl');
			$design->display('pop_footer.tpl');
		} else {
			$this->client_view($id,1);
		}
	}
	function clients_apply_pop($v){
		$this->clients_apply($v,true);
		exit;
	}
	function clients_apply($v,$pop = false){
		global $design,$db,$user;
		$id=get_param_protected('id');
		if(!$id)
			return;
		if($this->check_tele($id)==0)
			return;

		$C = new ClientCS(get_param_protected('id'),true);

		if(isset($_POST['cl_cards_operations'])){ // привязать к истории
			$cli = $db->GetRow("select * from clients where id=".((int)$_POST['id']));
			if(
				isset($_POST['previous_reincarnation'])
			&& $cli['previous_reincarnation'] <> $_POST['previous_reincarnation']
			&& $_POST['previous_reincarnation'] <> $_POST['id']
			&& !( !$cli['previous_reincarnation'] && !$_POST['previous_reincarnation'])
			){
				if(clCards\setParent($db, $user, $_POST['previous_reincarnation'], $cli['client']))
					trigger_error("Предыдущие реквизиты успешно установлены");
				else
					trigger_error("Не удалось установить предыдущие реквизиты");
			}
			if($_POST['move_usages'] && $_POST['move_usages']<>$_POST['id'] && $user->HasPrivelege('clients','moveUsages')){
				if(clCards\moveUsages($db, $user, $_POST['move_usages'], $_POST['client']))
					trigger_error("Услуги перенесены");
				else
					trigger_error("Перенести услуги не удалось");
			}
			return false;
		}

		$inn = $C->F['inn'];
		$r = $db->getRow('select inn from clients where id='.$C->F['id']);
		$inn2 = $r['inn'];
		$dbl = 0;

		$cl_curcard = $C->F['client'];
		if(($tmp = strrpos($C->F['client'], '/'))!==false)
			$cl_main_card = substr($C->F['client'],0, $tmp);
		else
			$cl_main_card = $C->F['client'];


        //inn в карточке
		if( $inn!=$inn2 &&
			$r = $db->getRow('select client from clients where inn="'.$inn.'" and client not like "'.addcslashes($cl_main_card,"\\'").'%"')
		)
			$dbl = $r['client'];

        //в дополнительных inn
		if(
				$inn!=$inn2
			&&
				!$dbl
			&&
				(
					$r = $db->getRow('
						select
							client
						from
							clients
						inner join
							client_inn
						on
							client_inn.client_id = clients.id
						and
							is_active = 1
						where
							client_inn.inn = "'.$inn.'"
						and
							clients.client not like "'.addcslashes($cl_main_card,"\\'").'%"
					')
				)
		)
        $dbl = $r['client'];
        $design->assign('regions',$db->AllRecords('select * from regions', 'id'));

        if(!access('clients','inn_double') && $dbl){
			trigger_error('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Добавление невозможно');
		}else{
			if($dbl)
				trigger_error('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Имейте в виду');
			if($C->Apply()){
				clCards\SyncAdditionCards($db, $cl_main_card);

                if (!Sync1C::getClient()->saveClientCards($cl_main_card))
                {
                    trigger_error("Не удалось синхронизировать клиента с 1С");
                }

				if($pop){
					$design->display('pop_header.tpl');
					$design->display('reload_parent.tpl');
					$design->display('pop_footer.tpl');
				}else{
					$this->client_view($id,1);
				}
				return true;
			}else{
				trigger_error("Клиент с таким кодом уже есть.");
			}
		}
		$design->assign('client',$C->F);
		if($pop){
			$design->assign('form_action','apply_pop');
			$design->display('pop_header.tpl');
			$design->assign("history_flags", $this->get_history_flags($id));
			$design->display('clients/main_edit.tpl');
			$design->display('pop_footer.tpl');
		}else{
			$design->AddMain('clients/main_edit.tpl');
		}
		return false;
	}
	function clients_mkcontract($client){
		global $db;
		if(($tmp = strrpos($client, '/'))!==false)
			$cl_main_card = substr($client,0, $tmp);
		else
			$cl_main_card = $client;

		$q = "
			select
				cl.client
			from
				clients cl
			where
				cl.client rlike '^".$cl_main_card."/[0-9a-zA-Z]$'
			order by
				cl.client desc
			limit 1
		";
		$row = $db->getRow($q);

		if(!$row){
			$nc = $cl_main_card.'/2';
		}else{
			$last_pf = substr($row['client'],-1);
			if($last_pf == '9')
				$nc = $cl_main_card.'/a';
			elseif($last_pf == 'z')
				$nc = $cl_main_card.'/A';
			elseif($last_pf == 'Z'){
				trigger_error("Количество договоров клиента достигло максимального кол-ва");
				return;
			}else
				$nc = $cl_main_card.'/'.chr(ord($last_pf)+1);
		}

		# client_contacts
		$db->Query('start transaction');
		$q = "
			insert into clients (
				client,
				password,
				password_type,
				company,
				comment,
				address_jur,
				status,
				usd_rate_percent,
				company_full,
				address_post,
				address_post_real,
				type,
				manager,
				support,
				login,
				inn,
				kpp,
				bik,
				bank_properties,
				signer_name,
				signer_position,
				signer_nameV,
				firma,
				currency,
				currency_bill,
				stamp,
				nal,
				telemarketing,
				sale_channel,
				uid,
				site_req_no,
				signer_positionV,
				hid_rtsaldo_date,
				hid_rtsaldo_RUR,
				hid_rtsaldo_USD,
				credit,
				user_impersonate,
				address_connect,
				phone_connect,
				id_all4net,
				dealer_comment,
				form_type,
				metro_id,
				payment_comment,
				bank_city,
				bank_name,
				pay_acc,
				corr_acc
			) select
				'".$nc."',
				password,
				password_type,
				company,
				comment,
				address_jur,
				status,
				usd_rate_percent,
				company_full,
				address_post,
				address_post_real,
				type,
				manager,
				support,
				login,
				inn,
				kpp,
				bik,
				bank_properties,
				signer_name,
				signer_position,
				signer_nameV,
				firma,
				currency,
				currency_bill,
				stamp,
				nal,
				telemarketing,
				sale_channel,
				uid,
				site_req_no,
				signer_positionV,
				hid_rtsaldo_date,
				hid_rtsaldo_RUR,
				hid_rtsaldo_USD,
				credit,
				user_impersonate,
				address_connect,
				phone_connect,
				id_all4net,
				dealer_comment,
				form_type,
				metro_id,
				payment_comment,
				bank_city,
				bank_name,
				pay_acc,
				corr_acc
			from
				clients
			where
				client = '".$cl_main_card."'
			limit 1
		";
		$db->Query($q);
		$id = mysql_insert_id();
		$main_id = $db->getRow("select id from clients where client='".$cl_main_card."' limit 1");
		$q = "
			insert into client_contacts (
				client_id,
				type,
				data,
				user_id,
				ts,
				comment,
				is_active,
				is_official
			) select
				'".$id."',
				type,
				data,
				user_id,
				ts,
				comment,
				is_active,
				is_official
			from
				client_contacts
			where
				client_id=".$main_id['id'];
		$db->Query($q);
		$db->Query('commit');

        if (!Sync1C::getClient()->saveClientCard($id))
        {
            trigger_error("Не удалось синхронизировать клиента с 1С");
        }

		Header("Location: ?module=clients&id=".$id);
		exit();
	}
	function clients_restatus() {
		global $design;
		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$status=get_param_protected('status');
		$comment=get_param_protected('comment');
		$cs=new ClientCS($id);
		$cs->Add($status,$comment);
        voipNumbers::check();
		$this->client_view($id);
	}
	function clients_files($fixclient) {
		global $db,$design;

        $cId = get_param_integer("cid", 0);

		$client = ClientCS::FetchClient($cId ? $cId : $fixclient);
		if ($this->check_tele($client['id'])==0) return;
		$cs=new ClientCS($client['id']);
		$d = $cs->GetFiles();
		$design->assign('files',$d);
		$design->assign('file_last',count($d)-1);
		$design->AddMain('clients/files.tpl');
	}
	function clients_files_report() {
		global $db,$design;
		$manager = get_param_protected('manager');
		$def=getdate();
		$from=param_load_date('from_',$def);
		$to=param_load_date('to_',$def);
		$def['mday']=1; $cur_from=param_load_date('cur_from_',$def);
		$def['mday']=31; $cur_to=param_load_date('cur_to_',$def);
		$def['mon']--; if ($def['mon']==0) {$def['mon']=12; $def['year']--; }
		$def['mday']=1; $prev_from=param_load_date('prev_from_',$def);
		$def['mday']=31; $prev_to=param_load_date('prev_to_',$def);
		$R=array(); $GLOBALS['module_users']->d_users_get($R,'manager');
		$design->assign('users',$R);
		$design->assign('manager',$manager);

		$R = $db->AllRecords('select client_files.*,clients.client as client_client,clients.company as client_company,user_users.user as user,clients.manager as client_manager'.
								' FROM client_files'.
								' INNER JOIN clients ON clients.id=client_files.client_id'.
								' LEFT JOIN user_users ON user_users.id=client_files.user_id'.
								' WHERE client_files.ts>=FROM_UNIXTIME('.$from.') AND client_files.ts<=FROM_UNIXTIME('.$to.')'.
								(!$manager?'':' AND clients.manager="'.$manager.'"').
								' order by id');
		$i = 0; foreach ($R as &$r) $r['no'] = ++$i; unset($r);
		$design->assign('files',$R);
		$design->AddMain('clients/files_report.tpl');
	}
	function clients_file_put($fixclient) {
		global $design;
		$client = ClientCS::FetchClient($fixclient);
		if ($this->check_tele($client['id'])==0) return;

		$cs=new ClientCS($client['id']);
		$cs->AddFile(get_param_protected('name'),get_param_protected('comment'));
		if ($design->ProcessEx('errors.tpl')) header('Location: ?module=clients&action=files');
	}
	function clients_file_get($fixclient) {
		global $design;
		$cid = get_param_integer('cid');
		if ($this->check_tele($cid)==0) return;
		$cs=new ClientCS($cid);
		if ($f = $cs->GetFile(get_param_protected('id'))) {
			header('Content-Type: archive/zip');
			header("Pragma: ");
			header("Cache-Control: ");
		    header('Content-Transfer-Encoding: binary');
			header('Content-Disposition: attachment; filename="'.iconv("KOI8-R","CP1251",$f['name']).'"');
			header("Content-Length: " . filesize($f['path']));
			readfile($f['path']);
			$design->ProcessEx();
		}
	}

    function clients_file_send($fixclient)
    {
        global $design, $db;

        $clientId = get_param_protected("cid");
        $id = get_param_protected("id");

        $design->assign("emails", $ee = $db->AllRecordsAssoc("select data from client_contacts where client_id = '".$clientId."' and type='email' and is_official and is_active", "data", "data"));


		$cs=new ClientCS($clientId);
		$f = $cs->GetFile($id);

        if(!$f) return;

        $design->assign("file_name", $f['name']);
        $design->assign("file_name_send", iconv("koi8-r", "utf8", $f['name']));
        $design->assign("file_content", base64_encode(file_get_contents($f["path"])));
        $design->assign("msg_session", md5(rand()+time()));
        $design->assign("file_mime", $this->getMime($f["name"]));
        $design->AddMain("clients/file_send.tpl");
    }

    function getMime($name)
    {
        $name = strtolower($name);

        foreach(
                array(
                    "doc" => "application/msword",
                    "pdf" => "application/pdf",
                    "gif" => "image/gif",
                    "tif" => "image/tiff",
                    "tiff" => "image/tiff",
                    "jpeg" => "image/jpeg",
                    "jpg" => "image/jpeg",
                    "jpe" => "image/jpeg",
                    "htm" => "text/html",
                    "html" => "text/html",
                    "txt" => "text/plain",
                    "zip" => "application/zip",
                    "rar" => "application/rar",
                    "xls" => "application/vnd.ms-excel",
                    "ppt" => "application/vnd.ms-powerpoint"
                    ) as $ext => $mime)
        {
            if(strpos($name, ".".$ext))
            {
                return $mime;
            }
        }

        return "text/plain";
    }

	function clients_file_del($fixclient) {
		global $design;
		$client = ClientCS::FetchClient($fixclient);
		if ($this->check_tele($client['id'])==0) return;
		$cs=new ClientCS($client['id']);
		$cs->DeleteFile(get_param_protected('id'));
		if ($design->ProcessEx('errors.tpl')) header('Location: ?module=clients&action=files');
	}
	function clients_recontact() {
		global $design;
		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$cs=new ClientCS($id);
		$cs->AddContact(get_param_protected('type'),get_param_protected('data'),get_param_protected('comment'),get_param_protected('official')?1:0);
		$design->assign('contact_open',true);
		$this->client_view($id);
	}
	function clients_inn() {
		global $design,$db,$user;
		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$cid = get_param_protected('cid','');
		if ($cid=='') {
			$inn=get_param_protected('inn');
            $inn = trim($inn);
			$comment=get_param_protected('comment');
			$dbl = 0;
			if ($r = $db->getRow('select client from clients where inn="'.$inn.'"')) $dbl = $r['client'];
			if (!$dbl && ($r = $db->getRow('select client from clients inner join client_inn on client_inn.client_id=clients.id and is_active=1 where client_inn.inn="'.$inn.'"'))) $dbl = $r['client'];
			if (access('clients','inn_double') || !$dbl) {
				$db->QueryInsert('client_inn',array('ts'=>array('NOW()'),'client_id'=>$id,'user_id'=>$user->Get('id'),'inn'=>$inn,'comment'=>$comment));
				if ($dbl) {
					trigger_error('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Имейте в виду');
					return $this->client_view($id,1);
				}
			} else {
				trigger_error('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Добавление невозможно');
				return $this->client_view($id,1);
			}
		} else {
			$db->Query('update client_inn set is_active='.get_param_integer('act').' where client_id='.$id.' and id='.intval($cid));
		}
		$design->ProcessEx();
		Header("Location: ?module=clients&id=".$id."&action=edit");
	}
	function clients_pay_acc() {
		global $design,$db,$user;

		$id=get_param_protected('id');

		if ($this->check_tele($id)==0) return;

		$cid = trim(get_param_protected('cid',''));

        $payAcc=get_param_protected('pay_acc');
        $payAcc = trim($payAcc);

        if($cid)
        {
            $db->Query($q = "delete from client_pay_acc where client_id = '".$id."' and id='".$cid."'");
        }elseif($payAcc && strlen($payAcc) > 5){
            $db->Query($q = "insert into client_pay_acc set pay_acc='".$payAcc."',client_id ='".$id."', who = '".$user->Get("id")."', `date` = now()");
        }

		Header("Location: ?module=clients&id=".$id."&action=edit");
	}
	function clients_recontact2() {
		global $design,$db,$user;
		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$cid=get_param_protected('cid');
		$active=get_param_integer('act');
		$design->assign('contact_open',true);
		$cs=new ClientCS($id);
		$cs->ActivateContact($cid,$active);
		$this->client_view($id);
	}
	function clients_recontract($clientClient){
		global $design,$db;
		$id=get_param_protected('id');
		if($this->check_tele($id)==0)
			return;
		$content = get_param_raw('contract_content');

        $group = ClientCS::contract_getFolder(get_param_raw("contract_template_group"));

		if(!$content)
			$content = ClientCS::getContractTemplate('template_'.$group."_".get_param_protected('contract_template'));

        if(strpos($content, "{/literal}</style>") === false)
            $content = preg_replace("/<style([^>]*)>(.*?)<\/style>/six", "<style\\1>{literal}\\2{/literal}</style>", $content);

		$content = self::contract_fix_static_parts_of_template($content, '', $id);
		$cs=new ClientCS($id);
		$contractId = $cs->AddContract(
			$content,
			get_param_protected('contract_no'),
			get_param_protected('contract_date'),
			get_param_protected('contract_dop_date'),
			get_param_protected('comment')
		);

		$this->fix_contract($id, $contractId);
		header("Location: ./?module=clients&id=".$id."&contract_open=true");
		exit();

		$design->assign('contract_open',true);
		$this->client_view($id);
	}
	function clients_recontract2() {
		global $design,$db,$user;
		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$cid=get_param_protected('cid');
		$active=get_param_integer('act');
		$design->assign('contract_open',true);
		$db->Query('update client_contracts set is_active="'.$active.'",ts=NOW(),user_id="'.$user->Get('id').'" where client_id="'.$id.'" and id="'.$cid.'"');
		$this->client_view($id);
	}
	function check_tele($id) {
		global $design,$user,$db;
		if (access('clients','edit')) return 1;
		if (!access('clients','edit_tele')) return 0;
		$db->Query('select status from clients where id="'.$id.'"');
		$r=$db->NextRecord();
		if ($r['status']=='telemarketing') return 1;
		if ($r['status']=='income') return 1;
		if ($r['status']=='negotiations') return 1;
		return 0;
	}
    function clients_create(){
        global $design, $db;

        $C=new ClientCS(null,true);
        $C->status='income';
        $C->user_impersonate = 'client';

        $isInnDbl = false;
        $inn = get_param_protected("inn");
        if($inn)
            if($r = $db->getRow('select client, id from clients where inn="'.$inn.'" /*and status = "work"*/'))
                $isInnDbl = $r["client"]." (id:".$r["id"].")";

		if($isInnDbl && !access('clients','inn_double')){
			trigger_error('Такой же ИНН есть, как минимум, у клиента '.$isInnDbl.'. Добавление невозможно');
		}else{
			if($isInnDbl)
				trigger_error('Такой же ИНН есть, как минимум, у клиента '.$isInnDbl.'. Имейте в виду');

            if ($C->Create()){

                if (!Sync1C::getClient()->saveClientCard($C->F['id']))
                {
                    trigger_error("Не удалось синхронизировать клиента с 1С");
                }

                $this->client_view($C->id,1);
                return ;
            }else{
                trigger_error("Такой клиент уже существует.");
            }
        }
        $design->assign('client',$C->F);
        $design->assign("history_flags", $this->get_history_flags(0));
        $design->AddMain('clients/main_edit.tpl');
    }
	function get_client_info($client){
    global $db;
    if (is_numeric($client)) {
        $q='(id="'.$client.'")';
    } else $q='(client="'.$client.'")';
    $db->Query("select * from clients where	".$q);
    if ($r=$db->NextRecord()) return $r; else return false;
	}
	function clients_sc() {
		global $db,$design;
		include INCLUDE_PATH.'db_view.php';
		$view=new DbViewSaleChannels();
		$view->Display('module=clients&action=sc','module=clients&action=sc_edit');
	}
	function clients_sc_edit(){
		global $db,$design;
		include INCLUDE_PATH.'db_view.php';
		$view=new DbViewSaleChannels();
		$dbf=new DbFormSaleChannels();
		if (($id=get_param_integer('id')) && !($dbf->Load($id))) return;
		$dbf->Process();
		$dbf->Display(array('module'=>'clients','action'=>'sc_edit','id'=>$id),$view->Headers[$view->fieldset],$id?'Редактирование':'Добавление');
	}

	function clients_print_yota_contract($fixclient){
		global $design,$db;

		if(isset($_REQUEST['save_vals'])){
			Header('Content-type: text/plain; charset=utf8');
			unset($_POST['module'],$_POST['action'],$_POST['save_vals']);
			$json = '{';
			foreach($_POST as $k=>$v){
				$json .= $k.":'".addcslashes($v, "\\\\'")."',";
			}
			$json = substr($json, 0, strlen($json)-1)."}";

			$query = "
				replace into
					`clients_contracts_yota`
				set
					client_id = (select id from clients where client='".addcslashes($fixclient, "\\\\'")."' limit 1),
					json_data = '".addcslashes($json, "\\\\'")."'
			";

			$db->Query($query);
			echo "ok";
			exit();
		}elseif(isset($_REQUEST['get_vals'])){
			Header('Content-type: text/plain; charset=utf8');
			$db->Query('set names koi8r');
			$db->Query("select json_data from clients_contracts_yota where client_id = (select id from clients where client='".addcslashes($fixclient,"\\\\'")."' limit 1)");
			$json = $db->NextRecord(MYSQL_ASSOC);
			if($json)
				echo $json['json_data'];
			else
				echo "false";
			exit();
		}

		if(isset($_GET['print_page']) && is_numeric($_GET['print_page']) && (int)$_GET['print_page']<>1100){
			$tpl = array(1,6,7,8);
			if(in_array($_GET['print_page'],$tpl)){
				$select_client_data = "
					select
						`c`.`company_full`,
						`c`.`signer_positionV`,
						`c`.`signer_nameV`,
						`c`.`signer_name`,
						`c`.`signer_position`,
						`c`.`inn`,
						`c`.`kpp`,
						`c`.`bik`,
						`c`.`bank_properties`,
						`c`.`address_post_real`,
						`c`.`address_jur`,
						`c`.`phone_connect`,
						(select `data` from `client_contacts` where `client_id`=`c`.`id` and `type`='fax' limit 1) `fax`,
						(select `data` from `client_contacts` where `client_id`=`c`.`id` and `type`='phone' limit 1) `phone`,
						(select `comment` from `client_contacts` where `client_id`=`c`.`id` and `type`='phone' limit 1) `contact_name`,
						(select `data` from `client_contacts` where `client_id`=`c`.`id` and `type`='email' limit 1) `email`
					from
						`clients` `c`
					where
						`c`.`client` = '".addcslashes($fixclient,"\\\\'")."'
				";
DBG::sql_out($select_client_data);
				$db->Query($select_client_data);
				$row = $db->NextRecord(MYSQL_ASSOC);

				function escape_html_to_svg($str){
					return str_replace('&laquo;','&#171;',str_replace('&raquo;','&#187;',$str));
				}

				$bp = escape_html_to_svg(trim($row['bank_properties']));
				preg_match('|р/сч?[\s]*([0-9]+)|i',$bp,$mats_rs);
				preg_match('|к/сч?[\s]*([0-9]+)|i',$bp,$mats_ks);
				preg_match('|р/сч?[\s]*[0-9]+(.+?)к/сч?[\s]*[0-9]+|i',$bp,$mats_ab);
				$bank = array(
					'pay_account'=>$mats_rs[1],
					'cor_account'=>$mats_ks[1],
					'address'=>trim($mats_ab[1])
				);
				unset($bp,$mats_rs,$mats_ks,$mats_ab);
				$add = escape_html_to_svg(trim($row['address_jur']));
				preg_match('/^[0-9]+/',$add,$mats_zip);
				preg_match('/(?:г.|^[0-9]+[\s,]+)([^,]+)/',$add,$mats_city);
				preg_match('/ул.[^,]+/',$add,$mats_street);
				preg_match('/д.[^,]+/',$add,$mats_housenum);
				preg_match('/стр.[^,]/',$add,$mats_housebuild);
				$address_jur = array(
					'zip'=>$mats_zip[0],
					'city'=>trim($mats_city[1]),
					'street'=>trim($mats_street[0]),
					'housenum'=>trim($mats_housenum[0]),
					'housebuild'=>trim($mats_housebuild[0])
				);
				unset($mats_zip,$mats_city,$mats_street,$mats_housenum,$mats_housebuild);
				$add = escape_html_to_svg(trim($row['address_post_real']));
				preg_match('/^[0-9]+/',$add,$mats_zip);
				preg_match('/(?:г.|^[0-9]+[\s,]+)([^,]+)/',$add,$mats_city);
				preg_match('/ул.[^,]+/',$add,$mats_street);
				preg_match('/д.[^,]+/',$add,$mats_housenum);
				preg_match('/с(тр)?\.[^,]/',$add,$mats_housebuild);
				$address_post = array(
					'zip'=>$mats_zip[0],
					'city'=>trim($mats_city[1]),
					'street'=>trim($mats_street[0]),
					'housenum'=>trim($mats_housenum[0]),
					'housebuild'=>trim($mats_housebuild[0])
				);
				unset($mats_zip,$mats_city,$mats_street,$mats_housenum,$mats_housebuild);

				$design->assign('client_company',iconv('koi8r','utf8',escape_html_to_svg($row['company_full'])));
				$design->assign('page1',array(
					'city'=>iconv('koi8r','utf8','Москва'),
					'year_single_digit'=>substr(date('Y'),3),
					'in_face'=>iconv('koi8r','utf8',$row['signer_positionV'].' '.$row['signer_nameV']),
					'with_base'=>iconv('koi8r','utf8',($row['with_base']=='')?'устава':$row['with_base'])
				));
				$design->assign('page6',array(
					'address_jur_zip'=>$address_jur['zip'],
					'address_jur_city'=>iconv('koi8r','utf8',$address_jur['city']),
					'address_jur_other'=>iconv('koi8r','utf8',$address_jur['street'].' '.$address_jur['housenum'].' '.$address_jur['housebuild']),
					'address_post_zip'=>$address_post['zip'],
					'address_post_city'=>iconv('koi8r','utf8',$address_post['city']),
					'address_post_other'=>iconv('koi8r','utf8',$address_post['street'].' '.$address_post['housenum'].' '.$address_post['housebuild']),
					'phone'=>$row['phone'],
					'fax'=>$row['fax'],
					'phone_fax_separator'=>($row['phone'] && $row['fax'])?' / ':'',
					'email'=>$row['email'],
					'inn'=>$row['inn'],
					'kpp'=>$row['kpp'],
					'bik'=>$row['bik'],
					'bank_pay_acc'=>$bank['pay_account'],
					'bank_address'=>iconv('koi8r','utf8',$bank['address']),
					'bank_cor_acc'=>$bank['cor_account'],
					'client_name'=>iconv('koi8r','utf8',$row['signer_name'])
				));

				$design->assign('page7',array(
					'in_face'=>iconv('koi8r','utf8',$row['signer_positionV'].' '.$row['signer_nameV'])
				));

				$design->assign('page8',array(
					'address_street'=>iconv('koi8r','utf8',$address_jur['street']),
					'address_housenum'=>iconv('koi8r','utf8',$address_jur['housenum']),
					'address_housebuild'=>iconv('koi8r','utf8',$address_jur['housebuild']),
					'client_position'=>iconv('koi8r','utf8',$row['signer_position']),
					'partner'=>iconv('koi8r','utf8','ООО "Эм Си Эн"'),
					'contact'=>iconv('koi8r','utf8',$row['contact_name'])
				));

				$content = $design->fetch("../store/yota/page".((int)$_GET['print_page']).".svg.tpl");
			}else{
				$content = file_get_contents(STORE_PATH.'yota/page'.((int)$_GET['print_page']).'.svg');
			}
			@ini_set('zlib.output_compression','on');
			@ini_set('zlib.output_compression_level', '9');
			Header('Content-type: image/svg+xml');
			echo $content;
			exit();
		}elseif((int)$_GET['print_page']===1100){
			Header('Content-type: text/html; charset=utf8');
			echo file_get_contents('../design/clients/yota_print.html');
			exit();
		}
		Header('Content-type: text/html; charset=utf8');
		echo file_get_contents('../design/clients/yota_frames.tpl');
		exit();
	}

	static function contract_fix_static_parts_of_template(&$content, $client, $clientId=0){
		global $db;
        global $design;

        if(($pos = strpos($content, "{\$include_")) !== false)
        {
        	$c = substr($content, $pos);
        	$templateName = substr($c, 10, strpos($c, "}")-10);

        	$fname =STORE_PATH."contracts/template_".$templateName.".html";

        	if(file_exists($fname))
        	{
        		$c = file_get_contents($fname);
        		$design->assign("include_".$templateName, $c);
        	}

        	$fname =STORE_PATH."contracts/".$templateName.".html";
        	if(file_exists($fname))
        	{
        		$c = file_get_contents($fname);
        		$design->assign("include_".$templateName, $c);
        	}


        }

        if(strpos($content, "{*#subarenda_cmc#*}") !== false)
        {
            $r = $db->AllRecords($q =
                        "SELECT *,
                        unix_timestamp(u.actual_from) `from`, unix_timestamp(u.actual_to) `to`, amount,
                        substring_index(param_value, ',',1) floor, substring(param_value, instr(param_value, ',')+1) office, t.price
                        FROM `usage_extra` u , tarifs_extra t
                        where client = '".$client."'
                        and u.tarif_id = t.id
                        and t.status ='itpark' and (description like 'Аренда%' or description like 'аренда%')
                        and actual_from < '2029-01-01' and unix_timestamp(u.actual_to) > ".time()."
                        order by  actual_from desc, u.id desc");

            $rr = array("from" => @$r[0]["from"], "to" => @$r[0]["to"], "sum" => 0);
            $s = array();
            foreach($r as $a)
            {
                $rr["sum"] += $a["amount"]*$a["price"];
                $s[] = array(
                        "floor" => $a["floor"],
                        "office" => $a["office"],
                        "amount" => round($a["amount"],4),
                        "is_store" => strpos(strtolower($a["office"]), "склад") !== false
                        );
            }
            $rr["s"] = $s;



            $design->assign("contract_date", $db->GetValue("select contract_date from client_contracts where client_id = '".$clientId."' and comment like '%огово%' order by id desc limit 1"));


            //printdbg($r);
            //printdbg($rr);

            $design->assign("subarenda", $rr);
        }

		if(strpos($content, '{*#mcm#*}')!==false){
            //Услуги по обеспечению контроля территории
            $design->assign("mcm", $db->GetRow(
                        "select actual_from, actual_to, amount, price 
                        from usage_extra u, tarifs_extra t 
                        where tarif_id in (196,330,332,333) and client='".$client."' and tarif_id = t.id order by u.id desc limit 1"));
        }

		if(strpos($content, '{*#voip_moscow_tarifs_mob#*}')!==false){
			$repl = '';
			// москва(моб.)
			$query = "
				select
					`destination_name`,
					`destination_prefix`,
					substring(`destination_prefix` from 2 for 3) `code`,
					`rate_RUR`
				from
					`price_voip`
				where
					`dgroup`=0
				and
					`dsubgroup`=0
				order by
					`destination_prefix`
			";
			$db->Query($query);
			while($row=$db->NextRecord(MYSQL_ASSOC)){
				$repl .= "<tr>\n\t<td>".$row['destination_name']." - ".$row['code']."</td>\n\t<td>".$row['destination_prefix']."</td>\n\t<td width='30'>".$row['rate_RUR']."</td>\n</tr>";
			}
			$content = str_replace('{*#voip_moscow_tarifs_mob#*}', $repl, $content);
		}
		return $content;
	}

    static function contract_apply_support_phone($region)
    {
        switch($region)
        {
            case '97': $phone = "(861) 204-00-99"; break;
            case '98': $phone = "(812) 372-69-99"; break;
            case '95': $phone = "(343) 302-00-99"; break;
            case '99':
            default: 
                $phone = "(495) 950-56-78";
        }

        global $design;

        $design->assign("support_phone", $phone);
    }

    static function contract_apply_firma($firma)
    {
        $firma = $firma ? $firma : "mcn";

        $firms = array(
                "mcn_telekom" => array(
                    "name" => "ООО &laquo;МСН Телеком&raquo;",
                    "address" => "115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8",
        			      "post_address" => "115162, г. Москва, а/я &#8470;21",
                    "inn" => "7727752084",
                    "kpp" => "772401001",
                    "acc" => "40702810038110015462",
                    "bank" => "Московский банк Сбербанка России ОАО, г.Москва",
                    "kor_acc" => "30101810400000000225",
                    "bik" => "044525225",
                    "phone" => "(495) 950-56-78",
                    "fax" => "(495) 638-50-17",
                    "email" => "info@mcn.ru",
                    "director" => "Мельников А. К.",
                    "director_" => "Мельникова А. К.",
                    "director_post" => "Генеральный директор",
                    "director_post_" => "Генерального директора"

                    ),
                "mcn" => array(
                    "name" => "ООО &laquo;Эм Си Эн&raquo;",
                    "address" => "113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130",
                    "post_address" => "115162, г. Москва, а/я &#8470;21",
                    "inn" => "7727508671",
                    "kpp" => "772701001",
                    "acc" => "40702810600301422002",
                    "bank" => "ЗАО КБ &laquo;Ситибанк&raquo;",
                    "kor_acc" => "30101810300000000202",
                    "bik" => "044525202",
                    "phone" => "(495) 950-56-78",
                    "fax" => "(495) 638-50-17",
                    "email" => "info@mcn.ru",
                    "director" => "Мельников А. К.",
                    "director_" => "Мельникова А. К.",
                    "director_post" => "Генеральный директор",
                    "director_post_" => "Генерального директора"
                    ),
                "mcm" => array(
                    "name" => "ООО &laquo;МСМ&raquo;",
                    "address" => "117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97",
                    "inn" => "7727667833",
                    "kpp" => "772701001",
                    "acc" => "40702810500540001425",
                    "bank" => "ОАО &laquo;БАНК УРАЛСИБ&raquo;",
                    "kor_acc" => "30101810100000000787",
                    "bik" => "044525787",
                    "phone" => "(495) 950-58-41",
                    "email" => "arenda@mcn.ru",
                    "director" => "Мельников Е. И.",
                    "director_" => "Мельникова Е. И.",
                    "director_post" => "Директор",
                    "director_post_" => "Директора"
                    ),
                "ooocmc" => array(
                    "name" => "ООО &laquo;Си Эм Си&raquo;",
                    "address" => "117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97",
                    "inn" => "7727701308",
                    "kpp" => "772701001",
                    "acc" => "40702810800540001507",
                    "bank" => "ОАО &laquo;БАНК УРАЛСИБ&raquo;",
                    "kor_acc" => "30101810100000000787",
                    "bik" => "044525787",
                    "phone" => "(495) 950-58-41",
                    //"fax" => "(499) 123-55-33",
                    "email" => "arenda@mcn.ru",
                    "director" => "Надточеева Н. А.",
                    "director_" => "Надточееву Н. А. ",
                    "director_post" => "Заместитель Генерального директора",
                    "director_post_" => "Заместителя Генерального директора"
                    ),
          "all4geo" => array(
            "name" => "ООО &laquo;Олфогео&raquo;",
            "address" => "115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8",
            "inn" => "7727752091",
            "kpp" => "772401001",
            "acc" => "40702810038110016607",
            "bank" => "ОАО Сбербанк России",
            "kor_acc" => "30101810400000000225",
            "bik" => "044525225",
            //"phone" => "(495) 950-58-41",
            //"fax" => "(499) 123-55-33",
            //"email" => "arenda@mcn.ru",
            "director" => "Котельникова О. И.",
            "director_" => "Котельникову О. И.",
            "director_post" => "Генеральный директор",
            "director_post_" => "Генеральный директор"
          ),
                    );

        $f = $firms[$firma];


        $d = $f["name"]."<br /> Юридический адрес: ".$f["address"].
        (isset($f["post_address"]) ? "<br /> Почтовый адрес: ".$f["post_address"] : "").
        "<br /> ИНН ".$f["inn"].", КПП ".$f["kpp"]."<br /> Банковские реквизиты:<br /> р/с:&nbsp;".$f["acc"]." в ".$f["bank"]."<br /> к/с:&nbsp;".$f["kor_acc"]."<br /> БИК:&nbsp;".$f["bik"]."<br /> телефон: ".$f["phone"].(isset($f["fax"]) && $f["fax"] ? "<br /> факс: ".$f["fax"] : "")."<br /> е-mail: ".$f["email"];

        global $design;

        $design->assign("firm_detail", $d);
        $design->assign("firm", $f);
    }

	function clients_rpc_findClient1c(){
		require_once INCLUDE_PATH.'1c_integration.php';
		$clS = new \_1c\clientSyncer($db);

		$cl = $clS->findClient(null, null, $_GET['findInn']);

		if(!$cl)
			echo "false";
		else{
			Header('Contetn-Type: plain/text; charset="koi8-r"');
			$attrs = $cl->getDetailsArr("\\'");
			echo "{
				company:'".$attrs['company']."',
				company_full:'".$attrs['company_full']."',
				inn:'".$attrs['inn']."',
				bik:'".$attrs['bik']."',
				pay_acc:'".$attrs['pay_acc']."',
				corr_acc:'".$attrs['corr_acc']."',
				bank_name:'".$attrs['bank_name']."',
				bank_city:'".$attrs['bank_city']."',
				address_jur:'".$attrs['address_jur']."',
				kpp:'".$attrs['kpp']."',
				type:'".$attrs['type']."'
			}";
		}
		exit();
	}
	function clients_rpc_findBank1c(){
    global $db;
		//require_once INCLUDE_PATH.'1c_integration.php';
		//$clS = new \_1c\clientSyncer($db);

    $bik = $db->GetRow("select * from bik b where b.bik='".mysql_real_escape_string($_GET['findBik'])."'");

		if(!$bik)
			echo "false";
		else{
			Header('Contetn-Type: plain/text; charset="koi8-r"');
			echo "{
				bik:'".$bik['bik']."',
				corr_acc:'".$bik['corr_acc']."',
				bank_name:'".$bik['bank_name']."',
				bank_city:'".$bik['bank_city']."'
			}";
		}
		exit();
	}

	function get_history_flags($clientId)
	{
		global $db;

		$past = date("Y-m-01"); // с начала текущего месяца
		$future = date("Y-m-01", strtotime("+1 month", strtotime(date("Y-m-01")))); // с начала следующего месяца

		static $a = array();
		$a = array("d_past" => $past, "d_future" => $future, "past" => 0, "future" => 0,
			"m" => array(
				"1" => array("d" => strtotime("-2 month", strtotime(date("Y-m-01"))), "v"=>0),
				"2" => array("d" => strtotime("-1 month", strtotime(date("Y-m-01"))), "v"=>0),
				"3" => array("d" => strtotime(date("Y-m-01")), "v"=>0),
				"4" => array("d" => strtotime("+1 month", strtotime(date("Y-m-01"))), "v"=>0)

			)
		);

		if(!$clientId)  return $a;
		if(isset($a["is_set"]))  return $a;

		foreach($a["m"] as $idx => $b)
		{
			$a["m"][$idx]["v"] = (int)$db->GetValue($sql = "
								select id
								from log_client
								where
										type='fields'
									and client_id = '".$clientId."'
									and (ts > '".date("Y-m-d", $b["d"])." 00:00:00' or apply_ts >= '".date("Y-m-d", $b["d"])."')
									and is_overwrited='no'"
			);

			$a["m"][$idx]["n"] = mdate("месяца", $a["m"][$idx]["d"]);

		}
		$a["is_set"] = true;

		return $a;
	}

	function clients_view_history()
	{
		global $db, $design, $user;

        $design->assign("isAdmin", access("clients", "history_edit"));
        $design->assign("user_id", $user->Get("id"));

		$clientId = get_param_raw("id", "-1");

        if($verId = get_param_integer("del_section", "0"))
        {
            $db->Query("delete from log_client where id = '".$verId."'");
            $db->Query("delete from log_client_fields where ver_id = '".$verId."'");
            header("Location: ./?module=clients&id=".$clientId."&action=view_history");
            exit();
        }

        if($fId = get_param_integer("del_value", "0"))
        {
            historyView::delValue($fId);
            header("Location: ./?module=clients&id=".$clientId."&action=view_history");
            exit();
        }

        if($verId = get_param_integer("del_apply", 0))
        {
            $db->Query("update log_client set apply_ts = '0000-00-00' where id = '".$verId."'");
        }

        if($verId = get_param_integer("add_apply", 0))
        {
            list($year,$month,$day) = explode("-", get_param_raw("date")."--");
            $db->Query("update log_client set apply_ts = '".$year."-".$month."-".$day."' where id = '".$verId."'");
        }

        if(
                ($fs = get_param_integer("fs", 0))
                && ($ff = get_param_raw("ff", array()))
                )
        {
            $db->Query("update log_client_fields set ver_id = '".$fs."' where id in ('".implode("','", $ff)."')");
        }



		$log = $db->AllRecords(
			"select user_id,client_id, lc.id, unix_timestamp(lc.ts) as ts ,u.name, is_overwrited, is_apply_set, unix_timestamp(apply_ts) as apply_ts
			from log_client lc
			left join user_users u on (u.id = lc.user_id)
			where lc.client_id = '".$clientId."' and lc.type='fields'
			order by lc.id desc");


		foreach($log as $idx => $l)
		{
			$log[$idx]["ts"] = mdate("d месяца Y", $log[$idx]["ts"])." ".date("H:i:s",$log[$idx]["ts"]);
			$log[$idx]["apply"] = $l["apply_ts"] ? array((int)date("Y", $l["apply_ts"]), (int)date("m", $l["apply_ts"]), (int)date("d", $l["apply_ts"])): false;
			$log[$idx]["apply_ts"] = $l["apply_ts"]  ? mdate("d месяца Y", $l["apply_ts"]) : false;
			$log[$idx]["fields"] = array();

			foreach($db->AllRecords("select * from log_client_fields where ver_id = '".$l["id"]."'") as $f)
			{
				if($f["field"] == "voip_is_day_calc" && $f["value_from"] == "0" && $f["value_to"] == "")
				{
					// skip
				}else{
					$f["name"] = $this->f($f["field"]);
					$log[$idx]["fields"][] = $f;
				}
			}

			if(!$log[$idx]["fields"])
			{
				$log[$idx]["fields"][] = array("name" => "Изменены поля", "value_from" => $l["comment"], "value_to" => false);
			}
		}

		$design->assign("log", $log);
		$design->assign("view_only", get_param_raw("view_only", "false") == "true");
		$design->ProcessEx("clients/history.htm");
	}



	function f($l)
	{
		$f = array(
			"company" => "Компания",
			"company_full" => "Полное название компании",
			"address_jur" => "Юридический адрес",
			"address_post" => "Почтовый адрес",
			"address_post_real" => "Действительный почтовый адрес",
			"address_connect" => "Предполагаемый адрес подключения",
			"phone_connect" => "Предполагаемый телефон подключения",
			"metro_id" => "Станция метро",
			"payment_comment" => "Комментарии к платежу",
			"sale_channel" => "Канал продаж",
			"telemarketing" => "Телемаркетинг",
			"manager" => "Менеджер",
	        "support" => "Техподдержка",
	        "bank_properties" => "Банковские реквизиты",
	        "inn" => "ИНН",
	        "kpp" => "КПП",
	        "bik" => "БИК",
	        "corr_acc" => "К/С",
	        "pay_acc" => "Р/С",
	        "bank_name" => "Название банка",
	        "bank_city" => "Город банка",
	        "signer_position" => "Должность подписывающего лица",
	        "signer_name" => "ФИО подписывающего лица",
	        "signer_positionV" => "Должность подписывающего лица, в вин. падеже",
	        "signer_nameV" => "ФИО подписывающего лица, в вин. падеже",
	        "firma" => "Фирма",
	        "stamp" => "Печатать штамп",
	        "nds_zero" => "НДС 0%",
	        "nal" => "Нал",
	        "currency" => "Валюта",
	        "credit" => "Кредит",
	        "voip_credit_limit" => "Телефония, лимит использования (месяц)",
	        "voip_credit_limit_day" => "Телефония, лимит использования (день)",
	        "voip_disabled" => "- Выключить телефонию,",
	        "voip_is_day_calc" => "- Включить пересчет дневного лимита",
	        "password" => "Пароль",
	        "usd_rate_percent" => "USD уровень в процентах",
	        "type" => "Тип",
	        "id_all4net" => "ID в All4Net",
	        "user_impersonate" => "Наследовать права пользователя",
	        "dealer_comment" => "Комментарий для дилера",
	        "form_type" => "Формирование с/ф",
	        "price_type" => "Тип цены",
	        "mail_print" => "Печать писем",
			"mail_who" => "Кому письмо",
			"head_company" => "Головная компания",
			"head_company_address_jur" => "Юр. адрес головной компании"

		);
		return isset($f[$l]) ? $f[$l] : $l;

	}


	function clients_p_edit(){
		global $db,$design;

		if(isset($_GET['pid']) && !isset($_POST['gone'])){
			$cli = $db->GetRow("select * from phisclients where pk=".(int)$_GET['pid']);
			$design->assign('cli',$cli);
		}elseif(isset($_POST['gone']) && !isset($_POST['pid'])){
			$err = 0;
			$db->Query('start transaction');
			$pid = $db->QueryInsert("clients",array(
				'type'=>'priv',
				'status'=>'work'
			));
			if(!$err && !($err |= mysql_errno()))
				$db->Query("update clients set client='pid".$pid."' where id=".$pid);
			if(!$err && !($err |= mysql_errno()))
				$db->QueryInsert("phisclients",array(
					'pk'=>$pid,
					'fio'=>$_POST['fio'],
					'currency'=>$_POST['currency'],
					'phone'=>$_POST['phone'],
					'email'=>$_POST['email'],
					'phone_connect'=>$_POST['phone_connect'],
					'contact_info'=>$_POST['contact_info'],
					'phone_owner'=>$_POST['phone_owner'],
					'address_single_string'=>$_POST['address_single_string'],
					'addr_city'=>$_POST['addr_city'],
					'addr_street'=>$_POST['addr_street'],
					'addr_house'=>$_POST['addr_house'],
					'addr_housing'=>$_POST['addr_housing'],
					'addr_build'=>$_POST['addr_build'],
					'addr_flat'=>$_POST['addr_flat'],
					'addr_porch'=>$_POST['addr_porch'],
					'addr_floor'=>$_POST['addr_floor'],
					'addr_intercom'=>$_POST['addr_intercom'],
					'passp_series'=>$_POST['passp_series'],
					'passp_num'=>$_POST['passp_num'],
					'passp_whos_given'=>$_POST['passp_whos_given'],
					'passp_when_given'=>$_POST['passp_when_given'],
					'passp_code'=>$_POST['passp_code'],
					'passp_birthday'=>$_POST['passp_birthday'],
					'reg_city'=>$_POST['reg_city'],
					'reg_street'=>$_POST['reg_street'],
					'reg_house'=>$_POST['reg_house'],
					'reg_housing'=>$_POST['reg_housing'],
					'reg_build'=>$_POST['reg_build'],
					'reg_flat'=>$_POST['reg_flat']
				));

			if(!$err && !($err |= mysql_errno()))
				$db->Query('commit');
			else{
				$db->Query('rollback');
			}

			header('Location: ?module=clients&id=pid'.$pid);
			exit();
		}elseif(isset($_POST['gone']) && isset($_POST['pid'])){
			$pid = $_POST['pid'];
			$db->QueryUpdate("phisclients",'pk',array(
					'pk'=>$pid,
					'fio'=>$_POST['fio'],
					'currency'=>$_POST['currency'],
					'phone'=>$_POST['phone'],
					'email'=>$_POST['email'],
					'phone_connect'=>$_POST['phone_connect'],
					'contact_info'=>$_POST['contact_info'],
					'phone_owner'=>$_POST['phone_owner'],
					'address_single_string'=>$_POST['address_single_string'],
					'addr_city'=>$_POST['addr_city'],
					'addr_street'=>$_POST['addr_street'],
					'addr_house'=>$_POST['addr_house'],
					'addr_housing'=>$_POST['addr_housing'],
					'addr_build'=>$_POST['addr_build'],
					'addr_flat'=>$_POST['addr_flat'],
					'addr_porch'=>$_POST['addr_porch'],
					'addr_floor'=>$_POST['addr_floor'],
					'addr_intercom'=>$_POST['addr_intercom'],
					'passp_series'=>$_POST['passp_series'],
					'passp_num'=>$_POST['passp_num'],
					'passp_whos_given'=>$_POST['passp_whos_given'],
					'passp_when_given'=>$_POST['passp_when_given'],
					'passp_code'=>$_POST['passp_code'],
					'passp_birthday'=>$_POST['passp_birthday'],
					'reg_city'=>$_POST['reg_city'],
					'reg_street'=>$_POST['reg_street'],
					'reg_house'=>$_POST['reg_house'],
					'reg_housing'=>$_POST['reg_housing'],
					'reg_build'=>$_POST['reg_build'],
					'reg_flat'=>$_POST['reg_flat']
				));
			header('Location: ?module=clients&action=p_edit&pid='.$pid);
			exit();
		}else{
			$design->assign('mode_new',true);
		}

		$design->AddMain('clients/phisclient.html');
	}
}

class historyView
{
    public function delValue($fId)
    {
        global $db;

        //$cur = $db->GetRow(" select client_id,field,value_from, value_to, if(apply_ts='0000-00-00',ts, concat(apply_ts,' 0000-00-00')) tts from log_client l, log_client_fields f where f.ver_id =l.id and f.id= '".$fId."'");
        //printdbg($cur);

        //echo strtotime($t);

        //exit();
        $db->Query("delete from log_client_fields where id = '".$fId."'");
    }
}
