<?php


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
        if (access_action('services','in_view')) {$this->services_in_view($fixclient); return;}
        if (access_action('services','co_view')) {$this->services_co_view($fixclient); return;}
    }

// ==========================================================================================================================================
    function services_in_async($fixclient) {
        global $db,$design,$_RESULT;
        include INCLUDE_PATH."JsHttpRequest.php";
        $JsHttpRequest = new Subsys_JsHttpRequest_Php();
        $JsHttpRequest->setEncoding("koi8-r");
        $node=get_param_protected('node');
        $port_type=get_param_protected('port_type');
        if ($port_type=="pppoe") $port_type='pppoe","dedicated';
        $R=array(); $db->Query('select port_name from tech_ports where node="'.$node.'" and port_type IN ("'.$port_type.'") order by port_name');
        while ($r=$db->NextRecord()) $R[$r['port_name']]=$r['port_name'];
        $_RESULT=array(
                    'ports'        => $R,
                    );
        if (isset($design)) $design->ProcessEx('errors.tpl');
    }
    function services_in_report(){
        global $design,$db,$user,$module_users;
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
        else
            $port_type = false;
        $design->assign('port_type',$port_type);
        $manager = get_param_protected('manager');
        $design->assign('manager',$manager);
        $hide_slow = get_param_protected('hide_slow');
        $design->assign('hide_slow',$hide_slow);
        $unlim = get_param_protected('unlim');
        $design->assign('unlim',$unlim);
        $show_off = get_param_raw('show_off',false);
        $design->assign('show_off',$show_off);

        $R=array();
        $module_users->d_users_get($R,'manager');
        $design->assign('managers',$R);

        $connections=array();

        $fil = '';
        if($port_type && count($port_type))
            $fil .= " AND `tp`.`port_type` in ('".join("','",$port_type)."')";
        if($manager)
            $fil .= ' AND `cl`.`manager` = "'.$manager.'"';
        if($hide_off)
            $fil .= ' AND `uip`.`actual_to` >= FROM_UNIXTIME('.strtotime("+1 day", $to).')';
        if($hide_slow){
            $fil .= ' AND (`ti`.`adsl_speed` IS NULL or substr(`ti`.`adsl_speed` from instr(`ti`.`adsl_speed`,"/")+1) not in ("128","256"))';
        }
        if($unlim){
            // только безлимитные
            // downstream(вторая часть скорости, после /) у mgts не равна той, которая у adsl_speed
            $fil .= " AND IF(`ti`.`id` is null,1,`ti`.`name` like '%безлимитный%' collate koi8r_general_ci
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
                `cl`.`manager`,
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
                 if(!eregi("Безлимитный", $p["tarif"]["name"]))
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
        trigger_error('TODO9 - временно отключено');
    }
    function services_in_view_routed($fixclient){
/*        global $design;
        $router=get_param_protected('router','');
        if (!$router) {trigger_error('Не выбран роутер'); return;}
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
        trigger_error('TODO9 - временно отключено');
    }

    function services_in_add($fixclient,$suffix="internet",$tarif_type=""){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }

        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        $dbf = new DbFormUsageIpPorts();
        $dbf->SetDefault('client',$fixclient);
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
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageIpPorts();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action='.$suffix2.'_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'in_apply'),'Услуги','Редактировать подключение');
    }
    function services_in_add2($fixclient,$id='',$suffix="internet"){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
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
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageIpRoutes();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=in_view');    
        } else {
            $dbf->Display(array('module'=>'services','action'=>'in_apply2'),'Услуги','Редактировать сеть');
        }
    }
    
    function services_in_act($fixclient,$suffix="internet"){
        global $design,$db;
        $id=get_param_protected('id');
        if ($id=='') return;
        
        Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $conn=$db->GetRow("select * from usage_ip_ports where id='".$id."'");
        $routes=array(); $db->Query('select * from usage_ip_routes where (port_id="'.$id.'") and (actual_from<=NOW()) and (actual_to>=NOW()) order by id');
        while ($r=$db->NextRecord()) {
            $t=explode("/",$r['net']);
            $t=explode('.',$t[0]);
            $t[3]++;
            $r['gate']=implode(".",$t);
            $routes[]=$r;    
        }
        //if (!$routes || !$conn) {trigger_error('Сеть и подключения не найдены'); return; }
        //$conn['actual_from']=convert_date($conn['actual_from']);
        $design->assign('conn',$conn);
        $design->assign('port',$db->GetRow("select * from tech_ports where id='".$conn['port_id']."'"));
        $design->assign('routes',$routes);
        $design->assign('cpe',get_cpe_history("usage_ip_ports",$id));
        ClientCS::Fetch($conn['client']);
        $design->assign('ppp',$db->AllRecords('select * FROM usage_ip_ppp where client="'.$conn['client'].'"'));
        $design->assign('internet_suffix',$suffix);
        $sendmail = get_param_raw('sendmail',0);
        if($sendmail){
            $msg = $design->fetch('../store/acts/'.$suffix.'_act.tpl');
            $query = 'select group_concat(`cc`.`data`) `mails` from `clients` `cl` left join `client_contacts` `cc` on `cc`.`client_id`=`cl`.`id` and `cc`.`type`="email" and `cc`.`is_active`=1 where `cl`.`client`="'.addcslashes($fixclient, '\\"').'"';
            $db->Query($query);
            $mails = $db->NextRecord(MYSQL_ASSOC);
            $mails = $mails['mails'];
            $design->clear_all_assign();
            $design->assign('content_encode','base64');
            $design->assign('emails',$mails);
            $design->assign('subject',iconv('koi8r','utf8','Акт'));
            $design->assign('message',base64_encode(iconv('koi8r','utf8',str_replace('&nbsp;',' ',$msg))));
            echo $design->fetch('wellsend.html');
            exit();
        }
        $design->ProcessEx('../store/acts/'.$suffix.'_act.tpl');
    }    
    
    function services_in_close($fixclient,$suffix='internet'){
        global $design,$db;

        trigger_error("<h1><b>Вы сделали недопустимое действие! За вами выехали!</b></h1>");
        mail("dga@mcn.ru","error in_close","error in_close\n look logs");

        if(false)
        {
            if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
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
    }
    
    function services_co_add($fixclient){
        global $design;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $this->services_in_add($fixclient,'collocation','C');
    }
    function services_co_add2($fixclient,$id=''){
        global $db,$design;
        $this->services_in_add2($fixclient,$id,'collocation');
    }
    
    function services_co_act($fixclient){
        global $design,$db;
        $this->services_in_act($fixclient,'collocation');
    }    
    
    function services_co_close($fixclient){
        global $design,$db;
        $this->services_in_close($fixclient,'collocation');
    }
    
    function services_co_apply($fixclient){
        global $design,$db;
        $this->services_in_apply($fixclient,'collocation','co');
    }

    function services_co_apply2($fixclient){
        global $design,$db;
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
                    usage_voip.*,
                    IF((actual_from<=NOW())
                and
                    (actual_to>NOW()),1,0) as actual
                from
                    usage_voip
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
        } else {

            global $db_ats;

            $isDbAtsInited = $db_ats && $db != $db_ats;

            $db->Query($q='
                select
                    usage_voip.*,
                    IF((actual_from<=NOW())
                and
                    (actual_to>NOW()),1,0) as actual,
                    IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
                from
                    usage_voip
                where
                    client="'.$fixclient.'"
                order by
                    actual
                desc,
                '.$order
            );

            $R=array(); 
            while ($r=$db->NextRecord()) {
                $R[]=$r; 
                if ($r['is_trunk'] == '1') 
                    $has_trunk = true;
            }

            if ($isDbAtsInited)
            {
            }
            foreach ($R as &$r) {
                $r['tarif']=get_tarif_current('usage_voip',$r['id']);
                $r['cpe']=get_cpe_history('usage_voip',$r['id']);

                if ($isDbAtsInited)
                {
                    $r["vpbx"] = virtPbx::number_isOnVpbx($this->fetched_client["id"], $r["E164"]);
                } else {
                    $r["vpbx"] = false;
                }
            }

            $notAcos = array();
            foreach($db->AllRecords("select * from voip_permit where client = '".$fixclient."'") as $p) {
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
            $design->assign('is_vo_view', get_param_raw("action", "") == "vo_view");
            $design->assign('regions', $db->AllRecords('select * from regions order by if(id = 99, "zzz", name)','id') );
            $design->assign('cur_region', $db->GetValue('select region from clients where client="'.$fixclient.'"') );
            $design->AddMain('services/voip.tpl'); 

            if(get_param_raw("action", "") == "vo_view")
                $this->services_vo_permit($fixclient);
        }
    }

    function services_vo_permit($fixclient)
    {
        global $design, $db;

        if(!access("services_voip", "view_reg")) return;
        if(!function_exists("pg_connect")) return;

        $c = ClientCS::FetchClient($fixclient);
        if(!$c) return ;

        $phone = get_param_protected("phone", "");

        $nrs = array();
        $nns = array();
        foreach($db->AllRecords("select region, E164 as number from usage_voip where client = '".$c["client"]."'") as $n)
        {
            $nrs[$n["region"]][] = $n["number"];
            $nns[$n["number"]] = $n["region"];
        }

        $regs = array();
        if($phone && isset($nns[$phone]))
        {
            $regs = $this->getSIPregs($c["client"], $nns[$phone], $phone);
        }else{
            foreach($nrs as $region => $ns)
            {
                $reg = $this->getSIPregs($c["client"], $region, $phone);
                $regs = array_merge($regs, $reg);
            }
        }

        $design->assign('voip_permit',$regs);
        $design->assign('voip_permit_filtred',$phone);
        $design->AddMain('services/voip_permit.tpl'); 
    }

    private function getSIPregs($client, $region, $phone = "")
    {
        $schema = "";
        $needRegion = false;


        if($region == 99)
        {
            $conn = @pg_connect("host=85.94.32.237 dbname=nispd user=www password=dD99zmHRs2hR7PPGEMsg connect_timeout=1");
        }else{

            // Соловьев не переключил старую базу на новую, перед уходом в отпуск
            $dbname = "voipdb";

            $dbHost = str_replace("[region]", $region, R_CALLS_HOST);
        
            if(in_array($region, array(94, 95, 87, 97, 98, 88, 93))) // new schema. scynced
            {
                $schema = "astschema";
                $dbHost = "eridanus.mcn.ru";
                $needRegion = true;
            }

            $conn = @pg_connect($q="host=".$dbHost." dbname=".$dbname." user=".R_CALLS_USER." password=".R_CALLS_PASS." connect_timeout=1");

            if($conn && $schema)
                pg_query("SET search_path TO ".$schema.", \"\$user\", public");
        }

        if (!$conn)
        {
            $reg = Region::first(array("id" => $region));
            trigger_error("Ошибка соединения с сервером регистрации SIP в регионе \"".$reg->name."\" (id: ".$reg->id.")");
            return array();    
        }


        if(!$conn) return;

        if($region != "99")
        {
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
                    b.useragent, n.ds as direction
                    FROM 
                    sipdevices a 
                    INNER JOIN sipregs b ON a.name = b.name
                    LEFT JOIN numbers n ON n.number::varchar = a.callerid
                    WHERE 
                    client='".$client."' ".($needRegion ? "and a.region ='".$region."'" : "")."
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
                    ".($phone ? "callerid='".$phone."' or callerid='74959505680*".$phone."'" : "client='".$client."'")." 
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
                "reg96" => "37.228.82.12",
                "reg97" => "37.228.80.6",
                "reg98" => "37.228.81.6",
                "reg95" => "37.228.85.6",
                "reg94" => "37.228.83.6",
                "reg93" => "37.228.84.6",
                "reg87" => "37.228.86.6",
                "reg88" => "37.228.87.6"
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
                $perm = 'Автопривязка: еще не привязан';
            } else {
                $perm = 'разрешено: '.$permit.'<br>запрещено: '.$l['deny'];
            }
            $l["permit"] = $perm;

            $l["direction"] = isset($dirs[$l["direction"]]) ? $dirs[$l["direction"]] : $l["direction"];

            $regs[] = $l;
        }

        pg_close($conn);
        return $regs;
    }


    function services_vo_delete($fixclient)
    {
        global $db, $user;

        $id = get_param_protected("id", 0);

        $sendError = false;

        if($id)
        {
            $u=$db->GetValue($q = "select id from usage_voip where id = '".$id."' and client='".$fixclient."'");
            if($u)
            {
                $db->Query("delete from usage_voip where id = '".$id."' and client='".$fixclient."'");
            }else{
                trigger_error("unknown error");
            }
        }
    }


    function services_vo_settings_send($fixclient)
    {
        $c = ClientCS::FetchClient($fixclient);
        if(!$c) return ;


        $phone = get_param_protected("phone", "");
        $id = $fixclient;

        global $design, $db, $user;

        $isSent = false;
        $error = false;
        $emails = array();

        $design->assign("log", $db->AllRecords("select * from log_send_voip_settings where client='".$fixclient."' order by id desc limit 30"));

        $e164s = voipRegion::getClientE164s($c["client"]);

        foreach($db->AllRecords("SELECT data as email 
                    FROM `client_contacts` cc, clients c 
                    where c.client = '".$fixclient."' and client_id = c.id and cc.type = 'email' 
                    and is_active 
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

                $msg = voipRegion::getEmailMsg($c["client"], $_e164s);

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
                            "client" => $c["client"],
                            "date" => array("NOW()"),
                            "user" => $user->Get("user"),
                            "email" => $_email,
                            "phones" => implode(", ", $_e164s)
                            )
                        );

                mail(trim($_email), "[MCN] Настройки SIP", $msg, "From: \"Support MCN\" <noreply@mcn.ru>\nContent-Type: text/plain; charset=koi8-r");
                //mail("dga@mcn.ru", "[MCN] Настройки SIP - ".$_email, $msg, "From: \"Support MCN\" <noreply@mcn.ru>\nContent-Type: text/plain; charset=koi8-r");
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
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}

        $id=get_param_integer('id','');

        $db->Query('select * from usage_voip where (client="'.$fixclient.'") and /*(actual_from<=NOW()) and*/ (actual_to>NOW())');
        $R=array(); 
        while ($r=$db->NextRecord()) 
        {
            if (in_array(substr($r['E164'],0,4),array('7095','7495','7499'))) {
                $r['E164_first']=substr($r['E164'],0,4);
                $r['E164_last']=substr($r['E164'],4);
            }else{
                $r['E164_first']="";
                $r['E164_last']=$r['E164'];
            }
            $R[]=$r;
        }
        $design->assign('voip_connections',$R);
                
        $db->Query('select tech_cpe.*,usage_ip_ports.address from tech_cpe left join usage_ip_ports on usage_ip_ports.id=tech_cpe.id_service and tech_cpe.service="usage_ip_ports" where (tech_cpe.client="'.$fixclient.'") and (tech_cpe.actual_from<=NOW()) and (tech_cpe.actual_to>NOW())');
        $R=array(); while ($r=$db->NextRecord()) $R[]=$r;
        $design->assign('voip_devices',$R);
        ClientCS::Fetch($fixclient);
        ClientCS::FetchMain($fixclient);
        Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $design->ProcessEx('../store/acts/voip_act.tpl'); 
    }

    function services_vo_act_trunk($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
    
        $id=get_param_integer('id','');
    
        ClientCS::Fetch($fixclient);
        ClientCS::FetchMain($fixclient);
        Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));
    
        $design->ProcessEx('../store/acts/voip_act_trunk.tpl');
    }
    

    function services_in_dev_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        $client=get_param_raw('client','');
        $db->Query('select * from tech_cpe where (client="'.$client.'") 
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
            if(eregi("([0-9]{3})([0-9]{2})([0-9]{2})", $item, $match))
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
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $db->Query('select * from clients where client="'.$fixclient.'"'); $r=$db->NextRecord();
        $dbf = new DbFormUsageVoip();
        $dbf->SetDefault('client',$fixclient);
        if (isset($_GET['region'])){
            $dbf->SetDefault('region',intval($_GET['region']));
        }else{
            $dbf->SetDefault('region',$r['region']);
        }
        $dbf->Display(array('module'=>'services','action'=>'vo_apply'),'Услуги','Новое VoIP-подключение');
    }
    function services_vo_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageVoip();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        voipNumbers::check();
        if ($result=='delete') {
            header('Location: ?module=services&action=vo_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->nodesign = true;
        $dbf->Display(array('module'=>'services','action'=>'vo_apply'),'Услуги','Редактировать VoIP-подключение');
        $design->AddMain('phone/voip_edit.tpl');
    }
    function services_vo_close($fixclient){
        global $design,$db, $user;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
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
        voipNumbers::check();
        trigger_error('Номер отключен, создайте заявку на отключение');

        $this->services_vo_view($fixclient);
    }

// =========================================================================================================================================
    function services_dn_view($fixclient){
        global $db,$design;
        $this->fetch_client($fixclient);
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

        $db->Query('select domains.*,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual from domains'.($fixclient?' where client="'.$fixclient.'"':'').' ORDER BY IF((actual_from<=NOW()) and (actual_to>NOW()),0,1) ASC,'.$order);
        while ($r=$db->NextRecord()) $R[]=$r;
        $design->assign('domains',$R);
        $design->AddMain('services/domains.tpl'); 
    }
    function services_dn_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $db->Query('select * from clients where client="'.$fixclient.'"'); $r=$db->NextRecord();
        $dbf = new DbFormDomains();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'dn_apply'),'Услуги','Новое доменное имя');
    }
    function services_dn_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormDomains();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=dn_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'dn_apply'),'Услуги','Редактировать доменное имя');
    }
    function services_dn_close($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from domains where id='.$id);
        if (!($r=$db->NextRecord())) return;

        $db->Query('update domains set actual_to=NOW() where id='.$id);
        $this->services_dn_view($fixclient);
    }

