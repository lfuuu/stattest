<?
class m_phone extends IModule {	
	function phone_default($fixclient){
		global $db,$design;
		if (access('phone','readdr')) return $this->phone_redir($fixclient);
	}
	function _get_conditions() {
		global $db;
		$A=$db->AllRecords('select * from usage_phone_redir_conditions');
		$R=array();
		foreach ($A as $r) {
			$r['data']=$db->AllRecords('select * from usage_phone_redir_condition_data where condition_id='.$r['id']);
			$R[$r['id']]=$r;
		}
		return $R;
	}
	function _has_conflict($a,$b) {
		if ($a['id']==$b['id']) return false;
		if ($a['type']!=$b['type']) return false;
		if ($a['type']=='DAMAGE') return true;
		//���� �� �����أ���� ��������
		return false;
	}
	function phone_asterisk() {
		global $db,$design;
		$F = fopen(PATH_TO_ROOT.'asterisk-sip.conf','r');
		$s = fread($F,1024);
		fclose($F);
		$design->assign('conf_sip',$s);
		
		$F = fopen(PATH_TO_ROOT.'asterisk-extensions.conf','r');
		$s = fread($F,1024);
		fclose($F);
		$design->assign('conf_ext',$s);
		$design->AddMain('phone/asterisk.tpl');
	}
	function phone_asterisk_reload() {
		$d = getcwd();
		chdir(PATH_TO_ROOT);
		exec('/usr/bin/php -f asterisk-config.php');
		exec('/usr/bin/sudo /usr/sbin/asterisk -rx reload');
		trigger_error('������������ ���������');
		chdir($d);
		return $this->phone_asterisk();
	}
	function phone_redir($fixclient) {
		global $db,$design;
		$design->assign('conditions',$C=$this->_get_conditions());
		$R=$db->AllRecords('select usage_phone_redir.*,usage_voip.E164 from usage_phone_redir INNER JOIN usage_voip ON usage_voip.id=usage_phone_redir.voip_id AND usage_voip.client="'.$fixclient.'"');
		foreach ($R as &$r) {
			$r['conditions']=array();
			foreach ($C as $v) if (!$this->_has_conflict($v,$C[$r['condition_id']])) $r['conditions'][]=$v;
		}
		unset($r);

		$V=$db->AllRecords('select * from usage_voip where client="'.$fixclient.'" and actual_from<=NOW() and actual_to>=NOW()');
		$design->assign('redirs',$R);
		$design->assign('voips',$V);
		$design->AddMain('phone/redir.tpl');
	}
	function phone_redir_save($fixclient) {
		global $db,$design;
		$id=get_param_integer('id','');
		$R=array('condition_id'=>get_param_integer('condition_id'),'number'=>get_param_protected('number'));
		if (!$id) {
			$R['voip_id']=get_param_integer('voip_id');
			if (!$db->GetRow('select * from usage_voip where id='.$R['voip_id'].' and client="'.$fixclient.'"')) return;
			$db->QueryInsert('usage_phone_redir',$R);
		} else {
			$R['id']=$id;
			if (!$db->GetRow('select 1 from usage_phone_redir INNER JOIN usage_voip ON usage_voip.id=usage_phone_redir.voip_id where usage_phone_redir.id="'.$id.'" and client="'.$fixclient.'"')) return;
			$db->QueryUpdate('usage_phone_redir','id',$R);
		}
		header("Location: ".$design->LINK_START."module=phone&action=redir");
        exit;
		$design->ProcessEx('errors.tpl');
	}
	function phone_redir_del($fixclient) {
		global $db,$design;
		$id=get_param_integer('id',0);
		if (!$db->GetRow('select 1 from usage_phone_redir INNER JOIN usage_voip ON usage_voip.id=usage_phone_redir.voip_id where usage_phone_redir.id="'.$id.'" and client="'.$fixclient.'"')) return;
		$db->Query('delete from usage_phone_redir where id="'.$id.'"');
		header("Location: ".$design->LINK_START."module=phone&action=redir");
        exit;
		$design->ProcessEx('errors.tpl');
	}
	function phone_callback($fixclient){
		global $db,$design;
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$db->Query('select * from usage_phone_callback where client="'.$fixclient.'" and actual_to>NOW()');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign('phones_callback',$R);
		
		$db->Query('select * from usage_phone_callback where client="'.$fixclient.'" and actual_to<=NOW()');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign("del_phones",$R);
		$design->AddMain('phone/callback.tpl');	
	}
	function phone_callback_add($fixclient){
		global $db,$design;
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$phone=digits(get_param_protected('phone'));
		$type_phone=get_param_protected('type_phone');
		$type_dialplan=get_param_protected("type_dialplan");
		$actual_from=date("Y-m-d");
		$actual_to="2029-01-01";
		$comment=get_param_protected("comment");
		$sql="insert into usage_phone_callback 
			(client,phone,type_phone,type_dialplan,actual_from,actual_to,comment) 
			values ('$fixclient','$phone','$type_phone','$type_dialplan','$actual_from','$actual_to','$comment')";
			
		$db->Query($sql);
		trigger_error('<script language=javascript>window.location.href="?module=phone&action=callback";</script>');
	}
	function phone_callback_del($fixclient){
		global $db,$design;
		if (!$fixclient) {
			trigger_error('�������� �������'); 
			return;
	}
		$id=get_param_protected('id');
		$db->Query('update usage_phone_callback set actual_to=NOW() where id='.$id.' and client="'.$fixclient.'"');
		trigger_error('<script language=javascript>window.location.href="?module=phone&action=callback";</script>');
	}
	function phone_callback_change($fixclient){
		global $db,$design;
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$type_phone=get_param_protected('type_phone');
		$type_dialplan=get_param_protected("type_dialplan");
		$comment=get_param_protected("comment");
 
                $id=get_param_protected('id');
                $db->Query('update usage_phone_callback set type_phone="'.$type_phone.'",type_dialplan="'.$type_dialplan.'",comment="'.$comment.'" where id='.$id.' and client="'.$fixclient.'"');
		trigger_error('<script language=javascript>window.location.href="?module=phone&action=callback";</script>');
	}
	
