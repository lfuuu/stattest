<?php

use app\models\ClientAccount;
use app\dao\services\SmsServiceDao;
use app\dao\services\WelltimeServiceDao;
use app\dao\services\EmailsServiceDao;
use app\dao\services\ExtraServiceDao;
use app\models\Organization;
use app\classes\Event;
use app\classes\Assert;
use app\models\UsageVoip;
use app\models\TariffVoip;
use app\models\Number;
use app\models\User;

class m_services extends IModule{
    function GetMain($action,$fixclient){
        if(!isset($this->actions[$action]))
            return;
        $act = $this->actions[$action];
        if(!access($act[0],$act[1]))
            return;
        call_user_func(array($this,'services_'.$action),$fixclient);
    }
    function services_default($fixclient){
        if (access_action('services','vo_view')) {$this->services_vo_view($fixclient); return;}
        if (access_action('services','trunk_view')) {$this->services_trunk_view($fixclient); return;}
        if (access_action('services','in_view')) {$this->services_in_view($fixclient); return;}
        if (access_action('services','co_view')) {$this->services_co_view($fixclient); return;}
    }

// ==========================================================================================================================================
    function services_in_async($fixclient) {
        global $db;
        $node=get_param_protected('node');
        $port_type=get_param_protected('port_type');
        if ($port_type=="pppoe") $port_type='pppoe","dedicated';
        $R=array(); $db->Query('select port_name from tech_ports where node="'.$node.'" and port_type IN ("'.$port_type.'") order by port_name');
        while ($r=$db->NextRecord()) $R[$r['port_name']]=$r['port_name'];
        $_RESULT=array(
                    'ports'        => $R,
                    );
        echo json_encode($_RESULT);
    }
    function services_in_report(){
        global $design,$db;
        $def=getdate();
        $from=param_load_date('from_',$def);
        $to=param_load_date('to_',$def);
        $def['mday']=1;
        $cur_from=param_load_date('cur_from_',$def);
        $def['mday']=31;
        $cur_to=param_load_date('cur_to_',$def);
        $def['mon']--;
        if($def['mon']==0){
            $def['mon']=12;
            $def['year']--;
        }
        $def['mday']=1;
        $prev_from=param_load_date('prev_from_',$def);
        $def['mday']=31;
        $prev_to=param_load_date('prev_to_',$def);

        $hide_off = get_param_protected('hide_off');
        $design->assign('hide_off',$hide_off);
        $port_type = array();
        if(isset($_REQUEST['port_type']) && count($_REQUEST['port_type'])>0)
            foreach($_REQUEST['port_type'] as $tp){
                $port_type[] = addcslashes($tp, "\\'");
            }
        $design->assign('port_type',$port_type);
        $manager = get_param_protected('manager');
        $design->assign('manager',$manager);
        $hide_slow = get_param_protected('hide_slow');
        $design->assign('hide_slow',$hide_slow);
        $unlim = get_param_protected('unlim');
        $design->assign('unlim',$unlim);
        $show_off = get_param_raw('show_off',false);
        $design->assign('show_off',$show_off);

        $design->assign('managers', User::dao()->getListByDepartments('manager'));

        $connections=array();

        $fil = '';
        if($port_type && count($port_type))
            $fil .= " AND `tp`.`port_type` in ('".join("','",$port_type)."')";
        if($manager)
            $fil .= ' AND `cr`.`manager` = "'.$manager.'"';
        if($hide_off)
            $fil .= ' AND `uip`.`actual_to` >= FROM_UNIXTIME('.strtotime("+1 day", $to).')';
        if($hide_slow){
            $fil .= ' AND (`ti`.`adsl_speed` IS NULL or substr(`ti`.`adsl_speed` from instr(`ti`.`adsl_speed`,"/")+1) not in ("128","256"))';
        }
        if($unlim){
            // только безлимитные
            // downstream(вторая часть скорости, после /) у mgts не равна той, которая у adsl_speed
            $fil .= " AND IF(`ti`.`id` is null,1,`ti`.`name` like '%безлимитный%'
                    AND substring(`uip`.`speed_mgts` from instr(`uip`.`speed_mgts`,'/')+1 for instr(substr(`uip`.`speed_mgts` from instr(`uip`.`speed_mgts`,'/')+1),'.')-1) <> substring(`ti`.`adsl_speed` from instr(`ti`.`adsl_speed`,'/')+1))";
        }
        if($show_off){
            $fil .= ' AND `uip`.`actual_to` >= FROM_UNIXTIME('.$from.')';
            $fil .= ' AND `uip`.`actual_to` <= FROM_UNIXTIME('.$to.')';
            $order_by_date = '`uip`.`actual_to` asc';
        }else{
            $fil .= ' AND `uip`.`actual_from`>=FROM_UNIXTIME('.$from.')';
            $fil .= ' AND `uip`.`actual_from` <= FROM_UNIXTIME('.$to.')';
            $order_by_date = '`uip`.`actual_from` asc';
        }

        // <editor-fold defaultstate="collapsed" desc="query">
        $query = "
            SELECT
                `uip`.`speed_mgts`,
                `uip`.`id`,
                `uip`.`client`,
                `cr`.`manager`,
                `cl`.`id` AS `clientid`,
                `tp`.`port_type`,
                `tp`.`port_name` `port`,
                `tp`.`node`,
                `uip`.`actual_from`,
                `uip`.`actual_to`,
                IF((`uip`.`actual_from`<=now()) AND (`uip`.`actual_to`>now()),1,0) `actual`,
                `ti`.`adsl_speed`,
                `ti`.`name`,
                `ti`.`mb_month`,
                `ti`.`pay_month`,
                `ti`.`pay_mb`,
                `lt`.`comment`
            FROM
                `usage_ip_ports` `uip`
            LEFT JOIN
                `tech_ports` `tp`
            ON
                `tp`.`id` = `uip`.`port_id`
            INNER JOIN
                `clients` `cl`
            ON
                `cl`.`client` = `uip`.`client`
            AND
                `cl`.`status` not in ('deny','tech_deny')
            LEFT JOIN client_contract cr ON cr.id = cl.contract_id
            LEFT JOIN
                `log_tarif` `lt`
            ON
                `lt`.`service`='usage_ip_ports'
            AND
                `lt`.`id_service` = `uip`.`id`
            LEFT JOIN
                `tarifs_internet` `ti`
            ON
                `ti`.`id` = `lt`.`id_tarif`
            WHERE
                `uip`.`client` <> ''
            AND
                `cl`.`status` not in ('deny','tech_deny')".$fil."
            AND
                (
                    `lt`.`id` IS NULL
                OR
                    `lt`.`id` = (
                        SELECT
                            `id`
                        FROM
                            `log_tarif`
                        WHERE
                            `service` = 'usage_ip_ports'
                        AND
                            (`date_activation` <= NOW())
                        AND
                            `id_service` = `uip`.`id`
                        ORDER BY
                            `date_activation` desc,
                            `ts` desc,
                            `id` desc
                        LIMIT 1
                    )
                )
            AND
                (`ti`.`id` is null or `ti`.`type` <> 'C')
            GROUP BY
                `uip`.`id`
            ORDER BY
                ".$order_by_date.",
                `cl`.`client`
        ";
        // </editor-fold>

