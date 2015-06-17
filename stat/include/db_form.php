<?php
use app\classes\StatModule;
use app\models\ClientAccount;
use app\classes\Assert;

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
            
            if (isset($this->dbform['actual_from']) && isset($this->dbform['actual_to'])) 
            {
                global $db;
                $ts = strtotime($this->dbform['actual_from']);
                if ($ts !== false)
                {
                    $this->dbform['actual_from'] = date('Y-m-d', $ts);
                } elseif ($this->dbform['id']) {
                    $this->dbform['actual_from'] = $db->GetValue('SELECT actual_from FROM ' . $this->table . ' WHERE id = ' . $this->dbform['id']);
                } else {
                    $this->dbform['actual_from'] = '4000-01-01';
                } 
                
                $ts = strtotime($this->dbform['actual_to']);
                if ($ts !== false)
                {
                    $this->dbform['actual_to'] = date('Y-m-d', $ts);
                } elseif ($this->dbform['id']) {
                    $this->dbform['actual_to'] = $db->GetValue('SELECT actual_to FROM ' . $this->table . ' WHERE id = ' . $this->dbform['id']);
                } else {
                    $this->dbform['actual_to'] = '4000-01-01';
                }
            }
        }
    }
    public function Load($id) {
        global $db;
        $add_select = '';
        if (isset($this->fields['actual_from']) && isset($this->fields['actual_to'])) 
        {
            $add_select = ',DATE_FORMAT(actual_from, "%d-%m-%Y") as actual_from,DATE_FORMAT(actual_to, "%d-%m-%Y") as actual_to';
        }
        $db->Query('select *'.$add_select.' from '.$this->table.' where id='.$id);
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
                    Yii::$app->session->addFlash('success', 'Запись обновлена');
                } else {
                    $db->Query('insert into '.$this->table.' SET '.$s);
                    $this->dbform['id']=$db->GetInsertId();
                    $p='add';
                    Yii::$app->session->addFlash('success', 'Запись добавлена');
                }
                $this->Load($this->dbform['id']);
            } else {
                $p='add';
                $this->data=$this->dbform;
            }
            return $p;
        }
    }

    public function fillUTCPeriod()
    {
        $client = ClientAccount::findOne(['client' => $this->dbform['client']]);
        Assert::isObject($client);
        $this->dbform['activation_dt'] = (new DateTime($this->dbform['actual_from'], new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $this->dbform['expire_dt'] = (new DateTime($this->dbform['actual_to'], new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
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
        StatModule::tt()->showTroubleList(1,'service',$client,$service,$service_id);
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
                                        $tarifId,$tarifLocalMobId,$tarifRussiaId,$tarifRussiaMobId,$tarifInternId,
                                        $dest_group, $minpayment_group, 
                                        $minpayment_local_mob, $minpayment_russia, $minpayment_intern)
    {
        global $db,$user;
        $db->Query('insert into log_tarif (service,id_service,id_user,ts,date_activation,comment,
                                        id_tarif,id_tarif_local_mob,id_tarif_russia,id_tarif_russia_mob,id_tarif_intern,
                                        dest_group,minpayment_group,
                                        minpayment_local_mob,minpayment_russia,minpayment_intern
                                    ) VALUES '.
                                    '("'.$service.'",'.$id.','.$user->Get('id').',NOW(),"'.addslashes($dateActivation).'","",'.
                                        intval($tarifId).','.intval($tarifLocalMobId).','.intval($tarifRussiaId).','.intval($tarifRussiaMobId).','.intval($tarifInternId).','.
                                        intval($dest_group).','.intval($minpayment_group).','.
                                        intval($minpayment_local_mob).','.intval($minpayment_russia).','.intval($minpayment_intern).
                                    ')');
        
    }
    public static function saveChangeHistory($cur = array(), $new = array(), $usage_name = '') 
    {
        global $user, $db;

        if (!$cur || count($cur) == 0 || count($new) == 0 || !strlen($usage_name))
            return;

        $fields = array();
        foreach ($cur as $k=>$v) {
            if (isset($new[$k]) && $new[$k] != $v) {
                $fields[$k] = array('value_from'=>$v, 'value_to'=>$new[$k]);
            }
        }
        if (!count($fields)) return;

        $post = array(
            'service'=>$usage_name,
            'service_id'=>$cur['id'],
            'user_id'=>$user->Get('id')
        );
        $log_usage_history_id = $db->QueryInsert('log_usage_history', $post);
        if ($log_usage_history_id) {
            foreach ($fields as $fld=>$v) {
                $post = array(
                                'log_usage_history_id'=>$log_usage_history_id,
                                'field'=>$fld,
                                'value_from'=>$v['value_from'],
                                'value_to'=>$v['value_to']
                );
                $db->QueryInsert('log_usage_history_fields', $post);
            }
        }
    }
    public static function assign_log_history($service, $id) {
        global $design, $db;
        $all = $db->AllRecords('
                SELECT 
                    l.*, u.user 
                FROM 
                    log_usage_history l 
                LEFT JOIN user_users u ON u.id=l.user_id 
                WHERE 
                    service="'.$service.'" AND service_id='.$id
                );
        foreach ($all as $k=>$v) {
            foreach($db->AllRecords("SELECT * FROM log_usage_history_fields WHERE log_usage_history_id = '".$v["id"]."'") as $f) {
                $f["name"] = HelpDbForm::_log_history__getFieldName($f["field"]);
                $all[$k]['fields'][] = $f;
            }
        }
        $design->assign('dbform_log_usage_history', $all);
    }
    public static function _log_history__getFieldName($fld = '') 
    {
        $names = array(
            'address'=>'адрес',
            'no_of_lines'=>'число линий',
            'allowed_direction'=>'Разрешенные направления',
            'region'=>'Регион',
            'actual_from'=>'активна с',
            'actual_to'=>'активна до',
            'E164'=>'номер телефона',
            'status'=>'состояние',
            'is_trunk'=>'Транк',
            'one_sip'=>'Одна SIP-учетка',
            'param_value'=>'param_value',
            'comment'=>'комментарий',
            'ip'=>'ip',
            'router'=>'роутер',
            'amount'=>'количество',
            'server_pbx_id'=>'Сервер АТС',
        );
        return isset($names[$fld]) ? $names[$fld] : $fld;
    }
}

class DbFormUsageIpPorts extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_ip_ports');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
        $this->fields['address']=array();
        
        $this->fields['port_type']=array('db_ignore'=>1,'enum'=>array('dedicated','pppoe','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON'),'default'=>'adsl','add'=>' onchange=form_ip_ports_hide()');

        $this->fields['node']=array('db_ignore'=>1,'add'=>' onchange="form_ip_ports_get_ports()" ');
        $this->fields['phone']=array('db_ignore'=>1);
        $this->fields['port']=array('db_ignore'=>1,'enum'=>array());

        $this->fields['port_id']=array('type'=>'hidden');
        $this->fields['amount']=array('default'=>'1');
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
        if (!isset($fixclient_data)) $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
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
                $v=''.$v;
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
        $current = $db->GetRow("select * from usage_ip_ports where id = '".$this->dbform["id"]."'");

        $this->fillUTCPeriod();

        $v=DbForm::Process();
        
        if ($v=='add' || $v=='edit') {
            HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_ip_ports');
            if (!($olddata=get_tarif_current("usage_ip_ports",$this->data['id']))) $b=1; else $b=0;
            if (!$b && $this->dbform['t_id_tarif']!=$olddata['id_tarif']) $b=1;
            if (!$b && $this->dbform['t_date_activation']!=$olddata['date_activation']) $b=1;
            if (!$b && $this->dbform['t_comment']!="") $b=1;
            if (!$this->dbform['t_id_tarif']) $b=0;
            if ($b) HelpDbForm::logTarif("usage_ip_ports",$this->dbform['id'],$this->dbform['t_id_tarif'],$this->dbform['t_date_activation']);

            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_ip_ports',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment'], isset($this->dbform['t_fields_changes']) ?$this->dbform['t_fields_changes'] : "");
        }

        return $v;
    }
}