// =========================================================================================================================================
    function services_em_view($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }

        $db->Query('
            select
                emails.*,
                IF(
                        (emails.actual_from<=NOW())
                    and
                        (emails.actual_to>NOW())
                ,1,0) as actual,
                count(email_whitelist.id) as count_filters
            from
                emails
            LEFT JOIN
                email_whitelist
            ON
                (
                    (email_whitelist.domain=emails.domain)
                AND
                    (
                        (email_whitelist.local_part="")
                    OR
                        (email_whitelist.local_part=emails.local_part)
                    )
                AND
                    (email_whitelist.sender_address="")
                AND
                    (email_whitelist.sender_address_domain="")
                )
            where
                emails.client="'.$fixclient.'"
            group by
                emails.id
        ');
        $R=array();
        while($r=$db->NextRecord())
            $R[]=$r;
        $design->assign('mails',$R);

        $db->Query($q='
            select
                *
            from
                bill_monthlyadd
            where
                (client="'.$fixclient.'")
            and
                (description LIKE "Виртуальный почтовый сервер%")
        ');

        $R=array(); while ($r=$db->NextRecord()) $R[]=$r;
        $design->assign('mailservers',$R);
        
        $db->Query('
            select
                id
            from
                bill_monthlyadd_reference
            where
                (description LIKE "Виртуальный почтовый сервер%")
        ');
        $r=$db->NextRecord();
        $design->assign('mailservers_id',$r['id']);

        $design->AddMain('services/mail.tpl'); 
    }
    
    function whitelist_load($fixclient,$filter,&$domains,&$mails,&$MCN,&$whlist){
        global $db,$design;
        $M=array(/*0=>'Доставлять всё',*/1=>'Добавлять в тему письма метку ---SPAM---',2=>'Уничтожать');
        $design->assign('em_options',$M);
        
        if ($filter){
            $db->Query('select * from emails where (client="'.$fixclient.'") and (id='.$filter.')');
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

        $domains=array(); $db->Query('select domain from domains where (client="'.$fixclient.'") and (actual_from<=NOW()) and (actual_to>NOW())'.$filter_domain_q);
        while ($r=$db->NextRecord()) $domains[]=$r['domain'];
        $design->assign('domains',$domains);
        
        $MCN=array();
        $db->Query('select emails.* from emails where (client="'.$fixclient.'") and (actual_from<=NOW()) and (actual_to>NOW())'.$filter_mail_q);
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
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer("id",0);
        $mode=get_param_integer("mode",0);
        if (!$id) return;
        $db->Query('select emails.* from emails where (client="'.$fixclient.'") and (id='.$id.')');
        if (!($r=$db->NextRecord())) return;

        if ($mode==2) $mode='discard';
            else if ($mode==1) $mode='mark';
                else $mode='pass';

        if ($r['spam_act']==$mode) return $this->services_em_view($fixclient);
        if ($mode=='pass'){
            $db->Query('update emails set spam_act="pass" where (client="'.$fixclient.'") and (id='.$id.')');
            $db->Query('insert into email_whitelist (local_part,domain,sender_address,sender_address_domain) values ("'.$r['local_part'].'","'.$r['domain'].'","","")');
        } else {
            if ($r['spam_act']=='pass'){
                $db->Query('select count(*) from email_whitelist where (local_part="") and (domain="'.$r['domain'].'") AND (email_whitelist.sender_address="") AND (email_whitelist.sender_address_domain="")');
                $r2=$db->NextRecord();
                if ($r2[0]!=0){
                    trigger_error("На, домене, которому принадлежит этот e-mail, отключена фильрация спама. Сначала включите её вручную.");
                } else {
                    $db->Query('delete from email_whitelist where (local_part="'.$r['local_part'].'") and (domain="'.$r['domain'].'") AND (email_whitelist.sender_address="") AND (email_whitelist.sender_address_domain="")');
                    $db->Query('update emails set spam_act="mark" where (client="'.$fixclient.'") and (id='.$id.')');
                }
            } else {
                $db->Query('update emails set spam_act="'.$mode.'" where (client="'.$fixclient.'") and (id='.$id.')');
            }
        }
        $this->services_em_view($fixclient);
    }

    function services_em_whitelist($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        
        $filter=get_param_integer("filter","");
        $domains=array();
        $mails=array();
        $MCN=array();    //ящики с mcn.ru
        $whlist=array();
        $this->whitelist_load($fixclient,$filter,$domains,$mails,$MCN,$whlist);

        if (!count($mails)) {trigger_error('У вас нет ни одного почтового ящика'); return;}
        $design->AddMain('services/mail_wh_list.tpl'); 
    }
    function services_em_whitelist_delete($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        
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
        
        trigger_error('<script language=javascript>window.location.href="?module=services&action=em_whitelist&filter='.$filter.'";</script>');
    }
    function services_em_whitelist_add($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
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
                trigger_error('Неправильный адрес');
                return;
            }
            $q1[]='domain';
            $q2[]='"'.$domain0.'"';
        }
        if($adr_radio1==0){
            $v=explode('@',$mail1);
            if(count($v)!=2){
                trigger_error('Неправильный адрес');
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
            trigger_error("<script type='text/javascript'>alert('Ошибка! Попробуйте снова. Если ошибка будет повторяться - обратитесь к программисту.')</script>");
        }else
            $db->Query('insert into email_whitelist ('.implode(',',$q1).') values ('.implode(',',$q2).')');
        trigger_error('<script language=javascript>window.location.href="?module=services&action=em_whitelist&filter='.$filter.'";</script>');
    }
    
/*    function services_em_add($fixclient){
        global $design,$db,$user;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
    
        if (!access('services_mail','full')){
            $this->dbmap->hidden['emails'][]='enabled';
            $this->dbmap->hidden['emails'][]='actual_to';
            $this->dbmap->hidden['emails'][]='actual_from';
            $this->dbmap->hidden['emails'][]='box_size';
            $this->dbmap->hidden['emails'][]='box_quota';
            $this->dbmap->hidden['emails'][]='last_modified';
        }

        $R=array('mcn.ru'); $db->Query('select domain from domains where client="'.$fixclient.'"');
        while ($r=$db->NextRecord()) $R[]=$r['domain'];
        $design->assign('domains',$R);

        $this->dbmap->ShowEditForm('emails','',array('client'=>$fixclient,'enabled'=>'1','actual_to'=>'2029-12-31','actual_from'=>'2029-12-31','box_size'=>0,'box_quota'=>50000,'domain'=>'mcn.ru','spam_act'=>'pass'),1);
        $design->AddMain('services/mail_add.tpl');
    }
    function services_em_apply($fixclient){
        global $design,$db;
        if (!access('services_mail','addnew') && !access('services_mail','edit')) return;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        if (!access('services_mail','edit') &&  get_param_raw('dbaction')!='add') {
            trigger_error('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
            return;
        }

        $filter=get_param_integer("filter","");

        if ($this->dbmap->ApplyChanges('emails')!="ok") {
            $this->dbmap->ShowEditForm('emails','',get_param_raw('row',array()));
            $design->AddMain('services/mail_add.tpl');
        } else {
            if (get_param_raw('dbaction')!='delete'){
//                if (!get_param_raw('id','')) $id=$db->GetInsertId();
//                $r=$this->dbmap->SelectRow('emails','id='.$id);
            }
            if (get_param_raw('dbaction')!='delete'){
                if (!get_param_raw('id','')) $id=$db->GetInsertId();
                $r=$this->dbmap->SelectRow('emails','id='.$id);
                $data=$design->fetch('services/mail_new.tpl');
                $headers = "From: MCN Info <info@mcn.ru>\n";
                $headers.= "Content-Type: text/plain; charset=windows-1251\n";
                $r['email']=$r['local_part'].'@'.$r['domain'];
                if (defined('MAIL_TEST_ONLY') && (MAIL_TEST_ONLY==1)) $r['email']='andreys75@mcn.ru, shepik@yandex.ru';
                mail($r['email'],"дНАПН ОНФЮКНБЮРЭ!",$data,$headers);
            }
            trigger_error('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
        }
    }*/
    
    
    function services_em_add($fixclient){
        global $design,$db,$user;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        if($user->Get('user')=='client'){
            $dbf = new DbFormEmailsSimple();    
        }else{
            $dbf = new DbFormEmails();
        }
        $dbf->SetDefault('client',$fixclient);
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
            trigger_error('Не выбран клиент');
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
            $design->ProcessX('empty.tpl');
        } else $dbf->Display(array('module'=>'services','action'=>'em_apply'),'Услуги','Редактировать e-mail ящик');
    }
    
    function services_em_chpass($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;
        $design->assign('email',$r);
        $design->AddMain('services/mail_chpass.tpl');
    }
    function services_em_chreal($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $pass1=get_param_protected('pass1','');
        $pass2=get_param_protected('pass2','');
        $db->Query('select * from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;
        if ($r['client']!=$fixclient) {trigger_error('Клиенты не совпадают'); return; }
        if ($pass1!=$pass2) {
            trigger_error('Пароли не совпадают');
            $this->services_em_chpass($fixclient);
//            trigger_error('<script language=javascript>window.location.href="?module=services&action=em_chpass&id='.$id.'";</script>');
            return;
        }
        $db->Query('update emails set password="'.$pass1.'" where id='.$id);
        trigger_error('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
    }
    function services_em_activate($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select *,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,(actual_from<=NOW()) as save_from from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;

        if ($r['actual']) {
            $db->Query('update emails set actual_to=NOW(),enabled=0 where id='.$id);
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
                $db->Query('update emails set actual_to="2029-12-31",enabled=1 where id='.$id);
            } else {
                $db->Query('update emails set actual_from=NOW(),actual_to="2029-12-31",enabled=1 where id='.$id);
            }
        }
        trigger_error('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
    }
/*    function services_em_toggle($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select *,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual from emails where id='.$id);
        if (!($r=$db->NextRecord())) return;
        if ($r['actual']==0) $r['enabled']=0; else $r['enabled']=1-$r['enabled'];
        $db->Query('update emails set enabled='.$r['enabled'].' where id='.$id);
        trigger_error('<script language=javascript>window.location.href="?module=services&action=em_view";</script>');
    }*/


// =========================================================================================================================================
    function services_ex_view($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $R=array();
        $db->Query($q='
            select
                T.*,
                S.*,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            from
                usage_extra as S
            inner join
                tarifs_extra as T
            on
                T.id=S.tarif_id
            and
                T.status in ("public","special","archive") and T.code not in ("welltime","wellsystem")
            where
                S.client="'.$fixclient.'"'
        );

        while ($r=$db->NextRecord()) {
            if ($r['param_name']) $r['description']=str_replace('%','<i>'.$r['param_value'].'</i>',$r['description']);
            if ($r['period']=='month') $r['period_rus']='ежемесячно'; else
            if ($r['period']=='year') $r['period_rus']='ежегодно';
            $R[]=$r;
        }

        $design->assign('services_ex',$R);
        $design->AddMain('services/ex.tpl'); 
    }
    function services_ex_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id',0);
        $db->Query('select S.*,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,T.* from usage_extra as S inner join tarifs_extra as T ON T.id=S.tarif_id where (S.id="'.$id.'") and (client="'.$fixclient.'")');
        if (!($r=$db->NextRecord())) return;
        if ($r['period']=='month') $r['period_rus']='ежемесячно'; else
        if ($r['period']=='year') $r['period_rus']='ежегодно';
        $design->assign('ad_item',$r);
        ClientCS::Fetch($r['client']);
        $design->ProcessEx('../store/acts/ex_act.tpl'); 
    }
    function services_ex_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageExtra();
        $dbf->SetDefault('client',$fixclient);

        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Новая дополнительная услуга');
    }
    function services_ex_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageExtra();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=ex_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Редактировать дополнительную услугу');
    }
    function services_ex_async($fixclient) {
        global $db,$design,$_RESULT;
        include INCLUDE_PATH."JsHttpRequest.php";
        $JsHttpRequest = new Subsys_JsHttpRequest_Php();
        $JsHttpRequest->setEncoding("koi8-r");
        $id=get_param_integer('id');
        $tarif_table = get_param_protected('tarif_table', 'extra');

        $R=array(); $db->Query('select * from tarifs_'.$tarif_table.' where id='.$id);
        $r=$db->NextRecord();
        $_RESULT=array(
                    'async_price'        => $r['price'].' '.$r['currency'],
                    'async_period'        => $r['period'],
                    'param_name'        => $r['param_name'],
                    'is_countable'        => $r['is_countable'],
                    );
        if (isset($design)) $design->ProcessEx('errors.tpl');
    }
    function services_ex_close($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('update usage_extra set actual_to=NOW() where id='.$id);
        trigger_error('<script language=javascript>window.location.href="?module=services&action=ex_view";</script>');
    }

    function services_ad_view($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $R=array();
        $db->Query('select bill_monthlyadd.*,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d from bill_monthlyadd where client="'.$fixclient.'"');
        while ($r=$db->NextRecord()) {
            if ($r['period']=='day') $r['period_rus']='каждый день'; else
            if ($r['period']=='week') $r['period_rus']='каждую неделю'; else
            if ($r['period']=='month') $r['period_rus']='каждый месяц'; else
            if ($r['period']=='year') $r['period_rus']='каждый год'; else
            if ($r['period']=='once') $r['period_rus']='единожды';
            $R[]=$r;
        }
        $design->assign('adds',$R);
        $design->AddMain('services/ad.tpl'); 
    }
    function services_ad_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id',0);
        $db->Query('select bill_monthlyadd.*,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual from bill_monthlyadd where (id="'.$id.'") and (client="'.$fixclient.'")');
        if (!($r=$db->NextRecord())) return;
        if ($r['period']=='day') $r['period_rus']='каждый день'; else
        if ($r['period']=='week') $r['period_rus']='каждую неделю'; else
        if ($r['period']=='month') $r['period_rus']='каждый месяц'; else
        if ($r['period']=='year') $r['period_rus']='каждый год'; else
        if ($r['period']=='once') $r['period_rus']='единожды';
        $design->assign('ad_item',$r);
        $design->assign('client',$db->GetRow('select * from clients where client="'.$r['client'].'"'));
        $design->ProcessEx('../store/acts/ad_act.tpl'); 
    }
    
    function services_ad_add($fixclient){
        global $db,$design;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormBillMonthlyadd();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'ad_apply'),'Услуги','Доп. услуги лучше заводить <a href="?module=services&action=ex_add">нового образца</a>');    //'Новая доп. услуга'
    }

    function services_ad_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormBillMonthlyadd();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=in_view');    
        } else {
            $dbf->Display(array('module'=>'services','action'=>'ad_apply'),'Услуги','Редактировать доп. услугу');
        }
    }
