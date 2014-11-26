<?
class m_routers {
    var $actions=array(
        'default'                => array('',''),
        'r_list'                => array('routers_routers','r'),
        'r_view'                => array('routers_routers','r'),
        'r_edit'                => array('routers_routers','r'),
        'r_add'                    => array('routers_routers','add'),
        'r_apply'                => array('routers_routers','edit'),
        'm_list'                => array('routers_models','r'),
        'm_add'                    => array('routers_models','w'),
        'm_apply'                => array('routers_models','w'),
        'd_act'                    => array('routers_devices','r'),
        'd_list'                => array('routers_devices','r'),
        'd_snmp'                => array('routers_devices','r'),
        'd_edit'                => array('routers_devices','r'),
        'd_add'                    => array('routers_devices','add'),
        'd_apply'                => array('routers_devices','edit'),
        'd_async'                => array('routers_devices','r'),
//        'modem_list'            => array('routers_modems','r'),
        'n_list'                => array('routers_nets','r'),
        'n_edit'                => array('routers_nets','r'),
        'n_add'                    => array('routers_nets','r'),
        'n_apply'                => array('routers_nets','r'),
        'n_report'                => array('routers_nets','r'),
        'n_acquire_as'            => array('',''),

        'datacenter_list'        => array('routers_routers','r'),
        'datacenter_view'        => array('routers_routers','r'),
        'datacenter_edit'        => array('routers_routers','r'),
        'datacenter_add'        => array('routers_routers','add'),
        'datacenter_apply'        => array('routers_routers','edit'),

        'server_pbx_list'        => array('routers_routers','r'),
        'server_pbx_view'        => array('routers_routers','r'),
        'server_pbx_edit'        => array('routers_routers','r'),
        'server_pbx_add'        => array('routers_routers','add'),
        'server_pbx_apply'        => array('routers_routers','edit'),

/*        'ports'                    =>array('routers_devices','add'),
        'ports'                    =>array('routers_devices','delete'),
        'ports_nodes'            =>array('routers_devices','add'),
        'ports_nodes'            =>array('routers_devices','delete'),
        'ports_names'            =>array('routers_devices','add'),
        'ports_names'            =>array('routers_devices','delete')
*/    );
    var $menu=array(
        array('Тех. площадка',            'datacenter_list'),
        array('Сервера АТС',            'server_pbx_list'),
        array('Роутеры',                'r_list'),
        array('Клиентские устройства',    'd_list'),
        array('SNMP устройства',        'd_snmp'),
        array('Модели CPE-устройств',    'm_list'),
//        array('Порты',                    'ports'),
//        array('Каналы',                    'modem_list'),
        array('Сети',                    'n_list'),
    );
    var $routers;
    var $devices;
    var $modems;

    function Init($fixclient){}

    function load_routers($fixclient){
        global $db,$design;
        if (is_array($this->routers)) return;
        $add=$fixclient?' where (usage_ip_ports.client="'.$fixclient.'")':'';
//TODO9
//        $db->Query('select * from tech_routers left join usage_ip_ports on usage_ip_ports.node=tech_routers.router'.$add.' group by tech_routers.router order by phone');
        $db->Query('select * from tech_routers');
        $this->routers=array();
        while ($r=$db->NextRecord()) $this->routers[$r['router']]=$r;
        $design->assign_by_ref('routers',$this->routers);
    }
    function load_devices($fixclient,$search='',$hide_linked = 0, $snmp_only = 0, $limit=0, $offset=0){
        global $db,$design;
        if (is_array($this->devices)) return;
        $add=$fixclient?' where (tech_cpe.client="'.$fixclient.'")':'';
        $addJ=''; $selJ = '';
        if ($search){
            if ($add) $add.=' and '; else $add=' where ';
            $add.='(INSTR(ip,"'.$search.'") OR INSTR(ip_nat,"'.$search.'") OR INSTR(numbers,"'.$search.'")'.(strlen($search) > 4 ? ' or serial like "%'.$search.'%"' : '').')';
        }
        if ($hide_linked) {
            if ($add) $add.=' and '; else $add=' where ';
            $add.='(service="" or id_service=0)';
        }
        if ($snmp_only) {
            if ($add) $add.=' and '; else $add=' where ';
            $add.='(tech_cpe.snmp=1)';
            $addJ.=' LEFT JOIN usage_ip_ports ON usage_ip_ports.id=tech_cpe.id_service AND tech_cpe.service="usage_ip_ports"';
            $addJ.=' LEFT JOIN tech_ports ON tech_ports.id=usage_ip_ports.port_id';
            $selJ.='usage_ip_ports.address,tech_ports.node,tech_ports.port_name,';
        }
        if($limit>0)
            $lim = ' limit '.$offset.', '.$limit;
        else
            $lim = '';
        $db->Query($q='select sql_calc_found_rows tech_cpe.*,tech_cpe_models.vendor,'.$selJ.
                        'tech_cpe_models.model,IF((tech_cpe.actual_from<=NOW()) and (tech_cpe.actual_to>NOW()),1,0) as actual'.
                        ' from tech_cpe INNER JOIN tech_cpe_models ON tech_cpe_models.id=tech_cpe.id_model '.
                        $addJ.$add.
                        ' order by actual desc,tech_cpe.id asc'.$lim);

        //echo $q;

        $this->devices=array();
        while ($r=$db->NextRecord()) $this->devices[$r['id']]=$r;
        $design->assign_by_ref('devices',$this->devices);
        $ret = $db->GetRow('select found_rows() `cnt`');
        return $ret['cnt'];
    }