	function phone_short($fixclient){
		global $db,$design;
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$db->Query('select * from phone_short where client="'.$fixclient.'"');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign('phones_short',$R);
		$design->AddMain('phone/short.tpl');
	}
	function phone_short_add($fixclient){
		global $db,$design;
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$phone=digits(get_param_protected('phone'));
		$phone_short=digits(get_param_protected('phone_short'));
		if (strlen($phone_short)!=3){
			trigger_error('����� ������� �ң�������� �������� �����');
			$design->assign('phone_short',$phone_short);
			$design->assign('phone',$phone);
			$this->phone_short($fixclient);	
		} else {
			$db->Query('insert into phone_short (client,phone_short,phone) values ("'.$fixclient.'","'.$phone_short.'","'.$phone.'")');
			trigger_error('<script language=javascript>window.location.href="?module=phone&action=short";</script>');
		}
	}
	function phone_short_del($fixclient){
		global $db,$design;
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$phone_short=digits(get_param_protected('phone_short'));
		$db->Query('delete from phone_short where client="'.$fixclient.'" and phone_short="'.$phone_short.'"');
		trigger_error('<script language=javascript>window.location.href="?module=phone&action=short";</script>');
	}
	function phone_report($fixclient){
		global $db,$design;
		global $db,$design;
		if (!$fixclient) {trigger_error('������ �� ������'); return;}

		$usage_voip=$db->AllRecords('select * from usage_voip where client="'.$fixclient.'" order by id');
		if (!count($usage_voip)){ trigger_error('������ '.$fixclient.' �� ���������� VoIP'); return; }
		$design->assign('phone',$phone=get_param_protected('phone',''));
		$phones = array();
		$phones_sel = array();
		foreach ($usage_voip as $r) {
			if (substr($r['E164'],0,4)=='7095') $r['E164']='7495'.substr($r['E164'],4);
			$phones[$r['E164']]=$r['E164'];
			if ($r['E164']==$phone) $phones_sel[]=$r['id'];
		}
		$design->assign('phones',$phones);

		$def=getdate();
		$def['mday']=1; $from=param_load_date('from_',$def);
		$def['mday']=31; $to=param_load_date('to_',$def);
		
		$def['mday']=1; $cur_from=param_load_date('cur_from_',$def);
		$def['mday']=31; $cur_to=param_load_date('cur_to_',$def);
		$def['mon']--; if ($def['mon']==0) {$def['mon']=12; $def['year']--; }
		$def['mday']=1; $prev_from=param_load_date('prev_from_',$def);
		$def['mday']=31; $prev_to=param_load_date('prev_to_',$def);
		$design->assign('detality',$detality=get_param_protected('detality','day'));
	
		if (!($stats=$GLOBALS['module_stats']->GetStatsVoIP($from,$to,$detality,$fixclient,$phones_sel,0,1))) return;
		$design->assign('stats',$stats);
		$design->AddMain('phone/report_skipped.tpl');
		$design->AddMain('phone/report_skipped_form.tpl');
	}

