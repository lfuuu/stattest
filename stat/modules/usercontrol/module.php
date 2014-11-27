<?
define('UC_IMAGESIZE',250);
class m_usercontrol {	
	var $actions=array(
					'default'			=> array('usercontrol','r'),
					'edit_pass'			=> array('usercontrol','edit_pass'),
					'apply_pass'		=> array('usercontrol','edit_pass'),
					'edit'				=> array('usercontrol','edit_full'),
					'apply'				=> array('usercontrol','edit_full'),

					'ex_flag'			=> array('',''),
				);

	//содержимое левого меню. array(название; действие (для проверки прав доступа); доп. параметры - строкой, начинающейся с & (при необходимости); картиночка ; доп. текст)
	var $menu=array(
					array('Информация',			'default'),
					array('Изменение пароля',	'edit_pass'),
					array('Изменение профайла', 'edit'),
				);

	function m_usercontrol(){	
		
	
	}

	function GetPanel($fixclient){
		$R=array();
		foreach($this->menu as $val){
			$act=$this->actions[$val[1]];
			if (access($act[0],$act[1])) $R[]=array($val[0],'module=usercontrol&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
		}
		if (count($R)>0){
            return array('О пользователе',$R);
		}
	}

	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;

		call_user_func(array($this,'usercontrol_'.$action),$fixclient);
	}
	
	//просмотр списка пользователей
	function usercontrol_default() {
		global $design,$db,$user;
		$c=$user->GetAsClient();
		if ($c){
			$db->Query('select * from clients where client="'.$c.'"');
			$r=$db->NextRecord();
			$design->assign('client',$r);
			if ($s = $db->getRow("select * from client_statuses where (id_client='".$r['id']."') and status!='' order by ts desc limit 1")) {
				$s['status_name'] = (isset(ClientCS::$statuses[$s['status']]) ? ClientCS::$statuses[$s['status']]['name'] : $s['status']);;
				$design->assign('status',$s);
			}
			
			$C = array();
			if (access('usercontrol','dealer')) {
				$sc = $db->getRow('select * from sale_channels where dealer_id = '.$r['id']);
				$T = array('usage_ip_ports'=>'Интернет','usage_voip'=>'IP-телефония','bill_monthlyadd'=>'Доп. услуги','usage_extra'=>'Доп. услуги','emails'=>'Почта');
				foreach ($db->AllRecords('select * from clients where sale_channel = '.$sc['id'].' order by id') as $r2) {
					$s = $db->getRow("select * from client_statuses where (id_client='".$r2['id']."') and status!='' order by ts desc limit 1");
					$r2['status_name'] = (isset(ClientCS::$statuses[$s['status']]) ? ClientCS::$statuses[$s['status']]['name'] : $s['status']);
					$r2['services'] = array();
					foreach (get_all_services($r2['client'],$r2['id']) as $v) {
						if ($v['status']=='working' || $v['status']=='connecting') {
							$v['service_name'] = (isset($T[$v['service']]))?$T[$v['service']]:'';
							$r2['services'][] = $v;
						}
					}

					$C[] = $r2;
				}
			}
			$design->assign('clients',$C);
			$design->AddMain('usercontrol/client.tpl');
		} else {
			$design->AddMain('usercontrol/operator.tpl');
		}
	}

	//работа со одним пользователем
	function usercontrol_edit() {
		global $design,$db,$user;
		$design->AddMain('usercontrol/edit.tpl');
	}
	function usercontrol_apply(){
		global $design,$db,$user;
		$name=get_param_protected('name');
		$email=get_param_protected('email');
		$icq=get_param_protected('icq');
		$phone_mobile=get_param_protected('phone_mobile');
		$phone_work=get_param_protected('phone_work');
		$q_photo=$this->process_photo($user->Get('id'));
		$db->Query('update user_users set name="'.$name.'",email="'.$email.'",icq="'.$icq.'",phone_work="'.$phone_work.'",phone_mobile="'.$phone_mobile.'"'.$q_photo.' where user="'.$user->Get('user').'"');
		$this->usercontrol_edit();
	}

	//работа с пользователями и с клиентами	
	function usercontrol_edit_pass() {
		global $design,$db,$user;
		$design->AddMain('usercontrol/chpass.tpl');
	}