/*    function services_ad_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $copyid=get_param_integer('copyid',0);
        
        $R=array(''); $db->Query('select * from bill_monthlyadd_reference');
        while ($r=$db->NextRecord()) $R[$r['id']]=$r;
        
        if ($copyid) {
            $price=$R[$copyid]['price'];
            $period=$R[$copyid]['period'];
        } else {
            $price='';
            $period='month';
        }
        $design->assign('copy',$R);
        $dt=getdate();
        $this->dbmap->ShowEditForm('bill_monthlyadd','',array('client'=>$fixclient,'price'=>$price,'period'=>$period,'actual_to'=>'2029-01-01','actual_from'=>date('Y-m-d'),'amount'=>'1'),1);
        $design->assign('copyid',$copyid);
        $design->AddMain('services/ad_add.tpl');
    }
    function services_ad_apply($fixclient){
        global $design,$db,$_POST;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        if ($this->dbmap->ApplyChanges('bill_monthlyadd')!="ok") {
            $this->dbmap->ShowEditForm('bill_monthlyadd','',get_param_raw('row',array()));
            $design->AddMain('services/ad_add.tpl');
        } else {
            if (get_param_raw('dbaction')!='delete'){
                if (!get_param_raw('id','')) $id=$db->GetInsertId();
                $r=$this->dbmap->SelectRow('bill_monthlyadd','id='.$id);
            }
            trigger_error('<script language=javascript>window.location.href="?module=services&action=ad_view";</script>');
        }
    }*/
    function services_ad_close($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from bill_monthlyadd where id='.$id);
        if (!($r=$db->NextRecord())) return;
        $db->Query('update bill_monthlyadd set actual_to=NOW() where id='.$id);
        trigger_error('<script language=javascript>window.location.href="?module=services&action=ad_view";</script>');
//        $this->services_ad_view($fixclient);
    }
    function services_ad_activate($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select *,IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual from bill_monthlyadd where id='.$id);
        if (!($r=$db->NextRecord())) return;
        
        if ($r['actual']) {
            $block=0;
            $db->Query('update bill_monthlyadd set actual_to=NOW() where id='.$id);
        } else {
            $block=0;
            $db->Query('update bill_monthlyadd set actual_from=NOW(),actual_to="2029-01-01" where id='.$id);
        }
        trigger_error('<script language=javascript>window.location.href="?module=services&action=ad_view";</script>');
//        $this->services_ad_view($fixclient);
    }