        $db->Query($query);
        $ret = array();
        $cnt = 1;
        while($row=$db->NextRecord(MYSQL_ASSOC)){
            $ret[] = array(
                'speed_mgts'=>str_replace('.00','',$row['speed_mgts']),
                'id'=>$row['id'],
                'client'=>$row['client'],
                'manager'=>$row['manager'],
                'actual_from'=>$row['actual_from'],
                'actual_to'=>$row['actual_to'],
                'actual'=>$row['actual'],
                'port_type'=>$row['port_type'],
                'port'=>$row['port'],
                'node'=>$row['node'],
                'ord'=>$cnt++,
                'tarif'=>array(
                    'name'=>$row['name'],
                    'adsl_speed'=>$row['adsl_speed'],
                    'mb_month'=>$row['mb_month'],
                    'pay_month'=>$row['pay_month'],
                    'pay_mb'=>$row['pay_mb'],
                    'comment'=>$row['comment']
                )
            );
        }
        $nPorts =& $ret;
        /*$wh = '';
        if($port_type)
            $wh.=' AND (tech_ports.port_type="'.$port_type.'")';
        if($manager)
            $wh.=' AND (clients.manager="'.$manager.'")';
        if($hide_off)
            $wh.=' AND (usage_ip_ports.actual_to>=FROM_UNIXTIME('.strtotime("+1 day", $to).'))';
        //if ($unlim) $wh.=" AND ()"

        $ports=$this->get_ports(
            '',
            0,
            '    (usage_ip_ports.actual_from>=FROM_UNIXTIME('.$from.'))
            AND
                usage_ip_ports.client!=""
            AND
                (usage_ip_ports.actual_from<=FROM_UNIXTIME('.$to.'))'.$wh,
                ',clients.manager',
            'INNER JOIN
                clients
            ON
                clients.client=usage_ip_ports.client
            AND
                clients.status
            NOT IN ("deny","tech_deny") ',
            'usage_ip_ports.actual_from ASC,
            clients.client
        ');
        $nPorts = array();
        $ord = 1; 

        //printdbg($ports);
        foreach ($ports as $p){
            $toAdd = true;
            if ($hide_slow && isset($p["tarif"]["adsl_speed"])){
                $pos = strpos($p["tarif"]["adsl_speed"],"/")+1;
                if (substr($p["tarif"]["adsl_speed"],$pos) == "128" ||
                        substr($p["tarif"]["adsl_speed"],$pos) == "256"){
                    $toAdd = false;
                }
            }

            if($unlim && $toAdd && isset($p["tarif"]))
            {
                 if(!preg_match("/Безлимитный/i", $p["tarif"]["name"]))
                 {
                     $toAdd = false;
                 }
            }

            if($unlim && isset($p["tarif"])){ // не показываем, если downsteram равен
                $adsl_speed = $p['tarif']['adsl_speed'];
                $mgts_speed = $p['speed_mgts'];
                $as = explode('/',$adsl_speed);
                $ms = explode('/',$mgts_speed);
                if(count($as)>1 && count($ms)>1 && $as[1]==$ms[1])
                    $toAdd = false;
            }

            if($toAdd){
                $p['ord'] = $ord++;

                $p["speed_mgts"] = str_replace(".00", "", $p["speed_mgts"]);
                $nPorts[] = $p;
            }
        }*/
        $design->assign_by_ref('ports',$nPorts);
        $design->assign('port_types',array('dedicated','pppoe','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota', 'GPON'));
        $design->AddMain('services/internet_report.tpl');
    }

    function services_in_view($fixclient){
        global $design,$db,$user;
        if (!$this->fetch_client($fixclient))
        {
            $R=array();
            $db->Query('select router from tech_routers');
            while($r=$db->NextRecord())
                $R[]=$r['router'];

            $design->assign('serv_routers',$R);
            $design->AddMain('services/internet_select.tpl');            
            return $R;
        } else {
            $connections=array();
            $ports=$this->get_ports($fixclient,0);
            foreach ($ports as $id_port=>$port) {
                $connections[$id_port]['nets']=$this->get_nets($id_port);
                $connections[$id_port]['route']='';
                foreach ($connections[$id_port]['nets'] as $net){
                    if ($net['actual']) $connections[$id_port]['route']=$net['net'];
                }
                $connections[$id_port]['data']=$port;
            };
            $design->assign('services_conn',$connections);
            $now=date("Y-m-d");
            $design->assign("now",$now);
            $design->assign('internet_suffix','internet');
            $design->AddMain('services/internet.tpl',1);
            return $connections;
        }
    }
    function services_in_view_ind($fixclient){
        global $design;
        /*
        $connections=array();
        $ports=$this->get_ports($fixclient,0,'(usage_ip_ports.node REGEXP "^[0-9]{7}") and (usage_ip_ports.port="mgts")');
        foreach ($ports as $id_port=>$port) if ($port['actual2']){
            $connections[$id_port]['nets']=$this->get_nets($id_port,'(actual_to>NOW())');
            $connections[$id_port]['data']=$port;
        };
        
        $design->assign('services_conn',$connections);
        $design->assign('show_client',1);
        $design->assign('internet_suffix','internet');
        $design->AddMain('services/internet_tiny.tpl'); */
        trigger_error2('TODO9 - временно отключено');
    }
    function services_in_view_routed($fixclient){
/*        global $design;
        $router=get_param_protected('router','');
        if (!$router) {trigger_error2('Не выбран роутер'); return;}
        $connections=array();
        $ports=$this->get_ports($fixclient,0,'(usage_ip_ports.node="'.$router.'") and (usage_ip_ports.port!="mgts")');
        foreach ($ports as $id_port=>$port) if ($port['actual2']){
            $connections[$id_port]['nets']=$this->get_nets($id_port,'(actual_to>NOW())');
            $connections[$id_port]['data']=$port;
        };
        
        $design->assign('services_conn',$connections);
        $design->assign('show_client',1);
        $design->assign('internet_suffix','internet');
        $design->AddMain('services/internet_tiny.tpl'); */
        trigger_error2('TODO9 - временно отключено');
    }

    function services_in_add($fixclient,$suffix="internet",$tarif_type=""){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }

        $r=$fixclient = ClientAccount::findOne($fixclient);
        $dbf = new DbFormUsageIpPorts();
        $dbf->SetDefault('client',$r->client);
        $dbf->SetDefault('address',$r['address_connect']);

        $dbf->Display(
            array(
                'module'=>'services',
                'action'=>'in_apply'
            ),
            'Услуги',
            'Новое подключение'
        );
    }
    function services_in_apply($fixclient,$suffix='internet',$suffix2='in'){
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageIpPorts();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action='.$suffix2.'_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'in_apply'),'Услуги','Редактировать подключение');
    }
    function services_in_add2($fixclient,$id='',$suffix="internet"){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        if(!$id)
            $id=get_param_integer('id','');
        if(!$id)
            return;
        $db->Query('SELECT usage_ip_ports.* FROM usage_ip_ports WHERE (usage_ip_ports.id='.$id.')');
        if(!($r=$db->NextRecord()))
            return;
        $dbf = new DbFormUsageIpRoutes();
        $dbf->SetDefault('port_id',$r['id']);
        $dbf->Display(
            array(
                'module'=>'services',
                'action'=>'in_apply2'
            ),
            'Услуги',
            'Новая сеть'
        );
    }

    function services_in_apply2($fixclient,$suffix='internet',$suffix2='in'){
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageIpRoutes();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=in_view');
            exit;
        } else {
            $dbf->Display(array('module'=>'services','action'=>'in_apply2'),'Услуги','Редактировать сеть');
        }
    }
    
    function services_in_act($fixclient,$suffix="internet"){
        global $design,$db;
        $id=get_param_protected('id');
        if ($id=='') return;

        $conn=$db->GetRow("select * from usage_ip_ports where id='".$id."'");
        $routes=array(); $db->Query('select * from usage_ip_routes where (port_id="'.$id.'") and (actual_from<=NOW()) and (actual_to>=NOW()) order by id');
        while ($r=$db->NextRecord()) {
            $t=explode("/",$r['net']);
            $t=explode('.',$t[0]);
            $t[3]++;
            $r['gate']=implode(".",$t);
            $routes[]=$r;    
        }
        //if (!$routes || !$conn) {trigger_error2('Сеть и подключения не найдены'); return; }
        //$conn['actual_from']=convert_date($conn['actual_from']);
        $design->assign('conn',$conn);
        $design->assign('port',$db->GetRow("select * from tech_ports where id='".$conn['port_id']."'"));
        $design->assign('routes',$routes);
        $design->assign('cpe',get_cpe_history("usage_ip_ports",$id));
        ClientCS::Fetch($conn['client']);

        //** Выпилить */
        //Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $client = $design->get_template_vars('client');
        $account = ClientAccount::findOne(["id" => $client["id"]]);
        $organization = Organization::find()->byId($account->contract->organization_id)->actual()->one();

        $design->assign('firma', $organization->getOldModeInfo());
        $design->assign('firm_director', $organization->director->getOldModeInfo());
        //** /Выпилить */

        $design->assign('ppp',$db->AllRecords('select * FROM usage_ip_ppp where client="'.$conn['client'].'"'));
        $design->assign('internet_suffix',$suffix);
        $sendmail = get_param_raw('sendmail',0);
        if($sendmail){
            $msg = $design->fetch('../store/acts/'.$suffix.'_act.tpl');
            $query = 'select group_concat(`cc`.`data`) `mails` from `clients` `cl` left join `client_contacts` `cc` on `cc`.`client_id`=`cl`.`id` and `cc`.`type`="email" and `cc`.`is_active`=1 where `cl`.`id`='.$fixclient;
            $db->Query($query);
            $mails = $db->NextRecord(MYSQL_ASSOC);
            $mails = $mails['mails'];
            $design->clear_all_assign();
            $design->assign('content_encode','base64');
            $design->assign('emails',$mails);
            $design->assign('subject','Акт');
            $design->assign('message',base64_encode(str_replace('&nbsp;',' ',$msg)));
            echo $design->fetch('wellsend.html');
            exit();
        }
        $design->ProcessEx('../store/acts/'.$suffix.'_act.tpl');
    }

    function services_in_act_pon($fixclient,$suffix="internet"){
        global $design,$db;
        $id=get_param_protected('id');
        if ($id=='') return;

        $conn=$db->GetRow("select * from usage_ip_ports where id='".$id."'");
        $routes=array(); $db->Query('select * from usage_ip_routes where (port_id="'.$id.'") and (actual_from<=NOW()) and (actual_to>=NOW()) order by id');
        while ($r=$db->NextRecord()) {
            $t=explode("/",$r['net']);
            $t=explode('.',$t[0]);
            $t[3]++;
            $r['gate']=implode(".",$t);
            $routes[]=$r;
        }
        //if (!$routes || !$conn) {trigger_error2('Сеть и подключения не найдены'); return; }
        //$conn['actual_from']=convert_date($conn['actual_from']);
        $design->assign('conn',$conn);
        $design->assign('port',$db->GetRow("select * from tech_ports where id='".$conn['port_id']."'"));
        $design->assign('routes',$routes);
        $design->assign('cpe',get_cpe_history("usage_ip_ports",$id));
        ClientCS::Fetch($conn['client']);

        //** Выпилить */
        //Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $client = $design->get_template_vars('client');
        $account = ClientAccount::findOne(["id" => $client["id"]]);
        $organization = Organization::find()->byId($account->contract->organization_id)->actual()->one();

        $design->assign('firma', $organization->getOldModeInfo());
        $design->assign('firm_director', $organization->director->getOldModeInfo());
        //** /Выпилить */

        $design->assign('ppp',$db->AllRecords('select * FROM usage_ip_ppp where client="'.$conn['client'].'"'));
        $design->assign('internet_suffix',$suffix);
        $sendmail = get_param_raw('sendmail',0);
        if($sendmail){
            $msg = $design->fetch('../store/acts/'.$suffix.'_act_pon.tpl');
            $query = 'select group_concat(`cc`.`data`) `mails` from `clients` `cl` left join `client_contacts` `cc` on `cc`.`client_id`=`cl`.`id` and `cc`.`type`="email" and `cc`.`is_active`=1 where `cl`.`id`='.$fixclient;
            $db->Query($query);
            $mails = $db->NextRecord(MYSQL_ASSOC);
            $mails = $mails['mails'];
            $design->clear_all_assign();
            $design->assign('content_encode','base64');
            $design->assign('emails',$mails);
            $design->assign('subject','Акт');
            $design->assign('message',base64_encode(str_replace('&nbsp;',' ',$msg)));
            echo $design->fetch('wellsend.html');
            exit();
        }
        $design->ProcessEx('../store/acts/'.$suffix.'_act_pon.tpl');
    }
    
    function services_in_close($fixclient,$suffix='internet'){
        global $design,$db;

        trigger_error2("<h1><b>Вы сделали недопустимое действие! За вами выехали!</b></h1>");
        mail("dga@mcn.ru","error in_close","error in_close\n look logs");

        if(false)
        {
            if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
            $id=get_param_integer('id','');
            if (!$id) return;
            $db->Query('select usage_ip_ports.*,tech_ports.port_name from usage_ip_ports LEFT JOIN tech_ports on tech_ports.id=usage_ip_ports.port_id where usage_ip_ports.id='.$id);
            if (!$r=$db->NextRecord()) return;

            $block=0;
            $db->Query('update usage_ip_routes set actual_to=NOW() where (port_id='.$id.') and (actual_to>NOW())');
            $db->Query('update usage_ip_ports set actual_to=NOW() where (id='.$id.') and (actual_to>NOW())');

            $this->routes_check($r['client']);
        }
    
        if ($suffix=='internet') {
            $this->services_in_view($fixclient);
        } else {
            $this->services_co_view($fixclient);
        }
    }
    
// =========================================================================================================================================    
    function services_co_view($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)){
            $connections=array();
            $ports=$this->get_ports($fixclient,1,'1');
            foreach ($ports as $id_port=>$port) {
                $connections[$id_port]['nets']=$this->get_nets($id_port,'(usage_ip_routes.actual_to>NOW())');
                $connections[$id_port]['data']=$port;
            };
            
            $design->assign('services_conn',$connections);
            $design->assign('show_client',1);
            $design->assign('internet_suffix','collocation');
            $design->AddMain('services/internet_tiny.tpl',1); 
        } else {
            $connections=array();
            $ports=$this->get_ports($fixclient,1);
            foreach ($ports as $id_port=>$port) {
                $connections[$id_port]['nets']=$this->get_nets($id_port);
                $connections[$id_port]['data']=$port;
            };
            
            $design->assign('services_conn',$connections);
            $design->assign('internet_suffix','collocation');
            $design->AddMain('services/internet.tpl'); 
        }
        return $connections;
    }
    
    function services_co_add($fixclient){
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $this->services_in_add($fixclient,'collocation','C');
    }
    function services_co_add2($fixclient,$id=''){
        $this->services_in_add2($fixclient,$id,'collocation');
    }
    
    function services_co_act($fixclient){
        $this->services_in_act($fixclient,'collocation');
    }    
    
    function services_co_close($fixclient){
        $this->services_in_close($fixclient,'collocation');
    }
    
    function services_co_apply($fixclient){
        $this->services_in_apply($fixclient,'collocation','co');
    }

    function services_co_apply2($fixclient){
        $this->services_in_apply2($fixclient,'collocation','co');
    }

