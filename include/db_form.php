<?
class DbForm {
    protected $table;
    protected $fields=array();
    public $data = null;
    public $dbform = null;
    public $dbform_action = null;
    public $nodesign = 0;
    public $includesPre = array(), $includesPreL = array(),$includesPreR = array(), $includesPost = array(), $includesPost2 = array(), $includesForm = array();

    public function __construct($table) {
        $this->table=$table;
        $this->fields['id']=array('type'=>'hidden');
    }
    public function SetDefault($key,$val) {
        $this->fields[$key]['default']=$val;    
    }
    public function isData($param) {
        return (is_array($this->data) && isset($this->data[$param]));    
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $design,$translate_arr;
        if (!isset($form_params['dbform_action'])) $form_params['dbform_action']='save';
        $design->assign('dbform_btn_new',!isset($this->data['id']));
        $design->assign('dbform_params',$form_params);
        $design->assign('dbform_table',$this->table);
        $R=array();

        foreach ($this->fields as $f=>$F) {
            $r=array('type'=>'text','add'=>'','assoc_enum'=>array(),'enum'=>array(),'caption'=>'','comment'=>'');
            $in_tag=0;
            $k=$this->table.'.'.$f;
            if (isset($translate_arr[$k])) {
                if (is_array($translate_arr[$k])) {
                    $r['caption']=$translate_arr[$k][0];
                    $r['comment']=' '.$translate_arr[$k][1];
                } else $r['caption']=$translate_arr[$k];
            } else {
                $k='*.'.$f;
                if (isset($translate_arr[$k])) {
                    if (is_array($translate_arr[$k])) {
                        $r['caption']=$translate_arr[$k][0];
                        $r['comment']=' '.$translate_arr[$k][1];
                    } else $r['caption']=$translate_arr[$k];
                } else $r['caption']=$f;
            }
            if (is_array($this->data) && isset($this->data[$f])) $r['value']=$this->data[$f];
            foreach ($F as $fk=>$fv) {
                if ($fk=='default') {
                    if (!isset($r['value'])) $r['value']=$fv;
                } elseif ($fk=='enum') {
                    $r['type']='select';
                    $r['enum']=$fv;
                } elseif ($fk=='assoc_enum') {
                    $r['type']='select';
                    $r['assoc_enum']=$fv;
                }elseif ($fk=='in_tag' && $fv==1) {
                    $in_tag=1;
                } else $r[$fk]=$fv;
            }
            if (!isset($r['value'])) $r['value']='';
            if ($in_tag) $r['add'].=' tag="'.addslashes($r['value']).'"';
            $R[$f]=$r;
        }

        $design->assign('dbform_h2',$h2);
        $design->assign('dbform_h3',$h3);
        $design->assign('dbform_data',$R);
        $design->assign('dbform_includesForm',$this->includesForm);
        $design->assign('dbform_includesPre',$this->includesPre);
        $design->assign('dbform_includesPreL',$this->includesPreL);
        $design->assign('dbform_includesPreR',$this->includesPreR);
        $design->assign('dbform_includesPost',$this->includesPost);
        $design->assign('dbform_includesPost2',$this->includesPost2);
        if (!$this->nodesign) $design->AddMain('dbform.tpl');
    }
    public function Get() {
        if (!$this->dbform_action || !$this->dbform)  {
            $this->dbform=get_param_raw('dbform',array());
            $this->dbform_action=get_param_raw('dbform_action','save');
        }
    }
    public function Load($id) {
        global $db;
        $db->Query('select * from '.$this->table.' where id='.$id);
        return ($this->data=$db->NextRecord());
    }
    public function Process($no_real_update = 0){
        global $db;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        if($this->dbform_action=='delete'){
            $db->Query('delete from '.$this->table.' where id='.$this->dbform['id']);
            return 'delete';
        }elseif($this->dbform_action=='save'){
            $R=array();
            $s='';
            $sDiff = "";

            foreach($this->fields as $f=>$F){
                if($f!='id' && !(isset($F['db_ignore']) && $F['db_ignore']==1)){
                    if(isset($F["type"]) && $F["type"] == "password" && $this->dbform[$f] == "*******") continue;
                    if($s)
                        $s.=',';
                    $s .= $f.'='.(
                        isset($this->dbform[$f])
                            ? (is_array($this->dbform[$f])?$this->dbform[$f][0]:'"'.addslashes($this->dbform[$f]).'"')
                            : '""'
                    );
                    if($this->dbform['id'] && isset($this->dbform[$f]) && isset($this->data[$f])){
                        if($this->dbform[$f] != $this->data[$f]){
                            $sDiff .= ($sDiff?", ":"").$f;

                            if($f == "actual_from" || $f == "actual_to"){
                                $sDiff .= "(".$this->data[$f]." => ".$this->dbform[$f].")";
                            }
                        }
                    }
                }
            }


            if (!$no_real_update) {
                if ($this->dbform['id']) {

                    if ($sDiff)
                    {
                        $this->dbform["t_fields_changes"] = $sDiff;
                    }

                    $db->Query($q='update '.$this->table.' SET '.$s.' WHERE id='.$this->dbform['id']);
                    $p='edit';
                    trigger_error('Запись обновлена');
                } else {
                    $db->Query('insert into '.$this->table.' SET '.$s);
                    $this->dbform['id']=$db->GetInsertId();
                    $p='add';
                    trigger_error('Запись добавлена');
                }
                $this->Load($this->dbform['id']);
            } else {
                $p='add';
                $this->data=$this->dbform;
            }
            return $p;
        }
    }
}
class HelpDbForm {
    public static function assign_tarif($service,$id, $postfix = '') {
        global $design;
        $design->assign('dbform_f_tarif'.$postfix,$T=get_tarif_history($service,$id));
        foreach ($T as $k=>$v) if ($v['is_current']) $t_cur=$v;
        if (!isset($t_cur)) $t_cur=null;
        $design->assign('dbform_f_tarif'.$postfix.'_current',$t_cur);
    }
    public static function assign_block($service,$id) {
        global $design;
        $design->assign('dbform_f_block',$R=get_block_history($service,$id));
        if (count($R)) @$design->assign('dbform_f_block_current',$R[count($R)-1]);
    }
    public static function assign_cpe($service,$id) {
        global $design;
        $design->assign('dbform_f_cpe',get_cpe_history($service,$id));
    }
    public static function assign_tt($service,$service_id,$client) {
        global $design,$module_tt;
        $module_tt->makeTroubleList(1,'service',3,$client,$service,$service_id);
    }
    public static function save_block($service,$id,$block,$comment, $fieldsChanges = "") {
        global $db,$user;
        $db->Query('insert into log_block (service,id_service,block,id_user,ts,comment,fields_changes) VALUES '.
                                    '("'.$service.'",'.$id.',"'.($block?1:0).'",'.$user->Get('id').',NOW(),"'.addslashes($comment).'","'.addslashes($fieldsChanges).'")');
    }
    public static function logTarif($service,$id,$tarifId,$dateActivation) {
        global $db,$user;
        $db->Query('insert into log_tarif (service,id_service,id_tarif,id_user,ts,comment,date_activation) VALUES '.
                                    '("'.$service.'",'.$id.','.intval($tarifId).','.
                                            $user->Get('id').',NOW(),"","'.addslashes($dateActivation).'")');
        
    }
    public static function logTarifUsage($service,$id,$dateActivation,
                                        $tarifId,$tarifLocalMobId,$tarifRussiaId,$tarifInternId,$tarifSngId,
                                        $dest_group, $minpayment_group, 
                                        $minpayment_local_mob, $minpayment_russia, $minpayment_intern, $minpayment_sng)
    {
        global $db,$user;
        $db->Query('insert into log_tarif (service,id_service,id_user,ts,date_activation,comment,
                                        id_tarif,id_tarif_local_mob,id_tarif_russia,id_tarif_intern,id_tarif_sng,
                                        dest_group,minpayment_group,
                                        minpayment_local_mob,minpayment_russia,minpayment_intern,minpayment_sng
                                    ) VALUES '.
                                    '("'.$service.'",'.$id.','.$user->Get('id').',NOW(),"'.addslashes($dateActivation).'","",'.
                                        intval($tarifId).','.intval($tarifLocalMobId).','.intval($tarifRussiaId).','.intval($tarifInternId).','.intval($tarifSngId).','.
                                        intval($dest_group).','.intval($minpayment_group).','.
                                        intval($minpayment_local_mob).','.intval($minpayment_russia).','.intval($minpayment_intern).','.intval($minpayment_sng).
                                    ')');
        
    }    
}