class DbFormUsageVoip extends DbForm {
    public function __construct() {
        global $db, $fixclient_data;

        $regions = array();
        foreach($db->AllRecords('select * from regions') as $item)
            $regions[$item['id']] = $item['code'].' - '.$item['name'];


        DbForm::__construct('usage_voip');
        $this->fields['region']=array('type'=>'select','assoc_enum'=>$regions,'add'=>' readonly', 'default'=>'99');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>date('d-m-Y'), 'add'=>"onchange='change_datepicker_value();' ");
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
        $this->fields['E164']=array("add" => " onchange='form_usagevoip_hide()'");
        $this->fields['no_of_lines']=array('default'=>1);
        $this->fields['line7800_id']=array("assoc_enum" => array());
        $this->fields['allowed_direction']=array('assoc_enum' => UsageVoip::$allowedDirection, 'default'=>'full');
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['is_trunk']=array("assoc_enum" => array("0"=>"Нет","1"=>"Да"));
        $this->fields['one_sip']=array("assoc_enum" => array("0"=>"Нет","1"=>"Да"));
        $this->fields['address']=array();
        $this->fields['edit_user_id']=array('type'=>'hidden');
        $this->fields['is_moved']=array("type" => 'checkbox', 'visible' => false);
        $this->fields['is_moved_with_pbx']=array("type" => 'checkbox', 'visible' => false);