	function phone_mail($fixclient){
		global $db,$design;	
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$db->Query('select usage_voip.*,phone_mail.phone_listen as phone_listen from usage_voip LEFT JOIN phone_mail ON (phone_mail.client="'.$fixclient.'") AND (phone_mail.phone=usage_voip.E164) where (usage_voip.client="'.$fixclient.'") and (usage_voip.DialPlan="city") and (usage_voip.actual_from<=NOW()) and (usage_voip.actual_to>NOW())');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign('phones_mail',$R);
		$db->Query('select * from phone_mail_files where client="'.$fixclient.'"');
		$r=$db->NextRecord();
		$design->assign('phone_mail_file',$r);
		$design->AddMain('phone/mail.tpl');
	}
	function phone_mail_save($fixclient){
		global $db,$design;	
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		$phone_listen=get_param_raw('phone_listen',array());
		$db->Query('select usage_voip.*,phone_mail.phone_listen as phone_listen from usage_voip LEFT JOIN phone_mail ON (phone_mail.client="'.$fixclient.'") AND (phone_mail.phone=usage_voip.E164) where (usage_voip.client="'.$fixclient.'") and (usage_voip.DialPlan="city") and (usage_voip.actual_from<=NOW()) and (usage_voip.actual_to>NOW())');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		foreach ($R as $r){
			if (isset($phone_listen[$r['id']]) && $r['phone_listen']!=digits($phone_listen[$r['id']])) {
				$np=digits($phone_listen[$r['id']]);
				if (!$np){
					if ($r['phone_listen']) $db->Query('delete from phone_mail where (client="'.$fixclient.'") and (phone="'.$r['E164'].'")');
				} else {
					if ($r['phone_listen']){
						$db->Query('update phone_mail set phone_listen="'.$np.'" where (client="'.$fixclient.'") and (phone="'.$r['E164'].'")');
					} else {
						$db->Query('insert into phone_mail (client,phone,phone_listen) values ("'.$fixclient.'","'.$r['E164'].'","'.$np.'")');
					}
				}
			}
		}
		trigger_error('<script language=javascript>window.location.href="?module=phone&action=mail";</script>');
	}
	function phone_mail_file($fixclient){
		global $db,$design;	
		if (!$fixclient) {trigger_error('�������� �������'); return;}
		if (!isset($_FILES['sound'])) return $this->phone_mail($fixclient);
		$sound=$_FILES['sound'];
		$comment=get_param_protected('comment');
		move_uploaded_file($sound['tmp_name'],SOUND_PATH.$fixclient.'.wav');
		$db->Query('select * from phone_mail_files where client="'.$fixclient.'"');
		$r=$db->NextRecord();
		if ($r){
			$db->Query('update phone_mail_files set comment="'.$comment.'",size="'.filesize(SOUND_PATH.$fixclient.'.wav').'" where (client="'.$fixclient.'")');
		} else {
			$db->Query('insert into phone_mail_files (client,comment,size) values ("'.$fixclient.'","'.$comment.'","'.filesize(SOUND_PATH.$fixclient.'.wav').'")');
		}
		trigger_error('<script language=javascript>window.location.href="?module=phone&action=mail";</script>');
	}
	function phone_tc() {
		global $db,$design;	
		include INCLUDE_PATH.'db_view.php';
		$view=new DbViewUsagePhoneRedirConditions();
		$view->Display('module=phone&action=tc','module=phone&action=tc_edit');
	}
	function phone_tc_edit($fixclient) {
		global $db,$design;	
		$id = get_param_integer('id');
		$dbf=new DbFormUsagePhoneRedirConditions();
		$dbf->Load($id);
		$dbf->Process();
		$dbf->Display(array('module'=>'phone','action'=>'tc_edit','id'=>$id),'Time Condition',$id?'��������������':'����������');
	}
	function phone_tc_edit2() {
		global $db,$design;	
		$id = get_param_integer('id');
		$period = get_param_raw('period');
		$condition_id = get_param_integer('condition_id');
		echo $id.'-'.$period.'-'.$condition_id.'!';
		if ($period) {
			if ($id) {
				$db->QueryUpdate('usage_phone_redir_condition_data','id',array('id'=>$id,'period'=>$period));
				$r = $db->GetRow('select * from usage_phone_redir_condition_data where id='.$id);
				if ($design->ProcessEx('errors.tpl')) {
                    header("Location: ".$design->LINK_START."module=phone&action=tc_edit&id=".$r['condition_id']);
                    exit;
                }
			} else {
				$db->QueryInsert('usage_phone_redir_condition_data',array('condition_id'=>$condition_id,'period'=>$period));
				if ($design->ProcessEx('errors.tpl')) {
                    header("Location: ".$design->LINK_START."module=phone&action=tc_edit&id=".$condition_id);
                    exit;
                }
			}
		} elseif ($id) {
			$r = $db->GetRow('select * from usage_phone_redir_condition_data where id='.$id);
			$db->Query('delete from usage_phone_redir_condition_data where id='.$id);
			if ($design->ProcessEx('errors.tpl')) {
                header("Location: ".$design->LINK_START."module=phone&action=tc_edit&id=".$r['condition_id']);
                exit;
            }
		}
	}
	