class DbFormUsageIpPorts extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_ip_ports');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['address']=array();
        
        $this->fields['port_type']=array('db_ignore'=>1,'enum'=>array('dedicated','pppoe','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON'),'default'=>'adsl','add'=>' onchange=form_ip_ports_hide()');

        $this->fields['node']=array('db_ignore'=>1,'add'=>' onchange="form_ip_ports_get_ports()" ');
        $this->fields['phone']=array('db_ignore'=>1);
        $this->fields['port']=array('db_ignore'=>1,'enum'=>array());

        $this->fields['port_id']=array('type'=>'hidden');
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        
        $this->includesPreL = array('dbform_internet_tarif.tpl');
        $this->includesPreR = array('dbform_block.tpl');
        $this->includesPre=array('dbform_tt.tpl');
        $this->includesPost =array('dbform_internet_tarif_history.tpl','dbform_block_history.tpl','dbform_cpe_history.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design,$user;
        if ($this->isData('id')) {
            $db->Query($q='select tech_ports.* from tech_ports where id='.$this->data['port_id']);
            $r=$db->NextRecord();
            $this->data['port_type']=$r['port_type'];
            $this->data['port']=$r['port_name'];
            $this->data['node']=$r['node'];
            $this->data['phone']=$r['node'];

            HelpDbForm::assign_tarif('usage_ip_ports',$this->data['id']);
            HelpDbForm::assign_block('usage_ip_ports',$this->data['id']);
            HelpDbForm::assign_cpe('usage_ip_ports',$this->data['id']);
            HelpDbForm::assign_tt('usage_ip_ports',$this->data['id'],$this->data['client']);
        }

        $this->fields['node']['enum']=array();
        $db->Query('select router from tech_routers order by router');
        while ($r=$db->NextRecord()) $this->fields['node']['enum'][]=$r['router'];

        $this->fields['port']['in_tag']=1;
        $db->Query($q="SELECT distinct port_name FROM tech_ports WHERE node='".$this->data['node']."' AND port_type='".$this->data['port_type']."' ORDER BY port_name");
        while($row=$db->NextRecord()){
            $this->fields['port']['enum'][] = $row['port_name'];
        }
        global $fixclient_data;
        if (!isset($fixclient_data)) $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        $R=$db->AllRecords('select * from tarifs_internet '.(isset($fixclient_data['currency'])?'where currency="'.$fixclient_data['currency'].'" ':'').'order by status,type_internet,name');
        $design->assign('dbform_f_tarifs',$R);
        $R = array();
        $A = array('I','C','V');
        foreach ($A as $a) {
            $R[] = array($a,'public',$a.'P');
            $R[] = array($a,'special',$a.'S');
            $R[] = array($a,'archive',$a.'A');
            if($a=='I'){
                $R[] = array($a,'adsl_su',$a.'Su');
                $R[] = array($a,'ss',$a.'Ss');
                $R[] = array($a,'sc',$a.'Sc');
            }
        }
        $design->assign('dbform_f_tarif_types',$R);

        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0) {
        global $db,$user;
        $this->Get();
        if (!isset($this->dbform['id'])) return '';

        $this->dbform['t_id_tarif']=0;
        if (isset($this->dbform['t_tarif_type']) && isset($this->dbform['t_tarif_status']) && isset($this->dbform['t_id_tarif'.$this->dbform['t_tarif_type'].$this->dbform['t_tarif_status']])) {
            $this->dbform['t_id_tarif']=$this->dbform['t_id_tarif'.$this->dbform['t_tarif_type'].$this->dbform['t_tarif_status']];
        }
        if ($this->dbform['port_type']=='adsl' || $this->dbform['port_type']=='adsl_cards' || $this->dbform['port_type']=='adsl_connect' || $this->dbform['port_type']=='adsl_karta' || $this->dbform['port_type']=='adsl_rabota' || $this->dbform['port_type']=='adsl_terminal' || $this->dbform['port_type']=='adsl_tranzit1'|| $this->dbform['port_type']=='yota'|| $this->dbform['port_type']=='GPON') {
            $v=$this->dbform['phone'];
            $v=preg_replace('/[^\d]+/','',$v);
            $v1=preg_replace('/^495/','',$v);
            $v2=preg_replace('/^095/','',$v);
            $v3=preg_replace('/^499/','',$v);
            if ($v1!=$v || $v2!=$v || strlen($v)==7) {
                $v='(495) '.($v1!=$v?$v1:$v2);
            } elseif ($v3!=$v) {
                $v='(499) '.$v3;
            } else {
                $v='????? '.$v;
            }
            $this->dbform['node']=$v;
            $this->dbform['port']='mgts';
        } else if ($this->dbform['port_type']=='wimax' || $this->dbform['port_type']=='yota') {
            $this->dbform['node']='';
            $this->dbform['port']='';    
        }
        $db->Query('select id from tech_ports where node="'.$this->dbform['node'].'" AND '.
                        'port_name="'.@$this->dbform['port'].'" AND port_type="'.$this->dbform['port_type'].'"');
        if ($r=$db->NextRecord()) {
            $this->dbform['port_id']=$r['id'];
        } else {
            $db->QueryInsert('tech_ports',array('node'=>$this->dbform['node'],'port_name'=>$this->dbform['port'],'port_type'=>$this->dbform['port_type']));    
            $this->dbform['port_id']=$db->GetInsertId();
        }
        $v=DbForm::Process();
        
        if ($v=='add' || $v=='edit') {
            if (!($olddata=get_tarif_current("usage_ip_ports",$this->data['id']))) $b=1; else $b=0;
            if (!$b && $this->dbform['t_id_tarif']!=$olddata['id_tarif']) $b=1;
            if (!$b && $this->dbform['t_date_activation']!=$olddata['date_activation']) $b=1;
            if (!$b && $this->dbform['t_comment']!="") $b=1;
            if (!$this->dbform['t_id_tarif']) $b=0;
            if ($b) HelpDbForm::logTarif("usage_ip_ports",$this->dbform['id'],$this->dbform['t_id_tarif'],$this->dbform['t_date_activation']);

            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_ip_ports',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment'], isset($this->dbform['t_fields_changes']) ?$this->dbform['t_fields_changes'] : "");
        }
    }
}