// =========================================================================================================================================
    function services_it_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $R=array();
        $db->Query('
            select
                T.*,
                S.*,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
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
                S.client="'.$fixclient.'"'
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
    }
    function services_it_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        $dbf = new DbFormUsageITPark();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Новая услуга ITPark');
    }

// =========================================================================================================================================
    function services_virtpbx_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){

            $db->Query($q='
            SELECT
                S.*,
                T.*,
                S.id as id,
                sp.name as server_pbx,
                c.status as client_status,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_virtpbx as S
            LEFT JOIN server_pbx sp ON sp.id = server_pbx_id
            LEFT JOIN clients c ON (c.client = S.client)
            LEFT JOIN tarifs_virtpbx as T ON T.id=S.tarif_id
            HAVING actual
            ORDER BY client,actual_from'

            );

            $R = array();
            $statuses = ClientCS::$statuses;
            while($r=$db->NextRecord()){
                $r["client_color"] = isset($statuses[$r["client_status"]]) ? $statuses[$r["client_status"]]["color"] : false;
                if($r['period']=='month')
                    $r['period_rus']='ежемесячно';
                elseif($r['period']=='year')
                    $r['period_rus']='ежегодно';
                $R[]=$r;
            }

            $design->assign('services_virtpbx',$R);
            $design->AddMain('services/virtpbx_all.tpl');

            //trigger_error('Не выбран клиент');
            return;
        }


        $R=array();
        $db->Query($q='
            SELECT
                T.*,
                S.*,
                S.id as id,
                sp.name as server_pbx,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_virtpbx as S
            LEFT JOIN server_pbx sp ON sp.id = server_pbx_id
            LEFT JOIN tarifs_virtpbx as T ON T.id=S.tarif_id
            WHERE S.client="'.$fixclient.'"'
        );

        $isViewAkt = false;
        while($r=$db->NextRecord()){
            if($r['period']=='month')
                $r['period_rus']='ежемесячно';
            $R[]=$r;

            if($r["actual"] && strpos($r["description"], "Виртуальная АТС пакет") !== false)
            {
                $isViewAkt = $r;
            }
        }

        $design->assign('virtpbx_akt',$isViewAkt);
        $design->assign('services_virtpbx',$R);
        $design->AddMain('services/virtpbx.tpl');
    }
    function services_virtpbx_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        $dbf = new DbFormUsageVirtpbx();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'virtpbx_apply'),'Услуги','Новая услуга Виртальная АТС');
    }
    function services_virtpbx_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageVirtpbx();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=virtpbx_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'virtpbx_apply'),'Услуги','Редактировать дополнительную услугу');
    }

    function services_virtpbx_act($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}

        $id=get_param_integer('id',0);

        if(!$id) {trigger_error('Ошибка в данных'); return;}

        $r = $db->GetRow('select * from usage_virtpbx where (client="'.$fixclient.'") and id ="'.$id.'"');

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

        Company::setResidents($db->GetValue("select firma from clients where client = '".$fixclient."'"));

        $design->assign('d',$r);
                
        $design->ProcessEx('services/virtpbx_act.tpl'); 
    }