    function m_routers(){}

    function GetMain($action,$fixclient){
        if (!isset($this->actions[$action])) return;
        $act=$this->actions[$action];
        if (!access($act[0],$act[1])) return;

        require_once INCLUDE_PATH.'db_map.php';
        $this->dbmap=new Db_map_nispd();
        $this->dbmap->SetErrorMode(2,0);
        call_user_func(array($this,'routers_'.$action),$fixclient);
    }
    function GetPanel($fixclient){
        $R=array(); $p=0;
        foreach($this->menu as $val){
            if ($val=='') {
                $p++;
                $R[]='';
            } else {
                $act=$this->actions[$val[1]];
                if (access($act[0],$act[1])) $R[]=array($val[0],'module=routers&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
            }
        }
        if (count($R)>$p){
            return array('Аппаратура',$R);
        }
    }

    function GetIP_PPP($port_id){
        global $db;
        $db->Query('select * from usage_ip_ppp where port_id="'.$port_id.'"');
        $R=array(); while ($r=$db->NextRecord()) $R[$r['id']]=$r;    
        return $R;
    }
    function GetIP_Router($port_id){
        global $db;
        $db->Query('select * from usage_ip_routes where port_id="'.$port_id.'"');
        $R=array(); while ($r=$db->NextRecord()) $R[$r['id']]=$r;    
        return $R;
    }

    function routers_default($fixclient){
        if (access('routers_routers','r')) {$this->routers_r_list($fixclient); return;}
        if (access('routers_devices','r')) {$this->routers_d_list($fixclient); return;}
        if (access('routers_modems','r'))  {$this->routers_modem_list($fixclient); return;}
    }
    function routers_ports($fixclient){
        if(access('routers_devices','add') && access('routers_devices','delete')){
            global $db,$design;
            $mode = get_param_protected("mode",'view');
            switch($mode){
                case 'view':{
                    $rows = $db->AllRecords("
                        SELECT
                            DISTINCT port_type
                        FROM
                            tech_ports
                        ORDER BY
                            port_type
                    ",null,MYSQL_ASSOC);
                    $ret = array();
                    foreach($rows as $row){
                        $ret[] = $row['port_type'];
                    }
                    $design->assign_by_ref('elements',$ret);
                    $design->assign('mode','types');
                    $design->AddMain('routers/ports_view.tpl');
                    break;
                }
            }
        }
    }
    function routers_ports_nodes($fixclient){
        if(access('routers_devices','add') && access('routers_devices','delete')){
            global $db,$design;
            $mode = get_param_protected("mode",'view');
            $type = get_param_protected("port_type",null);
            if(!$type)
                return $this->routers_ports($fixclient);

            switch($mode){
                case 'view':{
                    $rows = $db->AllRecords("
                        SELECT
                            DISTINCT node
                        FROM
                            tech_ports
                        WHERE
                            port_type = '".$type."'
                        AND
                            node <> ''
                        ORDER BY
                            node
                    ",null,MYSQL_ASSOC);
                    $ret = array();
                    foreach($rows as $row){
                        $ret[] = $row['node'];
                    }

                    $rows = $db->AllRecords("
                        SELECT
                            r.node,
                            IF(LENGTH(tp.node) OR tp.node='','Y','N') exist,
                            count(*) cnt
                        FROM
                            routers r
                        LEFT JOIN
                            tech_ports tp
                        ON
                            tp.node = r.node
                        AND
                            tp.port_type = '".$type."'
                        WHERE
                            r.node <> ''
                        GROUP BY
                            r.node,
                            tp.node
                    ",null,MYSQL_ASSOC);
                    $nodes = array();
                    foreach($rows as $row){
                        $nodes[$row['node']] = $row;
                    }

                    $design->assign_by_ref('elements',$ret);
                    $design->assign_by_ref('routers', $nodes);
                    $design->assign('type',$type);
                    $design->assign('mode','nodes');
                    $design->AddMain('routers/ports_view.tpl');
                    break;
                }
            }
        }
    }
    function routers_ports_names($fixclient){
        if(access('routers_devices','add') && access('routers_devices','delete')){
            global $db,$design;
            $mode = get_param_protected("mode",'view');
            $node = addcslashes(get_param_protected("node",null),"'\\");
            $port_type = addcslashes(get_param_protected("port_type",null),"'\\");
            if(!$node)
                return $this->routers_ports_nodes($fixclient);

            switch($mode){
                case 'rpc':{
                    $rows = $db->AllRecords("
                        SELECT
                            *
                        FROM
                            `tech_ports`
                        WHERE
                            `port_type`='".$port_type."'
                        AND
                            `node`='".$node."'
                    ",null,MYSQL_ASSOC);
                    $json = '{';
                    $i = 0;
                    foreach($rows as $port){
                        $json .= '\''.$port['id'].'\':{';
                        foreach($port as $key=>$val){
                            $json .= "'".$key."':'".addcslashes($val,"'\\")."',";
                        }
                        $json = substr($json,0,strlen($json)-1).'},';
                    }
                    $json = substr($json, 0, strlen($json)-1).'}';
                    header("Content-Type: text/plain; charset=utf-8");
                    echo $json;
                    exit();
                }
                case 'view':
                default:{
                    $rows = $db->AllRecords("
                        SELECT
                            DISTINCT port_name
                        FROM
                            tech_ports
                        WHERE
                            port_type = '".$type."'
                        AND
                            node = '".$node."'
                        ORDER BY
                            port_name
                    ",null,MYSQL_ASSOC);
                    $ena = array();
                    foreach($rows as $row){
                        $ena[] = $row['port_name'];
                    }
                    $rows = $db->AllRecords("
                        SELECT
                            DISTINCT port_name
                        FROM
                            tech_ports
                        WHERE
                            port_type = '".$type."'
                        AND
                            node <> '".$node."'
                        ORDER BY
                            port_name
                    ",null,MYSQL_ASSOC);
                    $dis = array();
                    foreach($rows as $row){
                        $dis[] = $row['port_name'];
                    }
                    $design->assign_by_ref('enable',$ena);
                    $design->assign_by_ref('disable',$dis);
                    $design->assign('type',$type);
                    $design->assign('node',$node);
                    $design->assign('mode','ports');
                    $design->AddMain('routers/ports_view.tpl');
                    break;
                }
            }
        }
    }

    function routers_r_list($fixclient){
        global $db,$design;
        $id = get_param_protected('id' , '');
        if ($id) return $this->routers_r_view($fixclient);
        $this->load_routers($fixclient);
        $design->AddMain('routers/main_routers.tpl');
    }
    function routers_r_view($fixclient){
        global $db,$design;
        $this->load_routers($fixclient);
        $id = get_param_protected('id' , '');
        if (!isset($this->routers[$id])) {trigger_error2('Такого роутера не существует'); return; }

        $so = get_param_integer ('so', 0);
        $order = $so ? 'desc' : 'asc';
        switch ($sort=get_param_integer('sort',1)){
            case 2: $order='port_id '.$order; break;
            case 3: $order='actual_from '.$order.',actual_to '.$order; break;
            case 4: $order='address '.$order; break;
            default: $order='client '.$order; break;    //=1
        }

        $db->Query('select usage_ip_ports.*,tech_ports.port_name as port,tech_ports.node,clients.company as client_company from usage_ip_ports '.
                        'left join clients on usage_ip_ports.client=clients.client '.
                        'left join tech_ports on tech_ports.id=usage_ip_ports.port_id '.
                        'where (tech_ports.node="'.$id.'")'.($fixclient?' and (usage_ip_ports.client="'.$fixclient.'")':'').' order by '.$order);
        $i=0;
        $R=array(); while ($r=$db->NextRecord()) $R[$r['id']]=$r;
        foreach ($R as $i=>$v){
            $R[$i]['ip_ppp']=$this->GetIP_PPP($i);
            $R[$i]['ip_routes']=$this->GetIP_Router($i);
        }
        $design->assign('so',$so);
        $design->assign('sort',$sort);
        $design->assign_by_ref('router',$this->routers[$id]);
        $design->assign('router_clients',$R);

        $R = array();
        $db->Query('select R.net,P.client,P.id,IF (R.actual_from<NOW() and R.actual_to>NOW() and P.actual_from<NOW() and P.actual_to>NOW(),1,0) as active from usage_ip_routes as R inner join usage_ip_ports as P ON P.id=R.port_id INNER JOIN tech_ports as TP ON TP.id=P.port_id WHERE TP.node="'.$this->routers[$id]['router'].'" ORDER BY R.actual_to DESC');
        while ($r=$db->NextRecord()) {
            $b=1;
            if ($b && isset($R[$r['net']])) $b=0;
            if ($b) {
                foreach ($R as $k=>$v) if (mask_match($r['net'],$k) || mask_match($k,$r['net'])) {$b=0; break;}
            }
            if ($b) $R[$r['net']]=$r;
        }
        ksort($R);
        $design->assign('nets',$R);
        $design->AddMain('routers/main_router.tpl');
    }
    function routers_r_edit($fixclient){
        global $db,$design;
        $this->load_routers($fixclient);
        $router = get_param_protected('router' , '');
        $this->dbmap->ApplyChanges('tech_routers');
        $this->dbmap->ShowEditForm('tech_routers','tech_routers.router="'.$router.'"',array(),1);
        $design->assign('router',$router);
        $design->AddMain('routers/db_r_edit.tpl');
    }
    function routers_r_add($fixclient){
        global $design;
        $this->dbmap->ShowEditForm('tech_routers','',array('actual_from'=>'2029-01-01','actual_to'=>'2029-01-01'),1);
        $design->AddMain('routers/db_r_add.tpl');
    }
    function routers_r_apply($fixclient){
        global $db,$design;
        if (($this->dbmap->ApplyChanges('tech_routers')!="ok") && (get_param_protected('dbaction','')!='delete')) {
            $this->dbmap->ShowEditForm('tech_routers','',get_param_raw('row',array()));
            $design->AddMain('routers/db_r_add.tpl');
        } else {
            trigger_error2('<script language=javascript>window.location.href="?module=routers&action=r_list";</script>');
        }
    }

    function routers_d_list($fixclient,$hide_linked = 0){
        global $db,$design;
        $page_count = 50;
        $search=get_param_protected('search' , '');
        $design->assign('search',$search);
        $page = get_param_raw('page',0);
        if($page>0)$page--;
        $pages_count = $this->load_devices($fixclient,$search,$hide_linked,0,$page_count,$page*$page_count);
        $pages_count = $pages_count/$page_count;
        if((int)$pages_count<$pages_count)
            $pages_count = ceil($pages_count);
        $design->assign('cur_page',$page+1);
        $design->assign('pages_count',$pages_count);
        $design->AddMain('routers/main_devices.tpl');
    }
    function routers_d_snmp($fixclient){
        global $db,$design;
        $this->load_devices($fixclient,'',0,1);
        $design->AddMain('routers/snmp_devices.tpl');
    }
    function routers_d_add($fixclient){
        global $design,$db;
        $db->Query('select * from clients where client="'.$fixclient.'"'); $r=$db->NextRecord();
        $dbf = new DbFormTechCPE();
        $dbf->SetDefault('client',$fixclient);
        $dbf->Display(array('module'=>'routers','action'=>'d_apply'),'Клиентские устройства','Новое устройство');
    }
    function routers_d_edit($fixclient){
        global $design,$db;
        $db->Query('select * from clients where client="'.$fixclient.'"'); $r=$db->NextRecord();
        $id=get_param_integer('id','');
        $dbf = new DbFormTechCPE();
        if ($id) $dbf->Load($id);
        $dbf->SetDefault('client',$fixclient);
        $design->assign('client',$r);
        $dbf->Display(array('module'=>'routers','action'=>'d_apply'),'Клиентские устройства','Редактирование');
    }
    function routers_d_apply($fixclient){
        global $design,$db;
        $dbf = new DbFormTechCPE();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=routers&action=d_list');
            exit;
            $design->ProcessX('empty.tpl');
        }
        $dbf->Display(array('module'=>'routers','action'=>'d_apply'),'Клиентские устройства','Редактирование');
    }
    function routers_d_async($fixclient) {
        global $db;
        $id_model=get_param_integer('id_model',0);
        $res=get_param_protected('res');
        $client=get_param_raw('client','');

        $R=array();
        $R['0']='';
        if ($res=='client') {
            if (!$client) {
                $sql='select clients.client as id,clients.client as text from clients ';
                $sql.=' INNER JOIN usage_ip_ports ON clients.client=usage_ip_ports.client';
                $sql.=' WHERE clients.status="work"';
                $sql.=' GROUP BY usage_ip_ports.client';
                $db->Query($sql);
                while ($r=$db->NextRecord()) $R[$r['id']]=$r['text'];
            } else $R[$client]=$client;
        } else {
            //printdbg($db->AllRecords("select * from usage_ip_ports where client='".$client."'"));
            //printdbg($db->AllRecords("select * from tech_ports where id = 7803"));
            $id=get_param_integer('id',0);
            $sql='select usage_ip_ports.id,
                concat(usage_ip_ports.id," - ",
                        if(tech_ports.node is not null , concat(tech_ports.node,"::",tech_ports.port_name," - "), ""),
                        usage_ip_ports.address) as text 
                    from usage_ip_ports';
            $sql.=' LEFT JOIN tech_ports ON tech_ports.id = usage_ip_ports.port_id';
            $sql.=' WHERE usage_ip_ports.client="'.$client.'"';
            $db->Query($sql);
            while ($r=$db->NextRecord()) $R[$r['id']]=$r['text'];
        }

        $db->Query('select * from tech_cpe_models where id="'.$id_model.'"');
        if (!$model=$db->NextRecord()) return;
        echo json_encode(array(
                    'data'            => $R,
                    'depositUSD'    => $model['default_deposit_sumUSD'],
                    'depositRUR'    => $model['default_deposit_sumRUR'],
                ));
    }

    function routers_d_act($fixclient)    {
        global $design, $db;
        if (!($id=get_param_integer('id'))) return;
        $cpe = $db->GetRow('select tech_cpe.*,model,vendor,type from tech_cpe INNER JOIN tech_cpe_models ON tech_cpe_models.id=tech_cpe.id_model WHERE tech_cpe.id='.$id);
        if (!$cpe) return;
        $client = $db->GetRow('select * from clients where client="'.$cpe['client'].'"');
        if (!$client) return;
        if ($client['currency']=='USD') {
            $currency=$db->GetRow('select * from bill_currency_rate where date="'.$cpe['actual_from'].'" and currency="USD"');
            $cpe['deposit_rur']=round($cpe['deposit_sumUSD']*$currency['rate'],2);
        } else {
            $cpe['deposit_rur']=round($cpe['deposit_sumRUR'],2);
        }
        if ($cpe['service']=='usage_ip_ports' && $cpe['id_service']) $design->assign('conn',$db->GetRow('select * from '.$cpe['service'].' where id='.$cpe['id_service']));
        $design->assign('cpe',$cpe);
        $design->assign('client',$client);

        $t = new ClientCS($client['id']);
        $t = $t->GetContracts();
        $design->assign('contract',$t[count($t)-1]);

        Company::setResidents($db->GetValue("select firma from clients where client = '".$cpe["client"]."'"));

        $act=get_param_integer('act');
        if ($act=='1') {
            $design->ProcessEx('../store/acts/act_device.tpl');
        } elseif ($act>1) {
            $design->ProcessEx('../store/acts/act_device'.$act.'.tpl');
        }
    }

    function routers_m_list($fixclient){
        global $db,$design;
        $search=get_param_protected('search' , '');
        $design->assign('search',$search);
        $design->assign('models',$db->AllRecords('select * from tech_cpe_models'));
        $design->AddMain('routers/main_models.tpl');
    }
    function routers_m_add($fixclient){
        global $design,$db;
        $db->Query('select * from clients where client="'.$fixclient.'"'); $r=$db->NextRecord();
        $dbf = new DbFormTechCPEModels();
        $dbf->Display(array('module'=>'routers','action'=>'m_apply'),'Модели клиентских устройств','Новое устройство');
    }
    function routers_m_apply($fixclient){
        global $design,$db;
        $dbf = new DbFormTechCPEModels();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if ($result=='delete') {
            header('Location: ?module=devices&action=m_list');
            exit;
            $design->ProcessEx('empty.tpl');
        }
        $dbf->Display(array('module'=>'routers','action'=>'m_apply'),'Модели клиентских устройств','Редактирование');
    }
    function routers_n_list() {
        global $design;
        include INCLUDE_PATH.'db_view.php';
        $view=new DbViewTechNets();
        $view->Display('module=routers&action=n_list','module=routers&action=n_edit');
        $design->AddMain('routers/net_list.tpl');
    }
    function routers_n_edit(){
        global $db,$design;
        include INCLUDE_PATH.'db_view.php';
        $dbf=new DbFormTechNets();
        if (($id=get_param_integer('id')) && !($dbf->Load($id))) return;
        $dbf->Process();
        $dbf->Display(array('module'=>'routers','action'=>'n_edit','id'=>$id),'Сети',$id?'Редактирование':'Добавление');
    }
    function routers_n_report() {
        global $design,$db;
        $L = new IPList();
        $R = $L->getByType();
        $design->assign('data',$R);
        $design->ProcessEx('pop_header.tpl');
        $design->ProcessEx('routers/net_report.tpl');
        $design->ProcessEx('pop_footer.tpl');
    }
    function routers_n_acquire_as(){
        $L = new IPList();
        $R = $L->getByType();
        $net = get_param_integer('query',31);
        $res = '';
        $dt = date('Y-m-d',time()-3600*24*90);
        $maxtime = time()-3600*24*30; // cast! $dt - don't work..

        // search equal net
        foreach(array('tech','off') as $type){
            if(!isset($R[$type]))
                continue;
            foreach($R[$type] as $ip=>$r){
                if($r[0]==$net){
                    if(($type!='off') || $r[1]<=$maxtime) {
                        $res = $ip.'/'.$net;
                        break 2;
                    }
                }
            }
        }

        if(!$res)
        foreach(array('off','tech','new') as $type){
            if(!isset($R[$type]))
                continue;
            foreach($R[$type] as $ip=>$r){
                if($r[0]<=$net){
                    if(($type!='off') || $r[1]<=$maxtime) {
                        $res = $ip.'/'.$net;
                        break 2;
                    }
                }
            }
        }

        echo json_encode(array(
            'data'=> $res
        ));
    }

    function routers_datacenter_list($fixclient){
        global $db ,$design;
        $design->assign('ds', $db->AllRecords('
                        select 
                            d.*, 
                            (select count(*) from server_pbx s where s.datacenter_id = d.id) as count 
                        from datacenter d'));
        $design->AddMain('routers/main_datacenters.tpl');
    }

    function routers_datacenter_add($fixclient){
        global $design, $db;
        $dbf = new DbFormDataCenter();
        $dbf->Display(array('module'=>'routers','action'=>'datacenter_apply'), 'Техническая площадка', 'Новая площадка');
    }

    function routers_datacenter_apply($fixclient){
        global $design, $db;
        $dbf = new DbFormDataCenter();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();
        if($result == "delete")
        {
            header("Location: ./?module=routers&action=datacenter_list");
            exit();
        }
        $dbf->Display(array('module'=>'routers','action'=>'datacenter_apply'), 'Технические площадки', 'Редактировние');
    }

    function routers_server_pbx_list($fixclient){
        global $db, $design;

        $search=get_param_protected('search' , '');
        $design->assign('search',$search);
        $design->assign('ds',$q = $db->AllRecords('
                    select 
                        s.*, 
                        d.name as datacenter, 
                        (select count(*) from usage_virtpbx u where u.server_pbx_id = s.id) as count 
                    from 
                        server_pbx s, datacenter d 
                    where 
                        s.datacenter_id = d.id'));

        $design->AddMain('routers/main_server_pbxs.tpl');
    }
    function routers_server_pbx_add($fixclient){
        global $design, $db;

        $db->Query('select * from clients where client="'.$fixclient.'"'); $r=$db->NextRecord();
        $dbf = new DbFormServerPbx();
        $dbf->Display(array('module'=>'routers','action'=>'server_pbx_apply'),'Сервера АТС','Добавление');
    }

    function routers_server_pbx_apply($fixclient)
    {
        global $design, $db;

        $dbf = new DbFormserverPbx();
        $id=get_param_integer('id','');

        if ($id) 
            $dbf->Load($id);

        $result=$dbf->Process();
        if($result == "delete")
        {
            header("Location: ./?module=routers&action=server_pbx_list");
            exit();
        }
        $dbf->Display(array('module'=>'routers','action'=>'server_pbx_apply'),'Сервера АТС','Редактирование');
    }
}
?>