// =========================================================================================================================================
    function services_vo_view($fixclient){
        global $db,$design;

        $so = get_param_integer ('so', 1);
        $order = $so ? 'asc' : 'desc';
        switch ($sort=get_param_integer('sort',3)){
            case 3: $order='E164 '.$order; break;
            case 4: $order='no_of_lines '.$order; break;
            case 6: $order='client '.$order; break;
            default: $order='actual_from '.$order.',actual_to '.$order; break;
        }
        $design->assign('sort',$sort);
        $design->assign('so',$so);

        $has_trunk = false;
        if (!$this->fetch_client($fixclient)) {
            $region = get_param_protected("letter_region");
            $phone=get_param_protected('phone','');

            $where = array();

            if($region && $region != "any")
                $where[] = "region = '".$region."'";

            if($phone)
                $where[] = "INSTR(E164,'".$phone."')";

            $db->Query("
                    select
                        usage_voip.*, c.id AS clientId,
                        IF((CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to),1,0) as actual
                    from
                        usage_voip
                        INNER JOIN clients c ON c.client = usage_voip.client
                        ".($where ? "where (".implode(") and (", $where).")" : "")."
                    order by
                        actual desc,
                        ".$order
            );

            $R=array();
            while ($r=$db->NextRecord()) {
              $R[]=$r;
              if ($r['is_trunk'] == '1')
                  $has_trunk = true;
            }

            $design->assign('phone',$phone);
            $design->assign('voip_conn',$R);
            $design->assign('has_trunk',$has_trunk);
            $design->AddMain('services/voip_all.tpl'); 
            return $R;
        } else {

            global $db_ats;

            $clientNick = ClientAccount::findOne($fixclient)->client;
            $isDbAtsInited = $db_ats && $db != $db_ats;

            $db->Query($q='
                select
                    usage_voip.*,
                    IF((CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to),1,0) as actual,
                    IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
                from
                    usage_voip
                where
                    client="'.$clientNick.'"
                order by
                    actual
                desc,
                '.$order
            );

            $R=array(); 
            $actualNumbers = array();
            while ($r=$db->NextRecord()) {
                $R[]=$r; 
                if ($r['is_trunk'] == '1') 
                    $has_trunk = true;

                if ( $r["actual"] )
                    $actualNumbers[] = $r["E164"];
            }

            $numberTypes = count($R) > 0 ? VirtPbx3::getNumberTypes($this->fetched_client["id"]) : [];

            try{
                foreach($this->getInOldSchema($actualNumbers) as $number) {
                    $numberTypes[$number] = 'old';
                }
            } catch(Exception $e) {
                trigger_error2($e->getMessage());
            }

            foreach ($R as &$r) {
                /** @var UsageVoip $usage */
                $usage = UsageVoip::findOne($r['id']);

                if (!($r['tarif'] = $usage->tariff)) {
                    $r['logTarif'] = $usage->getLogTariff($usage->actual_from);
                    $r['tarif'] = TariffVoip::findOne($r['logTarif']->id_tarif);
                }
                else {
                    $r['logTarif'] = $usage->logTariff;
                }
                $r['tarifs'] = [];
                if (
                    $r['logTarif']->id_tarif_local_mob &&
                    ($tarifLocalMob = TariffVoip::findOne($r['logTarif']->id_tarif_local_mob)) instanceof TariffVoip
                ) {
                    $r['tarifs']['local_mob'] = $tarifLocalMob;
                }
                if (
                    $r['logTarif']->id_tarif_russia_mob &&
                    ($tarifRussiaMob = TariffVoip::findOne($r['logTarif']->id_tarif_russia_mob)) instanceof TariffVoip
                ) {
                    $r['tarifs']['russia_mob'] = $tarifRussiaMob;
                }
                if (
                    $r['logTarif']->id_tarif_russia &&
                    ($tarifRussia = TariffVoip::findOne($r['logTarif']->id_tarif_russia)) instanceof TariffVoip
                ) {
                    $r['tarifs']['russia'] = $tarifRussia;
                }
                if (
                    $r['logTarif']->id_tarif_intern
                    && ($tarifIntern = TariffVoip::findOne($r['logTarif']->id_tarif_intern)) instanceof TariffVoip
                ) {
                    $r['tarifs']['intern'] = $tarifIntern;
                }

                $r['cpe'] = get_cpe_history('usage_voip',$r['id']);
                $r["vpbx"] = isset($numberTypes[$r["E164"]]) ? $numberTypes[$r["E164"]] : false;

                $r['number_status'] = Number::$statusList[$usage->voipNumber->status];

                foreach ($usage->usagePackages as $package) {
                    list ($description) = $package->helper->description;
                    $r['packages'][] = [
                        'description' => $description,
                        'tariff' => $package->tariff,
                        'stat' => $package->stat,
                    ];
                }
            }

            $numbers =
                Number::find()
                    ->leftJoin('`usage_voip` uv', 'uv.`E164` = ' . Number::tableName() . '.`number`')
                    ->where([
                        Number::tableName() . '.`client_id`' => $this->fetched_client['id'],
                        'uv.`E164`' => null,
                    ])
                    ->all();

            $notAcos = array();
            foreach($db->AllRecords("select * from voip_permit where client = '$clientNick'") as $p) {
                foreach($R as &$r)
                {
                    if($r["E164"] == $p["callerid"]){
                        $r["permit"] = $p["permit"];
                        $r["cl"] = $p["cl"];
                        $r["enable"] = $p["enable"];
                        continue 2;
                    }
                }
                $notAcos[] = $p;
            }

            $design->assign('voip_conn',$R);
            $design->assign('has_trunk',$has_trunk);
            $design->assign('voip_conn_permit',$notAcos);
            $design->assign('numbers', $numbers);
            $design->assign('is_vo_view', get_param_raw("action", "") == "vo_view");
            $design->assign('regions', $db->AllRecords('select * from regions order by if(id = 99, "zzz", name)','id') );
            $design->assign('cur_region', $db->GetValue('select region from clients where id="'.$fixclient.'"') );
            $design->AddMain('services/voip.tpl'); 

            if(get_param_raw("action", "") == "vo_view")
                $this->services_vo_permit($fixclient);

            return $R;
        }
    }

    function services_trunk_view($fixclient){
        global $db,$design;

        $client = ClientAccount::findOne($fixclient);
        if ($client) {
            global $db_ats;

            $isDbAtsInited = $db_ats && $db != $db_ats;

            $now = (new DateTime('now', $client->timezone))->format('Y-m-d');

            $db->Query($q="
                select
                    u.*,
                    IF((u.actual_from<='$now') and (u.actual_to>'$now'),1,0) as actual,
                    IF((u.actual_from<=('$now'+INTERVAL 5 DAY)),1,0) as actual5d
                from
                    usage_trunk u
                where
                    u.client_account_id={$client->id}
                order by actual desc, u.actual_from, u.actual_to
            ");

            $R=array();
            while ($r=$db->NextRecord()) {
                $R[]=$r;
            }

            $design->assign('items',$R);
            $design->assign('regions', $db->AllRecords('select * from regions order by if(id = 99, "zzz", name)','id') );
	    try {
                $design->assign('bill_trunks', \app\models\billing\Trunk::dao()->getListAll());
	    } catch (Exception $e) {
		trigger_error2($e->getMessage());
	    }
            $design->AddMain('services/trunk.tpl');

            return $R;
        }
    }

    function services_vo_permit($fixclient)
    {
        global $design, $db;

        if(!access("services_voip", "view_reg")) return;
        if(!function_exists("pg_connect")) return;

        $c = ClientAccount::findOne((is_numeric($fixclient)) ? $fixclient : ['client' => $fixclient]);
        if(!$c) return ;

        $phone = get_param_protected("phone", "");

        $nrs = array();
        $nns = array();
        foreach($db->AllRecords("select distinct region, E164 as number from usage_voip where client = '".$c["client"]."'") as $n)
        {
            $nrs[$n["region"]][] = $n["number"];
            $nns[$n["number"]] = $n["region"];
        }


        $regs = array();
        if($phone && isset($nns[$phone]))
        {
            $regs = $this->getSIPregs($c, $nns[$phone], $phone);

            if ($nns[$phone] == 99)
            {
                $reg = $this->getSIPregs($c, 991, $phone);
                $regs = array_merge($regs, $reg);
            }
        }else{
            foreach($nrs as $region => $ns)
            {
                $reg = $this->getSIPregs($c, $region, $phone);
                $regs = array_merge($regs, $reg);

                if ($region == 99)
                {
                    $reg = $this->getSIPregs($c, 991, $phone);
                    $regs = array_merge($regs, $reg);
                }
            }
        }

        $design->assign('voip_permit',$regs);
        $design->assign('voip_permit_filtred',$phone);
        $design->AddMain('services/voip_permit.tpl'); 
    }

    private function getInOldSchema($numbers)
    {
        //albis
        $resultNumbers = array();

        $conn = @pg_connect("host=".R_CALLS_99_HOST." dbname=".R_CALLS_99_DB." user=".R_CALLS_99_USER." password=".R_CALLS_99_PASS." connect_timeout=1");

        if (!$conn) 
        {
            //mail(ADMIN_EMAIL, "[pg connect]", "services/getInOldSchema");
            throw new Exception("Connection error (PG HOST: ".R_CALLS_99_HOST.")..");
        }

        $res = @pg_query("SELECT exten FROM extensions WHERE exten in ('".implode("', '", $numbers)."') AND enabled = 't'");

        if (!$res) 
            throw new Exception("Query error (PG HOST: ".R_CALLS_99_HOST.")");

        while($l = pg_fetch_assoc($res))
        {
            $number = $l["exten"];
            $resultNumbers[] = $number;
        } 

        return $resultNumbers;
    }

    private function getSIPregs($cl, $region, $phone = "")
    {
        $schema = "";
        $needRegion = false;


        if($region == 99)
        {
            $conn = @pg_connect("host=".R_CALLS_99_HOST." dbname=".R_CALLS_99_DB." user=".R_CALLS_99_USER." password=".R_CALLS_99_PASS." connect_timeout=1");

            if (!$conn)
            {
                mail(ADMIN_EMAIL, "[pg connect]", "services/getSIPregs");
            }
        }else{

            $dbname = "voipdb";

            $dbHost = str_replace("[region]", $region, R_CALLS_HOST);
        
            if(in_array($region, array(94, 95, 96, 87, 97, 98, 88, 89, 93, 991))) // new schema. scynced
            {
                $schema = "astschema";
                $dbHost = "eridanus.mcn.ru";
                $needRegion = true;
            }

            $conn = @pg_connect($q="host=".$dbHost." dbname=".$dbname." user=".R_CALLS_USER." password=".R_CALLS_PASS." connect_timeout=1");

            if (!$conn)
            {
                mail(ADMIN_EMAIL, "[pg connect]", "services/getSIPregs_2");
            }

            if($conn && $schema)
                pg_query("SET search_path TO ".$schema.", \"\$user\", public");
        }

        if (!$conn)
        {
            $reg = Region::first(array("id" => $region));
            $regionInfo = $reg ? '"' . $reg->name . '" (id: ' . $reg->id . ')' : '';
            trigger_error2("Ошибка соединения с сервером регистрации SIP в регионе " . $regionInfo);
            return array();    
        }


        if(!$conn) return;

        if($region != "99")
        {
            if ($region == 991)
            {
                $region = 99;
            }

            $result = pg_query($q = "
                    SELECT 
                    a.id, a.callerid, a.name, 
                    a.secret, a.context, a.host, 
                    a.permit, a.deny, 'reg".$region."' as ippbx,
                    to_char(a.registered, 'DD-MM-YYYY HH24:MI:SS') as registered, 
                    a.insecure, a.enabled, b.fullcontact, 
                    b.ipaddr, b.port, 
                    /*TIMESTAMP 'epoch' + b.regseconds::integer * INTERVAL '1 second' as registered, */
                    b.regseconds::integer::abstime::timestamp as registered, 
                    b.regseconds::integer - extract(epoch from now())::integer as regtime, 
                    extract(epoch from (b.regseconds::integer::abstime::timestamp - abstime(now()))) as regtime, 
                    b.useragent, n.ds as direction, a.autolink_ip, b.invite_ip, b.invite_contact
                    FROM 
                    sipdevices a 
                    INNER JOIN sipregs b ON a.name = b.name
                    LEFT JOIN numbers n ON n.number::varchar = a.callerid
                    WHERE 
                    a.client_id='".$cl["id"]."' ".($needRegion ? "and a.region ='".$region."'" : "")."
                    ORDER BY 
                    callerid, name");
        }else{
            $result = pg_query($q ="
                    SELECT 
                    ast_ds, client, callerid, 
                    name, ipaddr, permit, 
                    deny, ast_ds as direction, ".
                    ($region == 99 ? "":"'reg".$region."' as ")." ippbx, 
                    enabled, 
                    to_char(registered, 'DD-MM-YYYY HH24:MI:SS') as registered, 
                    round((EXTRACT (EPOCH FROM regseconds)-EXTRACT (EPOCH FROM registered)+1)) as regtime, 
                    useragent, fullcontact, 
                    current_timestamp<=regseconds as regvalid, 
                    secret 
                    FROM 
                    sip_users 
                    WHERE 
                    ".($phone ? "callerid='".$phone."' or callerid='74959505680*".$phone."'" : "client='".$cl["client"]."'")." 
                    ORDER BY 
                    name, callerid");
        }


        $dirs = array("my-full-out" => "Всё",
                "my-russia-out" => "Россия",
                "my-mskmob-out" => "МоскваМобил",
                "my-msk-out" => "Москва",

                "my-local-out" => "Местные стационарные",
                "my-local-mobile-out" => "Местный мобильные",
                "my-full-russia-out" => "Россия"
                );

        $pbx = array(
                "ast244" => "85.94.32.244",
                "ast245" => "85.94.32.245",
                "ast248" => "85.94.32.248",
                "reg99" => "sip.mcn.ru",
                "reg96" => "37.228.82.6",
                "reg97" => "37.228.80.6",
                "reg98" => "37.228.81.6",
                "reg95" => "37.228.85.6",
                "reg94" => "37.228.83.6",
                "reg93" => "37.228.84.6",
                "reg87" => "37.228.86.6",
                "reg88" => "37.228.87.6",
                "reg89" => "176.227.177.6" // Заявка 178491: Нужно добавить Владивосток в "просмотр регистраций". PBX = 176.227.177.6
                );


        $regs = array();
        while($l = pg_fetch_assoc($result))
        {
            if(!isset($l["regvalid"])) $l["regvalid"] = $l["regtime"] > 0;

            if(isset($pbx[$l["ippbx"]])) $l["ippbx"] = $pbx[$l["ippbx"]];

            $permit = preg_replace('/;/', '<br>', $l['permit']);
            if (($permit == '0.0.0.0/0.0.0.0') and ($l['deny'] == '')) {
                $perm = 'любой IP';
            } elseif ($l['deny'] == '0.0.0.0/0.0.0.0') {
                $perm = $permit;
            } elseif (($permit == '') and ($l['deny'] == '')) {
                if (isset($l["autolink_ip"]))
                {
                    if ($l["autolink_ip"] == "t")
                    {
                        $perm = 'Автопривязка: еще не привязан';
                    } else {
                        $perm = "Любой IP";
                    }
                } else {
                $perm = 'Автопривязка: еще не привязан';
                }
            } else {
                $perm = 'разрешено: '.$permit.'<br>запрещено: '.$l['deny'];
            }
            $l["permit"] = $perm;

            $l["direction"] = isset($dirs[$l["direction"]]) ? $dirs[$l["direction"]] : $l["direction"];

            if (!isset($l["invite_contact"]))
            {
                $l["invite_contact"] = $l["invite_ip"] = "";
            } else {
                $l["invite_contact"] = str_replace(array("<", ">"), "", $l["invite_contact"]);
            }

            $regs[] = $l;
        }

        pg_close($conn);
        return $regs;
    }


    function services_vo_delete($fixclient)
    {
        global $db, $user;

        $clientNick = ClientAccount::findOne($fixclient)->client;

        $id = get_param_protected("id", 0);

        $sendError = false;

        if($id)
        {
            $u=$db->GetValue($q = "select id from usage_voip where id = '".$id."' and client='$clientNick'");
            if($u)
            {
                $db->Query("delete from usage_voip where id = '".$id."' and client='$clientNick'");
            }else{
                trigger_error2("unknown error");
            }
        }

        header("Location: /client/view?id=".$fixclient);
        exit();
    }


    function services_vo_settings_send($fixclient)
    {
        global $design, $db, $db_ats, $user;
        $clientNick = ClientAccount::findOne($fixclient)->client;

        $isSent = false;
        $error = false;
        $emails = array();

        $design->assign("log", $db->AllRecords("select * from log_send_voip_settings where client='".$clientNick."' order by id desc limit 30"));
        $e164s = array();

        //$e164s = voipRegion::getClientE164s($c["client"]);

        foreach($db_ats->AllRecords("select number from a_number where client_id = $fixclient and enabled='yes'") as $l)
        {
            $e164s[$l["number"]] = $l["number"];
        }

        foreach($db->AllRecords("SELECT data as email 
                    FROM `client_contacts` cc, clients c 
                    where c.id = $fixclient and client_id = c.id and cc.type = 'email'
                    and cc.is_active 
                    order by data") as $l)
            $emails[$l["email"]] = $l["email"];

        if(get_param_raw("do_send", "") !== "")
        {
            $isSent = true;
            $_e164s = get_param_raw("e164", array());
            $_emails = get_param_raw("email", array());

            try{
                if(!$_e164s || !$_emails)
                    throw new Exception("Не выбраны номера или email'ы");

                foreach($_e164s as $e)
                    if(!isset($e164s[$e]))
                        throw new Exception("Не выбраны номера или email'ы");

                foreach($_emails as $_email)
                    if(!isset($emails[$_email]))
                        throw new Exception("Не выбраны номера или email'ы");

                $msg = voipRegion::getEmailMsg($fixclient, $_e164s);

                if(!$msg)
                {
                    voipRegion::getClientE164s(['id' => $fixclient, 'client' => $clientNick]); //для заполнения массива номер=>регион (voipRegion::$e164Region)
                    $msg = voipRegion::_getEmailMsg(['id' => $fixclient], $_e164s);
                }

                if(!$msg)
                    throw new Exception("Информация не найдена!");

            }catch(Exception $e)
            {
                $error = $e->getMessage();
            }

            if(!$error)
            foreach($_emails as $_email)
            {
                $db->QueryInsert("log_send_voip_settings", array(
                            "client" => $clientNick,
                            "date" => array("NOW()"),
                            "user" => $user->Get("user"),
                            "email" => $_email,
                            "phones" => implode(", ", $_e164s)
                            )
                        );

                mail(trim($_email), "[MCN] Настройки SIP", $msg, "From: \"Support MCN\" <noreply@mcn.ru>\nContent-Type: text/plain; charset=utf-8");
                //mail("adima123@yandex.ru", "[MCN] Настройки SIP - ".$_email, $msg, "From: \"Support MCN\" <noreply@mcn.ru>\nContent-Type: text/plain; charset=utf-8");
            }
        }

        $design->assign("e164s", $e164s);
        $design->assign("emails", $emails);
        $design->assign("isSent", $isSent);
        $design->assign("error", $error);
        $design->AddMain("services/voip_settings_send.tpl");
    }

    function services_vo_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}

        $account = ClientAccount::findOne($fixclient);
        $clientNick = $account->client;

        $rr = ["7499"];
        foreach($db->AllRecords("select code from regions order by length(code)") as $r)
        {
            $rr[] = $r["code"];
        }

        $db->Query('select * from usage_voip where (client="'.$clientNick.'") and /*(actual_from<=NOW()) and*/ (actual_to>NOW()) order by actual_from, E164');
        $R=array(); 
        while ($r=$db->NextRecord()) 
        {
            $isFind = false;
            foreach($rr as $l)
            {
                if(strpos($r["E164"], $l) === 0)
                {
                    $r["E164_first"] = $l;
                    $r["E164_last"] = substr($r["E164"], strlen($l));

                    $isFind = true;
                }
            }

            if (!$isFind) {
                $r['E164_first']="";
                $r['E164_last']=$r['E164'];
            }
            $R[]=$r;
        }
        $design->assign('voip_connections',$R);
                
        $db->Query('
            SELECT utc.*, uip.address
            FROM
                `usage_tech_cpe` utc
                    LEFT JOIN `usage_ip_ports` uip ON uip.`id` = utc.`id_service` AND utc.service = "usage_ip_ports"
            WHERE
                utc.client="' . $clientNick . '"
                AND utc.actual_from <= NOW()
                AND utc.actual_to > NOW()
        ');
        $R=array(); while ($r=$db->NextRecord()) $R[]=$r;
        $design->assign('voip_devices',$R);
        ClientCS::Fetch($fixclient);
        ClientCS::FetchMain($fixclient);

        //** Выпилить */
        //Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $client = $design->get_template_vars('client');
        $organization = $account->organization;
        Assert::isObject($organization, 'Организация у ЛС #' . $client['id'] . ' на найдена');

        $design->assign('firma', $organization->getOldModeInfo());
        $design->assign('firm_director', $organization->director->getOldModeInfo());
        //** /Выпилить */

        $sendmail = get_param_raw('sendmail',0);
        if($sendmail){
            $msg = $design->fetch('../store/acts/voip_act.tpl');
            $query = 'select group_concat(`cc`.`data`) `mails` from `clients` `cl` left join `client_contacts` `cc` on `cc`.`client_id`=`cl`.`id` and `cc`.`type`="email" and `cc`.`is_active`=1 where `cl`.`id`='.$fixclient;
            $db->Query($query);
            $mails = $db->NextRecord(MYSQL_ASSOC);
            $mails = $mails['mails'];
            $design->clear_all_assign();
            $design->assign('content_encode','base64');
            $design->assign('emails',$mails);
            $design->assign('subject','Акт');
            $design->assign('message',base64_encode(str_replace('&nbsp;',' ',$msg)));
            echo $design->fetch('wellsend.html');
            exit();
        }
        
        $design->ProcessEx('../store/acts/voip_act.tpl'); 
    }

    function services_vo_act_trunk($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
    
        ClientCS::Fetch($fixclient);
        ClientCS::FetchMain($fixclient);
        //** Выпилить */
        //Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $client = $design->get_template_vars('client');
        $account = ClientAccount::findOne(['id' => $client['id']]);
        $organization = Organization::find()->byId($account->contract->organization_id)->actual()->one();

        $design->assign('firma', $organization->getOldModeInfo());
        $design->assign('firm_director', $organization->director->getOldModeInfo());
        //** /Выпилить */

        $design->ProcessEx('../store/acts/voip_act_trunk.tpl');
    }
    

    function services_in_dev_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        $client=get_param_raw('client','');
        $db->Query('select * from usage_tech_cpe where (client="'.$client.'")
        #and (actual_from<=NOW()) and (actual_to>NOW()) 
        and id='.$id.'');
        $R=array(); while ($r=$db->NextRecord()) $R[]=$r;
        $numbers = $R[0]['numbers'];
        $numArray = array();
        $numArray = explode(",", $numbers);
        $newItemsArray = array();
        foreach($numArray as $item) {
            $item = trim($item);
            $item = preg_replace("/^7/","", $item);
            $item = preg_replace("/^495/", "", $item);
            $item = preg_replace("/^095/", "", $item);
            $data2Array = explode("x", $item);
            if(count($data2Array) == 2) {
                $item = trim($data2Array[0]);
                $count = $data2Array[1];
            } else {
                $count = 1;
            }
            if(preg_match("/([0-9]{3})([0-9]{2})([0-9]{2})/i", $item, $match))
            {
                $item = "(495) ".$match[1]."-".$match[2]."-".$match[3];
            }
            $newItemsArray[] = $item.($count > 1 ? " на ".$count." лииях" : "");    
        }
        $R[0]['numbers'] = '';
        foreach($newItemsArray as $item)
        {
            $R[0]['numbers'] .= "<li>".$item.";</li>";
        }
        $design->assign('voip_devices',$R);
        ClientCS::Fetch($client);
        $design->ProcessEx('../store/acts/dev_act.tpl'); 
    }

    function services_vo_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $account = ClientAccount::findOne($fixclient);
        $region = $account->region;
        $dbf = new DbFormUsageVoip();
        $dbf->SetDefault('client',$account->client);
        if (isset($_GET['region'])){
            $dbf->SetDefault('region',intval($_GET['region']));
        }else{
            $dbf->SetDefault('region',$region);
        }
        $dbf->Display(array('module'=>'services','action'=>'vo_apply'),'Услуги','Новое VoIP-подключение');
    }
    function services_vo_apply($fixclient){
        global $design;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageVoip();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();

        Event::go('ats2_numbers_check');

        if ($result=='delete') {
            header('Location: ?module=services&action=vo_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->nodesign = true;
        $dbf->Display(array('module'=>'services','action'=>'vo_apply'),'Услуги','Редактировать VoIP-подключение');
        $design->AddMain('phone/voip_edit.tpl');
    }
    function services_vo_close($fixclient){
        global $design,$db, $user;

        trigger_error2('Для отключения номера зайдите в редактирование номера');

        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from usage_voip where id='.$id);
        if (!($r=$db->NextRecord())) return;

        $db->Query('update usage_voip set actual_to=NOW(), edit_user_id='.$user->Get("id").' where id='.$id);
        $cl = ClientCS::FetchClient($r["client"]);
        $db->QueryInsert("log_block", array(
                    "service" => "usage_voip",
                    "id_service" => $id, 
                    "id_user" => $user->Get("id"),
                    "ts" => array("now()"),
                    "comment" => "Подключение закрыто"
                    )
                );

        Event::go('ats2_numbers_check');

        trigger_error2('Номер отключен, создайте заявку на отключение');

        $this->services_vo_view($fixclient);
    }

// =========================================================================================================================================
    function services_dn_view($fixclient){
        global $db,$design;
        $this->fetch_client($fixclient);
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $R=array();

        $so = get_param_integer ('so', 1);
        $order = $so ? 'asc' : 'desc';
        switch ($sort=get_param_integer('sort',1)){
            case 2: $order='primary_mx '.$order; break;
            case 3: $order='paid_till '.$order; break;
            case 4: $order='client '.$order; break;
            case 5: $order='actual_from '.$order.',actual_to '.$order; break;
            default: $order='domain '.$order; break;    //=1
        }
        $design->assign('sort',$sort);
        $design->assign('so',$so);

        $db->Query('select domains.*,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual from domains'.($clientNick?' where client="'.$clientNick.'"':'').' ORDER BY IF((actual_from<=NOW()) and (actual_to>NOW()),0,1) ASC,'.$order);
        while ($r=$db->NextRecord()) $R[]=$r;
        $design->assign('domains',$R);
        $design->AddMain('services/domains.tpl'); 
    }
    function services_dn_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormDomains();
        $dbf->SetDefault('client',ClientAccount::findOne(['id' => $fixclient])->client);
        $dbf->Display(array('module'=>'services','action'=>'dn_apply'),'Услуги','Новое доменное имя');
    }
    function services_dn_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormDomains();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=dn_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'dn_apply'),'Услуги','Редактировать доменное имя');
    }
    function services_dn_close($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from domains where id='.$id);
        if (!($r=$db->NextRecord())) return;

        $db->Query('update domains set actual_to=NOW() where id='.$id);
        $this->services_dn_view($fixclient);
    }

// =========================================================================================================================================
    function services_em_view($fixclient){
        global $db, $design;

        if (!$this->fetch_client($fixclient)) {
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $items = EmailsServiceDao::me()->getAllForClient($clientNick);

        $design->assign('mails', $items);
        $res = $items ? true : false;

        $R=array();
        $design->assign('mailservers',$R);

        if (!$res && $R)
            $res = true;

        $design->assign('mailservers_id',null);

        $design->AddMain('services/mail.tpl'); 

        return $res;
    }
    
    function whitelist_load($fixclient,$filter,&$domains,&$mails,&$MCN,&$whlist){
        global $db,$design;
        $M=array(/*0=>'Доставлять всё',*/1=>'Добавлять в тему письма метку ---SPAM---',2=>'Уничтожать');
        $design->assign('em_options',$M);
        $clientNick = ClientAccount::findOne($fixclient)->client;

        if ($filter){
            $db->Query('select * from emails where (client="'.$clientNick.'") and (id='.$filter.')');
            if (!($r=$db->NextRecord())) return;
            $filter_domain=$r['domain'];
            $filter_mail=$r['local_part'];
            $filter_domain_q=' and (domain="'.$r['domain'].'")';
            $filter_mail_q=' and (domain="'.$r['domain'].'") and (local_part="'.$r['local_part'].'")';
            $design->assign('em_filter',$r);
        } else {
            $filter_domain='';
            $filter_domain_q='';
            $filter_mail_q='';
            $design->assign('em_filter','');
        }

        $domains=array(); $db->Query('select domain from domains where (client="'.$clientNick.'") and (actual_from<=NOW()) and (actual_to>NOW())'.$filter_domain_q);
        while ($r=$db->NextRecord()) $domains[]=$r['domain'];
        $design->assign('domains',$domains);
        
        $MCN=array();
        $db->Query('select emails.* from emails where (client="'.$clientNick.'") and (actual_from<=NOW()) and (actual_to>NOW())'.$filter_mail_q);
        $mails=array(); while ($r=$db->NextRecord()) {
            $mails[$r['id']]=$r;
            if (($r['domain']=="mcn.ru") && (!$filter_domain || ($filter_domain=="mcn.ru"))) $MCN[]=$r['local_part'];
        }
        $design->assign('mails',$mails);

        if ($filter_domain_q){
            $dq=""; if (count($domains)>0) $dq='(domain IN ("'.implode('","',$domains).'") AND ((local_part="") or (local_part="'.$filter_mail.'"))) OR ';
        } else {
            $dq=""; if (count($domains)>0) $dq='(domain IN ("'.implode('","',$domains).'")) OR ';
        }
        $mq=""; if (count($MCN)>0) $mq='(domain="mcn.ru" and local_part IN ("'.implode('","',$MCN).'")) OR ';
        
        $db->Query('select * from email_whitelist where '.$dq.$mq.'0 order by id');
        $whlist=array(); while ($r=$db->NextRecord()) $whlist[$r['id']]=$r;
        $design->assign('whlist',$whlist);
        
    }
    function services_em_whitelist_toggle($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $id=get_param_integer("id",0);
        $mode=get_param_integer("mode",0);
        if (!$id) return;
        $db->Query('select emails.* from emails where (client="'.$clientNick.'") and (id='.$id.')');
        if (!($r=$db->NextRecord())) return;

        if ($mode==2) $mode='discard';
            else if ($mode==1) $mode='mark';
                else $mode='pass';

        if ($r['spam_act']==$mode) return $this->services_em_view($fixclient);
        if ($mode=='pass'){
            $db->Query('update emails set spam_act="pass" where (client="'.$clientNick.'") and (id='.$id.')');
            $db->Query('insert into email_whitelist (local_part,domain,sender_address,sender_address_domain) values ("'.$r['local_part'].'","'.$r['domain'].'","","")');
        } else {
            if ($r['spam_act']=='pass'){
                $db->Query('select count(*) from email_whitelist where (local_part="") and (domain="'.$r['domain'].'") AND (email_whitelist.sender_address="") AND (email_whitelist.sender_address_domain="")');
                $r2=$db->NextRecord();
                if ($r2[0]!=0){
                    trigger_error2("На, домене, которому принадлежит этот e-mail, отключена фильрация спама. Сначала включите её вручную.");
                } else {
                    $db->Query('delete from email_whitelist where (local_part="'.$r['local_part'].'") and (domain="'.$r['domain'].'") AND (email_whitelist.sender_address="") AND (email_whitelist.sender_address_domain="")');
                    $db->Query('update emails set spam_act="mark" where (client="'.$clientNick.'") and (id='.$id.')');
                }
            } else {
                $db->Query('update emails set spam_act="'.$mode.'" where (client="'.$clientNick.'") and (id='.$id.')');
            }
        }
        $this->services_em_view($fixclient);
    }

    function services_em_whitelist($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}

        $filter=get_param_integer("filter","");
        $domains=array();
        $mails=array();
        $MCN=array();    //ящики с mcn.ru
        $whlist=array();
        $this->whitelist_load($fixclient,$filter,$domains,$mails,$MCN,$whlist);

        if (!count($mails)) {trigger_error2('У вас нет ни одного почтового ящика'); return;}
        $design->AddMain('services/mail_wh_list.tpl'); 
    }
    function services_em_whitelist_delete($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}

        $id=get_param_integer("id",0);
        if (!$id) return;
            
        $filter=get_param_integer("filter","");
        $domains=array();
        $mails=array();
        $MCN=array();    //ящики с mcn.ru
        $whlist=array();
        $this->whitelist_load($fixclient,$filter,$domains,$mails,$MCN,$whlist);
                
        if (!isset($whlist[$id])) return;
        $db->Query('delete from email_whitelist where id='.$id);
        
        trigger_error2('<script language=javascript>window.location.href="?module=services&action=em_whitelist&filter='.$filter.'";</script>');
    }
    function services_em_whitelist_add($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }

        $adr_radio0=get_param_integer("adr_radio0",0);
        $mail0=get_param_integer('mail0',0);
        $domain0=get_param_protected('domain0','');
        $adr_radio1=get_param_integer("adr_radio1",0);
        $mail1=get_param_protected('mail1','');
        $domain1=get_param_protected('domain1','');

        $filter=get_param_integer("filter","");
        $domains=array();
        $mails=array();
        $MCN=array();    //ящики с mcn.ru
        $whlist=array();
        $this->whitelist_load($fixclient,$filter,$domains,$mails,$MCN,$whlist);

        $q1=array();
        $q2=array();
        if($adr_radio0==0){
            if(!isset($mails[$mail0]))
                return;
            $q1[]='local_part';
            $q2[]='"'.$mails[$mail0]['local_part'].'"';
            $q1[]='domain';
            $q2[]='"'.$mails[$mail0]['domain'].'"';
        }else{
            if(!$domain0){
                trigger_error2('Неправильный адрес');
                return;
            }
            $q1[]='domain';
            $q2[]='"'.$domain0.'"';
        }
        if($adr_radio1==0){
            $v=explode('@',$mail1);
            if(count($v)!=2){
                trigger_error2('Неправильный адрес');
                return;
            }
            $q1[]='sender_address';
            $q2[]='"'.$mail1.'"';
        }elseif($adr_radio1==1){
            $q1[]='sender_address_domain';
            $q2[]='"'.$domain1.'"';
        }else{
            $q1[]='sender_address_domain';
            $q2[]='""';
        }

        if(
            empty($q2)
        ||
            (
                empty($q2[array_search('local_part', $q1)])
            &&
                empty($q2[array_search('sender_address', $q1)])
            &&
                empty($q2[array_search('domain',$q1)])
            &&
                empty($q2[array_search('sender_host_address',$q1)])
            )
        ){
            trigger_error2("<script type='text/javascript'>alert('Ошибка! Попробуйте снова. Если ошибка будет повторяться - обратитесь к программисту.')</script>");
        }else
            $db->Query('insert into email_whitelist ('.implode(',',$q1).') values ('.implode(',',$q2).')');
        trigger_error2('<script language=javascript>window.location.href="?module=services&action=em_whitelist&filter='.$filter.'";</script>');
    }

    function services_em_add($fixclient){
        global $design,$db,$user;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }

        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        if($user->Get('user')=='client'){
            $dbf = new DbFormEmailsSimple();    
        }else{
            $dbf = new DbFormEmails();
        }
        $dbf->SetDefault('client',$clientNick);
        $dbf->Display(
            array(
                'module'=>'services',
                'action'=>'em_apply'
            ),
            'Услуги',
            'Новый e-mail ящик'
        );
    }
    function services_em_apply($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $dbf = new DbFormEmails();
        $id=get_param_integer('id','');
        if($id)
            $dbf->Load($id);
        $result=$dbf->Process();
        if($result && include_once(INCLUDE_PATH.'welltime_integration.php')){
            $ms = new \welltime\MBox_syncer($db);
            if($result <> 'delete')
                $ms->UpdateMailBox(array(
                    'local_part'=>$_POST['dbform']['local_part'],
                    'password'=>$_POST['dbform']['password'],
                    'domain'=>$_POST['dbform']['domain']
                ));
        }
        if($result=='delete' || $result=='add') {
            header('Location: ?module=services&action=em_view');
            exit;
            $design->ProcessX('empty.tpl');
        } else $dbf->Display(array('module'=>'services','action'=>'em_apply'),'Услуги','Редактировать e-mail ящик');
    }
    
    function services_em_chpass($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;
        $design->assign('email',$r);
        $design->AddMain('services/mail_chpass.tpl');
    }
    function services_em_chreal($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $id=get_param_integer('id','');
        if (!$id) return;
        $pass1=get_param_protected('pass1','');
        $pass2=get_param_protected('pass2','');
        $db->Query('select * from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;
        if ($r['client']!=$clientNick) {trigger_error2('Клиенты не совпадают'); return; }
        if ($pass1!=$pass2) {
            trigger_error2('Пароли не совпадают');
            $this->services_em_chpass($fixclient);
//            trigger_error2('<script language=javascript>window.location.href="?module=services&action=em_chpass&id='.$id.'";</script>');
            return;
        }
        $db->Query('update emails set password="'.$pass1.'" where id='.$id);
        trigger_error2('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
    }
    function services_em_activate($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select *,
            IF((CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to),1,0) as actual,
                (actual_from<=NOW()) as save_from 
            from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;

        if ($r['actual']) {
            $db->Query("update emails set actual_to=NOW(),enabled=0, status='archived' where id=".$id);
            if(include_once(INCLUDE_PATH.'welltime_integration.php')){
                $mb = new \welltime\MBox_syncer($db);
                $mb->DeleteMailBox($r['local_part'].'@'.$r['domain']);
            }
        } else {
            if(include_once(INCLUDE_PATH.'welltime_integration.php')){
                $mb = new \welltime\MBox_syncer($db);
                $mb->UpdateMailBox(array(
                    'local_part'=>$r['local_part'],
                    'password'=>$r['password'],
                    'domain'=>$r['domain']
                ));
            }
            if ($r['save_from']) {
                $db->Query("update emails set actual_to='4000-01-01',enabled=1, status='working' where id=".$id);
            } else {
                $db->Query("update emails set actual_from=NOW(),actual_to='4000-01-01',enabled=1,status='working' where id=".$id);
            }
        }
        trigger_error2('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
    }
/*    function services_em_toggle($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select *,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;
        if ($r['actual']==0) $r['enabled']=0; else $r['enabled']=1-$r['enabled'];
        $db->Query('update emails set enabled='.$r['enabled'].' where id='.$id);
        trigger_error2('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
    }*/


// =========================================================================================================================================
    function services_ex_view($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $items = ExtraServiceDao::me()->getAllForClient($clientNick);

        for ($i=0, $s=sizeof($items); $i<$s; $i++) {
            if ($items[$i]['param_name'])
                $items[$i]['description'] = str_replace('%', '<i>' . $items[$i]['param_value'] . '</i>', $items[$i]['description']);

            if ($items[$i]['period'] == 'month')
                $items[$i]['period_rus'] = 'ежемесячно';
            else if ($items[$i]['period'] == 'year')
                $items[$i]['period_rus'] = 'ежегодно';
        }

        $design->assign('services_ex', $items);
        $design->AddMain('services/ex.tpl'); 
        return $items;
    }
    function services_ex_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $id=get_param_integer('id',0);
        $db->Query('select S.*,
            IF((CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to),1,0) AS actual,
                T.* 
            from usage_extra as S inner join tarifs_extra as T ON T.id=S.tarif_id where (S.id="'.$id.'") and (client="'.$fixclient.'")');
        if (!($r=$db->NextRecord())) return;
        if ($r['period']=='month') $r['period_rus']='ежемесячно'; else
        if ($r['period']=='year') $r['period_rus']='ежегодно';
        $design->assign('ad_item',$r);
        ClientCS::Fetch($r['client']);
        $design->ProcessEx('../store/acts/ex_act.tpl'); 
    }
    function services_ex_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $dbf = new DbFormUsageExtra();
        $dbf->SetDefault('client',$clientNick);

        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Новая дополнительная услуга');
    }
    function services_ex_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageExtra();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=ex_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Редактировать дополнительную услугу');
    }
    function services_ex_async($fixclient) {
        global $db;
        $id=get_param_integer('id');
        $tarif_table = get_param_protected('tarif_table', 'extra');

        $db->Query('select * from tarifs_'.$tarif_table.' where id='.$id);
        $r=$db->NextRecord();
        $_RESULT=array(
                    'async_price'        => $r['price'].' '.$r['currency'],
                    'async_period'        => $r['period'],
                    'param_name'        => $r['param_name'],
                    'is_countable'        => $r['is_countable'],
                    );
        echo json_encode($_RESULT);
    }
    function services_ex_close($fixclient){
        global $db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('update usage_extra set actual_to=NOW() where id='.$id);
        trigger_error2('<script language=javascript>window.location.href="?module=services&action=ex_view";</script>');
    }

// =========================================================================================================================================
    function services_it_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $R=array();
        $db->Query('
            select
                T.*,
                S.*,
                IF(CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to,1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            from
                usage_extra as S
            inner join
                tarifs_extra as T
            on
                T.id=S.tarif_id
            and
                T.status in ("itpark")
            where
                S.client="'.$clientNick.'"'
        );
        while($r=$db->NextRecord()){
            if($r['param_name'])
                $r['description']=str_replace('%','<i>'.$r['param_value'].'</i>',$r['description']);
            if($r['period']=='month')
                $r['period_rus']='ежемесячно';
            elseif($r['period']=='year')
                $r['period_rus']='ежегодно';
            $R[]=$r;
        }

        $design->assign('services_it',$R);
        $design->AddMain('services/it.tpl');
        return $R;
    }
    function services_it_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $dbf = new DbFormUsageITPark();
        $dbf->SetDefault('client',$clientNick);
        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Новая услуга ITPark');
    }