// =========================================================================================================================================
    function services_8800_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){

            $design->assign("filter_manager", $filterManager = get_param_protected('filter_manager', ''));
            

            $db->Query($q='
            SELECT
                S.*,
                T.*,
                S.id as id,
                c.status as client_status,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_8800 as S
            LEFT JOIN clients c ON (c.client = S.client)
            LEFT JOIN tarifs_8800 as T ON T.id=S.tarif_id
            '.($filterManager ? "where c.manager = '".$filterManager."'" : "").'
            HAVING actual
            ORDER BY client,actual_from'

            );

            $R = array();
            $statuses = ClientCS::$statuses;
            while($r=$db->NextRecord()){
                $r["client_color"] = isset($statuses[$r["client_status"]]) ? $statuses[$r["client_status"]]["color"] : false;
                if($r['period']=='month')
                    $r['period_rus']='ежемесячно';
                $R[]=$r;
            }

            $m=array();
            $GLOBALS['module_users']->d_users_get($m,'manager');

            $design->assign(
                'f_manager',
                $m
            );

            $design->assign('services_8800',$R);
            $design->AddMain('services/8800_all.tpl');
            return;
        }


        $R=array();
        $db->Query($q='
            SELECT
                T.*,
                S.*,
                S.id as id,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_8800 as S
            LEFT JOIN tarifs_8800 as T ON T.id=S.tarif_id
            WHERE S.client="'.$fixclient.'"'
        );

        $isViewAkt = false;
        while($r=$db->NextRecord()){
            if($r['period']=='month')
                $r['period_rus']='ежемесячно';
            $R[]=$r;
        }

        $design->assign('services_8800',$R);
        $design->AddMain('services/8800.tpl');
    }
    function services_8800_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        $dbf = new DbFormUsage8800();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'8800_apply'),'Услуги','Новая услуга 8800');
    }
    function services_8800_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsage8800();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=8800_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'8800_apply'),'Услуги','Редактировать услугу 8800');
    }

