<?php
use app\classes\StatModule;
use app\models\ClientAccount;
use app\models\ClientDocument;
use app\models\ClientFile;

//просмотр списка клиентов с фильтрами и поиском / просмотр информации о конкретном клиенте
class m_clients {
	var $actions=array(
					'default'		=> array('clients','read'),
					'recontract'	=> array('',''),
					'recontract2'	=> array('',''),
					'send'	=> array('',''),
					'contract_edit'	=> array('',''),
					'contract_form'	=> array('',''),
					'mkcontract'	=> array('clients','new'),
					'print'			=> array('clients','read'),
					'print_yota_contract' => array('clients','file'),
					'rpc_findClient1c'	=> array('clients','new'),
					'rpc_findBank1c'	=> array('clients','new'),
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
			$r = \app\models\HistoryVersion::getVersionOnDate(ClientAccount::className(), $id, $c['contract_date']);
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
		$client = \app\models\HistoryVersion::getVersionOnDate(ClientAccount::className(), $contract['client_id'], $contract['contract_date']);
		$design->assign('contract',$contract);
		$design->assign('client',$client);
        $design->assign('content',ClientDocument::dao()->getTemplate($client['id'].'-'.$contract['id']));

		$design->AddMain('clients/contract_edit.tpl');
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

		Header("Location: /clients/clientview?id=".$id);
		exit();
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

			header('Location: /client/clientview?id='.$pid);
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