	function usercontrol_apply_pass() {
		global $design,$db,$user;
		$pass=get_param_protected('pass');
		$pass2=get_param_protected('pass2');
		$password=get_param_protected('password');
		if (!$pass && !$pass2){
			trigger_error2('Пароль не изменился');
			$this->usercontrol_default();
		} else if ($pass!=$pass2){
			trigger_error2('Пароли не совпадают');
			$this->usercontrol_edit_pass();
		} else {
			if ($c=$user->GetAsClient()){
				$db->Query('select count(*) from clients where (client="'.$c.'") and (password="'.$password.'")');
			} else $db->Query('select count(*) from user_users where (user="'.$user->Get('user').'") and (pass="'.password::hash($password).'")');


			$r=$db->NextRecord();
			if ($r[0]==1){
				trigger_error2('Ваш новый пароль - '.$pass);
				$c=$user->GetAsClient();
				if ($c){
					$db->Query('select * from user_users where user="'.$user->Get('user').'"');
				} else {
					$db->Query('select * from clients where client="'.$c.'"');
				}
				$r=$db->NextRecord();

				$message = "Вы изменили пароль на сервере ".PROTOCOL_STRING.$_SERVER['HTTP_HOST']."\n";
				$message.= "Ваш новый пароль - ".$pass."\n";
				$message.= "Запишите его в надёжном месте (лучше всего - в голове) и постарайтесь не забывать" . "\n";
				$message.= "\n\n\n";
				$message.= "You have changed password at ".PROTOCOL_STRING.$_SERVER['HTTP_HOST']." server\n";
				$message.= "Your new password is ".$pass."\n";
				$message.= "Please, write it in private place and try not to forget." . "\n";
				$message.= "\n\n\n";
				
				$b=@mail($r['email'],'MCN.ru - ваш новый пароль | your new password',$message,"Reply-To: support@mcn.ru\nFrom: support@mcn.ru\nContent-Type: plain/text; charset=utf-8\n");
				if ($b){
					trigger_error2('Письмо с уведомлением отправлено на '.$r['email']);
					trigger_error2('В случае возникновения проблем обращайтесь в службу поддержки');
				} else {
					trigger_error2('Письмо с уведомлением на '.$r['email'].' отправить не удалось. Обратитесь в службу поддержки');
				}
				
				if ($c=$user->GetAsClient()){
					$db->Query('update clients set password="'.$pass.'" where client="'.$c.'"');
				} else {
					$db->Query('update user_users set pass="'.password::hash($pass).'" where user="'.$user->Get('user').'"');
				}
				$this->usercontrol_default();
			} else {
				trigger_error2('Вы ошиблись в наборе старого пароля.');
				$this->usercontrol_edit_pass();
			}
		}
	}

	function process_photo($id){
		global $db,$user;
		$q_photo='';
		$change=get_param_protected('photo_change',0);
		if (isset($_FILES['photo'])) $photo=$_FILES['photo'];
		if ($change==1){
			if (isset($photo)){
				preg_match('/\.(.+)$/',$photo['name'],$m);
				$size=GetImageSize($photo['tmp_name']);
				$moved=0;
				if (($size[0]>UC_IMAGESIZE) || ($size[1]>UC_IMAGESIZE)){
					$im='';
					if ($size[2]==1) $im=ImageCreateFromGif($photo['tmp_name']); else
						if ($size[2]==2) $im=ImageCreateFromJpeg($photo['tmp_name']); else
							if ($size[2]==3) $im=ImageCreateFromPng($photo['tmp_name']);
					if ($im) {
						$mx=($size[0]>$size[1])?$size[0]:$size[1];
						$nW=floor(UC_IMAGESIZE*$size[0]/$mx);						
						$nH=floor(UC_IMAGESIZE*$size[1]/$mx);
						$im2=ImageCreateTrueColor($nW,$nH);
						ImageCopyResampled($im2,$im,0,0,0,0,$nW,$nH,$size[0],$size[1]);
						ImageJpeg($im2,IMAGES_PATH.'users/'.$id.'.jpg',65);
						$q_photo=',photo="jpg"';
						$moved=1;
					} else trigger_error2('Невозможно изменить размер картинки.');
				}
				if (!$moved) {
					move_uploaded_file($photo['tmp_name'],IMAGES_PATH.'users/'.$id.'.'.$m[1]);
					$q_photo=',photo="'.$m[1].'"';
				}
			} else {
				$q_photo=',photo=""';		
			}
		}
		return $q_photo;
	}

	function usercontrol_ex_flag(){
		global $db,$user;
		if (!access('usercontrol','edit_flags')) exit;
		$flag=get_param_protected('flag'); if (!$flag) exit;
		$value=get_param_integer('value');
		$user->SetFlag($flag,$value);
		exit;
	}
}

	
?>