        $this->includesPreL = array('dbform_voip_tarif.tpl');
        $this->includesPreR = array('dbform_block.tpl');
        $this->includesPre=array('dbform_tt.tpl');
        $this->includesPost =array('dbform_voip_tarif_history.tpl','dbform_block_history.tpl');
    }
    /**
     *  Проверяет возможно ли перемещение данной услуги
     */
    private function prepareMovedFieldsForDispaly()
    {
        $check_move = UsageVoip::checkNumberIsMoved($this->data['E164'],$this->data['actual_from']);
        if (!empty($check_move))
        {
            $this->fields['is_moved']['visible'] = true;
            if ($this->data['is_moved'])
            {
                $this->fields['moved_from']=array("type" => "label");
                $this->data['moved_from'] = '<a target="_blank" href="/clients/view?id='. $check_move->client . '">' . $check_move->client . '</a>';
            }
            $check_move_with_pbx = UsageVirtpbx::checkNumberIsMovedWithPbx( $check_move->client, $this->data['client'],$this->data['actual_from']);
            if (!empty($check_move_with_pbx))
            {
                $this->fields['is_moved_with_pbx']['visible'] = true;
            }
            
        }
        $check_move = UsageVoip::checkNumberWasMoved($this->data['id']);
        if (!empty($check_move))
        {
            $this->fields['moved_to']=array("type" => "label");
            $this->data['moved_to'] = '<a target="_blank" href="/clients/view?id='. $check_move->client . '">' . $check_move->client . '</a>';
        }
    }
    public function Display($form_params = array(),$h2='',$h3='') {
        global $db,$design,$fixclient_data;
        $this->fields['table_name']=array("type" => 'hidden', 'value' => 'usage_voip');
        if ($this->isData('id')) {
            $this->prepareMovedFieldsForDispaly();
            HelpDbForm::assign_tarif('usage_voip',$this->data['id']);
            HelpDbForm::assign_tarif('usage_voip2',$this->data['id'],'2');
            HelpDbForm::assign_block('usage_voip',$this->data['id']);
            HelpDbForm::assign_tt('usage_voip',$this->data['id'],$this->data['client']);

            $region = $this->data['region'];
            $client = $this->data["client"];
        }else{
            $this->fields['ats3']=array(
                "type" => "include", 
                "file" => "services/voip_ats3_add.tpl"
            );

            $design->assign("form_ats3", $this->makeFormData());

            $region = $this->fields['region']['default'];
            $client = $fixclient_data["client"];
        }

        $lines7800 = $db->AllRecordsAssoc($q =  "select id, E164 from usage_voip where LENGTH(E164) <= 5 and client = '".($client)."'
            and (
                cast(now() as date) between actual_from and actual_to
                and id not in (select line7800_id from usage_voip where client = '".$client."')
        
        )".($this->data["line7800_id"] ? " or id = '".$this->data["line7800_id"]."'" : "")."
            ", "id", "E164")?:array();


        $line7800_default = array("0" => "Не задано");

        $this->fields["line7800_id"]["assoc_enum"] = $line7800_default + $lines7800;

        global $fixclient_data;
        if (!isset($fixclient_data)) $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
        $R=$db->AllRecords('select * from tarifs_voip '.
                            (isset($fixclient_data['currency'])?'where currency="'.$fixclient_data['currency'].'" ':'').' and region="'.$region.'" '.
                            'order by status, month_line, month_min_payment', 'id');
        $design->assign('dbform_f_tarifs',$R);
        $design->assign('region',$region);
        DbForm::Display($form_params,$h2,$h3);
    }
   /**
     *  Изменяет флаг перемещения вместе у номера, на который был перенесен данный номер
     *  @param array $current актуальная информация о номере
     */
    private function updateMovedFieldsBeforeSave($current)
    {
        if (!$this->dbform['is_moved'])
        {
            $this->dbform['is_moved_with_pbx'] = 0;
        }
        
        $check_move = UsageVoip::checkNumberWasMoved($this->data['id']);
        if (!empty($check_move) && $this->dbform['actual_to'] != $current['actual_to'])
        {
            $to_number = UsageVoip::first($check_move->id);
            $to_number->is_moved = 0;
            $to_number->save();
        }
    }
    public function Process($no_real_update = 0){
        global $db,$user;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';

        if($this->dbform['is_trunk'] == '0' && !$this->check_number()) return;

        $this->dbform['edit_user_id'] = $user->Get('id');
        $current = $db->GetRow("select * from usage_voip where id = '".$this->dbform["id"]."'");

        $this->fillUTCPeriod();

        $this->updateMovedFieldsBeforeSave($current);
        
        HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_voip');
        $v=DbForm::Process();

        if ($v == "add") //ats3
        {
            $this->processAts3FormAdd($this->dbform["id"]);
        }

        if ($v=='add' || $v=='edit') {
            $b = 1;
            if ($this->dbform['t_id_tarif'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_local_mob'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_russia'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_russia_mob'] == 0) $b=0;
            if ($this->dbform['t_id_tarif_intern'] == 0) $b=0;
            if ($this->dbform['t_minpayment_group'] == '') $b=0;
            if ($this->dbform['t_minpayment_local_mob'] == '') $b=0;
            if ($this->dbform['t_minpayment_russia'] == '') $b=0;
            if ($this->dbform['t_minpayment_intern'] == '') $b=0;

            $this->dbform["E164"] = trim($this->dbform["E164"]);
            
            if(preg_match("/^8/", $this->dbform["E164"], $o))
            {
                die("<font style='color:red'><b>Номер начинается на 8-ку!</b></font>");
            }elseif(strlen($this->dbform["E164"]) > 5 && strlen($this->dbform["E164"]) != 11 && strlen($this->dbform["E164"]) != 10) // if not line_without_number
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
                    if ($this->dbform['t_id_tarif_russia_mob']!=$olddata['id_tarif_russia_mob']) $b=1;
                    if ($this->dbform['t_id_tarif_intern']!=$olddata['id_tarif_intern']) $b=1;
                    if ($this->dbform['t_date_activation']!=$olddata['date_activation']) $b=1;
                    if ($this->dbform['t_comment']!='') $b=1;
                    if ($this->dbform['t_dest_group']!=$olddata['dest_group']) $b=1;
                    if ($this->dbform['t_minpayment_group']!=$olddata['minpayment_group']) $b=1;
                    if ($this->dbform['t_minpayment_local_mob']!=$olddata['minpayment_local_mob']) $b=1;
                    if ($this->dbform['t_minpayment_russia']!=$olddata['minpayment_russia']) $b=1;
                    if ($this->dbform['t_minpayment_intern']!=$olddata['minpayment_intern']) $b=1;
                    if ($this->dbform['t_date_activation']!=$olddata['date_activation']) $b=1;
                    if (!$b) continue;

                    HelpDbForm::logTarifUsage("usage_voip",
                            $usage_id,$this->dbform['t_date_activation'],
                            $this->dbform['t_id_tarif'],$this->dbform['t_id_tarif_local_mob'],$this->dbform['t_id_tarif_russia'],$this->dbform['t_id_tarif_russia_mob'],$this->dbform['t_id_tarif_intern'],
                            $this->dbform['t_dest_group'],$this->dbform['t_minpayment_group'],
                            $this->dbform['t_minpayment_local_mob'],$this->dbform['t_minpayment_russia'],$this->dbform['t_minpayment_intern']
                        );
                }

                if (defined("AUTOCREATE_SIP_ACCOUNT") && AUTOCREATE_SIP_ACCOUNT && !$this->dbform["is_trunk"]) {
                    
                    $toAutoCreate = false;

                    if ($v == "add")
                    {
                        $toAutoCreate = true;
                    } elseif ($v == "edit")
                    {
                        foreach(array("actual_from", "actual_to", "one_sip", "no_of_lines", ) as $field)
                        {
                            if ($this->dbform[$field] != $current[$field])
                            {
                                $toAutoCreate = true;
                                break;
                            }
                        }
                    }

                    if ($toAutoCreate)
                    {
                        event::go("autocreate_accounts", $this->data["id"]."|".$this->data["one_sip"]);
                    }
                }

            }else{
                trigger_error2("Не сохранено! Выберите тариф");
            }
        }
        voipNumbers::check();
        return $v;
    }

    private function makeFormData()
    {
        global $db, $fixclient_data;

        if (!is_array($fixclient_data) || !isset($fixclient_data["client"])) {
            throw new Exception("Клиент не найден");
        }

        $client = $fixclient_data["client"];
        $data = [
            "vpbxs" => $db->AllRecordsAssoc("select id, concat('id:', id, ' (',actual_from,')') as name from usage_virtpbx where client='".$client."' and cast(now() as date) between actual_from and actual_to", "id", "name"),
            "multis" => $db->AllRecordsAssoc("select id, name from multitrunk order by name", "id", "name")
            ];


        return $data;
    }

    private function processAts3FormAdd($usageId)
    {
        if (!get_param_raw("voip_ats3_add")) return;

        $data = ["usage_id" => $usageId, "client" => $this->data["client"], "number" => $this->data["E164"]];

        foreach(["type_connect", "sip_accounts", "vpbx_id", "multitrunk_id"] as $f) {
            $data[$f] = get_param_raw($f);
        }

        $usage = \app\models\UsageVoip::findOne(["id" => $usageId]);
        $usage->create_params = json_encode($data);
        $usage->save();

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
                trigger_error2("Введенный номер пересекается с id:".$c["id"].", клиент:".$c["client"].", c ".$c["actual_from"]." по ".$c["actual_to"].")");
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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
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
                trigger_error2('Такой адрес уже занят');
                return '';
            }
        }
        $current = $db->GetRow("select * from emails where id = '".$this->dbform["id"]."'");
        
        $v=DbForm::Process();
        
        if ($v=='add' || $v=='edit') {
            HelpDbForm::saveChangeHistory($current, $this->dbform, 'emails');
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
        $this->fields['actual_from']=array('type'=>'hidden','default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('type'=>'hidden','default'=>'01-01-4000');
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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
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
        $current = $db->GetRow("select * from domains where id = '".$this->dbform["id"]."'");
        $v=DbForm::Process();
        if ($v=='add' || $v=='edit') {
            HelpDbForm::saveChangeHistory($current, $this->dbform, 'domains');
            if (!isset($this->dbform['t_block'])) $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('domains',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);
        }
        return $v;
    }

}