	function phone_voip($fixclient) {
		global $db,$design;
		if (!$fixclient) return;
		$R = $db->AllRecords('select usage_voip.*,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual from usage_voip where client="'.$fixclient.'" order by actual desc,id asc');
		foreach ($R as &$r) {
			$r['tarif']=get_tarif_current('usage_voip',$r['id']);
			$r['cpe']=get_cpe_history('usage_voip',$r['id']);
		} unset($r);
		$design->assign('voip_conn',$R);
		$design->assign('voip_access',$this->getAccess());
		$design->AddMain('phone/voip.tpl'); 
	}
	function getAccess() {
		global $fixclient_data,$user;
		static $acc = null;
		if ($acc===null) {
			if (isset($user) && !$user->GetAsClient()) {
				$acc = true;
				return $acc;
			}
			if (isset($fixclient_data) && count($fixclient_data)) {
				//$T = $GLOBALS['module_newaccounts']->getClientTotals($fixclient_data);
				$T['debt'] = 0;
			} else {
				$T['debt'] = 0;
			}
			if ($T['debt']<0) $acc=true; else $acc=false;
		}
		return $acc;
	}
	function voipGetId($id = null) {
		if ($id === null) $id = get_param_integer('id',0);
		return $id;
	}
	function voipGetData($fixclient,$fullAccess,$id,$vF = null) {
		global $db;
		if (!$id && !$fullAccess) return false;
		$F = array('id'=>$id);
		if (!is_array($vF)) {
			if ($id) {
				$F = $db->GetRow('select * from usage_voip where client="'.$fixclient.'" AND id='.$id);
			} else {
				$F['no_of_lines'] = 1;
				$F['actual_to'] = date('Y-m-d',time()+3600*24*14);
			}
			if ($vF===null) $F['NoSave'] = true;
		} else {
			if ($fullAccess && isset($vF['actual_to'])) $F['actual_to'] = date('Y-m-d',strtotime($vF['actual_to']));
//			if (isset($vF['no_of_lines'])) $F['no_of_lines']=max(min(intval($vF['no_of_lines']),($fullAccess?10:3)),1);
			$F['no_of_lines'] = 1;
		}
		if (!$id) {
			$F['actual_from']=date('Y-m-d');
			$r = $db->GetRow('select max(E164) as B,min(E164) as A from usage_voip where actual_from<=NOW() and actual_to>=NOW() AND LENGTH(E164)=7 AND E164 LIKE "10%"');
			if (!$r['A'] && !$r['B']) {
				$i = '1000001';
			} elseif ($r['A']>'1000001') {
				$i = intval($r['A'])-1;
			} else {
				$i = intval($r['B'])+1;
			}
			$F['E164'] = $i;
			$F['client']=$fixclient;
		}
		$F['new_tarif_id'] = 0;
		if ($fullAccess && isset($vF['new_tarif_id'])) {
			$v=intval($vF['new_tarif_id']);
			if ($v && ($r=$db->QuerySelectRow('tarifs_voip',array('id'=>$v,'is_clientSelectable'=>1)))) $F['new_tarif_id'] = $v;
		}
		return $F;
	}