// =========================================================================================================================================
    function services_virtpbx_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){

            $vpbxs = $db->AllRecords($q='
            SELECT
                S.*,

                S.id as id,
                r.name as regionName,
                c.status as client_status,
                c.id as client_id,
                IF((CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_virtpbx as S
            LEFT JOIN datacenter d ON d.region = S.region
            LEFT JOIN regions r ON r.id = d.region
            LEFT JOIN clients c ON (c.client = S.client)

            HAVING actual
            ORDER BY client,actual_from'

            );

            $R = array();
            $statuses = ClientAccount::$statuses;
            foreach($vpbxs as $r){
                $r['tarif']=get_tarif_current('usage_virtpbx',$r['id']);
                $r["client_color"] = isset($statuses[$r["client_status"]]) ? $statuses[$r["client_status"]]["color"] : false;
                if($r['tarif']['period']=='month')
                    $r['period_rus']='ежемесячно';
                elseif($r['tarif']['period']=='year')
                    $r['period_rus']='ежегодно';
                $R[]=$r;
            }

            $design->assign('services_virtpbx',$R);
            $design->AddMain('services/virtpbx_all.tpl');

            //trigger_error2('Не выбран клиент');
            return;
        }


        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $R=array();
        $vpbxs = $db->AllRecords($q='
            SELECT
                
                S.*,
                S.id as id,
                r.name as regionName,
                IF(CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to,1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_virtpbx as S
            LEFT JOIN datacenter d ON d.region = S.region
            LEFT JOIN regions r ON r.id = d.region
            
            WHERE S.client="'.$clientNick.'"'
        );

        $isViewAkt = false;
        foreach($vpbxs as $r){
            $r['tarif']=get_tarif_current('usage_virtpbx',$r['id']);
            if($r['tarif']['period']=='month')
                $r['period_rus']='ежемесячно';
            $R[]=$r;

            if($r["actual"] && (strpos($r["tarif"]["description"], "Виртуальная АТС пакет") !== false || strpos($r["tarif"]["description"], "ВАТС ") !== false))
            {
                $isViewAkt = $r;
            }
        }

        $design->assign('virtpbx_akt',$isViewAkt);
        $design->assign('services_virtpbx',$R);
        $design->AddMain('services/virtpbx.tpl');
        return $R;
    }
    function services_virtpbx_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $dbf = new DbFormUsageVirtpbx();
        $dbf->SetDefault('client',$clientNick);
        $dbf->Display(array('module'=>'services','action'=>'virtpbx_apply'),'Услуги','Новая услуга Виртальная АТС');
    }
    function services_virtpbx_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageVirtpbx();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=virtpbx_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'virtpbx_apply'),'Услуги','Редактировать дополнительную услугу');
    }

    function services_virtpbx_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $account = ClientAccount::findOne(['id' => $fixclient]);
        $clientNick = $account->client;

        $id=get_param_integer('id',0);

        if(!$id) {trigger_error2('Ошибка в данных'); return;}

        $r = $db->GetRow('select * from usage_virtpbx where (client="'.$clientNick.'") and id ="'.$id.'"');

        $r["login"] = "______________";
        $r["password"] = "______________";

        //parse login / pass
        $v = $r["comment"];
        $v = trim($v);

        if(preg_match_all('/(\S+)/', $v, $o) && count($o[0]) >= 2)
        { 
            $r["login"] = $o[0][0];
            $r["password"] = $o[0][count($o[0])-1];
        }

        //** Выпилить */
        //Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $client = $design->get_template_vars('client');
        $organization = Organization::find()->byId($account->contract->organization_id)->actual()->one();

        $design->assign('firma', $organization->getOldModeInfo());
        $design->assign('firm_director', $organization->director->getOldModeInfo());
        //** /Выпилить */

        $design->assign('d',$r);

        $sendmail = get_param_raw('sendmail',0);
        if($sendmail){
            $msg = $design->fetch('../store/acts/virtpbx_act.tpl');
            $query = 'select group_concat(`cc`.`data`) `mails` from `clients` `cl` left join `client_contacts` `cc` on `cc`.`client_id`=`cl`.`id` and `cc`.`type`="email" and `cc`.`is_active`=1 where `cl`.`client`="'.addcslashes($fixclient, '\\"').'"';
            $db->Query($query);
            $mails = $db->NextRecord(MYSQL_ASSOC);
            $mails = $mails['mails'];
            $design->clear_all_assign();
            $design->assign('content_encode','base64');
            $design->assign('emails',$mails);
            $design->assign('subject','Акт');
            $design->assign('message',base64_encode(str_replace('&nbsp;',' ',$msg)));
            echo $design->fetch('wellsend.html');
            exit();
        }
        
        $design->ProcessEx('services/virtpbx_act.tpl'); 
    }
    function services_virtpbx_delete($fixclient)
    {
        global $db, $user;

        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $id = get_param_protected("id", 0);

        $vpbx = $db->GetRow($q = "select id, actual_from from usage_virtpbx where id=".$id." and client = '".$clientNick."'");

        if ($vpbx)
        {
            if ($vpbx["actual_from"] > "3000-01-01")
            {
                $db->QueryDelete("log_tarif", array("service" => "usage_virtpbx", "id_service" => $vpbx["id"]));
                $db->QueryDelete("usage_virtpbx", array("id" => $vpbx["id"]));
            }
        }
    }