class DbFormUsageIpRoutes extends DbForm{
    public function __construct() {
        DbForm::__construct('usage_ip_routes');
        $this->fields['port_id']=array('type'=>'hidden');
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
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
            $this->fields['actual_from']['default']=date('d-m-Y');
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
            trigger_error2('Адрес сети отсутствует');
        }

        $from = $this->dbform['actual_from'];
        $to = $this->dbform['actual_to'];

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
                        "'.$from.'" between actual_from and actual_to
                    or 
                        "'.$to.'" between actual_from and actual_to
                    or 
                        (actual_from between "'.$from.'" and "'.$to.'" and actual_to between "'.$from.'" and "'.$to.'")
                    )
                    and
                        net="'.addslashes($this->dbform['net']).'"
                    and
                        id!="'.addslashes($this->dbform['id']).'"')
        );


        if ($v) {$this->dbform['net']=''; trigger_error2('Сеть уже занята'); header("Location: ./?module=services&action=in_add2"); exit();}
        $current = $db->GetRow("select * from usage_ip_routes where id = '".$this->dbform["id"]."'");
        $action=DbForm::Process($p);

        if (!$v && !$p && $action!='delete') {
            HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_ip_routes');
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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
        $this->fields['code']=array('type'=>'hidden');
        $this->fields['tarif_id']=array('type'=>'hidden');
        $this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['param_value']=array();
        $this->fields['amount']=array("default" => 1);
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
        if (!isset($fixclient_data)) $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
        if ($this->isData('id')) {

            HelpDbForm::assign_block('usage_extra',$this->data['id']);
            HelpDbForm::assign_tt('usage_extra',$this->data['id'],$this->data['client']);

            $db->Query('select id,description,price,currency from tarifs_extra where 1 './*(isset($fixclient_data['currency'])?'and currency="'.$fixclient_data['currency'].'" ':'').*/'and id='.$this->data['tarif_id']);
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

        $current = $db->GetRow("select * from usage_extra where id = '".$this->dbform["id"]."'");

        $this->fillUTCPeriod();

        HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_extra');

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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
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
            $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
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
            $this->fields['tarif_id']['add']=' onchange=form_usage_extra_get(this)';
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

        $this->fillUTCPeriod();

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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
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
            $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            HelpDbForm::assign_block('usage_welltime',$this->data['id']);
            HelpDbForm::assign_tt('usage_welltime',$this->data['id'],$this->data['client']);

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

        $current = $db->GetRow("select * from usage_welltime where id = '".$this->dbform["id"]."'");

        $this->fillUTCPeriod();

        HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_welltime');

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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'), 'add'=>"onchange='optools.voip.check_e164.move_checking();' ");
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
        //$this->fields['tarif_id']=array('type'=>'hidden');
        //$this->fields['tarif_str']=array('db_ignore'=>1);
        $this->fields['server_pbx_id']=array('assoc_enum'=>$db->AllRecordsAssoc("select id, name from server_pbx order by name", "id", "name"), 'default' => 2);
        $this->fields['amount']=array("default" => 1);
        $this->fields['status']=array('enum'=>array('connecting','working'),'default'=>'connecting');
        $this->fields['comment']=array();
        $this->fields['is_moved']=array("type" => 'checkbox', 'visible' => false);
        $this->fields['moved_from']=array('type' => 'select', 'visible' => false, 'with_hidden' => true);
        
        $this->includesPre=array('dbform_block.tpl');
        $this->includesPre2=array('dbform_tt.tpl');
        $this->includesPost=array('dbform_block_history.tpl','dbform_usage_extra.tpl');
        $this->includesPreL = array('dbform_vpbx_tarif.tpl');
        $this->includesPost =array('dbform_vpbx_tarif_history.tpl','dbform_block_history.tpl');
    }
    /**
     *  Проверяет возможно ли перемещение данной услуги
     */
    private function prepareMovedFieldsForDispaly()
    {
        $check_move = UsageVirtpbx::getAllPosibleMovedPbx($this->data['actual_from'], $this->data['client']);
        if (!empty($check_move))
        {
            $this->fields['is_moved']['visible'] = true;
            $this->fields['moved_from']['visible'] = true;
            $this->fields['moved_from']['assoc_enum'] = $check_move;
            
            if ($this->data['is_moved'])
            {
                $moved_numbers = UsageVoip::getMovedNumber($check_move[$this->data['moved_from']], $this->data['client'], $this->data['actual_from']);
                if (!empty($moved_numbers))
                {
                    $this->fields['moved_numbers']=array("type" => "label");
                    $str = '';
                    foreach ($moved_numbers as $k=>$v)
                    {
                        $str .= $v->number . ': ';
                        $str .= '<a target="_blank" href="pop_services.php?table=usage_voip&id='. $v->from_id . '">' .  $v->from_client . '</a> => ';
                        $str .= '<a target="_blank" href="pop_services.php?table=usage_voip&id='. $v->to_id . '">' . $v->to_client . '</a>';
                        if ($k+1 != count($moved_numbers))
                        {
                            $str .= '<br/>';
                        }
                    }
                    $this->data['moved_numbers'] = $str;
                }
            }
        }
        $check_move = UsageVirtpbx::checkVpbxWasMoved($this->data['id']);
        if (!empty($check_move))
        {
            $this->fields['moved_to']=array("type" => "label");
            $this->data['moved_to'] = '<a target="_blank" href="/clients/view?id='. $check_move->client . '">' . $check_move->client . '</a>';
        }
    }
    public function Display($form_params = array(),$h2='',$h3='') {
         global $db,$design, $fixclient_data;

        $this->fields['table_name']=array("type" => 'hidden', 'value' => 'usage_virtpbx');
        if(!isset($fixclient_data))
            $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
        if ($this->isData('id')) {
            $this->prepareMovedFieldsForDispaly();
            HelpDbForm::assign_block('usage_virtpbx',$this->data['id']);
            HelpDbForm::assign_tt('usage_virtpbx',$this->data['id'],$this->data['client']);
            HelpDbForm::assign_tarif('usage_virtpbx',$this->data['id']);
        }

        $design->assign('dbform_f_tarifs',$db->AllRecords('select id, description, price, currency, status from tarifs_virtpbx'));

        DbForm::Display($form_params,$h2,$h3);
    }
    /**
     *  Изменяет флаг перемещения вместе с АТС у номеров, при изменение АТС
     *  @param array $current актуальная информация об АТС внесения изменений
     */
    private function updateMovedFieldsBeforeSave($current)
    {
        $moved_numbers = array();
        
        $check_move = UsageVirtpbx::checkVpbxWasMoved($this->data['id']);
        if (!empty($check_move) && $this->dbform['actual_to'] != $current['actual_to'])
        {
            $to_vpbx = UsageVirtpbx::first($check_move->id);
            $to_vpbx->is_moved = 0;
            $to_vpbx->save();
            
            $moved_numbers = UsageVoip::getMovedNumber($current['client'], $check_move->client, $check_move->actual_from);
        }
        
        if (!$this->dbform['is_moved'] && $current['is_moved'])
        {
            $check_move = UsageVirtpbx::checkVpbxIsMoved($current['actual_from']);
            if (!empty($check_move))
            {
                $moved_numbers = UsageVoip::getMovedNumber($check_move->client, $current['client'], $current['actual_from']);
            }
        }
        
        if (!empty($moved_numbers))
        {
            $moved_ids = array();
            foreach ($moved_numbers as $k=>$v)
            {
                $moved_ids[] = $v->to_id;
            }
            UsageVoip::update_all(array('set'=>array('is_moved_with_pbx' => 0), 'conditions' => array('id IN (?)', $moved_ids)));
        }
        
        if (!$this->dbform['is_moved'])
        {
            $this->dbform['moved_from'] = '';
        }
    }
    public function Process($no_real_update = 0){
        global $db,$user,$design;
        $this->Get();
        if(!isset($this->dbform['id']))
            return '';

        if(!$this->check_virtats()) {
            $this->fields['actual_from']['default']=$this->dbform['actual_from'];
            $this->fields['actual_to']['default']=$this->dbform['actual_to'];
            $this->fields['amount']['default']=$this->dbform['amount'];
            $this->fields['status']['default']=$this->dbform['status'];
            $this->fields['comment']['default']=$this->dbform['comment'];

            if (isset($this->dbform['t_id_tarif'])) {
                $cur_tarif = $db->getRow('select * from tarifs_virtpbx where id='.$this->dbform['t_id_tarif']);
                $cur_tarif['date_activation'] = $this->dbform['t_date_activation'];
                $design->assign('dbform_f_tarif_current', $cur_tarif);
                
            }

            return;
        }

        $current = $db->GetRow("select * from usage_virtpbx where id = '".$this->dbform["id"]."'");

        $this->fillUTCPeriod();

        $this->updateMovedFieldsBeforeSave($current);
        
        HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_virtpbx');

        $cur_tarif = get_tarif_current('usage_virtpbx',$this->dbform['id']);

        $v=DbForm::Process();
        if($v=='add' || $v=='edit'){
            if(!isset($this->dbform['t_block']))
                $this->dbform['t_block'] = 0;
            HelpDbForm::save_block('usage_virtpbx',$this->dbform['id'],$this->dbform['t_block'],$this->dbform['t_comment']);

            if ($this->dbform['t_id_tarif'] != $cur_tarif['id'] || $this->dbform['t_date_activation'] != $cur_tarif['date_activation']) {
                HelpDbForm::logTarif('usage_virtpbx', $this->dbform['id'], $this->dbform['t_id_tarif'], $this->dbform['t_date_activation']);
            }
        }
        return $v;
    }

    private function check_virtats()
    {
        global $db;

        $f = $this->dbform["actual_from"];
        $t = $this->dbform["actual_to"];

        $c = $db->GetRow(
                "select * from usage_virtpbx where ((actual_from between '".$f."' and '".$t."' or actual_to between '".$f."' and '".$t."' or (actual_from <= '".$f."' and '".$t."' <= actual_to ))) and id != '".$this->dbform["id"]."' and client='".$this->dbform["client"]."'");

        if($c)
        {
            trigger_error2("На указанные даты виртуальная АТС, у этого клиента, уже работает");
            return false;
        }

        return true;
    }
}