	function voipSaveData($F) {
		global $db,$user;
		if (isset($F['NoSave'])) return null;
		$newTarifId = $F['new_tarif_id']; unset($F['new_tarif_id']);
		$id = $F['id'];
		if ($id) {
			$db->QueryUpdate('usage_voip','id',$F);
			$newTarifDate = 'NOW()+INTERVAL 1 DAY';
		} else {
			$id = $db->QueryInsert('usage_voip',$F);
			$newTarifDate = 'NOW()';
		}
		if ($newTarifId) {
			$db->Query('insert into log_tarif (service,id_service,id_tarif,id_user,ts,comment,date_activation) VALUES '.
								'("usage_voip",'.$id.','.$newTarifId.','.
										(isset($user)?$user->Get('id'):0).',NOW(),"by client",'.$newTarifDate.')');
		}
		return $id;
	}
		
		
	function phone_voip_edit ($fixclient) {
		global $db,$design,$fixclient_data;
		if (!$fixclient) return;
		$F = $this->voipGetData($fixclient,$this->getAccess(),get_param_integer('id'),get_param_raw('f',null));
		if ($id = $this->voipSaveData($F)) {
			if ($design->ProcessEx('errors.tpl')) {
                header("Location: ".$design->LINK_START."module=phone&action=voip_edit&id=".$id);
                exit;
            }
			return;
		}
		$T = get_tarif_history('usage_voip',$F['id']);
		$F['tarif'] = null; $F['tarif_tomorrow'] = null; 
		foreach ($T as $v) {
			if ($v['is_current']) {
				$F['tarif'] = $db->QuerySelectRow('tarifs_voip',array('id'=>$v['id_tarif']));
			}
			if ($v['is_next'] && ($v['date_activation']<=date('Y-m-d',time()+3600*24))) {
				$F['tarif_tomorrow'] = $db->QuerySelectRow('tarifs_voip',array('id'=>$v['id_tarif']));
			}
		}
		$r = $db->getRow('select username,secret from iptel_sip_users2 where usage_voip_id='.$F['id']);
		$design->assign('secret',$r);

		$design->assign('voip_tarifs',$db->AllRecords('select * from tarifs_voip where is_clientSelectable=1'));
		$design->assign('r',$F);
		$design->assign('voip_access',$this->getAccess());
		$design->AddMain('phone/voip_edit.tpl');
	}
}
	
?>