// =========================================================================================================================================
    function services_sms_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){


            $db->Query($q='
            SELECT
                S.*,
                T.*,
                S.id as id,
                c.status as client_status,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_sms as S
            LEFT JOIN clients c ON (c.client = S.client)
            LEFT JOIN tarifs_sms as T ON T.id=S.tarif_id
            HAVING actual
            ORDER BY client,actual_from'

            );

            $R = array();
            $statuses = ClientCS::$statuses;
            while($r=$db->NextRecord()){
                $r["client_color"] = isset($statuses[$r["client_status"]]) ? $statuses[$r["client_status"]]["color"] : false;
                if($r['period']=='month')
                    $r['period_rus']='ежемесячно';
                $R[]=$r;
            }

            $m=array();
            $GLOBALS['module_users']->d_users_get($m,'manager');

            $design->assign(
                'f_manager',
                $m
            );

            $design->assign('services_sms',$R);
            $design->AddMain('services/sms_all.tpl');
            return;
        }


        $R=array();
        $db->Query($q='
            SELECT
                T.*,
                S.*,
                S.id as id,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            FROM usage_sms as S
            LEFT JOIN tarifs_sms as T ON T.id=S.tarif_id
            WHERE S.client="'.$fixclient.'"'
        );

        $isViewAkt = false;
        while($r=$db->NextRecord()){
            if($r['period']=='month')
                $r['period_rus']='ежемесячно';
            $R[]=$r;
        }

        $design->assign('services_sms',$R);
        $design->AddMain('services/sms.tpl');
    }
    function services_sms_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        $dbf = new DbFormUsageSms();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'sms_apply'),'Услуги','Новая услуга CMC');
    }
    function services_sms_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageSms();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=sms_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'sms_apply'),'Услуги','Редактировать услугу CMC');
    }

// =========================================================================================================================================
    function services_welltime_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){

            $db->Query($q='
            select
                T.*,
                S.*,
                c.status as client_status,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            from
                usage_welltime as S
            left join clients c on (c.client = S.client)

            inner join
                tarifs_extra as T
            on
                T.id=S.tarif_id
            and
                T.code in ("welltime")

            having actual
            order by client,actual_from'

            );

            $R = array();
            $statuses = ClientCS::$statuses;
            while($r=$db->NextRecord()){
                $r["client_color"] = isset($statuses[$r["client_status"]]) ? $statuses[$r["client_status"]]["color"] : false;
                if($r['period']=='month')
                    $r['period_rus']='ежемесячно';
                elseif($r['period']=='year')
                    $r['period_rus']='ежегодно';
                $R[]=$r;
            }

            $design->assign('services_welltime',$R);
            $design->AddMain('services/welltime_all.tpl');

            //trigger_error('Не выбран клиент');
            return;
        }
        $R=array();



        /*
        $db->Query($q='
            select
                T.*,
                S.*,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            from
                usage_extra as S
            inner join
                tarifs_extra as T
            on
                T.id=S.tarif_id
            and
                T.code in ("welltime")
            where
                S.client="'.$fixclient.'"'
        );*/
        $db->Query($q='
            select
                T.*,
                S.*,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
                IF((actual_from<=(NOW()+INTERVAL 5 DAY)),1,0) as actual5d
            from
                usage_welltime as S
            inner join
                tarifs_extra as T
            on
                T.id=S.tarif_id
            and
                T.code in ("welltime")
            where
                S.client="'.$fixclient.'"'
        );

        $isViewAkt = false;
        while($r=$db->NextRecord()){
            if($r['period']=='month')
                $r['period_rus']='ежемесячно';
            elseif($r['period']=='year')
                $r['period_rus']='ежегодно';
            $R[]=$r;

        }

        $design->assign('services_welltime',$R);
        $design->AddMain('services/welltime.tpl');
    }
    function services_welltime_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        $dbf = new DbFormUsageWelltime();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'welltime_apply'),'Услуги','Новая услуга Welltime');
    }
    function services_welltime_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageWelltime();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=welltime_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'welltime_apply'),'Услуги','Редактировать дополнительную услугу');
    }
// =========================================================================================================================================
    function services_wellsystem_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $R=array();
        $db->Query($q='
            select
                T.*,
                S.*,
                IF((actual_from<=NOW()) and (actual_to>NOW()),1,0) as actual,
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
                S.client="'.$fixclient.'"'
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
    }
    function services_wellsystem_add($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $db->Query('select * from clients where client="'.$fixclient.'"');
        $r=$db->NextRecord();
        $dbf = new DbFormUsageWellSystem();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'ex_apply'),'Услуги','Новая услуга WellSystem');
    }