class DbFormUsageSms extends DbForm{
    public function __construct() {
        global $db;

        DbForm::__construct('usage_sms');
        $this->fields['client']=array('type'=>'label');
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
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
            $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);

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

        $current = $db->GetRow("select * from usage_sms where id = '".$this->dbform["id"]."'");

        $this->fillUTCPeriod();

        HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_sms');

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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
        $this->fields['activation_dt']=array('type'=>'hidden');
        $this->fields['expire_dt']=array('type'=>'hidden');
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
            $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
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

        $this->fillUTCPeriod();

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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');
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
        if (!isset($fixclient_data)) $fixclient_data=StatModule::clients()->get_client_info($this->data['client']);
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
        $current = $db->GetRow("select * from usage_ip_ppp where id = '".$this->dbform["id"]."'");
        
        $v=DbForm::Process();
        if ($v=='add' || $v=='edit') {
            HelpDbForm::saveChangeHistory($current, $this->dbform, 'usage_ip_ppp');
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
        $this->fields['actual_from']=array('default'=>date('d-m-Y'));
        $this->fields['actual_to']=array('default'=>'01-01-4000');

        $this->fields['id_model']=array('type'=>'enum','add'=>' onchange=form_cpe_get_clients()','assoc_enum'=>array());
        $this->fields['client']=array('in_tag'=>1,'add'=>' onchange=form_cpe_get_services()','enum'=>array());
        $this->fields['service']=array('type'=>'hidden','default'=>'usage_ip_ports');
        $this->fields['id_service']=array('in_tag'=>1,'enum'=>array());

        $this->fields['deposit_sumUSD']=array();
        $this->fields['deposit_sumRUB']=array();
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
            trigger_error2('Такой серийный номер занят');
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

class DbFormTechCPEModels extends DbForm{
    public function __construct() {
        DbForm::__construct('tech_cpe_models');
        $this->fields['vendor']=array();
        $this->fields['model']=array();
        $this->fields['type']=array('enum'=>array('','voip','router','adsl','wireless', 'pon'));
        $this->fields['default_deposit_sumUSD']=array();
        $this->fields['default_deposit_sumRUB']=array();
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
        global $db;
        DbForm::__construct('datacenter');

        $this->fields['name'] = array();
        $this->fields['address'] = array();
        $this->fields['comment'] = array();
        $this->fields['region'] = array("assoc_enum" => $db->AllRecordsAssoc("select id, name from regions order by id", "id", "name"));

    }
}

class DbFormServerPbx extends DbForm{
    public function __construct() {
        global $db, $db_ats;

        DbForm::__construct('server_pbx');

        $this->fields['name'] = array();
        $this->fields['ip'] = array();

        $this->fields['datacenter_id'] = array("assoc_enum" => $db->AllRecordsAssoc("select name,id from datacenter order by name desc", "id", "name"));

        $trunks = array(0 => "-- Не установленно --");

        foreach ($db_ats->AllRecordsAssoc("select name,id from a_multitrunk where parent_id = 0 order by name desc", "id", "name")as $id => $name)
            $trunks[$id] = $name;

        $this->fields['trunk_vpbx_id'] = array("assoc_enum" => $trunks);

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
        }elseif ($table=='domains') {
            return new DbFormDomains();
        }elseif ($table=='usage_extra') {
            return new DbFormUsageExtra();
        }elseif ($table=='usage_welltime') {
            return new DbFormUsageWelltime();
        }elseif ($table=='usage_virtpbx') {
            return new DbFormUsageVirtpbx();
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
    'usage_voip.E164'                => 'номер телефона',
    '*.ClientIPAddress'        => 'IP-адрес',
    '*.enabled'                => 'включено',
    '*.date_last_writeoff'    => 'дата последнего списания',
    '*.status'                => 'состояние',
    'usage_voip.is_trunk'              => 'Оператор',
    'usage_voip.one_sip'              => 'Одна SIP-учетка',
    'usage_voip.allowed_direction'      => 'Разрешенные направления',
        
    'emails.local_part'        => 'почтовый ящик',
    'emails.box_size'        => 'занято, Kb',
    'emails.box_quota'        => 'размер ящика',

    'newpayments.type'                    => 'тип платежа',
    'newpayments.bank'                    => 'Банк',
    'newpayments.bill_no'                => 'номер счёта',
    'newpayments.sum'                => 'сумма в рублях',
    'newpayments.payment_date'            => 'дата платежа',

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
    'tech_cpe.deposit_sumRUB'    => 'сумма залога в RUB',
    'tech_cpe_models.type'    => 'тип устройства',
    'tech_cpe_models.default_deposit_sumUSD'    => 'сумма залога в USD по умолчанию',
    'tech_cpe_models.default_deposit_sumRUB'    => 'сумма залога в RUB по умолчанию',
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
    '*.priceid'        => 'ID тарифной группы',
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
    '*.courier_id' => 'Курьер',
    '*.num_ports' => 'Количество портов',
    '*.overrun_per_port' => 'Превышение за порт',
    '*.space' => 'Пространство Мб',
    '*.overrun_per_gb' => 'Превышение за Gb',
    '*.is_record' => 'Запись звонков',
    '*.is_web_call' => 'Звонки с сайта',
    '*.is_fax' => 'Факс',
    '*.datacenter_id' => 'Тех. площадка',
    '*.server_pbx_id' => 'Сервер АТС',
    '*.number' => 'Номер',
    '*.per_month_price' => 'Абонентская плата (с НДС)',
    '*.per_sms_price' => 'за 1 СМС (с НДС)',
    '*.gpon_reserv' => 'Сеть под GPON',
    '*.trunk_vpbx_id' => 'Транк на VPBX',
    'newpayments.ecash_operator' => "Оператор платежа",
    'usage_voip.region' => 'Регион',
    'usage_voip.line7800_id' => 'Линия без номера для номера 8800',
    'usage_voip.is_moved' => 'Перемещенный номер',
    'usage_voip.is_moved_with_pbx' => 'Перемещен вместе с АТС',
    'usage_virtpbx.is_moved' => 'Перемещенная АТС',
    'usage_virtpbx.moved_numbers' => 'Номера перемещенные с АТС',
    '*.moved_from' => 'Перемещен с ',
    '*.moved_to' => 'Перемещен на ',
    );
?>
