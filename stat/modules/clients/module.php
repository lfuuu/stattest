<?php
use app\classes\StatModule;
use app\models\ClientContractType;
use app\models\ClientStatuses;
use app\models\ClientAccount;
use app\classes\Assert;
use app\models\ClientGridSettings;
use app\models\ClientBP;
use app\models\ClientDocument;
use app\models\ClientFile;
use app\models\LkWizardState;

//просмотр списка клиентов с фильтрами и поиском / просмотр информации о конкретном клиенте
class m_clients {
	var $actions=array(
					'default'		=> array('clients','read'),
					'search_as'		=> array('clients','read'),
					'edit'			=> array('',''),					//права проверяются потом
					'restatus'		=> array('clients','restatus'),
					'recontact'		=> array('',''),
                    'recontactLK'   => array('',''),
					'recontact2'	=> array('',''),
					'recontract'	=> array('',''),
					'recontract2'	=> array('',''),
					'contract_form'	=> array('',''),
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
                    'rpc_loadBPStatuses'=> array('',''),
                    'rpc_setBlocked'    => array('clients', 'client_type_change'),
                    'rpc_setVoipDisabled' => array("clients", "client_type_change"),
                    'view_history'		=> array('clients', 'edit'),
                    'client_edit'       => array('clients', 'edit'),
                    'contragent_edit'   => array('clients', 'edit'),
                    'publish_comment'   => array('', ''),

					'p_edit' => array('clients','edit')
				);

	//содержимое левого меню. array(название; действие (для проверки прав доступа); доп. параметры - строкой, начинающейся с & (при необходимости); картиночка ; доп. текст)
	var $menu=array(
//					array('Мои клиенты',			'my'),
//					array('Все клиенты',			'all'),
					array('Новый клиент',			'new'),
                    /*
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
				    array('Поставщики',             'show','&subj=distr'),
				    array('Приостановленные',       'show','&subj=suspended'),
				    array('Отказ/задаток',          'show','&subj=denial'),
					array('Интернет Магазин',		'show','&subj=once'),
					array('Операторы',	            'show','&subj=operator'),
					array('Телефония отключена',	'show','&subj=voip_disabled'),
					array('Временно заблокирован',	'show','&subj=blocked'),
                     */
					array('',						'sc'),
					array('Каналы продаж',			'sc'),
					array('Отчёт по файлам',		'files_report'),                     
				);
//###################################################################
	function color_status($status_code){
		if (!isset(ClientCS::$statuses[$status_code])) return '';
		return ClientCS::$statuses[$status_code]['color'];
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

	function GetPanel($fixclient){
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
            return array('Клиенты',$R);
		}
	}