// =========================================================================================================================================
    function services_ppp_view($fixclient){
        global $db,$design;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $R = array();
        $db->Query('
            select
                *,
                IF(
                        (actual_from<=NOW())
                    and
                        (actual_to>NOW())
                    and
                        (enabled=1)
                ,1,0) as actual
            from
                usage_ip_ppp
            where
                client = "'.$fixclient.'"
        ');
        while($r=$db->NextRecord())
            $R[]=$r;
        $design->assign('ppps',$R);
        $design->AddMain('services/ppp.tpl'); 
    }
    function services_ppp_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $db->Query('select * from clients where client="'.$fixclient.'"'); $r=$db->NextRecord();
        $dbf = new DbFormUsageExtra();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'services','action'=>'ppp_apply'),'Услуги','Новый ppp-логин');
    }
    function services_ppp_apply($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $dbf = new DbFormUsageExtra();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=services&action=ppp_view');
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'services','action'=>'ppp_apply'),'Услуги','Редактировать ppp-логин');
    }
    function services_ppp_append($fixclient){
        global $design,$db;
        if(!$this->fetch_client($fixclient)){
            trigger_error('Не выбран клиент');
            return;
        }
        $ass = array(); //бгг
        $client = addcslashes($fixclient, "\\'");
        if(isset($_POST['append_ppp_ok'])){
            $query_ins = "
                INSERT INTO
                    `usage_ip_ppp`
                SET
                    `client` = '".$client."',
                    `login` = '".addcslashes($_POST['pppoe_login'], "\\'")."',
                    `password` = '".addcslashes($_POST['pppoe_pass'], "\\'")."',
                    `ip` = '".preg_replace('/^[^0-9\.]+$/','',$_POST['ip_address'])."',
                    `nat_to_ip` = '".preg_replace('/^[^0-9\.]+$/','',$_POST['nat_2_ip'])."',
                    `actual_from` = now(),
                    `actual_to` = now()
            ";
            $db->Query($query_ins);
            trigger_error('Логин успешно добавлен!');
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
                `client`='".$client."'
            order by
                `id`";
        $db->Query($query);
        $ppps = array();
        while($row = $db->NextRecord(MYSQL_ASSOC)){
            $ppps[] = $row;
        }
        if(!count($ppps)){
            trigger_error('У пользователя нет ни одного ppp логина.');
            return;
        }
        $ppp_last = $ppps[count($ppps)-1];
        $sip = explode(".",$ppp_last['ip']);
        if($sip[3]<254)
            $sip[3]++;
        else
            $sip[3] = '000';

        $aff = count($ppps)+1;
        $ass['login'] = $client.$aff;
        $ass['client'] = $fixclient;
        $ass['ip'] = implode('.',$sip);
        $ass['password'] = substr(md5($client.$ass['login'].microtime().rand()),0,8);
        $ass['nat_2_ip'] = $ppp_last['nat_to_ip'];
        $design->assign('ass',$ass);
        $design->AddMain('services/append_ppp.tpl');
    }
/*function services_ppp_add($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}

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
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        if (!access('services_ppp','edit') &&  get_param_raw('dbaction')!='add') {
            trigger_error('<script language=javascript>window.location.href="?module=services&action=ppp_view";</script>');
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
            trigger_error('<script language=javascript>window.location.href="?module=services&action=ppp_view";</script>');
        }
    }*/

    function services_ppp_chpass($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from usage_ip_ppp where id='.$id);
        if (!($r=$db->NextRecord())) return;
        $design->assign('ppp',$r);
        $design->AddMain('services/ppp_chpass.tpl');
    }
    function services_ppp_chreal($fixclient){
        global $design,$db;
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $pass1=get_param_protected('pass1','');
        $pass2=get_param_protected('pass2','');
        $db->Query('select * from usage_ip_ppp where id='.$id);
        if (!($r=$db->NextRecord())) return;
        if ($r['client']!=$fixclient) {trigger_error('Клиенты не совпадают'); return; }
        if ($pass1!=$pass2) {
            trigger_error('Пароли не совпадают');
            $this->services_ppp_chpass($fixclient);
            return;
        }
        $db->Query('update usage_ip_ppp set password="'.$pass1.'" where id='.$id);
        trigger_error('<script language=javascript>window.location.href="?module=services&action=ppp_view";</script>');
    }
    
    function services_ppp_activate($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        if (!$id) return;
        $db->Query('select * from usage_ip_ppp where (id='.$id.') and (client="'.$fixclient.'")');
        if (!($r=$db->NextRecord())) return;
        
        if ($r['enabled']) {
            $db->Query('update usage_ip_ppp set enabled=0,actual_to=NOW() where id='.$id);
        } else {
            $db->Query('update usage_ip_ppp set enabled=1,actual_from=NOW(),actual_to="2029-01-01" where id='.$id);
        }
        $this->services_ppp_view($fixclient);
    }

    function services_ppp_activateall($fixclient){
        global $design,$db;    
        if (!$this->fetch_client($fixclient)) {trigger_error('Не выбран клиент'); return;}
        $value=get_param_integer('value',0); if ($value) $value=1;
        if ($value==0){
            $db->Query('update usage_ip_ppp set enabled=0 where (client="'.$fixclient.'")');
        } else {
            $db->Query('update usage_ip_ppp set enabled=1 where (client="'.$fixclient.'") and (actual_from<=NOW()) and (actual_to>NOW())');            
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
        $db->Query($q='
            SELECT
                usage_ip_ports.*,
                IF((usage_ip_ports.actual_from<=NOW()) and (usage_ip_ports.actual_to>NOW()),1,0) as actual,
                tech_ports.port_name as port,
                tech_ports.node,
                tech_ports.port_type,
                IF(usage_ip_ports.actual_from<=(NOW()+INTERVAL 5 DAY),1,0) as actual5d '.$select.'
            FROM
                usage_ip_ports
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
                '.($wh?$wh:'(usage_ip_ports.client="'.$client.'")').'
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
/*            $r['cpe']=$db->AllRecords('select tech_cpe.*,type,vendor,model from tech_cpe '.
                    'LEFT JOIN tech_cpe_models ON tech_cpe_models.id=tech_cpe.id_model '.
                    'where tech_cpe.service="usage_ip_ports" and tech_cpe.id_service="'.$r['id'].'" '.
                    'AND tech_cpe.actual_from<=NOW() and tech_cpe.actual_to>=NOW() '.
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
        $db->Query('select * from clients where (client="'.$fixclient.'")');
        if (!($r=$db->NextRecord())) return 0;
        $design->assign('client',$r);
        $this->fetched_client=$r;
        return 1;
        
    }
    function client_ports_lock($client,$val){
        global $db;
        $db->Query('update usage_ip_ppp set enabled="'.$val.'" where client="'.$client.'"');
        return (($db->AffectedRows()>0)?1:0);
    }
    function routes_check($client){
        global $db;
        $db->Query("select count(*) from usage_ip_ports where (actual_to>NOW()) and (client=\"{$client}\")");
        $r=$db->NextRecord();
        if (isset($r[0]) && $r[0]=="0"){
            trigger_error("Клиенту установлен статус \"отключен\"");
            $db->Query("update clients set status=\"disabled\" where client=\"{$client}\"");
            $db->Query("select count(*) from usage_voip where (actual_from<=NOW()) and (actual_to>=NOW()) and (client=\"{$client}\")");
            $r=$db->NextRecord();
            if (isset($r[0]) && ($r[0])) trigger_error("Внимание! У клиента осталась IP-телефония.");
        } else {
            $db->Query("select status from clients where client=\"{$client}\"");
            $r=$db->NextRecord();
            if ($r[0]!="work") {
            trigger_error("Клиенту установлен статус \"включен\"");
                $db->Query("update clients set status=\"work\" where client=\"{$client}\"");
            }
        }
    }

    function services_e164(){
        global $db,$design;

        $free_or_non = get_param_raw('free_or_non',null);
        $pref = get_param_raw('pref',null);
        if(!is_null($pref)){
            $sp = array();
            foreach($pref as $v)
                $sp[] = (int)$v;
            $fqwp = 'and substring(`vn`.`number` from 1 for 4) in ('.implode(',',$sp).')';
            $nqwp = 'and substring(`uv`.`e164` from 1 for 4) in ('.implode(',',$sp).')';
        }else{
            $fqwp = '';
            $nqwp = '';
        }
        $is = get_param_raw('is',null);
        $si = '';
        if(!is_null($is) && count($is)<3){
            $spec = array();
            if(in_array('just',$is)){
                $spec[]="'0'";
            }
            if(in_array('special1',$is)){
                $spec[] = "'1'";
            }
            if(in_array('special2',$is)){
                $spec[] = "'2'";
            }
            if(in_array('special3',$is)){
                $spec[] = "'3'";
            }
            if(in_array('special4',$is)){
                $spec[] = "'4'";
            }
            if(count($spec)>0)
                $si .= "    and `vn`.`beauty_level` in (".implode(',',$spec).") ";

            if(in_array('reserved',$is)){
                $si .= "    and `vn`.`client_id` is not null ";

            }

        }
        $is_our = get_param_raw('is_our','');
        $design->assign('is_our',$is_our);
        if ($is_our == 'our')
            $of = ' and vn.client_id=764 ';
        elseif($is_our == 'reserve')
            $of = ' and vn.client_id is not null and vn.client_id <> 764 ';
        elseif($is_our == 'alien')
            $of = ' and (vn.client_id is null or vn.client_id <> 764) ';
        else
            $of = '';

        $filter_count_calls = get_param_raw('filter_count_calls',null);
        $count_calls = get_param_raw('count_calls',null);
        $design->assign('filter_count_calls',$filter_count_calls);
        $design->assign('count_calls',$count_calls);

        if(!is_null($filter_count_calls) && !is_null($count_calls) && preg_match('/^(=|<|>)?\d+$/',$count_calls,$m)){
            if(!isset($m[1]) || !$m[1]){
                $cc = '='.$count_calls;
            }else
                $cc = $count_calls;
            $cc = " and `vn`.`nullcalls_last_2_days` ".$cc;
        }else
            $cc = '';

        $num_prefs_query = "
            select
                distinct substring(`number` from 1 for 4) `pref`
            from
                `voip_numbers`
            order by
                `number`
        ";

        $numbers_free = "

            select *,
                   if(date_add(ifnull(actual_to,'2000-01-01'), interval 6 month) <= now(), 'Y', 'N') as to_add
            from (
            select
                `vn`.*,
                substring(`number` from 1 for 4) `pref`,
                (select max(actual_to) as actual_to from usage_voip uv where uv.e164=vn.number) as actual_to
            from
                `voip_numbers` `vn`
            where
                `vn`.`usage_id` is null
            ".$fqwp.$si.$of.$cc."
            order by
                `vn`.`our` desc,
                ifnull(actual_to, '2000-01-01'),
                `vn`.`number`
                    )f
        ";


        $numbers_non_free = "
            select
                `uv`.`actual_from`,
                `uv`.`actual_to`,
                `uv`.`client`,
                `uv`.`e164`,
                `uv`.`no_of_lines`,
                `uv`.`status`,
                substring(vn.`number` from 1 for 4) `pref`,
                `vn`.*
            from
                `voip_numbers` `vn`
            left join
                `usage_voip` `uv`
            on
                `vn`.`usage_id` = `uv`.`id`
            where `vn`.usage_id is not null
            ".$nqwp.$si.$of."
            order by
                `vn`.`number`
        ";

        $prefses = array_keys($db->AllRecords($num_prefs_query,'pref',MYSQL_ASSOC));

        $subaction = get_param_raw('sub', '');
        $design->assign('sub',$subaction);
        if($subaction == 'show'){
            $design->assign('fon',$free_or_non);
            $design->assign('pref_',$pref);
            $design->assign('is',$is);

            if(is_null($free_or_non) || in_array('free',$free_or_non)){
                $fn = $db->AllRecords($numbers_free,null,MYSQL_ASSOC);
                $design->assign('free_nums',$fn);
                $design->assign('free_count',count($fn));
            }
            if(is_null($free_or_non) || in_array('nonfree',$free_or_non)){
                $nf = $db->AllRecords($numbers_non_free,null,MYSQL_ASSOC);
                $design->assign('nonfree_nums',$nf);
                $design->assign('nonfree_count',count($nf));
            }
        }

        $design->assign('prefs',$prefses);
        $design->assign('prefs_len',count($prefses));

        $design->AddMain('services/voip_e164_pane.tpl');
    }
    function services_e164_edit($fixclient){
        global $db, $design, $user;
        $e164 = get_param_protected('e164','');
        $reserve = get_param_raw('reserve',null);
        if(!is_null($reserve)){
            $reserve = (int)$reserve;
            if ($reserve == 0) $reserve = 'NULL';
            $db->Query($q = "
                update
                    `voip_numbers`
                set
                    `client_id`=".$reserve.",
                    edit_user_id=".$user->Get('id')."
                where usage_id is null and
                    `number` = '".$e164."'
            ");
            header('location: ?module=services&action=e164_edit&e164='.$e164);
            die();
        }
        $query = "
                select
                    `vn`.*,
                    `cl`.`client`,
                    `cl`.`company_full`,
                    `vn`.`nullcalls_last_2_days` `count_calls`
                from
                    `voip_numbers` `vn`
                left join
                    `clients` `cl`
                on
                    `cl`.`id` = `vn`.`client_id`
                where
                    `vn`.`number` = '".$e164."'";

        $num = $db->AllRecords($query, null, MYSQL_ASSOC);

        $log = $db->AllRecords("
                select
                    date_format(`es`.`time`,'%Y-%m-%d %H:%i:%s') `human_time`,
                    `uu`.`user`,
                    `es`.`user` `user_id`,
                    `cl`.`client`,
                    `es`.`client` `client_id`,
                    `es`.`addition`,
                    `es`.`action`
                from
                    `e164_stat` `es`
                left join
                    `clients` `cl`
                on
                    `cl`.`id` = `es`.`client`
                left join
                    `user_users` `uu`
                on
                    `uu`.`id` = `es`.`user`
                where
                    `es`.`e164`='".$e164."' and `es`.`action`<>'nullCall'
                order by
                    `es`.`time` desc
            ");

        if(count($num)==0){
            header('location: ?module=services&action=e164');
            die();
        }

        $n = $num[0];
        $design->assign('e164',$n['number']);
        $design->assign('is_using',($n['usage_id']!='')?true:false);
        $design->assign('is_free',($n['client_id']=='')?true:false);
        $design->assign('is_reserved',($n['client_id']!='' && $n['usage_id']=='')?true:false);
        $design->assign('beauty_level',$n['beauty_level']);
        $design->assign('client',$n['client']);
        $design->assign('usage_id',$n['usage_id']);
        $design->assign('client_id',$n['client_id']);
        $design->assign('company',$n['company_full']);
        $design->assign('count_calls',$n['count_calls']);
        $design->assign('current_client',$fixclient);
        $design->assign('current_client_id',$db->GetValue("select * from clients where '".$fixclient."' in (client, id)"));
        $design->assign('logs',$log);
        $design->AddMain('services/voip_e164_edit.tpl');
    }
    function services_get_tarifs($fixclient)
    {
        global $db;
        $region = get_param_protected('region','');
        $Res = array();
        $C = $db->GetRow('select * from clients where client="'.$fixclient.'"');
        $R=$db->AllRecords('select status, id, name, month_number, month_line, dest, month_min_payment from tarifs_voip where currency="'.$C['currency'].'" and region="'.$region.'" '.
                'order by status, month_line, month_min_payment', 'id');
        foreach ($R as $r) {
            $Res[$r['id']] = array(
                            'id'=>$r['id'],
                            'name'=>Encoding::toUtf8($r['name']),
                            'month_number'=>$r['month_number'], 
                            'month_line'=>$r['month_line'], 
                            'status'=>$r['status'], 
                            'dest'=>$r['dest'],
                            'month_min_payment'=>$r['month_min_payment']
            );
        }

        echo json_encode($Res);
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
                FROM ".($region == 99 ? "sip_users" : "sipdevices")." WHERE client='".$client."' ".($region != 99 ? "and region = '".$region."'" : "")."
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

            $e164s[$callerId] = $l["callerid"];
            self::$e164Region[$callerId] = $region;
        }

        return $e164s;
    }

    static private function _getRegions($client)
    {
        global $db;

        $rs = array();
        foreach($db->AllRecords("select distinct region from usage_voip where client = '".$client."' order by region desc") as $r)
            $rs[] = $r["region"];

        return $rs;
    }

    static public function getEmailMsg($client, $needSendE164)
    {
        $a = array();
        foreach($needSendE164 as $n)
        {
            if(!isset($a[self::$e164Region[$n]]))
                $a[self::$e164Region[$n]] = array();

            $a[self::$e164Region[$n]][] = $n;
        }


        $msgHeader = "Здравствуйте!\n";

        $msg = "";
        foreach($a as $region => $numbers)
            $msg .= self::getRegionRegs($client, $region, $numbers);

        return $msg ? $msgHeader.$msg : false;
    }

    static private function getRegionRegs($client, $region, $_e164s)
    {
        self::__db_connect($region);

        $pbx = array(
                "ast244" => "85.94.32.244",
                "ast245" => "85.94.32.245",
                "ast248" => "85.94.32.248",
                "reg96" => "37.228.82.12",
                "reg97" => "37.228.80.6",
                "reg98" => "37.228.81.6",
                "reg95" => "37.228.85.6",
                "reg94" => "37.228.83.6",
                "reg93" => "37.228.84.6",
                "reg87" => "37.228.86.6",
                "reg88" => "37.228.87.6"
                );

        $callerids = $names = array();
        foreach($_e164s as $e)
        {
            if(strpos($e, "trunk:") !== false)
                $names[] = str_replace("trunk:", "", $e);
            else
                $callerids[] = $e;
        }

        $result = pg_query(
                $q = "SELECT *,name, callerid, permit, deny, secret, 
                ".($region == 99  ? "":"'reg".$region."' as ")." ippbx
                FROM ".($region == 99 ? "sip_users" : "sipdevices")." 
                WHERE client = '".$client."' 
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
                "Номер телефона: ".($l["callerid"]? $l["callerid"]: "***trunk***")."\n".
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
        }else{
            $dbname = "voipdb";
            $dbHost = str_replace("[region]", $region, R_CALLS_HOST);
            $schema = "";

            if(in_array($region, array(94, 95, 87, 97, 98, 88, 93))) // new schema. scynced
            {
                $schema = "astschema";
                $dbHost = "eridanus.mcn.ru";
            }

            $conn = pg_connect($q = "host=".$dbHost." dbname=".$dbname." user=".R_CALLS_USER." password=".R_CALLS_PASS);
            if($schema)
                pg_query("SET search_path TO ".$schema.", \"\$user\", public");
        }
    }
}
?>