class DbFormUsageVoip extends DbForm {
    public function __construct() {
        global $db;
        $regions = array();
        foreach($db->AllRecords('select * from regions') as $item)
            $regions[$item['id']] = $item['code'].' - '.$item['name'];

        DbForm::__construct('usage_voip');
        $this->fields['region']=array('type'=>'select','assoc_enum'=>$regions,'add'=>' readonly');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['E164']=array();
        $this->fields['no_of_lines']=array('default'=>1);
        $this->fields['no_of_callfwd']=array('default'=>0);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['address']=array();
        $this->fields['edit_user_id']=array('type'=>'hidden');
        $this->includesPreL = array('dbform_voip_tarif.tpl');
        $this->includesPreR = array('dbform_block.tpl');
        $this->includesPre=array('dbform_tt.tpl');
        $this->includesPost =array('dbform_voip_tarif_history.tpl','dbform_block_history.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design;
        if ($this->isData('id')) {
            HelpDbForm::assign_tarif('usage_voip',$this->data['id']);
            HelpDbForm::assign_tarif('usage_voip',$this->data['id'],'_sng');
            HelpDbForm::assign_tarif('usage_voip2',$this->data['id'],'2');
            HelpDbForm::assign_block('usage_voip',$this->data['id']);
            HelpDbForm::assign_tt('usage_voip',$this->data['id'],$this->data['client']);
            $region = $this->data['region'];
        }else{
            $region = $this->fields['region']['default'];
        }
        global $fixclient_data;
        if (!isset($fixclient_data)) $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        $R=$db->AllRecords('select * from tarifs_voip '.
                            (isset($fixclient_data['currency'])?'where currency="'.$fixclient_data['currency'].'" ':'').' and region="'.$region.'" '.
                            'order by status, month_line, month_min_payment', 'id');
        $design->assign('dbform_f_tarifs',$R);
        $design->assign('region',$region);
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';

        if(!$this->check_number()) return;

        $this->dbform['edit_user_id'] = $user->Get('id');
        $v=DbForm::Process();
        if ($v=='add' || $v=='edit') {
            $b = 1;
            if ($this->dbform['t_id_tarif'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_local_mob'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_russia'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_intern'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_sng'] == 0) $b=0;
            if ($this->dbform['t_minpayment_group'] == '') $b=0;
            if ($this->dbform['t_minpayment_local_mob'] == '') $b=0;
            if ($this->dbform['t_minpayment_russia'] == '') $b=0;
            if ($this->dbform['t_minpayment_intern'] == '') $b=0;
            if ($this->dbform['t_minpayment_sng'] == '') $b=0;

            $this->dbform["E164"] = trim($this->dbform["E164"]);
            
            if(preg_match("/^8/", $this->dbform["E164"], $o))
            {
                die("<font style='color:red'><b>Номер начинается на 8-ку!</b></font>");
            }elseif(strlen($this->dbform["E164"]) > 5 && strlen($this->dbform["E164"]) != 11) // if not line_without_number
            {
                die("<font style='color:red'><b>Номер задан не верно!</b></font>");
            }else
            if ($b)
            {
                if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
                HelpDbForm::save_block('usage_voip',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);

                $usages = array();
                if(isset($this->dbform['t_apply_for_all_tarif_id']))
                {
                    $res = $db->AllRecords("select id from usage_voip where client='".$this->dbform['client']."' and cast(now() as date) between actual_from and actual_to");
                    foreach($res as $r) $usages[] = $r['id'];
                }else 
                    $usages[] = $this->data['id'];

                foreach ($usages as $usage_id) {
                    $olddata=get_tarif_current("usage_voip",$usage_id);
                    if (isset($this->dbform['t_apply_for_all_tarif_id']) && $olddata['id_tarif'] != $this->dbform['t_apply_for_all_tarif_id']) continue;
                    $b = 0;
                    if ($this->dbform['t_id_tarif']!=$olddata['id_tarif']) $b=1;
                    if ($this->dbform['t_id_tarif_local_mob']!=$olddata['id_tarif_local_mob']) $b=1;
                    if ($this->dbform['t_id_tarif_russia']!=$olddata['id_tarif_russia']) $b=1;
                    if ($this->dbform['t_id_tarif_intern']!=$olddata['id_tarif_intern']) $b=1;
                    if ($this->dbform['t_id_tarif_sng']!=$olddata['id_tarif_sng']) $b=1;
                    if ($this->dbform['t_date_activation']!=$olddata['date_activation']) $b=1;
                    if ($this->dbform['t_comment']!='') $b=1;
                    if ($this->dbform['t_dest_group']!=$olddata['dest_group']) $b=1;
                    if ($this->dbform['t_minpayment_group']!=$olddata['minpayment_group']) $b=1;
                    if ($this->dbform['t_minpayment_local_mob']!=$olddata['minpayment_local_mob']) $b=1;
                    if ($this->dbform['t_minpayment_russia']!=$olddata['minpayment_russia']) $b=1;
                    if ($this->dbform['t_minpayment_intern']!=$olddata['minpayment_intern']) $b=1;
                    if ($this->dbform['t_minpayment_sng']!=$olddata['minpayment_sng']) $b=1;
                    if ($this->dbform['t_date_activation']!=$olddata['date_activation']) $b=1;
                    if (!$b) continue;

                    HelpDbForm::logTarifUsage("usage_voip",
                            $usage_id,$this->dbform['t_date_activation'],
                            $this->dbform['t_id_tarif'],$this->dbform['t_id_tarif_local_mob'],$this->dbform['t_id_tarif_russia'],$this->dbform['t_id_tarif_intern'],$this->dbform['t_id_tarif_sng'],
                            $this->dbform['t_dest_group'],$this->dbform['t_minpayment_group'],
                            $this->dbform['t_minpayment_local_mob'],$this->dbform['t_minpayment_russia'],$this->dbform['t_minpayment_intern'],$this->dbform['t_minpayment_sng']
                        );
                }
            }else{
                trigger_error("Не сохранено! Выберите тариф");
            }
        }
        voipNumbers::check();
        return $v;
    }

    private function check_number()
    {
        global $db;

        if($this->dbform["E164"])
        {
            $f = $this->dbform["actual_from"];
            $t = $this->dbform["actual_to"];

            $c = $db->GetRow(
                    "select * from usage_voip where ((actual_from between '".$f."' and '".$t."' or actual_to between '".$f."' and '".$t."' or (actual_from <= '".$f."' and '".$t."' <= actual_to )) and e164 = '".$this->dbform["E164"]."') and id != '".$this->dbform["id"]."'");

            if($c)
            {
                trigger_error("Введенный номер пересекается с id:".$c["id"].", клиент:".$c["client"].", c ".$c["actual_from"]." по ".$c["actual_to"].")");
                return false;
            }
        }

        return true;
    }

}

class DbFormEmails extends DbForm {
    public function __construct() {
        DbForm::__construct('emails');
        $this->fields['client']=array('type'=>'label','default'=>'');
        $this->fields['actual_from']=array('default'=>date('Y-m-d'));
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['local_part']=array('type'=>'text');
        $this->fields['domain']=array('type'=>'include','file'=>'dbform_emails_domain.tpl','add'=>'');
        $this->fields['password']=array("type" => "password");
        $this->fields['box_quota']=array('assoc_enum'=>array('50000'=>'50 Mb','100000'=>'100 Mb'));
        $this->fields['box_size']=array('type'=>'label');
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['t_comment']=array('db_ignore'=>1);
        $this->includesPre = array('dbform_block.tpl');
        $this->includesPost =array('dbform_block_history.tpl');
    }