// =========================================================================================================================================
    function services_sms_view($fixclient)
    {
        global $design;
        if (!$this->fetch_client($fixclient)) {
            $items = SmsServiceDao::me()->getAll();
            $statuses = ClientAccount::$statuses;
            for ($i=0, $s=sizeof($items); $i<$s; $i++) {
                $items[$i]["client_color"] = isset($statuses[ $items[$i]["client_status"] ])
                    ? $statuses[ $items[$i]["client_status"] ]["color"]
                    : false;
                if ($items[$i]['period'] == 'month')
                    $items[$i]['period_rus'] = 'ежемесячно';
            }
            $design->assign('f_manager', User::dao()->getListByDepartments('manager'));
            $design->assign('services_sms', $items);
            $design->AddMain('services/sms.tpl');
            return $items;
        }
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $items = SmsServiceDao::me()->getAllForClient($clientNick);
        $isViewAkt = false;
        for ($i=0, $s=sizeof($items); $i<$s; $i++) {
            if ($items[$i]['period'] == 'month')
                $items[$i]['period_rus'] = 'ежемесячно';
        }
        $design->assign('services_sms', $items);
        $design->AddMain('services/sms.tpl');
        return $items;
    }

    function services_sms_add($fixclient){
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $dbf = new DbFormUsageSms();
        $dbf->SetDefault('client',$clientNick);
        $dbf->Display(array('module'=>'services','action'=>'sms_apply'),'Услуги','Новая услуга CMC');
    }

    function services_sms_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageSms();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=sms_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'sms_apply'),'Услуги','Редактировать услугу CMC');
    }

    function services_welltime_view($fixclient){
        global $design;
        if (!$this->fetch_client($fixclient)) {
            $items = WelltimeServiceDao::me()->getAll();
            $statuses = ClientAccount::$statuses;
            for ($i=0, $s=sizeof($items); $i<$s; $i++) {
                $items[$i]["client_color"] = isset($statuses[ $items[$i]["client_status"] ])
                    ? $statuses[ $items[$i]["client_status"] ]["color"]
                    : false;
                if ($items[$i]['period'] == 'month')
                    $items[$i]['period_rus'] = 'ежемесячно';
                else if($items[$i]['period'] == 'year')
                    $items[$i]['period_rus'] = 'ежегодно';
            }
            $design->assign('services_welltime', $items);
            $design->AddMain('services/welltime_all.tpl');
            //trigger_error2('Не выбран клиент');
            return $items;
        }
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $items = WelltimeServiceDao::me()->getAllForClient($clientNick);
        $isViewAkt = false;
        for ($i=0, $s=sizeof($items); $i<$s; $i++) {
            if ($items[$i]['period'] == 'month')
                $items[$i]['period_rus'] = 'ежемесячно';
            else if($items[$i]['period'] == 'year')
                $items[$i]['period_rus'] = 'ежегодно';
        }
        $design->assign('services_welltime', $items);
        $design->AddMain('services/welltime.tpl');
        return $items;
    }

    function services_wellsystem_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;

        $R=array();
        $db->Query($q='
        select
            T.*,
            S.*,
            IF(CAST(NOW() AS DATE) BETWEEN actual_from and actual_to,1,0) as actual,
            IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
        from
            usage_extra as S
        inner join
            tarifs_extra as T
        on
            T.id=S.tarif_id
        and
            T.code in ("wellsystem")
        where
            S.client="'.$clientNick.'"'
        );
        while($r=$db->NextRecord()){
            if($r['param_name'])
                $r['description']=str_replace('%','<i>'.$r['param_value'].'</i>',$r['description']);
            if($r['period']=='month')
                $r['period_rus']='ежемесячно';
            elseif($r['period']=='year')
                $r['period_rus']='ежегодно';
            $R[]=$r;
        }
        $design->assign('services_wellsystem',$R);
        $design->AddMain('services/wellsystem.tpl');
        return $R;
    }

    function services_welltime_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $dbf = new DbFormUsageWelltime();
        $dbf->SetDefault('client',ClientAccount::findOne($fixclient)->client);
        $dbf->Display(array('module'=>'services','action'=>'welltime_apply'),'Услуги','Новая услуга Welltime');
    }
    function services_welltime_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageWelltime();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=welltime_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'welltime_apply'),'Услуги','Редактировать дополнительную услугу');
    }