	function GetMain($action,$fixclient){

		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'clients_'.$action),$fixclient);
	}

	function clients_search_as($fixclient) {
		global $design;
		if (isset($_GET['query'])) $_GET['search']=$_GET['query'];
		$design->assign('clients',array());
		$this->clients_list(false,5,20);
		echo json_encode(array(
					'data'		=> $design->fetch('clients/as_search.tpl'),
					));
	}

	function clients_my($fixclient) {
		global $design,$user;

        $this->client_unfix();

		// запоминаем что дальше всех клиентов надо фильтровать по менеджеру
		$_SESSION['clients_my'] = $user->_Login;
		$this->clients_headers('my');
		$this->clients_list(true);
		$design->assign('name_of_action', 'Мои клиенты');
		$design->AddMain('clients/main_clients.tpl');
	}

	function client_unfix(){
		// фильтр по менеджеру обнуляем
		// Фильтр по типу клиента обнуляем
		$_SESSION['clients_filter'] = '';
		$_SESSION['clients_my'] = '';
		$_SESSION['clients_client'] = '';

		$GLOBALS['fixclient']="";
        if(get_param_raw("unfix",0)){
            $rf = $_SERVER["HTTP_REFERER"];
            preg_match_all("#(?:(?:\?|&)([^=]+?)=([^&]+?))+#U", $rf, $out, PREG_SET_ORDER);
            $d = array();
            foreach($out as $a)
                $d[$a[1]] = $a[2];

            if(
                    !(
                    (isset($d["module"]) && $d["module"] == "clients" && $d["id"]) ||
                    (isset($d["module"]) && $d["module"] == "newaccounts" && $d["action"] == "make_1c_bill")
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

	function clients_show($fixclient){
		global $design,$user;
		$subj=get_param_protected("subj");
		if (!$subj) return;
		$design->assign('hide_tt_list',1);
		$_SESSION['clients_filter'] = $subj;
		$this->clients_headers('show', $subj);

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
            case 'suspended': $design->assign('name_of_action','Приостановленные');break;
			case 'denial':  $design->assign('name_of_action','Отказ/задаток');break;
			case 'reserved': $design->assign('name_of_action','Резервирование канала');break;
			case 'voip_disabled': $design->assign('name_of_action', 'Телефония отключена');break;
			case 'blocked': $design->assign('name_of_action', 'Временно заблокирован');break;
			case 'once': $design->assign('name_of_action', 'Интернет Магазин');break;
			case 'distr': $design->assign('name_of_action', 'Поставщики');break;
			case 'operator': $design->assign('name_of_action', 'Операторы');break;
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
		} elseif ($contragentId = get_param_protected("contragent_id")){
            $this->client_contragent($contragentId);
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
		//$L['1'] = '1';
		//for ($i = ord('a');$i<=ord('z');$i++) $L[chr($i)] = chr($i);
		//ksort($L);
		$L['*'] = '*';
		$L['@'] = '@';
		$L['!'] = 'Клиенты ItPark';
		$L['+'] = 'Тип: Дистрибютор';
		$L['-'] = 'Тип: Оператор';
		if (isset($user->_Priveleges['firms']) && count($user->_Priveleges['firms']) > 0) {
			foreach ($user->_Priveleges['firms'] as $firm=>$v) {
				switch ($firm) {
					case 'mcn_telekom':
					    $L['firma:mcn_telekom'] = 'ООО "МСН Телеком"';
					break;
					case 'mcn':
					    $L['firma:mcn'] = 'ООО "Эм Си Эн"';
					break;
					case 'markomnet_new':
					    $L['firma:markomnet_new'] = 'ООО "МАРКОМНЕТ"';
					break;
					case 'markomnet_service':
					    $L['firma:markomnet_service'] = 'ООО "МАРКОМНЕТ сервис"';
					break;
					case 'ooomcn':
					    $L['firma:ooomcn'] = 'ООО "МСН"';
					break;
					case 'all4net':
					    $L['firma:all4net'] = 'ООО "ОЛФОНЕТ"';
					break;
					case 'ooocmc':
					    $L['firma:ooocmc'] = 'ООО "Си Эм Си"';
					break;
					case 'mcm':
					    $L['firma:mcm'] = 'ООО "МСМ"';
					break;
					case 'all4geo':
					    $L['firma:all4geo'] = 'ООО "Олфогео"';
					break;
					case 'wellstart':
					    $L['firma:wellstart'] = 'ООО "Веллстарт"';
					break;
				}
			}
		} else {
            $L['firma:mcn_telekom'] = 'ООО "МСН Телеком"';
            $L['firma:mcn'] = 'ООО "Эм Си Эн"';
            $L['firma:markomnet_new'] = 'ООО "МАРКОМНЕТ"';
            $L['firma:markomnet_service'] = 'ООО "МАРКОМНЕТ сервис"';
            //$L['firma:markomnet'] = 'ООО "МАРКОМНЕТ (старый)"';
            $L['firma:ooomcn'] = 'ООО "МСН"';
            $L['firma:all4net'] = 'ООО "ОЛФОНЕТ"';
            $L['firma:ooocmc'] = 'ООО "Си Эм Си"';
            $L['firma:mcm'] = 'ООО "МСМ"';
            $L['firma:all4geo'] = 'ООО "Олфогео"';
            $L['firma:wellstart'] = 'ООО "Веллстарт"';
		}
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
			$design->assign('filter_clients_date_to_m',$_POST['filter_clients_date_to_m']);
			$design->assign('filter_clients_date_to_d',$_POST['filter_clients_date_to_d']);
		}else {
			$date_from = $date_to = null;
			$design->assign('filter_clients_date_from_y','');
                        $design->assign('filter_clients_date_from_m','');
                        $design->assign('filter_clients_date_from_d','');
                        $design->assign('filter_clients_date_to_y','');
                        $design->assign('filter_clients_date_to_m','');
                        $design->assign('filter_clients_date_to_d','');
                }

		if ($smode && $smode!=1 && $smode!=5){
			$my = '';
			$letter = '';
		}
		$where="1 ";
		$join="";
		$group='';
		$where_2='';

		if($my!=='')
			$where.="and (cl.manager='$my') ";

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
            if($letter=='!'){
				$where .= " and cl.client in (select client from usage_extra ue inner join tarifs_extra te on ue.tarif_id=te.id and te.status='itpark') ";
            } elseif (substr($letter,0,5) == 'firma') {
                $firma = substr($letter,6);
                $where.="and cl.firma ='".$firma."' ";
            }else{
				$where.="and cl.client LIKE '{$letter}%' ";
			}
		}

    if($letter_region != "any")
    {
      $region = (int)$letter_region;
      $where .= " and cl.region = '".$region."'";
    }
    if (isset($user->_Priveleges['firms']) && count($user->_Priveleges['firms']) > 0) {
        $where .= " and cl.firma IN ('".implode("','", array_keys($user->_Priveleges['firms']))."')";
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
                {
					$in_c = "'".$cls[0]['client']."'";
                } else {
                    $in_c = "''";
                }
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
		$sort=get_param_integer('sort',1);
		if ($filter == 'income' && $sort == 1) {
		    $sort=8;
		    $order='desc';
		    $so=0;
		}
		
		switch($sort){
			case 2: $order='cl.company '.$order; break;
			case 3: $order='cl.currency '.$order; break;
			case 4: $order='cl.sale_channel '.$order; break;
			case 5: $order='cl.manager '.$order; break;
			//case 6: $order='cl.support '.$order; break;
			//case 7: $order='cl.telemarketing '.$order; break;
			case 8: $order='cl.created '.$order; break;
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
			if(isset(ClientCS::$statuses[$r['status']])){
				$r['status_name']=ClientCS::$statuses[$r['status']]['name'];
				$r['status_color']=ClientCS::$statuses[$r['status']]['color'];
			}
			if(isset($SC[$r['sale_channel']]))
				$r['sale_channel'] = $SC[$r['sale_channel']]['name'];
			$R[]=$r;
		}

        util::pager("cl");

        if(!count($R) && is_numeric($search))
        {
		$tt_exists = Trouble::exists($search);
		if ($tt_exists)
		{
			header("Location: ./?module=tt&action=view&id=".urlencode($search));
			exit;
		}
        }
        // posible bill
        if(!count($R) && strlen($search) > 4){
            if($db->GetRow("select bill_no from `newbills` where bill_no = '".$db->escape($search)."'")){
                Header("Location: ./?module=newaccounts&action=search&search=".urlencode($search));
                exit;
            }
        }

        // posible income order
        if(!count($R) && strlen($search) > 4){
           $incomeOrder = GoodsIncomeOrder::first(array(
                       "conditions" => array("number" => $search),
                       "order" => "date desc",
                       "limit" => 1
                       )
                   );
            if($incomeOrder){
                header("Location: ./?module=incomegoods&action=order_view&id=".urlencode($incomeOrder->id));
                exit;
            }
        }

		if($move_if_single && (count($R)==1) && $flag_single){
			Header("Location: ?module=clients&id=".($R[0]['id']));
			exit;
		}

		if (strlen($search) && count($R) > 1) $design->assign('hide_tt_list',1);

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

		$c = $db->GetRow('select * from client_document where id="'.intval($id).'"');
		//if (!($r = $db->GetRow('select * from clients where id='.$c['client_id'].' limit 1'))) {trigger_error2('Такого клиента не существует');return;}

        $email = "";
        if (($em = $db->GetRow('SELECT data FROM `client_contacts` where client_id = '.$c["client_id"].' and type = "email" and is_official = 1 order by id desc limit 1')))
        {
            $email = $em["data"];
        }

		$p=data_encode($c['id'].'-'.$c['client_id']);
		$adr=LK_PATH."docs/?code=".str_replace('=','%%3D',$p);

		$body="Уважаемые Господа!" . "<br><br>" . "Отправляем Вам договор:" . "<br>";
		$body.="<a href=\"".$adr."\">".$adr."</a><br><br>";

        //echo "<html><meta http-equiv=\"refresh\" content=\"0;url=http://85.94.32.194/welltime/?module=com_agent_panel&set_action=new_msg&subject=".rawurlencode ("MCN - договор")."&new_msg=".rawurlencode ($body).(!empty($email) ? "&to=".$email: "" )."\"/><body></body></html>";
        echo "<html><meta http-equiv=\"refresh\" content=\"0;url=http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&subject=".rawurlencode ("MCN - договор")."&new_msg=".rawurlencode ($body).(!empty($email) ? "&to=".$email: "" )."\"/><body></body></html>";

		$design->ProcessEx('empty.tpl');
	}


	function clients_print($fixclient,$default_data=''){
		global $design,$db;

		if (!($id=get_param_integer('id',$fixclient))) return;

        $data=get_param_raw('data', $default_data);

		if ($data=='contract') {
			$c = $db->GetRow('select * from client_document where id="'.intval($id).'"');
			$id = $c['client_id'];
			$r = ClientCS::getOnDate($id, $c['contract_date']);
		} else {
			$c = null;
			$r = $db->GetRow('select * from clients where (id="'.$id.'") limit 1');
		}
		if (!$r) {
			trigger_error2('Такого клиента не существует');
			return;
		}

        if ($data=='contract') {

            $contract = ClientDocument::findOne($c["id"]);
            if($contract) {
            	echo $contract->content;
                exit();
            } else {
            	echo "Ошибка. Файл не найден " . STORE_PATH . $file;
            	exit();
            }


        } else {

        	ClientCS::Fetch($r,$c);

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

        \app\assets\TinymceAsset::register(Yii::$app->view);

		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$contract = $db->GetRow('select *, unix_timestamp(contract_date) as ts, unix_timestamp(contract_dop_date) as ts_dop  from client_document where id="'.intval($id).'"');
		$client = ClientCS::getOnDate($contract['client_id'], $contract['contract_date']);
		$design->assign('contract',$contract);
		$design->assign('client',$client);
        $design->assign('content',ClientDocument::dao()->getTemplate($client['id'].'-'.$contract['id']));

		$design->AddMain('clients/contract_edit.tpl');
	}
	function client_view($id,$show_edit = 0,$design_echo = 1){

		global $design, $db, $user;

        if (is_numeric($id)) {
          $clientAccount = ClientAccount::findOne($id);
        } else {
          $clientAccount = ClientAccount::find()->andWhere(['client' => $id])->one();
        }
        Assert::isObject($clientAccount);

        $timezones = \app\models\Region::find()->select('timezone_name')->groupBy('timezone_name')->indexBy('timezone_name')->asArray()->all();
        $timezones[$clientAccount->timezone_name] = ['timezone_name' => $clientAccount->timezone_name];
        $design->assign('timezones', $timezones);

    $superClient = $clientAccount->superClient;
    $contragents = $superClient->contragents;

    $design->assign('clientAccount', $clientAccount);
    $design->assign('superClient', $superClient);
    $design->assign('contragents', $contragents);
    $design->assign('is_wizard_allow', LkWizardState::isBPStatusAllow($clientAccount->business_process_status_id, $clientAccount->id));

    $voip = new VoipStatus;
    $voip->loadClient($id);
    $voip_counters = $voip->loadVoipCounters();
    $voip->showCountersWarning();
    $design->assign('voip_counters',$voip_counters);

    $design->assign("edit_user", $user->Get('user'));

    if (get_param_raw("do", "") == "make_contract")
    {
        $db->QueryUpdate("client_document", "id", ["id" => get_param_integer("contract", 0), "contract_dop_date" => "2012-01-01", "type" => "contract"]);
    }
    
    
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
					cl.client prev_r_cl,
                    s.name as super_client_name
				from
					clients
				LEFT JOIN  user_users as uA  ON uA.user=clients.manager
				left join  clients cl on cl.id = clients.previous_reincarnation
                LEFT JOIN client_super s ON (clients.super_id = s.id)
				where
					'.$q.'
				limit 1
		');



		if(!$r){
			trigger_error2('Такого клиента не существует');
			return;
		}


        if(access("clients", "read_multy"))
                if($r["type"] != "multi"){
                trigger_error2('Доступ к клиенту ограничен');
                return;
            }

        if (get_param_raw("sync"))
        {
            event::go("add_account", $r["id"]);
            header("Location: ./?module=clients&id=".$r["id"]);
            exit();
        }


		if(strrpos($r['client'],'/')!==false){
			$cl_main_card = substr($r['client'],0,-2);
			$design->assign('card_type','addition');
		}else{
			$cl_main_card = $r['client'];
			$design->assign('card_type','main');
		}


        /*
		$_cards_sel = "select id,client from clients where client<>'' and client = '".$cl_main_card."' or client like '".$cl_main_card."/%' order by client";
		$_cards = $db->AllRecords($_cards_sel,null,MYSQL_ASSOC);
		$design->assign('_cards',$_cards);
        */

		//$design->assign('all_cls',$db->AllRecords("select id,client from clients where client<>'' order by client",null,MYSQL_ASSOC));

		$r['status_name'] = (isset(ClientCS::$statuses[$r['status']]) ? ClientCS::$statuses[$r['status']]['name'] : $r['status']);
		$r['status_color'] = (isset(ClientCS::$statuses[$r['status']]) ? ClientCS::$statuses[$r['status']]['color'] : '');
        $r["price_type"] = $r["price_type"] ? $r["price_type"] : ClientCS::GetIdByName("price_type", "Розница");

        $design->assign('user_flag_statusbox',$user->Flag('statusbox'));

		$design->assign('fixclient',$id);
		$GLOBALS['fixclient'] = $id;
		$design->assign('request_uri',str_replace('&id='.$id,'',$_SERVER['REQUEST_URI']));

        StatModule::tt()->get_counters($id);

		ClientCS::$statuses[$r['status']]['selected'] = ' selected';
        $design->assign_by_ref('statuses',ClientCS::$statuses);
        $design->assign("contract_types", ClientContractType::find()->orderBy("sort")->all());
        $design->assign("bussines_processes", ClientBP::find()->select(["id", "name"])->where(["client_contract_id" => $r["contract_type_id"]])->orderBy("sort")->all());

        //bp statuses
        $design->assign("business_process", ClientGridSettings::find()->select(["id", "name"])->where(["grid_business_process_id" => $r["business_process_id"], "show_as_status" => 1])->orderBy("sort")->all());
        
        
        $cs = new ClientCS($r['id']);

		if(!$show_edit){
			$design->assign('contacts',$cs->GetContacts());
            $design->assign('lk_contacts',$cs->GetContactsFromLK());
            $design->assign('contracts',$cs->GetContracts());
			$design->assign('contact',$cs->GetContact(false));

			$r['data_cs'] = $cs->GetAllStatuses();

			$design->assign('cfiles',count($clientAccount->files));

			if($design_echo){
				$design->AddMain('clients/main_client.htm');
			}

			if($r['client']){
                $design->assign('is_secondary_output',1);
                $this->showServersTroubles($r);
                StatModule::tt()->showTroubleList(1,'client',$r['client']);

                $design->AddMain("clients/service_header.htm");
                $isServiceEnabled = false;

                if(StatModule::services()->services_in_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_co_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_ppp_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_trunk_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_vo_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::routers()->routers_d_list($r['client'],1)) $isServiceEnabled = true;
				//StatModule::routers()->routers_d_list($r['client']);
                if(StatModule::services()->services_em_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_ex_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_it_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_welltime_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_virtpbx_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_sms_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_wellsystem_view($r['client'])) $isServiceEnabled = true;
                if(StatModule::services()->services_ad_view($r['client'])) $isServiceEnabled = true;
                $design->assign('log_company', ClientCS::getClientLog($r["id"], array("company_name")));

                $design->assign("is_service_enabled", $isServiceEnabled);

			}

		}else{
			$design->assign('log', ClientCS::getClientLog($r["id"]));

			$design->assign('selected_channel', $r['sale_channel']);
            $design->assign("l_metro", ClientCS::GetMetroList());
            $design->assign("sale_channels", ClientCS::GetSaleChannelsList());


            $R=array();
            StatModule::users()->d_users_get($R,'account_managers');
            StatModule::users()->d_users_get($R,'manager');

            $rAccountManagers = $rManagers = $R;

            if(isset($rAccountManagers[$r['account_manager']]))
                $rAccountManagers[$r['account_manager']]['selected']=' selected';
            $design->assign('account_managers',$rAccountManagers);

            if(isset($rManagers[$r['manager']]))
                $rManagers[$r['manager']]['selected']=' selected';
            $design->assign('users_manager',$rManagers);


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
		$_SESSION['clients_client'] = $r['client'];
    }

    function showServersTroubles($client)
    {
        global $db;

        $isData = false;
        $serverIds = [];

        foreach($db->AllRecords("
            SELECT DISTINCT region 
            FROM `usage_voip`
            WHERE client = '".$client["client"]."'
            AND CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to") as $r)
        {
            $region = $r["region"];

            foreach($db->AllRecords("
                SELECT s.id 
                FROM datacenter d, server_pbx s 
                WHERE region='".$region."' 
                AND d.id = s.datacenter_id 
                ORDER BY s.id") as $server)
            {
                $serverIds[] = $server["id"];
            }
        }

        if ($serverIds)
        {
            if(StatModule::tt()->showServerTroubles($serverIds))
            {
                $isData = true;
            }
        }

        return $isData;
    }

    function client_contragent($contragentId)
    {
        global $db;

        if ($clientId = $db->GetValue("select id from clients where contragent_id = '".$contragentId."' order by id"))
        {
            header("Location: ./?module=clients&id=".$clientId);
            exit();
        } else {
            trigger_error2("Контрагент не найден!");
        }
    }


	function clients_new() {
		global $design, $db,$user;
        $design->assign('mode_new',1);

        $currentUser = $user->Get('user');

        $R=array();
        StatModule::users()->d_users_get($R,'account_managers');
        StatModule::users()->d_users_get($R,'manager');

        $rAccountManagers = $rManagers = $R;

        if(isset($rAccountManagers[$currentUser]))
            $rAccountManagers[$currentUser]['selected']=' selected';
        $design->assign('account_managers',$rAccountManagers);

        if(isset($rManagers[$currentUser]))
            $rManagers[$currentUser]['selected']=' selected';
        $design->assign('users_manager',$rManagers);


        $client = [
            "client"=>"idNNNN",
            "credit"=>-1,
            "firma" => "mcn_telekom",
            "price_type" => ClientCS::GetIdByName("price_type", "Розница"),
            "password" => substr(md5(time()+rand(1,1000)*rand(10000,10000)), 3, 8),
            "voip_credit_limit_day" => 1000,
            "is_active" => 1,
            "contract_type_id" => 2,
            "business_process_id" => 1,
            "business_process_status_id" => 19,
            "credit" => 0,
            'timezone_name' => 'Europe/Moscow',
        ];
        $design->assign("client", $client);

        $design->assign("contract_types", ClientContractType::find()->orderBy("sort")->all());
        $design->assign("bussines_processes", ClientBP::find()->select(["id", "name"])->where(["client_contract_id" => $client["contract_type_id"]])->orderBy("sort")->all());
        $design->assign("bp_statuses", ClientGridSettings::find()->select(["id", "name"])->where(["grid_business_process_id" => $client["business_process_id"], "show_as_status" => 1])->orderBy("sort")->all());

        $bp = $this->clients_rpc_loadBPStatuses("", false);
        $design->assign("business_processes_all", json_encode($bp["processes"]));

        $design->assign("l_price_type", ClientCS::GetPriceTypeList());
        $design->assign("l_metro", ClientCS::GetMetroList());
        $design->assign("sale_channels", ClientCS::GetSaleChannelsList());
        $design->assign('regions',$db->AllRecords('select * from regions order by id desc', 'id'));

        $design->assign("history_flags", $this->get_history_flags(0));

        $timezones = \app\models\Region::find()->select('timezone_name')->groupBy('timezone_name')->indexBy('timezone_name')->asArray()->all();
        $design->assign('timezones', $timezones);

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

        if ($C->F['timezone_name'] != $_POST['timezone_name']) {
            $firstTransaction =
                \app\models\Transaction::find()
                    ->andWhere(['client_account_id' => $C->F['id']])
                    ->limit(1)
                    ->one();
            if ($firstTransaction) {
                trigger_error2("Не возможно изменить таймзону лицевого счета. По нему уже существуют финансовые транзакции");
            }
        }

		if(isset($_POST['cl_cards_operations'])){ // привязать к истории
			$cli = $db->GetRow("select * from clients where id=".((int)$_POST['id']));
			if(
				isset($_POST['previous_reincarnation'])
			&& $cli['previous_reincarnation'] <> $_POST['previous_reincarnation']
			&& $_POST['previous_reincarnation'] <> $_POST['id']
			&& !( !$cli['previous_reincarnation'] && !$_POST['previous_reincarnation'])
			){
				if(clCards\setParent($db, $user, $_POST['previous_reincarnation'], $cli['client']))
					trigger_error2("Предыдущие реквизиты успешно установлены");
				else
					trigger_error2("Не удалось установить предыдущие реквизиты");
			}
			if($_POST['move_usages'] && $_POST['move_usages']<>$_POST['id'] && $user->HasPrivelege('clients','moveUsages')){
				if(clCards\moveUsages($db, $user, $_POST['move_usages'], $_POST['client']))
					trigger_error2("Услуги перенесены");
				else
					trigger_error2("Перенести услуги не удалось");
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
							client_inn.is_active = 1
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
			trigger_error2('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Добавление невозможно');
		}else{
			if($dbl)
				trigger_error2('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Имейте в виду');
			if($C->Apply()){
				clCards\SyncAdditionCards($db, $cl_main_card);

				try {
					if (($Client = Sync1C::getClient())!==false)
					   $Client->saveClientCards($cl_main_card);
					else trigger_error2('Ошибка синхронизации с 1С.');
				} catch (Sync1CException $e) {
					$e->triggerError();
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
				trigger_error2("Клиент с таким кодом уже есть.");
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
            {
				$nc = $cl_main_card.'/a';
            } elseif ($last_pf == 'z')
            {
				trigger_error2("Количество договоров клиента достигло максимального кол-ва");
				return;
			} else {
				$nc = $cl_main_card.'/'.chr(ord($last_pf)+1);
            }
		}

        $cp_fields = " password, password_type, company, comment, address_jur, status, usd_rate_percent, company_full, address_post, address_post_real, type, manager, login, inn, kpp, bik, bank_properties, signer_name, signer_position, signer_nameV, firma, currency, stamp, nal, sale_channel, uid, site_req_no, signer_positionV, credit, user_impersonate, address_connect, phone_connect, id_all4net, dealer_comment, form_type, metro_id, payment_comment, bank_city, bank_name, pay_acc, corr_acc";

		# client_contacts
		$db->Query('start transaction');
		$q = "
			insert into clients (
                    client, ".$cp_fields."
			) select
				'".$nc."',
                ".$cp_fields."
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

		try {
			if ($syncClient = Sync1C::getClient())
                $syncClient->saveClientCard($id);
		} catch (Sync1CException $e) {
			$e->triggerError();
		}

		Header("Location: ?module=clients&id=".$id);
		exit();
	}
	function clients_restatus() {
		global $design;
		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$comment=get_param_protected('comment');
        $contractTypeId=get_param_protected('contract_type_id', 0);
        $businessProcessId=get_param_protected('business_process_id', 0);
        $businessProcessStatusId=get_param_protected('business_process_status_id', 0);

		$cs=new ClientCS($id);
		$cs->Add($comment);
		$cs->SetContractType($contractTypeId, $businessProcessId, $businessProcessStatusId);
                
        event::go("client_set_status", $id);

        voipNumbers::check();
		$this->client_view($id);
	}
	function clients_files($fixclient) {
		global $db,$design;

        $cId = get_param_integer("cid", 0);

		$client = ClientCS::FetchClient($cId ? $cId : $fixclient);
		if ($this->check_tele($client['id'])==0) return;

        $a = ClientAccount::findOne($cId);

        if (!$a)
            throw new Exception("ЛС не найден");

        $design->assign('files', $a->files);
        $design->assign('client_id', $cId);

		$design->AddMain('clients/files.tpl');
	}
	function clients_files_report() {
		global $db,$design;
		$manager = get_param_protected('manager');
		$dateFrom = new DatePickerValues('date_from', 'today');
                $dateTo = new DatePickerValues('date_to', 'today');

                $from = $dateFrom->getTimestamp();
                $to = $dateTo->getTimestamp();
                
                DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');
                DatePickerPeriods::assignPeriods(new DateTime());
                
		$R=array(); StatModule::users()->d_users_get($R,'manager');
		$design->assign('users',$R);
		$design->assign('manager',$manager);

		$R = $db->AllRecords('select client_files.*,UNIX_TIMESTAMP(client_files.ts) as ts,clients.client as client_client,clients.company as client_company,user_users.user as user,clients.manager as client_manager'.
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

        $clientId = get_param_integer("cid", 0);
		$client = ClientCS::FetchClient($clientId);
		if ($this->check_tele($client['id'])==0) return;

        $a = ClientAccount::findOne($clientId);

        if (!$a)
            throw new Exception("ЛС не найден");

        $a->fileManager->addFile(get_param_protected('comment'), get_param_protected('name'));

        header('Location: /?module=clients&action=files&cid='.$a->id);
        exit();
	}
    function clients_file_get($fixclient) {
        global $design;

        $cid = get_param_integer('cid');
        $fileId = get_param_protected('id');

        if ($this->check_tele($cid)==0) return;

        $f = ClientFile::findOne(["client_id" => $cid, "id" => $fileId]);
        if (!$f)
            throw new Exception("Файл не найден");

        header("Content-Type: ".$f->mime);
        header("Pragma: ");
        header("Cache-Control: ");
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.iconv("UTF-8","CP1251",$f->name).'"');
        header("Content-Length: " . strlen($f->content));
        echo $f->content;
        exit();
    }

    function clients_file_send($fixclient)
    {
        global $design, $db;

        $clientId = get_param_protected("cid");
        $id = get_param_protected("id");

        $design->assign("emails", $ee = $db->AllRecordsAssoc("select data from client_contacts where client_id = '".$clientId."' and type='email' and is_official and is_active", "data", "data"));

        $a = ClientAccount::findOne($clientId);

        if (!$a)
            throw new Exception("ЛС не найден");

		$f = ClientFile::findOne(["client_id" => $a->id, "id" => $id]);

        if (!$f)
            throw new Exception("Файл не найден");

        $design->assign("file_name", $f->name);
        $design->assign("file_name_send", $f->name);
        $design->assign("file_content", base64_encode($f->content));
        $design->assign("msg_session", md5(rand()+time()));
        $design->assign("file_mime", $f->mime);
        $design->AddMain("clients/file_send.tpl");
    }


	function clients_file_del($fixclient) {
        global $design;

        $clientId = get_param_protected("id");
		$client = ClientCS::FetchClient($clientId);
        if ($this->check_tele($client['id'])==0) return;

        $a = ClientAccount::findOne($clientId);

        if ($a)
        {
            $a->fileManager->removeFile(get_param_raw("file_id"));
        }

        header('Location: /?module=clients&action=files&cid='.$a->id);
        exit();
	}
    function clients_recontact() {
        global $design;
        
        $id=get_param_protected('id');

        if (get_param_raw("add_contact"))
        {
            if ($this->check_tele($id)==0) return;
            $cs=new ClientCS($id);
            $type = get_param_protected('type');
            $data = get_param_protected('data');
            $dataId = $cs->AddContact($type,$data,get_param_protected('comment'),get_param_protected('official')?1:0);
            event::go("contact_add_".$type, array("client_id" => $id, "contact_id" => $dataId, "email" => $data));
        } elseif (get_param_raw("set_admin")) {

            $adminContactId = get_param_integer('admin_contact');

            if ($adminContactId && ($cc = ClientContact::find('first', array('id' => $adminContactId, 'is_official' => 1, 'is_active' => 1)))){
                $adminContactId = $cc->id;
            } else {
                $adminContactId = 0;
            }

            if ($cl = ClientCard::find($id))
            {
                $cl->admin_contact_id = $adminContactId;
                $cl->admin_is_active = 1;
                $cl->save();
            }
        }
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
			if (!$dbl && ($r = $db->getRow('select client from clients inner join client_inn on client_inn.client_id=clients.id and client_inn.is_active=1 where client_inn.inn="'.$inn.'"'))) $dbl = $r['client'];
			if (access('clients','inn_double') || !$dbl) {
				$db->QueryInsert('client_inn',array('ts'=>array('NOW()'),'client_id'=>$id,'user_id'=>$user->Get('id'),'inn'=>$inn,'comment'=>$comment));
				if ($dbl) {
					trigger_error2('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Имейте в виду');
					return $this->client_view($id,1);
				}
			} else {
				trigger_error2('Такой же ИНН есть, как минимум, у клиента '.$dbl.'. Добавление невозможно');
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
    function clients_recontactLK() {
        global $design,$db,$user;
        $id=get_param_protected('id');
        if ($this->check_tele($id)==0) return;
        $cid=get_param_protected('cid');
        $active=get_param_raw('act');
        $design->assign('contact_open',true);
        $cs=new ClientCS($id);
        $cs->ActivateContactLK($cid,$active);
        $this->client_view($id);
    }
	function clients_recontract($clientClient){
		global $design,$db;
		$id=get_param_protected('id');
		if($this->check_tele($id)==0)
            return;


        $content = get_param_raw('contract_content');
        $contractType = get_param_raw("contract_type", "contract");
        $contractGroup = get_param_raw("contract_template_group");
        $contractTemplate = get_param_protected('contract_template');
        $contractDate = get_param_protected('contract_date');
        $contractNo = get_param_protected('contract_no');


        $contractId = ClientDocument::dao()->addContract(
            $id,

            $contractType,
            $contractGroup,
            $contractTemplate,

			$contractNo,
            $contractDate,

            $content,
			get_param_protected('comment')
		);

		header("Location: ./?module=clients&id=".$id."&contract_open=true");
		exit();
	}
	function clients_recontract2() {
		global $design,$db,$user;
		$id=get_param_protected('id');
		if ($this->check_tele($id)==0) return;
		$cid=get_param_protected('cid');
		$active=get_param_integer('act');
		$design->assign('contract_open',true);
		$db->Query('update client_document set is_active="'.$active.'",ts=NOW(),user_id="'.$user->Get('id').'" where client_id="'.$id.'" and id="'.$cid.'"');
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
			trigger_error2('Такой же ИНН есть, как минимум, у клиента '.$isInnDbl.'. Добавление невозможно');
		}else{
			if($isInnDbl) {
                trigger_error2('Такой же ИНН есть, как минимум, у клиента ' . $isInnDbl . '. Имейте в виду');
            }

            if ($C->Create()){

                $contractTypeId=get_param_protected('contract_type_id', 0);
                $businessProcessId=get_param_protected('business_process_id', 0);
                $businessProcessStatusId=get_param_protected('business_process_status_id', 0);

                $C->SetContractType($contractTypeId, $businessProcessId, $businessProcessStatusId);

                try {
                    if ($syncClient = Sync1C::getClient())
                        $syncClient->saveClientCard($C->F['id']);

                } catch (Sync1CException $e) {
                    $e->triggerError();
                }

                $this->client_view($C->id,1);
                return ;
            }else{
                trigger_error2("Такой клиент уже существует.");
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
			$db->Query('set names utf8');
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

				$design->assign('client_company',escape_html_to_svg($row['company_full']));
				$design->assign('page1',array(
					'city'=>'Москва',
					'year_single_digit'=>substr(date('Y'),3),
					'in_face'=>$row['signer_positionV'].' '.$row['signer_nameV'],
					'with_base'=>$row['with_base']==''?'устава':$row['with_base']
				));
				$design->assign('page6',array(
					'address_jur_zip'=>$address_jur['zip'],
					'address_jur_city'=>$address_jur['city'],
					'address_jur_other'=>$address_jur['street'].' '.$address_jur['housenum'].' '.$address_jur['housebuild'],
					'address_post_zip'=>$address_post['zip'],
					'address_post_city'=>$address_post['city'],
					'address_post_other'=>$address_post['street'].' '.$address_post['housenum'].' '.$address_post['housebuild'],
					'phone'=>$row['phone'],
					'fax'=>$row['fax'],
					'phone_fax_separator'=>($row['phone'] && $row['fax'])?' / ':'',
					'email'=>$row['email'],
					'inn'=>$row['inn'],
					'kpp'=>$row['kpp'],
					'bik'=>$row['bik'],
					'bank_pay_acc'=>$bank['pay_account'],
					'bank_address'=>$bank['address'],
					'bank_cor_acc'=>$bank['cor_account'],
					'client_name'=>$row['signer_name']
				));

				$design->assign('page7',array(
					'in_face'=>$row['signer_positionV'].' '.$row['signer_nameV']
				));

				$design->assign('page8',array(
					'address_street'=>$address_jur['street'],
					'address_housenum'=>$address_jur['housenum'],
					'address_housebuild'=>$address_jur['housebuild'],
					'client_position'=>$row['signer_position'],
					'partner'=>'ООО "Эм Си Эн"',
					'contact'=>$row['contact_name']
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



	function clients_rpc_findClient1c(){
		require_once INCLUDE_PATH.'1c_integration.php';
		$clS = new \_1c\clientSyncer($db);

		$cl = $clS->findClient(null, null, $_GET['findInn']);

		if(!$cl)
			echo "false";
		else{
			Header('Contetn-Type: plain/text; charset="utf-8"');
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

    $bik = $db->GetRow("select * from bik b where b.bik='".$db->escape($_GET['findBik'])."'");

		if(!$bik)
			echo "false";
		else{
			Header('Contetn-Type: plain/text; charset="utf-8"');
			echo "{
				bik:'".$bik['bik']."',
				corr_acc:'".$bik['corr_acc']."',
				bank_name:'".$bik['bank_name']."',
				bank_city:'".$bik['bank_city']."'
			}";
		}
		exit();
    }
    public function clients_rpc_setVoipDisabled()
    {
        $accountId = get_param_integer("account_id", 0);
        $isDisabled = (get_param_raw("is_disabled", "false") == "true");

        $client = clientAccount::findOne($accountId);

        Assert::isObject($client);

        if ((bool)$client->voip_disabled != $isDisabled)
        {
            $client->voip_disabled = $isDisabled ? 1 : 0;
            $client->save();
        }
        echo "ok";
        exit();
    }

    public function clients_rpc_setBlocked($fixclient)
    {
        $accountId = get_param_integer("account_id", 0);
        $isBlocked = (get_param_raw("is_blocked", "false") == "true" ? 1 : 0);

        $client = ClientAccount::findOne(["id" => $accountId]);

        Assert::isObject($client);

        if ($isBlocked != $client->is_blocked)
        {
            $client->is_blocked = $isBlocked ? 1 : 0;
            $client->save();
        }

        echo "ok";
        exit();
    }

    public function clients_rpc_loadBPStatuses($fixclient, $isJSON = true)
    {
        $processes = [];
        foreach(ClientBP::find()->orderBy("sort")->all() as $b)
        {
            $processes[] = ["id" => $b->id, "up_id" => $b->client_contract_id, "name" => $b->name];
        }

        $statuses = [];
        foreach(ClientGridSettings::find()->select(["id", "name", "grid_business_process_id"])->where(["show_as_status" => 1])->orderBy("sort")->all() as $s)
        {
            $statuses[] = ["id" => $s->id, "name" => $s->name, "up_id" => $s->grid_business_process_id];
        }

        $res = ["processes" => $processes, "statuses" => $statuses];

        if ($isJSON)
        {
            echo json_encode($res);
            exit();
        } else {
            return $res;
        }
    }

	function get_history_flags($clientId)
	{
		global $db;

		$past = date("Y-m-01"); // с начала текущего месяца
		$future = date("Y-m-01", strtotime("+1 month", strtotime(date("Y-m-01")))); // с начала следующего месяца

		static $a = array();
		$a = array("d_past" => $past, "d_future" => $future, "past" => 0, "future" => 0,
			"m" => array(
				"0" => array("d" => strtotime("-3 month", strtotime(date("Y-m-01"))), "v"=>0),
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

        $clientId = get_param_raw("client_id", 0);
        $superId = get_param_raw("super_id", 0);

        $isAdmin = access("clients", "history_edit") && $clientId;

        $design->assign("isAdmin", $isAdmin);
        $design->assign("user_id", $user->Get("id"));

        $field = $superId ? "super_id" : "client_id";
        $id = $superId ?: $clientId;


        if($isAdmin)
        {
            historyViewAction::check($clientId);
            $design->assign("c", ClientCS::getOnDate($clientId, date("Y-m-d")));
        }


		$log = $db->AllRecords(
			"select user_id,client_id, lc.id, lc.ts, u.name, is_overwrited, is_apply_set, unix_timestamp(apply_ts) as apply_ts
			from log_client lc
			left join user_users u on (u.id = lc.user_id)
			where lc.".$field." = '".$id."' and lc.type='fields'
			order by lc.id desc");


		foreach($log as $idx => $l)
		{
			//$log[$idx]["ts"] = mdate("d месяца Y", $log[$idx]["ts"])." ".date("H:i:s",$log[$idx]["ts"]);
			$log[$idx]["apply"] = $l["apply_ts"] ? array((int)date("Y", $l["apply_ts"]), (int)date("m", $l["apply_ts"]), (int)date("d", $l["apply_ts"])): false;
			$log[$idx]["apply_ts"] = $l["apply_ts"]  ? mdate("d месяца Y", $l["apply_ts"]) : false;
			$log[$idx]["fields"] = array();

			foreach($db->AllRecords("select * from log_client_fields where ver_id = '".$l["id"]."'") as $f)
			{
				if($f["field"] == "voip_is_day_calc" && $f["value_from"] == "0" && $f["value_to"] == "")
				{
					// skip
				}else{
					$f["name"] = $this->_view_history__getFieldName($f["field"]);
					$log[$idx]["fields"][] = $f;
				}
			}

			if(!$log[$idx]["fields"])
			{
				$log[$idx]["fields"][] = array("name" => "Изменены поля", "value_from" => isset($l["comment"]) ?: '', "value_to" => false);
			}
		}

		$design->assign("log", $log);
		$design->assign("view_only", get_param_raw("view_only", "false") == "true");
		$design->ProcessEx("clients/history.htm");
	}



	function _view_history__getFieldName($l)
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
			"head_company_address_jur" => "Юр. адрес головной компании",
            "nds_calc_method" => "Метод расчета НДС",
            "name" => "Название",
            "account_manager" => "Аккаунт менеджер"
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

    public function clients_client_edit($fixclient)
    {
        global $design;

        $client = app\models\ClientSuper::findOne(get_param_raw("id"));
        Assert::isObject($client);

        if (get_param_raw("save"))
        {
            $name = get_param_raw("name");
            $accountManager = get_param_raw("account_manager");

            Assert::isNotEmpty($name, "Имя не задано");

            if ($name != $client->name || $accountManager != $client->account_manager)
            {
                $client->name = $name;
                $client->account_manager = $accountManager;
                $client->save();
            }
        }

        $accountManagers=array();
        StatModule::users()->d_users_get($accountManagers, 'account_managers');

        $design->assign("client", $client);
        $design->assign("account_managers", $accountManagers);
        $design->AddMain("clients/client_edit.htm");
    }

    public function clients_contragent_edit($fixclient)
    {
        global $design;


        $design->AddMain("clients/contragent_edit.html");
    }

    public function clients_publish_comment($fixclient)
    {
        $accountId = get_param_raw("account_id", 0);
        $statusId  = get_param_raw("status_id", 0);
        $isPublish = (int)(get_param_raw("publish", "false") == "true");


        $comment = ClientStatuses::find()->where(["id" => $statusId, "id_client" => $accountId])->one();

        Assert::isObject($comment);

        if ($comment)
        {
            if ($comment->is_publish != $isPublish)
            {
                $comment->is_publish = $isPublish;
                $comment->save();
            }
        }
    }

    public function clients_contract_form($fixclient)
    {
        global $design;

        $clientAccount = ClientAccount::find()->andWhere(['client' => $fixclient])->one();
        Assert::isObject($clientAccount);

        $cs = new ClientCS($clientAccount->id);

        $d = $cs->GetContracts();

        $maxs = ["agreement" => 0, "blank" => 0];

        foreach ($d as $k=>$vv){
            foreach($vv as $k2 => $v){
                $p = data_encode($v['id'].'-'.$v['client_id']);
                $d[$k][$k2]['link']=PROTOCOL_STRING.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/view.php?code='.$p;

                if ($k == "agreement" || $k == "blank")
                {
                    if (preg_match("/^\d{1,3}$/", $v["contract_dop_no"]))
                    {
                        $maxs[$k] = max($maxs[$k], $v["contract_dop_no"]);
                    }
                }
            }
        }

        $design->assign('contracts', $d);
        $design->assign('contract_start_numbers', $maxs);
        $design->assign('templates',ClientDocument::dao()->contract_listTemplates(true));
        $design->assign("client_id", $clientAccount->id);

        echo $design->fetch("clients/contract/form.htm");

        exit();
    }
}

