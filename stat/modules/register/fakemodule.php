<?
class m_register extends IModule {
	public $callStyle = 'new';
	public $actions = array ('default'=>'','apply'=>'','success'=>'','validate'=>'');
	
	function action_default () {
		global $design,$db;	
		$design->AddMain('register_form.tpl');
	}
	function action_success () {
		global $design,$db;
		$design->assign('reg_login',get_param_protected('login'));
		$design->AddMain('register_success.tpl');
		$design->AddMain('login.tpl');
	}
	private static function ValidationCode($id,$name) {
		return md5($id.md5(UDATA_SECRET.md5($name)));
	}
	function action_validate() {
		global $design,$db;
		$id = get_param_integer('id');
		$code = get_param_raw('code');
		$r = $db->GetRow('select * from clients where id='.$id);
		if ($r['user_impersonate']=='client_autoreg_unvalidated') {
			if (self::ValidationCode($r['id'],$r['client'])==$code) {
				trigger_error('Спасибо, ваш e-mail подтверждён.');
				global $module_phone;
				$F = $module_phone->voipGetData($r['client'],true,null,false);
				$r = $db->GetRow('select id from tarifs_voip where is_clientSelectable=1 and status="public" order by month_number asc');
				$F['new_tarif_id'] = $r['id'];
				$module_phone->voipSaveData($F);
				$db->QueryUpdate('clients','id',array('id'=>$id,'user_impersonate'=>'client_autoreg'));
			} else trigger_error('Неверный код');
		}
		$design->AddMain('login.tpl');
	}
	function action_apply(){
		global $design,$db;
		$regform=array(
			'user'	=> get_param_protected('user'),
			'pass'		=> get_param_protected('pass'),
			'pass2'		=> get_param_protected('pass2'),
			'name'		=> get_param_protected('name'),
			);
		$err=0;
		if ($regform['pass']!=$regform['pass2']) {$err=1; trigger_error('Пароли не совпадают');}
		if (strlen($regform['pass'])<3) {$err=1; trigger_error('Введите пароль в 3 символа или длиннее');}
		if (preg_match('/[^\d\w]\_\-]/',$regform['user'])) {$err=1; trigger_error('Допустимые символы в поле "Логин" - цифры, английские и русские буквы, знаки _-');}
		$r=$db->GetRow('select count(*) as C from user_users where user="'.$regform['user'].'"');
		if ($r['C']!=0) {$err=1; trigger_error('Такой пользователь уже существует');}
		$r=$db->GetRow('select count(*) as C from clients where client="'.$regform['user'].'"');
		if ($r['C']!=0) {$err=1; trigger_error('Такой пользователь уже существует');}
		if (!$regform['name']) {$err=1; trigger_error('Заполните поле "Полное имя" (хоть чем-нибудь)');}
		if ($err){
			$design->assign('regform',$regform);
			$design->AddMain('m_register_form.tpl');	
		} else {
			$id = $db->QueryInsert('clients',array('client'=>$regform['user'],'company'=>$regform['name'],'email'=>$regform['user'],'password'=>$regform['pass'],'credit'=>0,'user_impersonate'=>'client_autoreg_unvalidated'));
			$subj = 'пЕЦХЯРПЮЖХЪ Б MCN';
			$body = "бШ ГЮПЕЦХЯРПХПНБЮМШ.\nкНЦХМ: ".$regform['user']."\nоЮПНКЭ: ".$regform['pass']."\n\nдКЪ ГЮБЕПЬЕМХЪ ПЕЦХЯРПЮЖХХ ОПНИДХРЕ ОН ЩРНИ ЯЯШКЙЕ:\n";
			$body.= WEB_ADDRESS.WEB_PATH."register.php?action=validate&id=".$id."&code=".self::ValidationCode($id,$regform['user'])."\n";
			$body.= $id.'='.$regform['user'].'=';
			$headers = "From: MCN Info <info@mcn.ru>\n";
			$headers.= "Content-Type: text/plain; charset=windows-1251\n";
			if (!mail($regform['user'],$subj,$body,$headers)) {
				trigger_error('Регистрация не удалась. Лучше всего - обратитесь к нам по телефону.');
				$db->Query('delete from clients where id='.$id);
			} else {
				if ($design->ProcessEx('errors.tpl')) {
                    header("Location: register.php?action=success&login=".$regform['user']);
                    exit;
                }
			}
		}
	}
}
?>