// =========================================================================================================================================



    function services_wellsystem_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $dbf = new DbFormUsageWellSystem();
        $dbf->SetDefault('client',$clientNick);
        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Новая услуга WellSystem');
    }
// =========================================================================================================================================
    function services_ppp_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne($fixclient)->client;
        $R = array();
        $db->Query('
            select
                *,
                IF(
                    (CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to)
                    and
                        (enabled=1)
                ,1,0) as actual
            from
                usage_ip_ppp
            where
                client = "'.$clientNick.'"
        ');
        while($r=$db->NextRecord())
            $R[]=$r;
        $design->assign('ppps',$R);
        $design->AddMain('services/ppp.tpl'); 
        return $R;
    }
    function services_ppp_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageExtra();
        $dbf->SetDefault('client',ClientAccount::findOne($fixclient)->client);
        $dbf->Display(array('module'=>'services','action'=>'ppp_apply'),'Услуги','Новый ppp-логин');
    }
    function services_ppp_apply($fixclient){
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $dbf = new DbFormUsageExtra();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=ppp_view');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'ppp_apply'),'Услуги','Редактировать ppp-логин');
    }
    function services_ppp_append($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error2('Не выбран клиент');
            return;
        }
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $ass = array(); //бгг

        if(isset($_POST['append_ppp_ok'])){
            $query_ins = "
                INSERT INTO
                    `usage_ip_ppp`
                SET
                    `client` = '".$clientNick."',
                    `login` = '".addcslashes($_POST['pppoe_login'], "\\'")."',
                    `password` = '".addcslashes($_POST['pppoe_pass'], "\\'")."',
                    `ip` = '".preg_replace('/^[^0-9\.]+$/','',$_POST['ip_address'])."',
                    `nat_to_ip` = '".preg_replace('/^[^0-9\.]+$/','',$_POST['nat_2_ip'])."',
                    `actual_from` = now(),
                    `actual_to` = now()
            ";
            $db->Query($query_ins);
            trigger_error2('Логин успешно добавлен!');
            return;
        }
        $query = "
            select
                `login`,
                `ip`,
                `nat_to_ip`
            from
                `usage_ip_ppp`
            where
                `client`='".$clientNick."'
            order by
                `id`";
        $db->Query($query);
        $ppps = array();
        while($row = $db->NextRecord(MYSQL_ASSOC)){
            $ppps[] = $row;
        }
        if(!count($ppps)){
            trigger_error2('У пользователя нет ни одного ppp логина.');
            return;
        }
        $ppp_last = $ppps[count($ppps)-1];
        $sip = explode(".",$ppp_last['ip']);
        if($sip[3]<254)
            $sip[3]++;
        else
            $sip[3] = '000';

        $aff = count($ppps)+1;
        $ass['login'] = $clientNick.$aff;
        $ass['client'] = $clientNick;
        $ass['ip'] = implode('.',$sip);
        $ass['password'] = substr(md5($clientNick.$ass['login'].microtime().rand()),0,8);
        $ass['nat_2_ip'] = $ppp_last['nat_to_ip'];
        $design->assign('ass',$ass);
        $design->AddMain('services/append_ppp.tpl');
    }