    public function Load($id)
    {
        $r = DbForm::Load($id);
        if($r)
            if($r["password"] != "") $r["password"] = "*******";
        $this->data = $r;
        return $r;
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design;
        $client = ''; $domain = '';
        if ($this->isData('client')) $client=$this->data['client'];
        if (!$client) $client=$this->fields['client']['default'];
        if ($this->isData('domain')) $domain=$this->data['domain'];
        
        if ($this->isData('id')) {
            HelpDbForm::assign_block('emails',$this->data['id']);
        }

        $R=array('mcn.ru'); $db->Query('select domain from domains where client="'.$client.'"');
        while ($r=$db->NextRecord()) $R[]=$r['domain'];
        $design->assign('_domain',array('enum'=>$R,'value'=>$domain,'add'=>$this->fields['domain']['add']));
        unset($this->fields['domain']);
        $this->fields['local_part']['comment']=$design->fetch('dbform_emails_domain.tpl');
        
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();

        if(!isset($this->dbform['id']))
            return '';

        if($this->dbform_action!='delete'){
            $this->dbform['actual_from'] = date('Y-m-d',strtotime($this->dbform['actual_from']));
            $this->dbform['actual_to'] = date('Y-m-d',strtotime($this->dbform['actual_to']));
            $query = "
                select
                    *
                from
                    emails
                where
                    id!=".intval($this->dbform['id'])."
                and
                    actual_from<='".($this->dbform['actual_from'])."'
                and
                    actual_to>'".($this->dbform['actual_from'])."'
                and
                    domain='".addslashes($this->dbform['domain'])."'
                and
                    local_part='".addslashes($this->dbform['local_part'])."'";
            if($db->GetRow($query)) {
                trigger_error('Такой адрес уже занят');
                return '';
            }
        }
        
        $v=DbForm::Process();
        
        if ($v=='add' || $v=='edit') {
            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('emails',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}
class DbFormEmailsSimple extends DbForm {
    public function __construct() {
        DbForm::__construct('emails');
        $this->fields['client']=array('type'=>'label','default'=>'');
        $this->fields['actual_from']=array('type'=>'hidden','default'=>date('Y-m-d'));
        $this->fields['actual_to']=array('type'=>'hidden','default'=>'2029-01-01');
        $this->fields['local_part']=array('type'=>'text');
        $this->fields['domain']=array('type'=>'include','file'=>'dbform_emails_domain.tpl','add'=>'');
        $this->fields['password']=array();
        $this->fields['box_quota']=array('assoc_enum'=>array('50000'=>'50 Mb','100000'=>'100 Mb'));
        $this->fields['status']=array('type'=>'hidden','default'=>'connecting');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design;
        $R=array('mcn.ru'); $db->Query('select domain from domains where client="'.$this->fields['client']['default'].'"');
        while ($r=$db->NextRecord()) $R[]=$r['domain'];
        $design->assign('_domain',array('enum'=>$R,'value'=>'','add'=>$this->fields['domain']['add']));
        unset($this->fields['domain']);
        $this->fields['local_part']['comment']=$design->fetch('dbform_emails_domain.tpl');
        DbForm::Display($form_params,$h2,$h3);
    }
}

class DbFormDomains extends DbForm {
    public function __construct() {
        DbForm::__construct('domains');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['domain']=array();
        $this->fields['primary_mx']=array();
        $this->fields['registrator']=array('default'=>'RUCENTER-REG-RIPN');
        $this->fields['dns']=array();
        $this->fields['paid_till']=array();
        $this->fields['rucenter_form_no']=array();
        $this->fields['t_comment']=array('db_ignore'=>1);
        $this->includesPre = array('dbform_block.tpl');
        $this->includesPost =array('dbform_block_history.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design;
        if ($this->isData('id')) {
            HelpDbForm::assign_block('domains',$this->data['id']);
        }
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0) {
        global $db,$user;
        $this->Get();
        if (!isset($this->dbform['id'])) return '';
        $v=DbForm::Process();
        if ($v=='add' || $v=='edit') {
            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('domains',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }

}

class DbFormBillMonthlyadd extends DbForm {
    public function __construct() {
        DbForm::__construct('bill_monthlyadd');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['client']=array('type'=>'label');
        $this->fields['amount']=array('default'=>'1');
        $this->fields['description']=array();
        $this->fields['price']=array('type'=>'label');
        $this->fields['period']=array('type'=>'label');
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['t_comment']=array('db_ignore'=>1);
        $this->includesPreL = array('dbform_bill_montlyadd.tpl');
        $this->includesPreR = array('dbform_block.tpl');
        $this->includesPost =array('dbform_block_history.tpl');
    }    
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design;
        $R=$db->AllRecords('select * from bill_monthlyadd_reference'); //where status!="archive"');
        $design->assign('dbform_f_ref',$R);

        if ($this->isData('id')) {
            $R=$db->AllRecords('select bill_monthlyadd_log.*,user_users.user from bill_monthlyadd_log LEFT JOIN user_users ON user_users.id=bill_monthlyadd_log.who where id_service='.$this->data['id'].' order by ts'); //where status!="archive"');
            $design->assign('dbform_f_log',$R);

            HelpDbForm::assign_block('bill_monthlyadd',$this->data['id']);
        }        

        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0) {
        global $db,$user;
        $this->Get();
        if (!isset($this->dbform['id'])) return '';
        if ($this->dbform['id']) {
            $r=$db->GetRow('select * from bill_monthlyadd where id='.$this->dbform['id']);
            $r['id_service']=$r['id']; unset($r['id']);
            $r['who']=$user->Get('id');
            $r['ts']=array('NOW()');
            $db->QueryInsert('bill_monthlyadd_log',$r);
        }
        $v=DbForm::Process();

        if ($v=='add' || $v=='edit') {
            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('bill_monthlyadd',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
    }
}

class DbFormUsageIpRoutes extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_ip_routes');
        $this->fields['port_id']=array('type'=>'hidden');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['type']=array('enum'=>array('unused', 'uplink', 'uplink+pool', 'client', 'client-nat', 'pool', 'aggregate', 'reserved', 'gpon'),'default'=>'aggregate');
        $this->fields['net']=array();
        $this->fields['nat_net']=array();
        $this->fields['dnat']=array();
        $this->fields['flows_node']=array('default'=>'rubicon');
        $this->fields['up_node']=array();
        $this->fields['comment']=array();
        $this->fields['gpon_reserv']=array("assoc_enum" => array("0"=>"Нет","1"=>"Да"));
        $this->includesPost =array('dbform_internet_route_history.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design;
        if ($this->isData('id') && $this->data['id']) {
            $design->assign('dbform_f_route',$db->AllRecords('select log_usage_ip_routes.*,user_users.user from log_usage_ip_routes inner join user_users ON user_users.id=log_usage_ip_routes.user_id where usage_ip_routes_id='.$this->data['id'].' order by ts desc'));
        } elseif (isset($this->fields['port_id']['default'])) {
            $this->fields['actual_from']['default']=date('Y-m-d');
            $this->fields['net']['comment'] = ' &nbsp; <select id="getnet_size"><option value="29">/29 (8 ip-адресов)<option value="30">/30 (4 ip-адреса)</select><input type=button onclick="doGetNet()" value="выделить сеть" class=button id=getnet_button>';
        }
        DbForm::Display($form_params,$h2,$h3);
    }

    public function Process($no_real_update = 0) {
        global $db,$user;
        $this->Get();
        if (!isset($this->dbform['id'])) return '';
        $p=0;
        if($this->dbform_action=='save' && !$this->dbform['net']){
            $p=1;
            trigger_error('Адрес сети отсутствует');
        }
        $v=(
                $this->dbform_action=='save'
            &&
                $this->dbform['net']
            &&
                $db->GetRow($q='
                    select
                        *
                    from
                        usage_ip_routes
                    where
                        (
                            (actual_from<>"2029-01-01" and actual_from between "'.addslashes($this->dbform['actual_from']).'" and "'.addslashes($this->dbform['actual_to']).'")
                        or
                            (actual_to<>"2029-01-01" and actual_to between "'.addslashes($this->dbform['actual_from']).'" and "'.addslashes($this->dbform['actual_to']).'")
                        )
                    and
                        net="'.addslashes($this->dbform['net']).'"
                    and
                        id!="'.addslashes($this->dbform['id']).'"')
        );
        if ($v) {$this->dbform['net']=''; trigger_error('Сеть уже занята');}
        $action=DbForm::Process($p);

        if (!$v && !$p && $action!='delete') {
            $db->QueryInsert('log_usage_ip_routes',$qq = array(
                        'usage_ip_routes_id'    => $this->dbform['id']?:$db->GetValue("select last_insert_id()"), // edit or add
                        'user_id'                => $user->Get('id'),
                        'ts'                    => array('NOW()'),
                        'actual_from'            => $this->dbform['actual_from'],
                        'actual_to'                => $this->dbform['actual_to'],
                        'net'                    => $this->dbform['net'],
                        ));
        }

        return $v;
    }
}

class DbFormUsageExtra extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_extra');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['code']=array('type'=>'hidden');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['param_value']=array();
        $this->fields['amount']=array();
        $this->fields['async_price']=array('type'=>'label','db_ignore'=>1);
        $this->fields['async_period']=array('type'=>'label','db_ignore'=>1);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['comment']=array();
        $this->includesPre = array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost =array('dbform_block_history.tpl','dbform_usage_extra.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design;
        global $fixclient_data;
        if (!isset($fixclient_data)) $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_extra',$this->data['id']);
            HelpDbForm::assign_tt('usage_extra',$this->data['id'],$this->data['client']);

            $db->Query('select id,description,price,currency from tarifs_extra where 1 '.(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'').'and id='.$this->data['tarif_id']);
            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];
        } else {

            $allTarif= array();
            $db->Query('select id,code,description,price,currency from tarifs_extra where 1 '.(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'').'and status!="archive" order by description');
            $R=array(''); 
            while ($r=$db->NextRecord()) {
                $sName = $r['description'].' ('.$r['price'].' '.$r['currency'].')';
                $allTarif[$r['id']] = $sName;
                if($r["code"] == ""){
                    $R[$r['id']]=$sName;
                }
            }
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['add']=' onchange=form_usage_extra_get()';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';

            $this->fields['code']['type']='select';
            $this->fields['code']['add']=' onchange=form_usage_extra_group(this)';

            $groups = array();
            foreach($idData = $db->AllRecords("select code, group_concat(id) ids from tarifs_extra group by code order by code") as $g)
                $groups[$g["code"]] = $g["code"];


            $design->assign("tarif_group_data", $idData);
            $design->assign("tarif_data", $allTarif);

            $this->fields['code']['assoc_enum']=array(
                    ''=>'',
                    'domain'=>'Домен',
                    'ip'=>'IP',
                    'mailserver'=>'Почтовый сервер',
                    'phone_ats'=>'АТС',
                    'site'=>'Сайт',
                    'sms_gate'=>'SMS Gate', 
                    'uspd' => "УСПД",
                    //'welltime'=>'WellTime',
                    'wellsystems'=>'WellSystems'
                    );//$groups;
            $this->fields['code']['type']='no';
        }
        DbForm::Display($form_params,$h2,$h3);
    }    
    public function Process($no_real_update = 0) {
        global $db,$user;
        $this->Get();
        if (!isset($this->dbform['id'])) return '';
        $v=DbForm::Process();
        if ($v=='add' || $v=='edit') {
            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_extra',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}
class DbFormUsageITPark extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_extra');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['param_value']=array();
        $this->fields['amount']=array();
        $this->fields['async_price']=array('type'=>'label','db_ignore'=>1);
        $this->fields['async_period']=array('type'=>'label','db_ignore'=>1);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['t_comment']=array('db_ignore'=>1);
        $this->includesPre = array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost =array('dbform_block_history.tpl','dbform_usage_extra.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design;
        global $fixclient_data;
        if(!isset($fixclient_data))
            $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_extra',$this->data['id']);
            HelpDbForm::assign_tt('usage_extra',$this->data['id'],$this->data['client']);

            $db->Query('
                select
                    id,
                    description,
                    price,
                    currency
                from
                    tarifs_extra
                where
                    id='.$this->data['tarif_id'].
                (isset($fixclient_data['currency'])?' and currency="'.$fixclient_data['currency'].'" ':'')
            );
            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];
        }else{
            $db->Query('
            select
                id,
                description,
                price,
                currency
            from
                tarifs_extra
            where
                status="itpark"
                '.(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'')
            );
            $R=array('');
            while($r=$db->NextRecord())
                $R[$r['id']]=$r['description'].' ('.$r['price'].' '.$r['currency'].')';
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['add']=' onchange=form_usage_extra_get()';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';
        }
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        $v=DbForm::Process();
        if($v=='add' || $v=='edit'){
            if(!isset($this->dbform['t_block']))
                $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_extra',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}

class DbFormUsageWelltime extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_welltime');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['ip']=array();
        $this->fields['router']=array("enum" => array());
        $this->fields['amount']=array();
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['comment']=array();
        $this->includesPre=array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost=array('dbform_block_history.tpl','dbform_usage_extra.tpl');

        global $db;
        $this->fields['router']['enum']=array(""=>"");
        $db->Query('select router from tech_routers order by router');
        while ($r=$db->NextRecord()) $this->fields['router']['enum'][]=$r['router'];
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design;
        global $fixclient_data;
        if(!isset($fixclient_data))
            $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_welltime',$this->data['id']);
            HelpDbForm::assign_tt('usage_willtime',$this->data['id'],$this->data['client']);

            $db->Query('
                select
                    id,
                    description,
                    price,
                    currency
                from
                    tarifs_extra
                where
                    id='.$this->data['tarif_id'].
                (isset($fixclient_data['currency'])?' and currency="'.$fixclient_data['currency'].'" ':'')

            );
            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];
        }else{
            $db->Query('
            select
                id,
                description,
                price,
                currency
            from
                tarifs_extra
            where
                code="welltime" and status="public"
                '.(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'').
                " order by description"
            );
            $R=array('');
            while($r=$db->NextRecord())
                $R[$r['id']]=$r['description'].' ('.$r['price'].' '.$r['currency'].')';
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['add']=' onchange=form_usage_extra_get()';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';

        }
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        $v=DbForm::Process();
        if($v=='add' || $v=='edit'){
            if(!isset($this->dbform['t_block']))
                $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_welltime',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}

class DbFormUsageVirtpbx extends DbForm{
    public function __construct() {
        global $db;

        DbForm::__construct('usage_virtpbx');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['server_pbx_id']=array('assoc_enum'=>$db->AllRecordsAssoc("select id, name from server_pbx order by name", "id", "name"));
        $this->fields['amount']=array("default" => 1);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['comment']=array();
        $this->includesPre=array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost=array('dbform_block_history.tpl','dbform_usage_extra.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design, $fixclient_data;

        if(!isset($fixclient_data))
            $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_virtpbx',$this->data['id']);
            HelpDbForm::assign_tt('usage_virtpbx',$this->data['id'],$this->data['client']);

            $db->Query('
                select
                    id,
                    description,
                    price,
                    currency
                from
                    tarifs_virtpbx
                where
                    id='.$this->data['tarif_id']
            );

            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];
        }else{
            $db->Query('
            select
                id,
                description,
                price,
                currency
            from
                tarifs_virtpbx
            order by price'
            );
            $R=array('');
            while($r=$db->NextRecord())
                $R[$r['id']]=$r['description'].' ('.$r['price'].' '.$r['currency'].')';
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['add']=' onchange=form_usage_virtpbx_get()';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';
        }
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        $v=DbForm::Process();
        if($v=='add' || $v=='edit'){
            if(!isset($this->dbform['t_block']))
                $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_welltime',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}

class DbFormUsage8800 extends DbForm{
    public function __construct() {
        global $db;

        DbForm::__construct('usage_8800');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['number']=array("default" => "7800");
        $this->fields['amount']=array("default" => 1);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['comment']=array();
        $this->includesPre=array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost=array('dbform_block_history.tpl','dbform_usage_extra.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design, $fixclient_data;

        if(!isset($fixclient_data))
            $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_8800',$this->data['id']);
            HelpDbForm::assign_tt('usage_8800',$this->data['id'],$this->data['client']);

            $db->Query('
                select
                    id,
                    description,
                    price,
                    currency
                from
                    tarifs_8800
                where
                    id='.$this->data['tarif_id']
            );

            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];
        }else{
            $db->Query('
            select
                id,
                description,
                price,
                currency
            from
                tarifs_8800
            order by price'
            );
            $R=array('');
            while($r=$db->NextRecord())
                $R[$r['id']]=$r['description'].' ('.$r['price'].' '.$r['currency'].')';
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['add']=' onchange=form_usage_8800_get()';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';
        }
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        $v=DbForm::Process();
        if($v=='add' || $v=='edit'){

            $this->dbform["number"] = trim($this->dbform["number"]);

            if(!isset($this->dbform['t_block']))
                $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_8800',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}

class DbFormUsageSms extends DbForm{
    public function __construct() {
        global $db;

        DbForm::__construct('usage_sms');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['comment']=array();
        $this->includesPre=array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost=array('dbform_block_history.tpl','dbform_usage_extra.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design, $fixclient_data;

        if(!isset($fixclient_data))
            $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);

        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_sms',$this->data['id']);
            HelpDbForm::assign_tt('usage_sms',$this->data['id'],$this->data['client']);

            $db->Query('
                select
                    id,
                    description,
                    concat(per_month_price, " / ", per_sms_price) as price,
                    currency
                from
                    tarifs_sms
                where
                    id='.$this->data['tarif_id']
            );

            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];
        }else{
            $db->Query('
            select
                id,
                description,
                concat(per_month_price, " / ", per_sms_price) as price,
                currency
            from
                tarifs_sms
            order by per_sms_price desc, per_month_price desc'
            );
            $R=array('');
            while($r=$db->NextRecord())
                $R[$r['id']]=$r['description'].' ('.$r['price'].' '.$r['currency'].')';
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['add']=' onchange=form_usage_sms_get()';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';
        }
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        $v=DbForm::Process();
        if($v=='add' || $v=='edit'){

            if(!isset($this->dbform['t_block']))
                $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_sms',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}

class DbFormUsageWellSystem extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_extra');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['param_value']=array();
        $this->fields['amount']=array();
        $this->fields['async_price']=array('type'=>'label','db_ignore'=>1);
        $this->fields['async_period']=array('type'=>'label','db_ignore'=>1);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['t_comment']=array('db_ignore'=>1);
        $this->includesPre = array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost =array('dbform_block_history.tpl','dbform_usage_extra.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design;
        global $fixclient_data;
        if(!isset($fixclient_data))
            $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_extra',$this->data['id']);
            HelpDbForm::assign_tt('usage_extra',$this->data['id'],$this->data['client']);

            $db->Query('
                select
                    id,
                    description,
                    price,
                    currency
                from
                    tarifs_extra
                where
                    id='.$this->data['tarif_id'].
                (isset($fixclient_data['currency'])?' and currency="'.$fixclient_data['currency'].'" ':'')
            );
            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];
        }else{
            $db->Query('
            select
                id,
                description,
                price,
                currency
            from
                tarifs_extra
            where
                code="wellsystem"
                '.(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'')
            );
            $R=array('');
            while($r=$db->NextRecord())
                $R[$r['id']]=$r['description'].' ('.$r['price'].' '.$r['currency'].')';
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['add']=' onchange=form_usage_extra_get()';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';
        }
        DbForm::Display($form_params,$h2,$h3);
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        $v=DbForm::Process();
        if($v=='add' || $v=='edit'){
            if(!isset($this->dbform['t_block']))
                $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_extra',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }
}


class DbFormUsageIPPPP extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_ip_ppp');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');
        $this->fields['login']=array();
        $this->fields['password']=array();
        $this->fields['enabled']=array('enum'=>array('0','1'));
        $this->fields['ip']=array();
        $this->fields['nat_to_ip']=array();
        $this->fields['limit_kbps_in']=array();
        $this->fields['limit_kbps_out']=array();
        $this->fields['day_quota_in']=array();
        $this->fields['day_quota_in_used']=array('type'=>'label');
        $this->fields['day_quota_out']=array();
        $this->fields['day_quota_out_used']=array('type'=>'label');
        $this->fields['month_quota_in']=array();
        $this->fields['month_quota_in_used']=array('type'=>'label');
        $this->fields['month_quota_out']=array();
        $this->fields['month_quota_out_used']=array('type'=>'label');
//        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
//        $this->fields['t_comment']=array('db_ignore'=>1);
        $this->includesPre = array('dbform_block.tpl');
        $this->includesPost =array('dbform_block_history.tpl'); //,'dbform_usage_ip_ppp.tpl');
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design;
        global $fixclient_data;
        if (!isset($fixclient_data)) $fixclient_data=$GLOBALS['module_clients']->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_ip_ppp',$this->data['id']);
            HelpDbForm::assign_tt('usage_ip_ppp',$this->data['id'],$this->data['client']);

/*            $db->Query('select id,description,price,currency from tarifs_extra where 1 '.(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'').'and id='.$this->data['port_id']);
            $r=$db->NextRecord();
            $this->fields['tarif_str']['type']='label';
            $design->assign('tarif_real_id',$r['id']);
            $this->data['tarif_str']=$r['description'];*/
        } else {
            /*$db->Query('select id,description,price,currency from tarifs_extra where 1 '.(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'').'and status!="archive"');
            $R=array(''); while ($r=$db->NextRecord()) $R[$r['id']]=$r['description'].' ('.$r['price'].' '.$r['currency'].')';
            $this->fields['tarif_id']['type']='select';
            $this->fields['tarif_id']['assoc_enum']=$R;
            $this->fields['tarif_str']['type']='no';*/
        }
        DbForm::Display($form_params,$h2,$h3);
    }    
    public function Process($no_real_update = 0) {
        global $db,$user;
        $this->Get();
        if (!isset($this->dbform['id'])) return '';
        $v=DbForm::Process();
        if ($v=='add' || $v=='edit') {
            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            $text = ' (скорость вх - '.$this->dbform['limit_kbps_in'].',  - '.$this->dbform['limit_kbps_out'].')';
            HelpDbForm::save_block('usage_ip_ppp',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment'].$text);
        }
        return $v;
    }
}

class DbFormTechCPE extends DbForm{
    public function __construct() {
        DbForm::__construct('tech_cpe');
        $this->fields['actual_from']=array('default'=>'2029-01-01');
        $this->fields['actual_to']=array('default'=>'2029-01-01');

        $this->fields['id_model']=array('type'=>'enum','add'=>' onchange=form_cpe_get_clients()','assoc_enum'=>array());
        $this->fields['client']=array('in_tag'=>1,'add'=>' onchange=form_cpe_get_services()','enum'=>array());
        $this->fields['service']=array('type'=>'hidden','default'=>'usage_ip_ports');
        $this->fields['id_service']=array('in_tag'=>1,'enum'=>array());

        $this->fields['deposit_sumUSD']=array();
        $this->fields['deposit_sumRUR']=array();
        $this->fields['serial']=array('type'=>'first_text');
        $this->fields['mac']=array('type'=>'text');
        
        $this->fields['ip']=array();
        $this->fields['ip_nat']=array();
        $this->fields['ip_cidr']=array();
        $this->fields['ip_gw']=array();
        $this->fields['snmp']=array('assoc_enum'=>array('0'=>'&ndash; не опрашивать','1'=>'+ опрашивать'));
        $this->fields['admin_login']=array();
        $this->fields['admin_pass']=array();
        $this->fields['numbers']=array();
        $this->fields['logins']=array();
        $this->fields['owner']=array('enum'=>array('', 'mcn', 'client', 'mgts','synterra'),'default'=>'mgts');
        $this->fields['tech_support']=array('enum'=>array('', 'mcn', 'client', 'mgts','synterra'),'default'=>'mcn');

        $this->fields['ast_autoconf']=array('assoc_enum'=>array('0'=>'ручной (старый) режим','1'=>'автоматический режим'),'default'=>'0');
        $this->includesPre = array('dbform_cpe_add.tpl');
        $this->includesPost =array('dbform_cpe_add2.tpl','dbform_changelog.tpl');
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';
        $v =
            ($this->dbform_action=='save')
        &&
            (!$this->dbform['id'])
        &&
            ($this->dbform['serial']!='')
        &&
            $db->GetRow("
                select
                    *
                from
                    tech_cpe
                where
                    actual_from <= NOW()
                and
                    actual_to >= NOW()
                and
                    serial = '".addslashes($this->dbform['serial'])."'
                and
                    id != '".addslashes($this->dbform['id'])."'
            ");

        if($v){
            $this->dbform['serial']='';
            trigger_error('Такой серийный номер занят');
        }

        $this->dbform['service'] = 'usage_ip_ports';
        if(!$v && $this->dbform_action!='delete'){
            if(isset($this->dbform['t_C2U'])){
                $db->Query('
                    delete from
                        tech_cpe2voip
                    where
                        cpe_id = '.$this->dbform['id']
                );
                foreach($this->dbform['t_C2U'] as $usage=>$arrs){
                    $usage = intval($usage);
                    foreach($arrs as $line_number=>$line_enabled)
                        if($line_enabled==1){
                            $line_number = intval($line_number);
                            $db->QueryInsert(
                                'tech_cpe2voip',
                                array(
                                    'cpe_id'=>$this->dbform['id'],
                                    'usage_id'=>$usage,
                                    'line_number'=>$line_number
                                )
                            );
                        }
                }
            }
            unset($this->dbform['t_C2U']);
        }
        
       $res = DbForm::Process($v);

            $db->QueryInsert(
                'log_tech_cpe',
                array(
                    'tech_cpe_id' => $this->dbform['id'],
                    'user_id' => $user->Get('id'),
                    'ts' => array('NOW()'),
                )
            );

       return $res;
    }
    
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design;
        $this->fields['id_model']['assoc_enum']=array(''=>'');
        $db->Query('select id,vendor,model from tech_cpe_models order by vendor,model');
        while ($r=$db->NextRecord()) $this->fields['id_model']['assoc_enum'][$r['id']]=$r['vendor'].' '.$r['model'];
        if ($this->isData('id')) {
            $design->assign('dbform_f_history',$db->AllRecords('select log_tech_cpe.*,user_users.user from log_tech_cpe inner join user_users ON user_users.id=log_tech_cpe.user_id where tech_cpe_id='.$this->data['id'].' order by ts desc'));
            $db->Query('select U.id,U.E164,U.no_of_lines,T.line_number from usage_voip as U LEFT JOIN tech_cpe2voip as T ON T.cpe_id='.$this->data['id'].' AND T.usage_id=U.id where U.actual_from<=NOW() and U.actual_to>=NOW() and U.client="'.$this->data['client'].'"');
            $R = array();
            while ($r = $db->NextRecord()) {
                if (!isset($R[$r['E164']])) {
                    $v = array();
                    $v[0] = $r['id'];
                    for ($i=1;$i<=$r['no_of_lines'];$i++) $v[$i]=($i?0:'-');
                    $R[$r['E164']] = $v;
                }
                if ($r['line_number']!==null) {
                    $R[$r['E164']][$r['line_number']] = 1;
                }
            }
            $design->assign('dbform_f_C2U',$R);
        }
        DbForm::Display($form_params,$h2,$h3);
    }
}

class DbFormNewpayments extends DbForm{
    public function __construct() {
        DbForm::__construct('newpayments');
        $this->fields['client_id']=array('type'=>'hidden','default'=>'');
        $this->fields['client']=array('type'=>'label','db_ignore'=>1);
        $this->fields['sum_rub']=array('type'=>'text','default'=>'0.00');
        $this->fields['payment_date']=array('type'=>'text','default'=>date('Y-m-d'));
        $this->fields['oper_date']=array('type'=>'text','default'=>date('Y-m-d'));
        $this->fields['payment_no']=array('type'=>'text','default'=>'0');
        $this->fields['payment_rate']=array('type'=>'text');
        $this->fields['type']=array('assoc_enum'=>array('bank'=>'b bank','prov'=>'p prov', 'neprov'=>'n neprov'),'default'=>'bank', 'add'=>' onchange=form_newpayments_hide()');
        $this->fields['bank']=array('assoc_enum'=>array('citi'=>'CitiBank','mos'=>'Банк Москвы','ural'=>'УралСиб','sber'=>'Сбербанк'),'default'=>'mos');
        $this->fields['comment']=array('default'=>'');
        $this->fields['bill_no']=array('type'=>'text');
    }
    public function Process($no_real_update = 0) {
        global $db,$user;
        $this->Get();
        if (!isset($this->dbform['id'])) return '';
        $this->fields['add_user']=array();
        $this->fields['add_date']=array();
        $this->dbform['add_user']=$user->Get('id');
        $this->dbform['add_date']=array('NOW()');
        $this->fields['bill_vis_no']=array();
        $this->dbform['bill_vis_no']=$this->dbform['bill_no'];
        if (!$this->dbform['payment_rate']) {
            $this->dbform['payment_rate']=get_payment_rate_by_bill($this->dbform['payment_date'],$this->dbform['sum_rub'],$this->dbform['bill_no']);
        }
        return DbForm::Process();
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db;
        $c=$this->data['client_id'];
        if (!$c) $c=$this->fields['client_id']['default'];
        if ($c)
        {
            $this->fields['bill_no']['type']='enum';

            $R=array();

            //добавляем не оплаченные счета
            $billsNoPayed = array();
            foreach(NewBill::find('all', array(
                            'select' => 'bill_no',
                            'conditions' => array('client_id' => $c, 'is_payed' => 0),
                            'order' => 'bill_no'
                            )
                        ) as $bill) 
            {
                $billsNoPayed[]=$bill->bill_no;
            }
            if($billsNoPayed) 
            {
                $R[] = '';
                $R[] = 'счета не оплаченые';
                $R = array_merge($R, $billsNoPayed);
            }

            // добавляем оплаченные счета
            $billsPayed = array();
            foreach(NewBill::find('all', array(
                            'select' => 'bill_no',
                            'conditions' => array('client_id = ? and is_payed != 0', $c),
                            'order' => 'bill_no'
                            )
                        ) as $bill) 
            {
                $billsPayed[]=$bill->bill_no;
            }
            if($billsPayed)
            {
                $R[] = '';
                $R[] = 'счета оплаченые';
                $R = array_merge($R, $billsPayed);
            }


            // добавляем не полаченые заказы поставщикам
            $incomeGoodsNoPayed = array();
            foreach(GoodsIncomeOrder::find('all', array(
                        'select' => 'number',
                        'conditions' => array(
                            'is_payed' => 0,
                            'client_card_id' => $c),
                        'order' => 'number')) as $order)
            {
                $incomeGoodsNoPayed[]=$order->number;
            }
            if($incomeGoodsNoPayed)
            {
                $R[]='';
                $R[]='заказы не оплаченые';
                $R = array_merge($R, $incomeGoodsNoPayed);
            }

            $this->fields['bill_no']['enum']=$R;
        }
        $curr = '';
        if (isset($GLOBALS['fixclient_data']) && isset($GLOBALS['fixclient_data']['currency'])) $curr = $GLOBALS['fixclient_data']['currency'];
        if ($curr=='RUR') {
            $this->fields['payment_rate']['default']=1;
            if (!access('users','grant')) $this->fields['payment_rate']['type'] = 'hidden';
        } else {
            $r=$db->GetRow('select * from bill_currency_rate where date=NOW() and currency="USD"');
            $this->fields['payment_rate']['default']=$r['rate'];
        }
        DbForm::Display($form_params,$h2,$h3);
    }
}

class DbFormTechCPEModels extends DbForm{
    public function __construct() {
        DbForm::__construct('tech_cpe_models');
        $this->fields['vendor']=array();
        $this->fields['model']=array();
        $this->fields['type']=array('enum'=>array('','voip','router','adsl','wireless', 'pon'));
        $this->fields['default_deposit_sumUSD']=array();
        $this->fields['default_deposit_sumRUR']=array();
    }
}

class DbFormUsagePhoneRedirConditions extends DbForm {
    public function __construct() {
        DbForm::__construct('usage_phone_redir_conditions');
        $this->fields['title']=array();
        $this->fields['type']=array('enum'=>array('TIME','DAMAGE'));
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design;
        $id=$this->data['id'];
        if ($id) {
            $R = $db->AllRecords('select * from usage_phone_redir_condition_data where condition_id='.$id);
            $design->assign('dbform_f_rules',$R);
            $this->includesPost2 = array('dbform_redir_rules.tpl');
        }
        DbForm::Display($form_params,$h2,$h3);
    }    
}

class DbFormDataCenter extends DbForm{
    public function __construct() {
        DbForm::__construct('datacenter');

        $this->fields['name'] = array();
        $this->fields['address'] = array();
        $this->fields['comment'] = array();

    }
}

class DbFormServerPbx extends DbForm{
    public function __construct() {
        global $db;

        DbForm::__construct('server_pbx');

        $this->fields['name'] = array();
        $this->fields['ip'] = array();

        $this->fields['datacenter_id'] = array("assoc_enum" => $db->AllRecordsAssoc("select name,id from datacenter order by name desc", "id", "name"));

    }
}

class DbFormFactory {
    public static function Create($table) {

        if ($table=='usage_ip_routes') {
            return new DbFormUsageIpRoutes();
        }elseif ($table=='usage_ip_ports') {
            return new DbFormUsageIpPorts();
        }elseif ($table=='usage_ip_ppp') {
            return new DbFormUsageIPPPP();
        }elseif ($table=='usage_voip') {
            return new DbFormUsageVoip();
        }elseif ($table=='tech_cpe') {
            return new DbFormTechCPE();
        }elseif ($table=='tech_cpe_models') {
            return new DbFormTechCPEModels();
        }elseif ($table=='bill_monthlyadd') {
            return new DbFormBillMonthlyadd();
        }elseif ($table=='domains') {
            return new DbFormDomains();
        }elseif ($table=='usage_extra') {
            return new DbFormUsageExtra();
        }elseif ($table=='usage_welltime') {
            return new DbFormUsageWelltime();
        }elseif ($table=='usage_virtpbx') {
            return new DbFormUsageVirtpbx();
        }elseif ($table=='usage_8800') {
            return new DbFormUsage8800();
        }elseif ($table=='usage_sms') {
            return new DbFormUsageSms();
        }elseif ($table=='emails') {
            return new DbFormEmails();
        }
    }
}

$GLOBALS['translate_arr']=array(
    '*.actual_from'            => 'активна с',
    '*.actual_to'            => 'активна до',
    '*.net'                    => 'IP-адрес сети',
    '*.client'                => 'клиент',
    '*.address'                => 'адрес',
    '*.node'                => 'роутер',
    '*.port'                => 'порт',
    'routes.type'            => 'тип сети',
    '*.port_type'            => 'тип порта',
    '*.tarif'                => 'тариф',
    '*.trafcounttype'        => 'тип учёта траффика',
    '*.description'            => 'описание',
    '*.price'                => 'стоимость',
    '*.amount'                => 'количество',
    '*.period'                => 'период',
    '*.domain'                => 'домен',
    '*.password'            => 'пароль',
    '*.last_modified'        => 'дата последней модификации',
    '*.router'                => 'роутер',
    '*.phone'                => 'телефон',
    '*.location'            => 'местоположение',
    '*.reboot_contact'        => 'данные ответственного за перезагрузку',
    '*.adsl_modem_serial'    => 'серийный номер модема',
    '*.manufacturer'        => 'производитель',
    '*.model'                => 'модель',
    '*.serial'                => 'серийный номер',
    '*.location'            => 'местоположение',
    '*.E164'                => 'номер телефона',
    '*.ClientIPAddress'        => 'IP-адрес',
    '*.enabled'                => 'включено',
    '*.date_last_writeoff'    => 'дата последнего списания',
    '*.status'                => 'состояние',
        
    'emails.local_part'        => 'почтовый ящик',
    'emails.box_size'        => 'занято, Kb',
    'emails.box_quota'        => 'размер ящика',

    'newpayments.type'                    => 'тип платежа',
    'newpayments.bank'                    => 'Банк',
    'newpayments.bill_no'                => 'номер счёта',
    'newpayments.sum_rub'                => 'сумма в рублях',
    'newpayments.payment_date'            => 'дата платежа',
    'newpayments.payment_rate'            => 'курс доллара',

    '*.nat_net'                            => 'IP-адрес внутренней сети (via NAT)',
    '*.dnat'                            => 'dnat',
    '*.up_node'                            => 'up_node',
    '*.flows_node'                        => 'flows_node',
    '*.tarif_type'                        => 'тип тарифа',
    'routes.port_id'                    => 'подключение',
    'routes.comment'                    => 'комментарий',
    'routes.tarif_lastmonth'            => 'прошлый тариф',

    'usage_ip_ports.test_lines'            => 'test_lines Тестируемые линии',
    'usage_ip_ports.test_req_no'        => 'test_req_no Заявка в МГТС',

    'usage_voip.no_of_lines'            => 'число линий',
    'usage_voip.no_of_callfwd'            => 'кол-во переадресациий',
    'usage_voip.tech_voip_device_id'    => 'устройство',
    'usage_voip.tarif'                    => 'тариф',
        
    'clients_vip.num_unsucc'            => 'Текущее число неудачных попыток',
    'clients_vip.email'                    => 'Адрес e-mail',
    'clients_vip.phone'                    => 'Номер телефона',
    'clients_vip.important_period'        => 'В какое время отслеживать',
    'clients_vip.router'                => 'Роутер',
    'clients.with_base'                    => 'На основании',
    'clients.corr_acc'                    => 'К/С',
    'clients.pay_acc'                    => 'Р/С',
    'clients.bank_name'                    => 'Банк',
    'clients.bank_city'                    => 'Город Банка',

    'tech_cpe.type'            => 'тип устройства',
    'tech_cpe.service'        => 'связанная услуга',
    'tech_cpe.id_service'    => 'связанное подключение',
    'tech_cpe.mac'            => 'MAC-адрес',
    'tech_cpe.ip'            => 'IP-адрес',
    'tech_cpe.ip_nat'        => 'IP-адрес NAT',
    'tech_cpe.ip_cidr'        => 'IP-адрес CIDR',
    'tech_cpe.ip_gw'        => 'IP-адрес GW',
    'tech_cpe.admin_login'    => 'адмниский логин',
    'tech_cpe.admin_pass'    => 'адмниский пароль',
    'tech_cpe.numbers'        => array('номера','и, вроде бы, комментарий'),
    'tech_cpe.logins'        => 'логины',
    'tech_cpe.owner'        => 'владелец',
    'tech_cpe.tech_support'    => 'тех. поддержка',
    'tech_cpe.deposit_sumUSD'    => 'сумма залога в USD',
    'tech_cpe.deposit_sumRUR'    => 'сумма залога в RUR',
    'tech_cpe_models.type'    => 'тип устройства',
    'tech_cpe_models.default_deposit_sumUSD'    => 'сумма залога в USD по умолчанию',
    'tech_cpe_models.default_deposit_sumRUR'    => 'сумма залога в RUR по умолчанию',
    '*.vendor'                                => 'вендор',
    'tech_cpe_models.part_no'                => 'парт. номер',
    'tech_cpe.id_model'                        => 'модель устройства',
    '*.comment'                    =>    'комментарий',        
    '*.t_comment'                    =>    'комментарий',        
    
    'usage_phone_redir_conditions.type'        => 'тип условия',
    'usage_phone_redir_conditions.title'    => 'название условия',
    
    '*.currency'    => 'валюта',
    '*.name'        => 'название',
    '*.pay_once'    => 'плата за подключение',
    '*.pay_month'    => 'ежемесячная плата',
    '*.mb_month'    => 'мегабайт в месяц',
    '*.pay_mb'        => 'плата за мегабайт сверх лимита',
    '*.pay_mb'        => 'плата за мегабайт сверх лимита',
    '*.type_count'    => 'какой-то тип подсчёта',
    '*.type'        => 'тип тарифа',
    '*.month_unit'    => 'ежемесячно за Unit',
    '*.month_case'    => 'ежемесячно за Case',
    '*.month_r'        => 'Мб траффика Russia в месяц',
    '*.month_r2'    => 'Мб траффика Russia2 в месяц',
    '*.month_f'        => 'Мб траффика Foreign в месяц',
    '*.pay_r'        => 'цена за Мб превышения Russia',
    '*.pay_r2'        => 'цена за Мб превышения Russia2',
    '*.pay_f'        => 'цена за Мб превышения Foreign',
    '*.mb_disk'        => 'дисковое пространство',
    '*.has_dns'        => 'наличие DNS',
    '*.has_ftp'        => 'наличие FTP',
    '*.has_ssh'        => 'наличие SSH',
    '*.has_ssi'        => 'наличие SSI',
    '*.has_php'        => 'наличие PHP',
    '*.has_perl'    => 'наличие Perl',
    '*.has_mysql'    => 'наличие MySQL',
    '*.destination_name'    => 'название зоны назначения',
    '*.destination_prefix'    => 'префикс зоны назначения',
    '*.rate_USD'    => 'цена за минуту в USD',
    '*.rate_RUR'    => 'цена за минуту в рублях',
    '*.priceid'        => 'ID тарифной группы',        
    '*.dgroup'        => 'DGroup',
    '*.dsubgroup'    => 'DSubGroup',
    '*.month_line'    => 'ежемесячная плата за линию',
    '*.month_number'=> 'ежемесячная плата за номер',
    '*.once_line'    => 'плата за подключение линии',
    '*.once_number'    => 'плата за подключение номера',
    '*.adsl_speed'    => 'Скорость ADSL',
    '*.free_local_min'        => 'бесплатных местных минут',
    '*.edit_user'            => 'пользователь, изменивший тариф последний раз',
    '*.edit_time'            => 'время последнего изменения тарифа',
    '*.type_internet'        =>'тип интернет-тарифа',
    '*.param_name'            => array('Пояснение к параметру <b style="background:#F0F0F0">%</b>','(при необходимости)'),
    '*.code'            => 'Группа услуг',
    '*.tarif_id'            => 'услуга',
    '*.tarif_str'            => 'услуга',
    '*.async_price'            => 'стоимость',
    '*.async_period'        => 'период',
    '*.okvd_code'           => 'Единица измерения',
    'tarifs_extra.code'        => 'код услуги',
    'monitor_clients.allow_bad'                    => 'разрешенное число плохих пингов',
    'monitor_clients.period_mail'                => 'период между письмами, мин',
    'domains.registrator'                        => 'регистратор',
    'domains.paid_till'                            => 'оплачен до',
    'domains.rucenter_form_no'                    => 'номер клиента в RuCenter',
    '*.ast_autoconf'        => 'режим конфигурирования asteriskа',
    '*.is_countable'        => 'ограничение на количество',
    '*.dealer_id'        => 'ID дилера',
    '*.is_agent'        => 'Агент',
    '*.interest'        => 'Вознаграждение',
    'price_voip.operator' => 'Оператор',
    'price_voip.dgroup' => 'Направление',
    'price_voip.dsubgroup' => 'Подгруппа',
    'tarifs_voip.tarif_group' => 'Тарифная группа',
    '*.num_ports' => 'Количество портов',
    '*.overrun_per_port' => 'Превышение за порт',
    '*.space' => 'Пространство Мб',
    '*.overrun_per_mb' => 'Превышение за Мб',
    '*.is_record' => 'Запись звонков',
    '*.is_fax' => 'Факс',
    '*.datacenter_id' => 'Тех. площадка',
    '*.server_pbx_id' => 'Сервер АТС',
    '*.number' => 'Номер',
    '*.per_month_price' => 'Абонентская плата (с НДС)',
    '*.per_sms_price' => 'за 1 СМС (с НДС)',
    '*.gpon_reserv' => 'Сеть под GPON'
    );
?>