/*function services_ppp_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}

        $R=array('id'=>0); $db->Query('select * from usage_ip_ports where client="'.$fixclient.'"');
        while ($r=$db->NextRecord()) $R[$r['id']]=$r;
        $design->assign('ports',$R);
        
        require_once INCLUDE_PATH.'db_map.php';
        $this->dbmap=new Db_map_nispd();
        $this->dbmap->SetErrorMode(2,0);
        $this->dbmap->ShowEditForm('usage_ip_ppp','',array('client'=>$fixclient,'actual_to'=>'2029-01-01','actual_from'=>date('Y-m-d')),1);
        $design->AddMain('services/ppp_add.tpl');
    }
    function services_ppp_apply($fixclient){
        global $design,$db,$_POST;
        if (!access('services_ppp','addnew') && !access('services_ppp','edit')) return;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        if (!access('services_ppp','edit') &&  get_param_raw('dbaction')!='add') {
            trigger_error2('<script language=javascript>window.location.href="?module=services&action=ppp_view";</script>');
            return;
        }
        require_once INCLUDE_PATH.'db_map.php';
        $this->dbmap=new Db_map_nispd();
        $this->dbmap->SetErrorMode(2,0);
        if ($this->dbmap->ApplyChanges('usage_ip_ppp')!="ok") {
            $R=array('id'=>0); $db->Query('select * from usage_ip_ports where client="'.$fixclient.'"');
            while ($r=$db->NextRecord()) $R[$r['id']]=$r;
            $design->assign('ports',$R);

            $this->dbmap->ShowEditForm('usage_ip_ppp','',get_param_raw('row',array()));
            $design->AddMain('services/ppp_add.tpl');
        } else {
            if (get_param_raw('dbaction')!='delete'){
                if (!get_param_raw('id','')) $id=$db->GetInsertId();
                $r=$this->dbmap->SelectRow('usage_ip_ppp','id='.$id);
            }
            trigger_error2('<script language=javascript>window.location.href="?module=services&action=ppp_view";</script>');
        }
    }*/

    function services_ppp_chpass($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from usage_ip_ppp where id='.$id);
        if (!($r=$db->NextRecord())) return;
        $design->assign('ppp',$r);
        $design->AddMain('services/ppp_chpass.tpl');
    }
    function services_ppp_chreal($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $id=get_param_integer('id','');
        if (!$id) return;
        $pass1=get_param_protected('pass1','');
        $pass2=get_param_protected('pass2','');
        $db->Query('select * from usage_ip_ppp where id='.$id);
        if (!($r=$db->NextRecord())) return;
        if ($r['client']!=$clientNick) {trigger_error2('Клиенты не совпадают'); return; }
        if ($pass1!=$pass2) {
            trigger_error2('Пароли не совпадают');
            $this->services_ppp_chpass($fixclient);
            return;
        }
        $db->Query('update usage_ip_ppp set password="'.$pass1.'" where id='.$id);
        trigger_error2('<script language=javascript>window.location.href="?module=services&action=ppp_view";</script>');
    }
    
    function services_ppp_activate($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from usage_ip_ppp where (id='.$id.') and (client="'.$clientNick.'")');
        if (!($r=$db->NextRecord())) return;
        
        if ($r['enabled']) {
            $db->Query('update usage_ip_ppp set enabled=0,actual_to=NOW() where id='.$id);
        } else {
            $db->Query('update usage_ip_ppp set enabled=1,actual_from=NOW(),actual_to="4000-01-01" where id='.$id);
        }
        $this->services_ppp_view($fixclient);
    }

    function services_ppp_activateall($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error2('Не выбран клиент'); return;}
        $clientNick = ClientAccount::findOne(['id' => $fixclient])->client;
        $value=get_param_integer('value',0); if ($value) $value=1;
        if ($value==0){
            $db->Query('update usage_ip_ppp set enabled=0 where (client="'.$clientNick.'")');
        } else {
            $db->Query('update usage_ip_ppp set enabled=1 where (client="'.ClientAccount::findOne($fixclient)->client.'") and (actual_from<=NOW()) and (actual_to>NOW())');
        }
        $this->services_ppp_view($fixclient);
    }

// 89.235.136.0/24
// 85.94.33.0/24
// =========================================================================================================================================
    function &Tarif($id) {
        global $db;
        if (!isset($this->T)) $this->T=array();
        if (isset($this->T[$id])) return $this->T[$id];
        $db->Query('select * from tarifs_internet where id='.$id); $this->T[$id]=$db->NextRecord();
        return $this->T[$id];
    }
    function get_ports($client,$C,$wh='',$select = '',$join = '',$order = 'usage_ip_ports.id ASC'){
        global $db;
        $cidT = is_numeric($client) ? 'c.id':'c.client';
        $db->Query($q='
            SELECT
                usage_ip_ports.*,
                IF((usage_ip_ports.actual_from<=NOW()) and (usage_ip_ports.actual_to>NOW()),1,0) as actual,
                tech_ports.port_name as port,
                tech_ports.node,
                tech_ports.port_type,
                c.id AS clientid,
                IF(usage_ip_ports.actual_from<=(NOW()+INTERVAL 5 DAY),1,0) as actual5d '.$select.'
            FROM
                usage_ip_ports
            LEFT JOIN clients c ON c.client = usage_ip_ports.client
            LEFT JOIN
                tech_ports
            ON
                (tech_ports.id=usage_ip_ports.port_id)
            LEFT JOIN
                usage_ip_routes
            ON
                (usage_ip_ports.id=usage_ip_routes.port_id)
            '.$join.'
            WHERE
                '.($wh?$wh:'('.$cidT.'="'.$client.'")').'
            GROUP BY
                usage_ip_ports.id
            ORDER BY '.$order
        );

        $R=array();
        while($r=$db->NextRecord())
            $R[$r['id']]=$r;
        $V=array();

        foreach($R as $k=>$r){
            $T = get_tarif_history("usage_ip_ports",$r['id']);
            //printdbg($T);
            foreach($T as $t)
                if($t['is_current'])
                    $r['tarif']=$t;
                elseif($t['is_next'])
                    $r['tarif_next']=$t;
                elseif($t['is_previous'])
                    $r['tarif_previous']=$t;
//            $r['tarif']=get_tarif_current("usage_ip_ports",$r['id']);
//            $r['tarif_next']=get_tarif_next("usage_ip_ports",$r['id']);
            $r['cpe']=get_cpe_history('usage_ip_ports',$r['id']);
/*            $r['cpe']=$db->AllRecords('select usage_tech_cpe.*,type,vendor,model from usage_tech_cpe '.
                    'LEFT JOIN tech_cpe_models ON tech_cpe_models.id=usage_tech_cpe.id_model '.
                    'where usage_tech_cpe.service="usage_ip_ports" and usage_tech_cpe.id_service="'.$r['id'].'" '.
                    'AND usage_tech_cpe.actual_from<=NOW() and usage_tech_cpe.actual_to>=NOW() '.
                    'order by id');*/
            $collocation=0;
            if(isset($r['tarif']['type']) && $r['tarif']['type']=='C')
                $collocation=1;
            if($collocation==$C)
                $V[$k]=$r;
        }
        return $V;
    }
    function get_nets($port,$whsql=''){
        global $db;
        $db->Query($q='
            SELECT
                *,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual
            FROM
                usage_ip_routes
            WHERE
                (port_id='.$port.')'.($whsql?' and '.$whsql:''));
        $R=array(); while ($r=$db->NextRecord()) {
            $R[]=$r;
        };
        return $R;
    }
    function fetch_client($fixclient){
        global $db,$design;
        if (isset($this->fetched_client)) return 1;
        if ($design->var_is_array('client')) return 1;
        if (!$fixclient) return 0;
        $r = ClientAccount::findOne($fixclient);
        if (!$r) return 0;
        $design->assign('client',$r);
        $this->fetched_client=$r;
        return 1;
        
    }
    function routes_check($client){
        global $db;
        $db->Query("select count(*) from usage_ip_ports where (actual_to>NOW()) and (client=\"{$client}\")");
        $r=$db->NextRecord();
        if (isset($r[0]) && $r[0]=="0"){
            trigger_error2("Клиенту установлен статус \"отключен\"");
            $db->Query("update clients set status=\"disabled\" where client=\"{$client}\"");
            $db->Query("select count(*) from usage_voip where (actual_from<=NOW()) and (actual_to>=NOW()) and (client=\"{$client}\")");
            $r=$db->NextRecord();
            if (isset($r[0]) && ($r[0])) trigger_error2("Внимание! У клиента осталась IP-телефония.");
        } else {
            $db->Query("select status from clients where client=\"{$client}\"");
            $r=$db->NextRecord();
            if ($r[0]!="work") {
            trigger_error2("Клиенту установлен статус \"включен\"");
                $db->Query("update clients set status=\"work\" where client=\"{$client}\"");
            }
        }
    }

    function services_get_tarifs($fixclient)
    {
        global $db;
        $region = get_param_protected('region','');
        $Res = array();

        $account = ClientAccount::findOne($fixclient);
        $R=$db->AllRecords('select status, id, name, month_number, month_line, dest, month_min_payment from tarifs_voip where currency_id="'.$account['currency'].'" and connection_point_id="'.$region.'" and status != "archive"'.
                'order by status, month_line, month_min_payment', 'id');
        foreach ($R as $r) {
            $Res[$r['id']] = array(
                            'id'=>$r['id'],
                            'name'=>$r['name'],
                            'month_number'=>$r['month_number'], 
                            'month_line'=>$r['month_line'], 
                            'status'=>$r['status'], 
                            'dest'=>$r['dest'],
                            'month_min_payment'=>$r['month_min_payment']
            );
        }

        echo json_encode($Res);
        exit();
    }

    public function services_rpc_extendReserv($fixclient)
    {
        global $db, $user;

        $usage_id = get_param_integer("usage_id", 0);

        if ($usage_id > 0)
        {
            $usage_id = $db->GetValue("select id from usage_voip where actual_from > '3000-01-01' and actual_to > '3000-01-01' and id = '".$db->escape($usage_id)."'");

            if ($usage_id)
            {
                $max_id = $db->GetValue("select max(id) from log_tarif where service='usage_voip' and id_service='".$db->escape($usage_id)."'");

                $lastLog = $db->GetRow("select * from log_tarif where id = '".$max_id."'");

                unset($lastLog["id"]);
                $lastLog["ts"] = date("Y-m-d H:i:s");
                $lastLog["id_user"] = $user->Get("id");
                $lastLog["comment"] = "продление резерва";

                $db->QueryInsert("log_tarif", $lastLog);

                echo "ok";
                exit();
            } else {
                echo "Ошибка!";
            }
        } else {
            echo "Ошибка!";
        }
        exit();
    }
    
     /**
     * Функция проверяет, есть ли у данного сервиса сервис, с которой возможно был осуществлен переход
     */
    function services_rpc_check_move()
    {
        $table = isset($_REQUEST['table']) ? $_REQUEST['table'] : '';
        $result = array('is_moved' => false, 'is_moved_with_pbx' => false, 'posible_pbx' => false);
        if ($table == 'usage_voip')
        {
            $result['posible_pbx'] = 'NO';
            $number = isset($_REQUEST['number']) ? $_REQUEST['number'] : '';
            $actual_from = isset($_REQUEST['actual_from']) ? date('Y-m-d', strtotime($_REQUEST['actual_from'])) : '';
            if ($number && $actual_from)
            {
                $check_move = UsageVoip::checkNumberIsMoved($number, $actual_from);
                if (!empty($check_move))
                {
                    $result['is_moved'] = true;
                    $options = array();
                    $options['select'] = 'client';
                    $options['conditions'] = array('E164 = ? AND actual_from = ?', $number, $actual_from);
                    $client = UsageVoip::first($options)->client;
                    $check_move_with_pbx = UsageVirtpbx::checkNumberIsMovedWithPbx( $check_move->client, $client, $actual_from);
                    if (!empty($check_move_with_pbx))
                    {
                        $result['is_moved_with_pbx'] = true;
                    }
                }
            }
        } elseif ($table == 'usage_virtpbx') {
            $actual_from = isset($_REQUEST['actual_from']) ? date('Y-m-d', strtotime($_REQUEST['actual_from'])) : '';
            $client = isset($_REQUEST['client']) ? $_REQUEST['client'] : '';
            $result['is_moved_with_pbx'] = 'NO';
            if ($actual_from && $client)
            {
                $check_move = UsageVirtpbx::getAllPosibleMovedPbx($actual_from, $client);
                $result['is_moved'] = !empty($check_move);
                
                $result['posible_pbx'] = $check_move;
            }
        }

        return $result;
    }
    
    /**
     * Функция проверяет, можно ли использовать введенный номер
     */
    function services_rpc_check_e164()
    {
        global $db;
        $spec_numbers = array('9999', '7495', '7499', '', '0', '101');

        if ($_GET['e164'] == 'TRUNK') {
            $query = "select max(CONVERT(E164,UNSIGNED INTEGER))+1 as number from usage_voip where LENGTH(E164)=3";
            if (($res=$db->GetValue($query)) !== false) {
                return $res;
            } else 
                return "FAIL";
        }

        if(preg_match('/^FREE:(\d{2,7}|short)?/',$_GET['e164'],$m)){
            if(isset($m[1]) && $m[1] != 'short'){
                $ann = "and substring(`vn`.`number` from 1 for ".strlen($m[1]).") = '".$m[1]."'";
            } elseif ($m[1] == 'short') {
                $query = "select max(CONVERT(E164,UNSIGNED INTEGER))+1 as number from usage_voip where LENGTH(E164)<6 and e164 not in ('".implode("','", $spec_numbers)."')";
                if (($res=$db->GetValue($query)) !== false) {
                    while (in_array($res, $spec_numbers)) $res++;
                    return $res;
                } else 
                    return "FAIL";
            }else{
                $ann = '';
            }

            $query = "
                select `vn`.`number`, (select max(actual_to) from usage_voip uv where uv.e164 = vn.number) as actual_to
                from `voip_numbers` `vn`
                where `vn`.`beauty_level` = '0'
                and vn.client_id is null
                ".$ann."

                having date_add(ifnull(actual_to,'2000-01-01'), interval 6 month) <= now()

                order by
                ifnull(actual_to, '2000-01-01') asc , rand()
                limit 1
            ";

            $ret = $db->AllRecords($query);
            //printdbg($ret);
            if(count($ret) == 1)
                return $ret[0]['number'];
            else
                return "FAIL";
        }elseif(preg_match('/^isset:(\d+)?/',$_GET['e164'],$m)){
            $e164 = $m[1];
            if(!$e164){
                return '';
            }
            $query = "select number from voip_numbers where number='".($e164)."'";
            $ret = $db->AllRecords($query);
            if(count($ret)>0)
                return "is";
            return '';
        }

        $number = preg_replace('/[^0-9]+/','',$_GET['e164']);
        if(strlen($number)<4){
            return "false";
        }

        if(strlen($number)>5)
        {
            if(!preg_match("/^7[0-9]+$/", $_GET["e164"]) && !preg_match("/^36[0-9]+$/", $_GET["e164"])) //Россия или Венгрия
            {
                return "false";
            }
        }

        if (in_array($number, $spec_numbers)) {
            return 'true';
        }

        $region = $db->escape(get_param_raw("region", 0));
        
        if (strlen($number) > 5 && substr($number, 0, 4) != '7800') {
            $q = 'SELECT number FROM voip_numbers WHERE number="'.$number.'" and region = "'.$region.'"';
            $res = $db->getRow($q);
            if (!$res) {
                return "false";
            }
        }

        $actual_from = date('Y-m-d', strtotime($_GET['actual_from']));
        $actual_to = date('Y-m-d', strtotime($_GET['actual_to']));

        $query = "
            SELECT
                *
            FROM
                `usage_voip`
            WHERE
                `E164` = '".$number."'
            AND
                (
                    (
                        `actual_to` BETWEEN '".$actual_from."' AND '".$actual_to."'
                    and
                        (`actual_to` < '3000-01-01' or `status` = 'connection')
                    )
                or
                    (
                        `actual_from` BETWEEN '".$actual_from."' AND '".$actual_to."'
                    and
                        (`actual_from` < '3000-01-01' or `status` = 'connection')
                    )
                or
                    (
                        '".$actual_from."' BETWEEN `actual_from` AND `actual_to`
                    and
                        ('".$actual_from."' < '3000-01-01' or `status` = 'connection')
                    )
                )
        ";

        $res = $db->AllRecords($query);
        if(count($res)>0) {
            return "false";
        }else{
            $query = "
                SELECT
                    *
                FROM
                    `usage_voip`
                WHERE
                    `E164` = '".$number."'
                AND
                    now() between `actual_from` and `actual_to`
            ";

            $res = $db->AllRecords($query);
            if(count($res)>0){
                return "true_but";
            }else
                return "true";
        }
    }
    
    function services_check_pop_services($fixclient)
    {
        header('Content-Type: application/json');
        $data = array();
        $data['e164'] = $this->services_rpc_check_e164();
        $data['move'] = $this->services_rpc_check_move();
        echo json_encode($data);
        exit();
    }
    
    function services_check_services_move($fixclient)
    {
        header('Content-Type: application/json');
        $data = $this->services_rpc_check_move();
        echo json_encode($data);
        exit();
    }
}

class voipRegion
{
    private static $e164Region = array();
    private static $lastRegionServer = 0;

    static public function getClientE164s($client)
    {
        $e164s = array();

        foreach(self::_getRegions($client) as $region)
        {
            $e164s += self::_getRegionE164s($client, $region);
        }

        return $e164s;
    }

    static private function _getRegionE164s($client, $region)
    {
        self::__db_connect($region);

        $result = pg_query(
                $q = "SELECT distinct callerid, name
                FROM ".($region == 99 ? "sip_users" : "sipdevices")." WHERE client_id='".$client["id"]."' ".($region != 99 ? "and region = '".$region."'" : "")."
                ORDER BY callerid");

        $e164s = array();
        while($l = pg_fetch_assoc($result))
        {
            $callerId = $l["callerid"];
            if(!$callerId) {
                $l["callerid"] = $callerId = "trunk:".$l["name"];
            }

            if(strpos($l["callerid"], "+") !== false)
            {
                $l["callerid"] = preg_replace("@\+\d+@", "", $l["callerid"]);
                $l["callerid"] = preg_replace("@\d+\*@", "", $l["callerid"]);
            }

            if(strpos($l["callerid"], "*") !== false)
            {
                $l["callerid"] = preg_replace("@\d+\*@", "", $l["callerid"]);
            }

            $e164s[$callerId] = $l["callerid"];
            self::$e164Region[$l["callerid"]] = $region;
        }

        return $e164s;
    }

    static private function _getRegions($client)
    {
        global $db;

        $rs = array();
        foreach($db->AllRecords("select distinct region from usage_voip where client = '".$client["client"]."' order by region desc") as $r)
            $rs[] = $r["region"];

        return $rs;
    }

    static public function _getEmailMsg($client, $needSendE164)
    {
        $a = array();
        foreach($needSendE164 as $n)
        {
            $nn = $n;
            if(!isset(self::$e164Region[$n]))
            { 
                $nn = "trunk:".$n;
                if (!isset(self::$e164Region["trunk:".$n]))
                {
                    return false;
                }
            }

            if(!isset($a[self::$e164Region[$nn]]))
            {
                $a[self::$e164Region[$nn]] = array();
            }

            $a[self::$e164Region[$nn]][] = $nn;
        }


        $msgHeader = "Здравствуйте!\n";

        $msg = "";
        foreach($a as $region => $numbers)
            $msg .= self::getRegionRegs($client, $region, $numbers);

        return $msg ? $msgHeader.$msg : false;
    }

    static public function getEmailMsg($clientId, $needSendE164)
    {
        $msgHeader = "Здравствуйте!\n";
        $msg = "";

        foreach($needSendE164 as $n)
        {
            $msg .= self::getMsg($clientId, $n);
        }

        return $msg ? $msgHeader.$msg : false;
    }


    static private function getMsg($clientId, $number)
    {
        global $db_ats;

        $pbx = array(
                "99" => "sip.mcn.ru",
                "96" => "37.228.82.6",
                "97" => "37.228.80.6",
                "98" => "37.228.81.6",
                "95" => "37.228.85.6",
                "94" => "37.228.83.6",
                "93" => "37.228.84.6",
                "87" => "37.228.86.6",
                "88" => "37.228.87.6",
                "89" => "176.227.177.6"
                );

        $numberId = false;//ats2Numbers::getNumberId($number, $clientId);

        $msg = "";

        if ($numberId )
        {
            $number = $db_ats->GetRow("select * from a_number where id = '".$numberId."'");

            if ($number)
            {
                foreach($db_ats->AllRecords("select c_id from a_link where number_id = '".$numberId."' and c_type in ('line', 'trunk')") as $line)
                {
                    $lineId = $line["c_id"];

                    if ($lineId)
                    {
                        $a = account::get($lineId);

                        $permit = "";

                        if ($a["permit_on"] == "auto")
                        {
                            $permit = "автоматическая привязка при первой регистрации";
                        } elseif ($a["permit_on"] == "no") {
                            $permit = "любой адрес";
                        } elseif ($a["permit_on"] == "yes"){
                            $permit = "разрешено: ".str_replace(",", " ", $a["permit"]);
                        }

                        $msg .= "\n-----------------------------------------\n".
                            "Номер телефона: ".$number["number"]."\n".//($l["callerid"]? $l["callerid"]: "***trunk***")."\n".
                            "SIP proxy: ".$pbx[$number["region"]]."\n".
                            ($a["host_type"] == "dynamic" ? "register: Да\n" : "").
                            "username: ".$a["account"]."\n".
                            "password: ".$a["password"]."\n".
                            "привязка: ".$permit."\n";
                    }
                }
            }
        }

        return $msg;
    }

    static private function getRegionRegs($client, $region, $_e164s)
    {
        self::__db_connect($region);

        $pbx = array(
                "ast244" => "85.94.32.244",
                "ast245" => "85.94.32.245",
                "ast248" => "85.94.32.248",
                "reg96" => "37.228.82.6",
                "reg97" => "37.228.80.6",
                "reg98" => "37.228.81.6",
                "reg95" => "37.228.85.6",
                "reg94" => "37.228.83.6",
                "reg93" => "37.228.84.6",
                "reg87" => "37.228.86.6",
                "reg88" => "37.228.87.6",
                "reg89" => "176.227.177.6" // Заявка 178491: Нужно добавить Владивосток в "просмотр регистраций". PBX = 176.227.177.6
                );

        $callerids = $names = array();
        foreach($_e164s as $e)
        {
            if(strpos($e, "trunk:") !== false)
            {
                $names[] = str_replace("trunk:", "", $e);
                $names[] = str_replace("trunk:", "", $e)."+1";
                $names[] = str_replace("trunk:", "", $e)."*1";
            }else{
                $callerids[] = $e;
                $callerids[] = "74959505680*".$e;
            }
        }

        $result = pg_query(
                $q = "SELECT *,name, callerid, permit, deny, secret, 
                ".($region == 99  ? "":"'reg".$region."' as ")." ippbx
                FROM ".($region == 99 ? "sip_users" : "sipdevices")." 
                WHERE client_id = '".$client["id"]."' 
                and (
                    ".($callerids ? "callerid in ('".implode("','", $callerids)."')" : "").
                    ($callerids && $names ? " or (" : "").
                    ($names ? "callerid is null  and name in ('".implode("','", $names)."')" : "").
                    ($callerids && $names ? " )" : "")."
                )
            ORDER BY callerid, name");

        $regs = array();
        $msg = "";
        while($l = pg_fetch_assoc($result))
        {
            if(isset($pbx[$l["ippbx"]])) $l["ippbx"] = $pbx[$l["ippbx"]];
            else die("Неизветсный IPPBX, обратитесь к программисту");

            $permit = preg_replace('/;/', ' ', $l['permit']);
            if (($permit == '0.0.0.0/0.0.0.0') and ($l['deny'] == '')) {
                $perm = 'любой IP';
            } elseif ($l['deny'] == '0.0.0.0/0.0.0.0') {
                $perm = $permit;
            } elseif (($permit == '') and ($l['deny'] == '')) {
                $perm = 'автоматическая привязка при первой регистрации';
            } else {
                $perm = 'разрешено: '.$permit.'; запрещено: '.$l['deny'];
            }
            $l["permit"] = $perm;
            $regs[] = $l;

            if(strpos($l["callerid"], "74959505680*") !== false)
            {
                $l["callerid"] = "линия без номера (".str_replace("74959505680*", "", $l["callerid"]).")";
            }

            $msg .= "\n-----------------------------------------\n".
                "Номер телефона: ".($l["callerid"]? $l["callerid"]: (strpos($l["name"], "7800") !== false ? preg_replace("@\+\d+@", "", $l["name"]) : "***trunk***"))."\n".
                "SIP proxy: ".$l["ippbx"]."\n".
                "register: YES\n".
                "username: ".$l["name"]."\n".
                "password: ".$l["secret"]."\n".
                "привязка: ".$l["permit"]."\n";
        }
        return $msg;
    }

    static private function __db_connect($region)
    {
        if(self::$lastRegionServer == $region) return;

        self::$lastRegionServer = $region;

        if($region == "99")
        {
            $conn = pg_connect($q = "host=".R_CALLS_99_HOST." dbname=".R_CALLS_99_DB." user=".R_CALLS_99_USER." password=".R_CALLS_99_PASS);
            if (!$conn)
            {
                mail(ADMIN_EMAIL, "[pg connect]", "services/__db_connect");
            }

        }else{
            $dbname = "voipdb";
            $dbHost = str_replace("[region]", $region, R_CALLS_HOST);
            $schema = "";

            if(in_array($region, array(94, 95, 87, 97, 98, 88, 89, 93, 991))) // new schema. scynced
            {
                $schema = "astschema";
                $dbHost = "eridanus.mcn.ru";
            }

            $conn = @pg_connect($q="host=".$dbHost." dbname=".$dbname." user=".R_CALLS_USER." password=".R_CALLS_PASS." connect_timeout=1");
            if (!$conn)
            {
                mail(ADMIN_EMAIL, "[pg connect]", "services/__db_connect_2");
            }
            if($conn && $schema)
                pg_query("SET search_path TO ".$schema.", \"\$user\", public");
        }
    }
}
?>
