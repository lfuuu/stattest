<?php
/*
# 1 | открыт
# 2 | закрыт
  3 | трабл УСПД
# 4 | на выезде
  5 | коллекТрабл
  6 | массТрабл
  7 | выполнен
  8 | отработано
*/
use app\dao\TroubleDao;
use app\models\support\TicketComment;
use app\models\UsageVoip;
use app\models\ClientAccount;
use yii\web\View;

class m_tt extends IModule{
    var $is_active = 0;
    var $curtype = null;
    var $curfolder = null;
    var $page_num = 0;
    var $found_pages = 0;
    var $curclient = null;
    var $dont_again = null;
    var $dont_filters = null;
    var $cur_trouble_id = 0;

    //список прав.

    function InitDbMap(){
        if (isset($this->dbmap)) return;
        require_once INCLUDE_PATH.'db_map.php';
        $this->dbmap=new Db_map_nispd();
        $this->dbmap->SetErrorMode(2,0);
    }


    function GetMain($action,$fixclient){
        global $design,$db,$user;
        if (!isset($this->actions[$action])) return;
        $act=$this->actions[$action];
        if (!access($act[0],$act[1])) return;
        $this->is_active=1;
        call_user_func(array($this,'tt_'.$action),$fixclient);
    }
    function tt_default($fixclient){
        return $this->tt_list($fixclient);
    }
    function tt_list2($fixclient) { return $this->tt_list($fixclient); }
    function tt_list_cl($fixclient) { return $this->tt_list($fixclient); }

    function tt_list($fixclient){
        global $db,$design,$user;
        $this->curclient = $fixclient;
        $f = $user->Flag('tt_tasks');
        if($f<2 || $f>4)
            $f = 2;
        $mode = get_param_integer('mode',$f);
        if($mode>=2)
            $user->SetFlag('tt_tasks',$mode);
        if ($mode == 2) {
            $_SESSION['clients_filter'] = '';
            $_SESSION['clients_my'] = '';
            $_SESSION['clients_client'] = '';
            $GLOBALS['fixclient'] = '';
            $GLOBALS['fixclient_data'] = '';
            $this->curclient = null;
            $fixclient = null;
        }
        $service = get_param_protected('service',null);
        $service_id = get_param_integer('service_id',null);
        $server_id = get_param_integer('server_id',null);
        $this->showTroubleList($mode,'full',$fixclient,$service,$service_id,null,$server_id);
    }
    function tt_add($fixclient){
        global $db,$design,$user;

        $time = get_param_integer('time', 1);

        $date = new DateTime(get_param_protected('date_start',null), new DateTimeZone(Yii::$app->user->identity->timezone_name));
        $date->setTimezone(new DateTimeZone('UTC'));
        $dateStart = $date->format('Y-m-d H:i:s');

        $date = new DateTime(get_param_protected('date_finish_desired',null), new DateTimeZone(Yii::$app->user->identity->timezone_name));
        $date->setTimezone(new DateTimeZone('UTC'));
        if ((int) $time) {
            $date->modify('+ ' . $time . ' hours');
        }
        $dateFinishDesired = $date->format('Y-m-d H:i:s');

        $R = array();
        $R['trouble_type'] = get_param_protected('type' , 'trouble');
        $R['trouble_subtype'] = get_param_protected('trouble_subtype' , '');
        #if (!in_array($R['trouble_type'],array('task', 'out', 'trouble'))) $R['trouble_type'] = 'trouble';
        $R['client'] = get_param_protected('client' , null);
        if (!$R['client'] || !($db->GetRow('select * from clients where (client="'.$R['client'].'")'))) {trigger_error2('Такого клиента не существует'); return;}
        $R['time']=$time;
        $R['date_start'] = $dateStart;
        $R['date_finish_desired']=$dateFinishDesired;
        $R['problem']=get_param_raw('problem','');
        $user_main=get_param_protected('user','');
        $R['service']=get_param_protected('service','');
        $R['is_important']=get_param_protected('is_important','0');
        $R['bill_no'] = get_param_protected('bill_no','null');
        $R['service_id']=get_param_integer('service_id');
        $R['server_id']=get_param_integer('server_id', 0);
        $id = $this->createTrouble($R,$user_main);
        if ($design->ProcessEx('errors.tpl')) {
            header('Location: ?module=tt&action=view&id='.$id);
            exit;
        }
    }
    function tt_time($fixclient) {
        global $db,$design,$user;
        $id = get_param_integer('id',0);
        $R = $this->makeTroubleList(0,null,5,null,null,null,$id);
        if (!count($R)) {trigger_error2('Такой заявки у клиента '.$fixclient.' не существует'); return;}
        $trouble = $R[0];
        if (!$this->checkTroubleAccess($trouble)) return;

        if(($time =get_param_raw("time", "none")) !== "none")
        {
            $time = intval($time);
            $db->Query('update tt_stages set date_finish_desired = date_finish_desired + INTERVAL '.$time.' HOUR where stage_id='.$trouble['cur_stage_id']);
        }elseif(($dateActivation = get_param_raw("date_activation", "none")) !== "none")
        {
            if($datetimeActivation = (new DateTime($dateActivation, new DateTimeZone('Europe/Moscow')))->format('Y-m-d H:i:s'))
            {
                $db->Query('update tt_stages set date_start = "'.$datetimeActivation.'" where stage_id='.$trouble['cur_stage_id']);
            }

        }
        if ($design->ProcessEx('errors.tpl')) {
            header('Location: ?module=tt&action=view&id='.$trouble['id']);
            exit;
        }
    }
    function tt_move($fixclient)
    {
        global $db,$design,$user;

        $id = get_param_integer('id',0);
        $R = $this->makeTroubleList(0,null,5,null,null,null,$id);
        if(!count($R)){
            trigger_error2('Такой заявки у клиента '.$fixclient.' не существует');
            return;
        }
        $trouble = $R[0];
        if($this->checkTroubleAccess($trouble) || access('tt','comment'))
        {
            //
        }else
            return;

        $R = array();


        //rating
        $setState = get_param_integer('state',0);
        if(($setState == 2 || $setState == 7) && access('tt', 'rating') && !$this->isRated($trouble["id"]))
        {
            $R["rating"] = get_param_integer("trouble_rating", 0);
            $R["user_rating"] = $user->Get("user");
        }


        $comment=get_param_raw('comment','');
        if(!$this->checkTroubleAccess($trouble) && access('tt','comment'))
        {
            $lastStage = $trouble["stages"][count($trouble["stages"])-1];

            $R["user_main"] = $lastStage["user_main"];
            //$R["stage_id"] = $lastStage["stage_id"];
            $R["state_id"] = $lastStage["state_id"];

            if($lastStage['state_id']==3)
                $R["uspd"] = $lastStage["uspd"];
            if($lastStage['state_id']==4 && $lastStage['date_start']){
                $R["date_start"] = $lastStage["date_start"];
            }
        }else{
            $R['user_main']=get_param_protected('user',null);
            //$R['is_important']=get_param_protected('is_important',0);
            $R['state_id']=get_param_integer('state','');
            if($R['state_id']==3)
                $R['uspd']=get_param_protected('uspd','');
            if($R['state_id']==4){
                $R['date_start'] = get_param_protected('date_start','');
                if(!$R['date_start'])
                    unset($R['date_start']);
            }
        }
        if($trouble['bill_no'] && preg_match("/^(\d{6,7})$/", $trouble['bill_no']) && get_param_raw("to_admin", "") != "")
        {
            $db->QueryInsert("z_sync_admin",
                array(
                    "bill_no" => $trouble['bill_no'],
                    "event"   => 'to_admin',
                    "comment" => $comment
                )
            );
            $db->Query("update newbills set editor='admin' where bill_no = '".$trouble['bill_no']."'");
            $comment .= "\n<hr>Завака передана в admin.markomnet";
        }

        // to edit
        /*
        if($trouble["state_id"] == 1 && access('tt','admin'))
        {
            $dateActivation = get_param_raw("date_activation","");
            if(strtotime($dateActivation))
                $R["date_start"] = $dateActivation;
        }
        */

        // all4net bills && 1c bills (843434 | 201307/0123)
        // $trouble["trouble_type"] in ("shop_orders", "shop")
        if($trouble['bill_no'] && preg_match("/\d{6}\/\d{4}/", $trouble['bill_no'])/* && $trouble["trouble_type"] == "shop_orders"*/){

            $bill = $db->GetRow("select * from newbills where bill_no='".addcslashes($trouble['bill_no'],"\\'")."'");
            $newstate = $db->GetRow("select * from tt_states where id=".(int)$R['state_id']);
            if($newstate['state_1c']<>$bill['state_1c']){
                require_once(INCLUDE_PATH.'1c_integration.php');
                $bs = new \_1c\billMaker($db);
                $fault = null;
                $f = $bs->setOrderStatus($bill['bill_no'], $newstate['state_1c'], $fault);
                if(!$f){
                    echo "Не удалось обновить статус заказа:<br /> ".\_1c\getFaultMessage($fault)."<br />";
                    echo "<br /><br />";
                    echo "<a href='index.php?module=tt&action=view&id=".$trouble['id']."'>Вернуться к заявке</a>";
                    exit();
                }
                if($f){
                    if (strcmp($newstate['state_1c'],'Отказ') == 0){
                        $db->Query($q="update newbills set sum=0, sum_with_unapproved = 0, state_1c='".$newstate['state_1c']."' where bill_no='".addcslashes($trouble['bill_no'], "\\'")."'");
                        event::setReject($bill, $newstate);
                    }else{
                        $db->Query($q="update newbills set state_1c='".$newstate['state_1c']."' where bill_no='".addcslashes($trouble['bill_no'], "\\'")."'");
                    }
                }
            }
        }

        $cur_stage_id = 0;
        if($trouble["bill_no"] && $trouble["trouble_type"] == "incomegoods")
        {
            // find last income order. Number mast by repeated.
            $gio = GoodsIncomeOrder::find("first", array(
                        "conditions" => array(
                            "id = ?", $trouble["bill_id"]
                            ),
                        "order" => "date desc",
                        "limit" => 1)
                    );

            $gio_trouble = Trouble::find_by_bill_id($trouble["bill_id"]);
            $cur_state = $gio_trouble->current_stage->state;

            $new_state = TroubleState::find($R["state_id"]);

            if($new_state->state_1c != $cur_state->state_1c)
            {
                $isActive = $new_state->name != "Отказ";
                $gio->setStatusAndSave($new_state->state_1c, $isActive);

                $gio_trouble = Trouble::find_by_bill_id($trouble["bill_id"]);
                $cur_stage_id = $gio_trouble->current_stage->id;
            }
        }

        $this->createStage(
            $trouble['id'],
            $R,
            array(
                'comment'=>$comment,
                'stage_id'=>$trouble['stage_id']
            )
        );

        //remote 1c stages in incomgoods, to avoid duplication
        if ($cur_stage_id)
        {
            $stage = TroubleStage::find_by_stage_id($cur_stage_id);
            if ($stage)
            {
                $stage->delete();
            }
        }

        // новый - 15

        // если заявка уходит со стадии "новый", кто уводит - тот и менеджер счета (получает бонусы)
        // даже если переход с новой на новую


// todo: переделать на bill::getDocumentType


        if($trouble["bill_no"] && ($trouble["trouble_type"] == "shop_orders" || $trouble["trouble_type"] == "shop" || $trouble["trouble_type"] == "mounting_orders"))
        {
            $bill = \app\models\Bill::findOne(['bill_no' => $trouble["bill_no"]]);

            if($trouble['state_id'] == 15){
                $bill->dao()->setManager($bill->bill_no, Yii::$app->user->getId());
            }

            // проводим если новая стадия: закрыт, отгружен, к отгрузке
            if(in_array($R['state_id'], array(28, 23, 18, 7, 4,  17, 2, 20 ))){
                $bill->is_approved = 1;
                $bill->sum = $bill->sum_with_unapproved;
            }else{
                $bill->is_approved = 0;
                $bill->sum = 0;
            }
            $bill->save();
            $bill->dao()->recalcBill($bill);
            ClientAccount::dao()->updateBalance($bill->client_id);

        }
        if($trouble["bill_no"] && $trouble["trouble_original_client"] == "onlime")
        {
            $onlimeOrder = OnlimeOrder::find_by_bill_no($trouble["bill_no"]);
            if($onlimeOrder)
                $onlimeId = $onlimeOrder->external_id;

            if($onlimeId)
            {
                $status = null;
                if($trouble["state_id"] == 21)//reject
                {
                    $status = OnlimeRequest::STATUS_REJECT;
                }elseif(in_array($trouble["state_id"], array(2,20))) // normal close, delivered
                {
                    $status = OnlimeRequest::STATUS_DELIVERY;
                }

                if($status)
                    OnlimeRequest::post($onlimeId, $trouble["bill_no"], $status, $comment);
            }
        }

        if($trouble["bill_no"] && $trouble["trouble_original_client"] == "onlime")
        {
            $onlimeOrder = OnlimeOrder::find_by_bill_no($trouble["bill_no"]);
            if($onlimeOrder)
                $onlimeId = $onlimeOrder->external_id;

            if($onlimeId)
            {
                $status = null;
                if($R["state_id"] == 21)//reject
                {
                    $status = OnlimeRequest::STATUS_REJECT;
                }elseif($R["state_id"] == 20) // normal close, delivered
                {
                    $status = OnlimeRequest::STATUS_DELIVERY;
                }

                if($status)
                {
                    OnlimeRequest::post($onlimeId, $trouble["bill_no"], $status, $comment);
                }
            }
        }

        $troubleRecord = \app\models\Trouble::findOne($trouble['id']);
        $troubleRecord->mediaManager->addFiles($files = 'tt_files', $custom_names = 'custom_name_tt_files');

        if($trouble['bill_no'] && $trouble["trouble_type"] == "shop_orders")
        {
            header('Location: ?module=newaccounts&action=bill_view&bill='.$trouble['bill_no']);
            exit();
        }

        if($trouble['bill_no'] && $trouble["trouble_type"] == "incomegoods")
        {
            header('Location: ?module=incomegoods&action=order_view&id='.$gio->id);
            exit();
        }

        if($design->ProcessEx('errors.tpl'))
        {
            header('Location: ?module=tt&action=view&id='.$trouble['id']);
            exit;
        }
	}

    function tt_list_types($fixclient){
        global $db,$design;

        if(!$fixclient)
            $fixclient="\0";

        $types = $db->AllRecords("
            select
                tty.pk,
                tty.code,
                tty.name,
                tty.folders,
                (select count(*) from tt_troubles where trouble_type=tty.code) cnt
            from
                tt_types tty
            ",null,MYSQL_ASSOC);

        $design->assign_by_ref('tt_types',$types);
        $design->AddMain('tt/types_list.html');
    }
    function tt_view_type($fixclient){
        global $db, $design, $user;
        if(isset($_REQUEST['type_pk'])){
            $type = $db->GetRow('select * from tt_types where pk='.(int)$_REQUEST['type_pk']);
        }elseif(isset($_REQUEST['type'])){
            $type = $db->GetRow('select * from tt_types where code="'.addcslashes($_REQUEST['type'], "\\\"").'"');
        }else
            $type = null;

        if(!$type){
            header('Location: ./?module=tt&action=list_types');
            exit();
        }

        $_SESSION['get_folders'] = true;
        $folder = 0;

        if(isset($_REQUEST['folder']))
            $folder = (int)$_REQUEST['folder'];

        if(!$folder && !$user->Flag('tt_'.$type['code'].'_folder')){
            $folder = $db->GetValue("select pk from tt_folders where pk & (".$type['folders']."&~1) order by `order` LIMIT 0,1");
            $user->SetFlag('tt_'.$type['code'].'_folder',$folder);
        }elseif(!$folder){
            $folder = $user->Flag('tt_'.$type['code'].'_folder');
        }elseif($folder){
            $firstFolder = array(
                    "trouble" => 256,
                    "task" => 256,
                    "support_welltime" => 256,
                    "shop_orders" => 2,
                    "mounting_orders" => 2,
                    "order_welltime" => 2,
                    "incomegoods" => 214748364,
                    "connect" => 137438953472
                    );
            if($folder == 1)
            {
                $user->SetFlag('tt_'.$type['code'].'_folder',$firstFolder[$type["code"]]);
            }else{
                $user->SetFlag('tt_'.$type['code'].'_folder',$folder);
            }
        }

        $design->assign('tt_wo_explain',true); # убрать заголовок
        $design->assign('tt_type',$type);
        $design->assign('tt_folder',$folder);

        $this->curtype = $type;
        $this->curfolder = $folder;
        $this->curclient = $fixclient;
        $_GET['mode'] = 0;

        return $this->tt_list($fixclient);
    }

    function tt_report($fixclient){
        global $db,$design,$user;
        $this->curclient = $fixclient;
        $from=getdate();
        $from['mday']=1;
        $to=$from; $to['mday']=31;
        $from=param_load_date("from_",$from,true);
        $to=param_load_date("to_",$to,true);
        $design->assign('tt_from',$from);
        $design->assign('tt_to',$to);
        $design->assign('open',$open=get_param_integer('open',0));
        $W=array('AND',array('OR'));

        if ($open) $W[1][]='S.state_id!=2';
        $W[1][]='S.date_edit>="'.$from.'" AND S.date_edit<"'.$to.'"';

//        $date_desired = 'IF(trouble_type="out",DATE(date_finish_desired)+INTERVAL 36 HOUR,date_finish_desired)';
        $date_finish = 'IF(V.date_edit!=0,V.date_edit,NOW())';
        $P = array(
                'time_limit'        =>array(    'UNIX_TIMESTAMP(V.date_finish_desired)-UNIX_TIMESTAMP(V.date_start)'    ),
                'time_total'        =>array(    'UNIX_TIMESTAMP('.$date_finish.')-UNIX_TIMESTAMP(V.date_start)'    ),
                'time_over'            =>array(    'GREATEST(UNIX_TIMESTAMP('.$date_finish.')-UNIX_TIMESTAMP(V.date_finish_desired),0)'),

                'n_over'            =>array(    'IF('.$date_finish.'>V.date_finish_desired,1,0)'),
                'n_over_close'        =>array(    'IF((V.date_edit!=0) AND ('.$date_finish.'>V.date_finish_desired),1,0)'        ),
                'n_open'            =>array(    'IF(V.date_edit=0,1,0)'    ),
                'n_all'                =>array(    '1'        ),
            );
        $S = '';
        foreach ($P as $k=>$v) if (count($v)==1) {
            $S.=',SUM('.$v[0].') as '.$k;
        } else {
            $S.=',SUM('; $S2 = '';
            foreach ($v as $vk=>$vv) if ($vk===true) {
                $S.=$vv;
            } else {
                $S.='IF('.$vk.','.$vv.',';
                $S2.=')';
            }
            $S.=$S2.' as '.$k;
        }
        $R = $db->AllRecords("
            SELECT
                U.`id` AS user_id,
                V.`user_main` AS user " . $S . "
            FROM
                `tt_troubles` AS T
                    INNER JOIN `tt_stages` S ON S.`stage_id` = T.`cur_stage_id`
                    INNER JOIN `tt_stages` V ON V.`trouble_id` = T.`id` # //все этапы
                    LEFT JOIN `user_users` U ON U.`user` = V.`user_main`
            WHERE " . MySQLDatabase::Generate($W) . " GROUP BY V.`user_main` HAVING V.`user_main` != ''");

        $design->assign('tt_report',$R);
        $design->AddMain('tt/report.tpl');
        $design->AddMain('tt/report_form.tpl');
    }

    //всякие функции
    function tt_view($fixclient){
        global $db,$design,$user;
        $this->curclient = $fixclient;

        if(!$this->cur_trouble_id){
            $id = get_param_integer('id',$this->cur_trouble_id);
        }else{
            $id = $this->cur_trouble_id;
        }

        $R = $this->makeTroubleList(0,null,5,null,null,null,$id);
        if(!count($R)){
            trigger_error2('Такой заявки не существует');
            return;
        }
        $trouble = $R[0];

        $r = $db->GetRow('
            select
                *
            from
                user_users
            where user="'.$trouble['user_author'].'"'
        );
        $trouble['user_author_name'] = $r['name'];
        $tst = $this->getTroubleSubTypes(true);
        $trouble["trouble_subtype"] = $tst[$trouble["trouble_subtype"]];

        $d = $db->GetRow("select doer_id from tt_troubles t, tt_stages s
                left join tt_doers d using (stage_id)
                where t.bill_no ='".$trouble["bill_no"]."' and t.id = s.trouble_id
                order by d.id desc limit 1");
        $trouble["doer_id"] = $d ? $d["doer_id"] : false;

        $design->assign(
            'tt_client',
            $db->GetRow('
SELECT cr.*, cg.*, c.*, c.client as client_orig, cg.name AS company, cg.name_full AS company_full, cg.legal_type AS type, o.firma,
cg.position AS signer_position, cg.fio AS signer_fio, cg.positionV AS signer_positionV, cg.fioV AS signer_fioV, cg.legal_type AS type
FROM `clients` c
INNER JOIN `client_contract` cr ON cr.id=c.contract_id
left join organization o on cr.organization_id=o.id and o.actual_from < CAST(now() as DATE) and o.actual_to > CAST(now() as DATE)
INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
where c.client="'.$trouble['client_orig'].'"')
        );
        $design->assign('tt_write',$this->checkTroubleAccess($trouble));
        $design->assign('tt_edit',$this->checkTroubleAccess($trouble) && !in_array($trouble["state_id"], [2, 20, 21, 39, 40, 46,47,48]));
        $design->assign("tt_isClosed", in_array($trouble["state_id"], [2, 20, 21, 39, 40, 46,47,48]));
        $design->assign('tt_doComment',access('tt','comment'));
        $stage = $trouble['stages'][count($trouble['stages'])-1];

        $design->assign("cur_state", $stage["state_id"]);
        $design->assign("rated", $this->isRated($trouble["id"]));

        $allow_state = 0;
        if($trouble['bill_no'] && strpos($trouble['bill_no'], '/')){
            $bill = $db->GetRow("
                select
                    newbills.*,
                    (
                        select
                            count(*)
                        from
                            newbill_lines
                        where
                            bill_no=newbills.bill_no
                        and
                            `type`='good'
                    ) good_count
                from
                    newbills
                where
                    bill_no = '".addcslashes($trouble['bill_no'], "\\'")."'
            ");
            if(!$bill['good_count'])
                $allow_state = 16384 | 8388608;
        }

        $isRollback = (isset($bill) && $bill && $bill["is_rollback"]);
        $R = $db->AllRecords($q='
            select
                *
            from
                tt_states'.($isRollback ? "_rb" : "").'
            where
                pk & (
                    select
                        states
                    from
                        tt_types
                    where
                        code="'.addcslashes($trouble['trouble_type'], "\\\"").'"
                )'.($this->checkTroubleAccess($trouble)?'':' and id!=2')."
            and
                not (pk & (select deny & ~".$allow_state." from tt_states where id=".$stage['state_id']."))
            order by
                ".(($trouble['trouble_type']=='shop_orders')?'`oso`':($trouble['trouble_type']=='mounting_orders'?'`omo`':'`order`'))
        );

        if($trouble["bill_no"]){
            $l = $this->loadOrderLog($trouble["bill_no"]);
            $this->addLogToStages($trouble["stages"], $l);
        }else{
            $design->assign("admin_order", false);
        }

        if(isset($trouble["all4geo_id"]) && $trouble["all4geo_id"])
        {
            $a4g = $db->AllRecords("select id, date, status, status_text, comment  from tt_doer_stages where all4geo_id = '".$trouble["bill_no"]."' order by id ");
            $tsId = 0;
            foreach($a4g as $a)
            {
                while(
                           isset($trouble["stages"][$tsId])
                        && strtotime($a["date"]) > strtotime($trouble["stages"][$tsId]["date_edit"])
                        && $tsId < count($trouble["stages"])
                        )
                    $tsId++;

                $trouble["stages"][$tsId-1]["doer_stages"][] = $a;
            }
        }

        $trouble["problem"] = html_entity_decode($trouble["problem"], ENT_QUOTES, 'UTF-8');

        //printdbg($trouble);

        if($trouble["bill_no"] && isset($_GET["module"]) && $_GET["module"] != "newaccounts" && preg_match("/\d{6}(\/|-)\d{4}/", $trouble["bill_no"]))
        {
            header("Location: ./?module=newaccounts&action=bill_view&bill=".urlencode($trouble["bill_no"]));
            exit();
        }

        if ($trouble['support_ticket_id']) {
            $ticketComments =
                TicketComment::find()
                    ->andWhere(['ticket_id' => $trouble['support_ticket_id']])
                    ->orderBy('created_at')
                    ->all();
            foreach ($ticketComments as $k => $comment) {
                /** @var TicketComment $comment */
                if ($comment->user_id) {
                    $author = 'Пользователь';
                } else {
                    $author = 'Тех. поддержка';
                }
                $createdAt = $comment->getCreatedAt();
                $createdAt->setTimezone(new DateTimeZone('Europe/Moscow'));

                $comment['created_at'] = $createdAt->format('d.m.Y H:i:s');
                $ticketComments[$k] = [
                    'created_at' => $createdAt->format('d.m.Y H:i'),
                    'author'  => $author,
                    'text'  => $comment->text,
                ];
            }
            $design->assign('ticketComments', $ticketComments);
        }

        if ($trouble["service"] == "usage_voip") {
            $trouble["number"] = "";
            $usageVoip = UsageVoip::findOne($trouble["service_id"]);

            if ($usageVoip) {
                $trouble["number"] = $usageVoip->E164;
            }
        }

        if ($trouble["server_id"] != 0) {

            $trouble["server"] = $trouble["datacenter_name"] = $trouble["datacenter_region"] = "";

            $res = $this->setServerForTT($trouble["server_id"]);
            if ($res)
            {
                $trouble["server"] = $res["name"];
                $trouble["datacenter_name"] = $res["datacenter_name"];
                $trouble["datacenter_region"] = $res["datacenter_region"];
            }
        }

        $design->assign('tt_trouble',$trouble);
        $design->assign('tt_states',$R);

        $trouble = \app\models\Trouble::findOne($trouble['id']);
        if ($trouble) {
            $mediaManager = $trouble->mediaManager;
            $design->assign('tt_media', $mediaManager->getFiles());
        }

        $bill = false;

        $this->prepareTimeTable();
        $design->AddMain('tt/trouble.tpl');

    }

    function setServerForTT($serverId)
    {
        global $db;

        return $db->GetRow(
            "SELECT s.name, d.name AS datacenter_name, r.name AS datacenter_region 
            FROM `server_pbx` s 
            LEFT JOIN datacenter d ON (d.id = s.datacenter_id)
            LEFT JOIN regions r ON (r.id = d.region)
            WHERE s.id = ".$serverId);
    }


    function loadOrderLog($billNo){
        global $db;
        $list = $db->QuerySelectAll("newbill_change_log", array("bill_no" => $billNo));
        $_list = array(); foreach($list as $l) $_list[$l["id"]] = $l;
        $list = $_list;
        $fList = $db->AllRecords("SELECT f.* FROM `newbill_change_log` l , `newbill_change_log_fields` f where l.id = f.change_id and bill_no = '".$db->escape($billNo)."'");
        foreach($fList as $l)
            $list[$l["change_id"]]["fields"][] = $l;

        $strs = array();
        $fields = array("amount" => "кол-во","dispatch"=> "отгружено");
        foreach($list as $l){
            $a = "";
            switch($l["action"]){
               case "change" : $a = "Изменена"; break;
               case "add" : $a = "Добавлена"; break;
               case "delete" : $a = "Удалена"; break;
            }
            $s = $l["date"].": ".$a." позиция: <span title='".htmlspecialchars_($l["item"])."'>".(strlen($l["item"])>30 ? substr($l["item"],0,30)."...": $l["item"])."</span>";
            $ff = array();
            if($l["action"] == "change" && isset($l["fields"])){
                foreach($l["fields"] as $f)
                    $ff[] = (isset($fields[$f["field"]]) ? $fields[$f["field"]] : $f["field"]).": ".$f["from"]." => ".$f["to"];
            }
            if($ff)
                $s .= " ".implode(", ", $ff);

            $strs[$l["stage_id"]]= (isset($strs[$l["stage_id"]]) ? $strs[$l["stage_id"]]."<br>".$s : $s);

        }
        return $strs;
    }

    function addLogToStages(&$R, &$l){
        foreach($R as &$r)
            if(isset($l[$r["stage_id"]])) $r["comment"] .= (!$r["comment"] ? "" : "<hr>")."<font style=\"font-size: 7pt;\">".$l[$r["stage_id"]]."</font>";

    }
    function tt_timetable($fixclient) {
        global $db,$design,$user;
        $this->curclient = $fixclient;
        $this->showTimeTable(true);
    }

    function assignDate($prefix, $date)
    {
        global $design;

        list($m, $d, $y) = explode("-", date("m-d-Y",$date));

        $design->assign($prefix."_m", $m);
        $design->assign($prefix."_d", $d);
        $design->assign($prefix."_y", $y);
    }

    // если передан клиент, то добавляется фильтр по клиенту; если передана услуга, то и по услуге.
    //flags:
    //    1 = присваивать design:service=..,service_id=..,tt_client=client
    //    2 = присваивать design:troubles
    //  4 = возвращать список
    //    8 = был ли я ответственным

    function makeTroubleList($mode,$tt_design = null,$flags = 3,$client = null,$service=null,$service_id=null,$t_id = null, $server_id = null)
    {
        //printdbg(func_get_args());
        global $db,$user,$design;
        $tt_design = 'full';
                $state = false;


        if(get_param_raw("cancel", "") != "")
        {
            unset($_POST["filter_set"]);
            unset($_SESSION["trouble_filter"]);
        }

        $execFilter = false;

        if(get_param_raw("filter_set", "") !== "" || get_param_raw("create_date_from", "") == "prev_mon")
        {
            //clear
            unset($_SESSION["trouble_filter"]);

            $execFilter = true;

            if(isset($_REQUEST['state_filter']) && $_REQUEST['state_filter']!=='---'){ //
                $state  = $_REQUEST['state_filter'];
            }else
                $state = false;

            if(isset($_REQUEST['edit']) && $_REQUEST['edit']!=='---'){ //
                $editor = $_REQUEST['edit'];
            }else
                $editor = false;

            if(isset($_REQUEST['resp']) && $_REQUEST['resp']!=='---'){ //
                $resp = $_REQUEST['resp'];
            }else
                $resp = false;

            if(isset($_REQUEST['owner']) && $_REQUEST['owner']!=='---'){
                $owner = $_REQUEST['owner'];
            }else
                $owner = false;


            if(isset($_REQUEST['subtype']) && $_REQUEST['subtype'] !== '---'){
                $subtype = $_REQUEST['subtype'];
            }else
                $subtype = false;

            $dateFrom = new DatePickerValues('create_date_from', 'today');
            $dateTo = new DatePickerValues('create_date_to', 'today');
            $create_date_from = $dateFrom->getTimestamp();
            $create_date_to = $dateTo->getTimestamp();

            if(isset($_REQUEST['create_date_from']) && $_REQUEST['create_date_from']=='prev_mon')
            {
                $dateFrom = new DatePickerValues('create_date_from', '-1 month');
                $create_date_from = $dateFrom->getTimestamp();
                $_POST["is_create"] = "on";
            }

            $dateFrom = new DatePickerValues('active_date_from', 'today');
            $dateTo = new DatePickerValues('active_date_to', 'today');
            $active_date_from = $dateFrom->getTimestamp();
            $active_date_to = $dateTo->getTimestamp();

            $dateFrom = new DatePickerValues('close_date_from', 'today');
            $dateTo = new DatePickerValues('close_date_to', 'today');
            $close_date_from = $dateFrom->getTimestamp();
            $close_date_to = $dateTo->getTimestamp();


            $dates = array(
                    "on" => array(
                        "create" => get_param_raw("is_create", "") != "",
                        "active" => get_param_raw("is_active", "") != "",
                        "close" => get_param_raw("is_close", "") != ""),
                    "create" => array($create_date_from, $create_date_to),
                    "active" => array($active_date_from, $close_date_to),
                    "close" => array($close_date_from, $close_date_to)
                    );

            $filter = array("owner" => $owner, "resp" => $resp, "edit" => $editor, "subtype" => $subtype);
            $ons = $dates["on"];

            $_SESSION["trouble_filter"] = array("time_set" => time(), "date" => $dates, "filter" => $filter,"type_pk" => get_param_raw("type_pk"));

        }else{


            if(isset($_SESSION["trouble_filter"]) && $_SESSION["trouble_filter"])
            {
                if($_SESSION["trouble_filter"]["time_set"] + 600 < time() || @$_SESSION["trouble_filter"]["type_pk"] != get_param_raw("type_pk"))
                    unset($_SESSION["trouble_filter"]);
            }

            if(isset($_SESSION["trouble_filter"]) && $_SESSION["trouble_filter"] && get_param_raw("module", "") == "tt" && get_param_raw("filtred","false")=="true")
            {
                $execFilter = true;
                $filter = $_SESSION["trouble_filter"]["filter"];
                $dates = $_SESSION["trouble_filter"]["date"];

                $ons = $dates["on"];
                if($ons["create"])
                {
                    $create_date_from = $dates["create"][0];
                    $create_date_to = $dates["create"][1];
                    $design->assign('create_date_from', date('d-m-Y', $create_date_from));
                    $design->assign('create_date_to', date('d-m-Y', $create_date_to));
                }else{
                        $dateFrom = new DatePickerValues('create_date_from', 'today');
                        $dateTo = new DatePickerValues('create_date_to', 'today');
                        $create_date_from = $dateFrom->getTimestamp();
                        $create_date_to = $dateTo->getTimestamp();
                }

                if($ons["active"])
                {
                    $active_date_from = $dates["active"][0];
                    $active_date_to = $dates["active"][1];
                    $design->assign('active_date_from', date('d-m-Y', $active_date_from));
                    $design->assign('active_date_to', date('d-m-Y', $active_date_to));
                }else{
                        $dateFrom = new DatePickerValues('active_date_from', 'today');
                        $dateTo = new DatePickerValues('active_date_to', 'today');
                        $active_date_from = $dateFrom->getTimestamp();
                        $active_date_to = $dateTo->getTimestamp();
                }

                if($ons["close"])
                {
                    $close_date_from = $dates["close"][0];
                    $close_date_to = $dates["close"][1];
                    $design->assign('close_date_from', date('d-m-Y', $close_date_from));
                    $design->assign('close_date_to', date('d-m-Y', $close_date_to));
                }else{
                        $dateFrom = new DatePickerValues('close_date_from', 'today');
                        $dateTo = new DatePickerValues('close_date_to', 'today');
                        $close_date_from = $dateFrom->getTimestamp();
                        $close_date_to = $dateTo->getTimestamp();
                }

                $_SESSION["trouble_filter"]["time_set"] = time();

            }else{
                $filter = array("owner" => false, "resp" => false, "edit" => false, "subtype" => false);
                $ons = array("create" => false, "active" => false, "close" => false);

                $prefixs = array('create_', 'active_', 'close_');
                foreach ($prefixs as $prefix)
                {
                        $var_name = $prefix . 'date_to';
                        $mTime = new DatePickerValues($var_name, 'now');
                        $$var_name = $mTime->getTimestamp();
                        $var_name = $prefix . 'date_from';
                        $mTime = new DatePickerValues($var_name, 'now');
                        $$var_name = $mTime->getTimestamp();
                }

                if(isset($_REQUEST['create_date_from']) && $_REQUEST['create_date_from']=='prev_mon')
                {
                    $dateFrom = new DatePickerValues('create_date_from', '-1 month');
                    $create_date_from = $dateFrom->getTimestamp();
                }
            }

            $owner = $filter["owner"];
            $resp = $filter["resp"];
            $editor = $filter["edit"];
            $subtype = $filter["subtype"];
        }

        $design->assign("tt_show_filters", $execFilter);

        $design->assign("tt_show_add", get_param_raw("show_add_form", "") != "");

            $min_time = strtotime('first day of this month');
            $min_time = strtotime('-1 year', $min_time);

            if($create_date_from < $min_time)
            {
                $create_date_from = $min_time;
                $design->assign('create_date_from', date('d-m-Y', $create_date_from));
            }
            $create_date_from = date('Y-m-d',$create_date_from);

            if($create_date_to < $min_time)
            {
                $create_date_to = false;
                $design->assign('create_date_to', date('d-m-Y'));
            } else {
                $create_date_to = date('Y-m-d',$create_date_to).' 23:59:59';
            }

            if($active_date_from < $min_time)
            {
                $active_date_from = $min_time;
                $design->assign('active_date_from', date('d-m-Y', $active_date_from));
            }
            $active_date_from = date('Y-m-d',$active_date_from);

            if($active_date_to < $min_time)
            {
                $active_date_to = false;
                $design->assign('active_date_to', date('d-m-Y'));
            } else {
                $active_date_to = date('Y-m-d',$active_date_to).' 23:59:59';
            }

            if($close_date_from < $min_time)
            {
                $close_date_from = $min_time;
                $design->assign('close_date_from', date('d-m-Y', $close_date_from));
            }
            $close_date_from = date('Y-m-d',$close_date_from);

            if($close_date_to < $min_time)
            {
                $close_date_to = false;
                $design->assign('close_date_to', date('d-m-Y'));
            } else {
                $close_date_to = date('Y-m-d',$close_date_to).' 23:59:59';
            }

        $design->assign("filter",$filter);
        $design->assign("filter_head", array("action" => get_param_raw("action", "view_type"), "mode" => get_param_raw("mode", 0)));

        $design->assign("is_create", $ons["create"]);
        $design->assign("is_active", $ons["active"]);
        $design->assign("is_close", $ons["close"]);

        $W = array('AND');
        $join = '';
        $select = '';
        $use_stages = false;

        //if($mode == 0){
            if($state !== false)
                $W[] = 'S.state_id = '.((int)$state);
            if($editor !== false)
            {
                $W[] = "S.user_edit = '".addcslashes($editor,"\\\\'")."'";
                $use_stages = true;
            }
            if($resp !== false){
                if($resp == 'SUPPORT')
                    $W[] = "`S`.`user_main` in (select `user` from `user_users` where `usergroup`='support')";
                else
                    $W[] = "S.user_main = '".addcslashes($resp, "\\\\'")."'";
                $use_stages = true;
            }

            if($owner !== false)
                $W[] = "T.user_author = '".addcslashes($owner,"\\\\'")."'";

            if($subtype !== false)
                $W[] = "T.trouble_subtype = '".$subtype."'";

            if($create_date_from !== false && $ons["create"])
                $W[] = "T.date_creation >= '".$create_date_from."'";
            if($create_date_to !== false && $ons["create"])
                $W[] = "T.date_creation <= '".$create_date_to."'";

            if($active_date_from !== false && $ons["active"])
            {
                $W[] = "S.date_start >= '".$active_date_from."'";
                $use_stages = true;
            }
            if($active_date_to !== false && $ons["active"])
            {
                $W[] = "S.date_start <= '".$active_date_to."'";
                $use_stages = true;
            }

            if($close_date_from !== false && $ons["close"])
                $W[] = "T.date_close >= '".$close_date_from."'";
            if($close_date_to !== false && $ons["close"])
                $W[] = "T.date_close <= '".$close_date_to."'";
        //}

        if($service)
            $W[]='service="'.addslashes($service).'"';
        if($service_id)
            $W[]='service_id="'.addslashes($service_id).'"';
        if($t_id)
            $W[]='T.id='.intval($t_id);

        if($server_id)
        {
            $W[]='T.server_id in ("'.implode('","', (is_array($server_id) ? $server_id : [$server_id])).'")';
        } else if (!$t_id) { // убираем серверные траблы из списка простых заявок. Показывает траблу - если открыта детелизация
            if ($mode != 2 && $mode != 3)
                $W[]='T.server_id=0';
        }

        $showStages = ($mode>=1 || $client || $service || $service_id || $t_id || $server_id);

        if($mode>=1)
        {
            $W[]='S.state_id not in (2,20,21,39,40,46,47,48)';
            $use_stages = true;
        }
        if($mode==2 || $mode==3)
        {
            $W[]='S.date_start<=NOW()';
            $use_stages = true;
        }
        if($mode==2)
            $W[] = 'S.user_main="'.addslashes($user->Get('user')).'"';
        if($mode==3)
            $W[] = 'user_author="'.addslashes($user->Get('user')).'"';
        if($mode==4){
            $W[] = 'cr.manager="'.addslashes($user->Get('user')).'"';
        }if($mode==5){
            $W[] = "T.id IN (SELECT `tt`.`id` FROM `tt_troubles` `tt` INNER JOIN `tt_stages` `ts` ON `ts`.`trouble_id`=`tt`.`id` AND `ts`.`user_edit`='".addslashes($user->Get('user'))."' INNER JOIN `tt_stages` `ts1` ON `ts1`.`stage_id`=`tt`.`cur_stage_id` AND `ts1`.`state_id`<>2)";
        }

        $folders_join = $join;

        if (($flags&8)!=8) {
            $join.='LEFT JOIN tt_stages as S2 ON S2.trouble_id = T.id AND S2.user_main = "'.addslashes($user->Get('user')).'" ';
            $select = 'IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,';
        }

        if($this->curtype)
            $W[] = "T.trouble_type = '".$this->curtype['code']."'";

        $W_folders = $W;

        if($this->curfolder)
            $W[] = "T.folder&".$this->curfolder;

        if($client)
            $W[]='cl.id='.$client;

        $page = get_param_integer("page", 1);
        if(!$page) $page = 1;
        $recInPage = 50;

        if(isset($_SESSION["_mcn_user_login_stat.mcn.ru"]) && in_array($_SESSION["_mcn_user_login_stat.mcn.ru"], array("drupov", "vinokurov")))
            $recInPage = 300;


        $R = $db->AllRecords($q='
            SELECT sql_calc_found_rows
                T.*,
                S.*,
                T.client as client_orig,
                cl.id as clientid,
                (select count(1) from  newbill_sms bs where  T.bill_no = bs.bill_no) is_sms_send,
                T.client as trouble_original_client,
if(is_rollback is null or (is_rollback is not null and !is_rollback), tts.name, ttsrb.name) as state_name,
                tts.order as state_order,
                '.$select.'
                IF(S.date_start<=NOW(),1,0) as is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) as time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) as time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) as time_start,
                if(T.bill_no,(
                    SELECT cg.name FROM newbills nb
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    INNER JOIN `client_contract` cr ON cr.id=cl.contract_id
                    INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) as client,
                    is_payed,
                is_rollback,
                tt.name as trouble_name,
                cr.manager,
                cg.name AS company
            FROM
                tt_troubles as T
            '.$join.'
            INNER JOIN tt_stages as S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states as tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb as ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            LEFT JOIN `client_contract` cr ON cr.id=cl.contract_id
            LEFT JOIN `client_contragent` cg ON cg.id=cr.contragent_id
            WHERE '.MySQLDatabase::Generate($W).'
            GROUP BY T.id
            ORDER BY T.id
            DESC limit '.(($page-1)*$recInPage).','.$recInPage.'
        ');

        $resultCount = $db->GetValue('select found_rows() as count');
        util::pager_pg($resultCount, $recInPage);

        $url = "./?module=tt&action=".get_param_raw("action","")."&mode=".get_param_raw("mode", 0);
        if(($typePk = get_param_raw("type_pk","")) != "")
            $url .= "&type_pk=".$typePk;

        if(($folder = get_param_raw("folder","")) != "")
            $url .= "&folder=".$folder;

        $design->assign("pager_url", $url);

        $lMetro = \app\models\Metro::getList();
        $lLogistic = array(
            "none" => "--- Не установленно ---",
            "selfdeliv" => "Самовывоз",
            "courier" => "Доставка курьером",
            "auto" => "Доставка авто",
            "tk" => "Доставка ТК",
        );

        foreach($R as $k=>$r){
            $R[$k]["trouble_name"] = str_replace(array("заказы"), array("Заказ"), mb_strtoupper($r["trouble_name"]));
            if($r['time_pass'])
                $R[$k]['time_pass'] = time_period($r['time_pass']);
            if($r['time_start'])
                $R[$k]['time_start'] = time_period($r['time_start']);

            if($r['time_limit'])
                $R[$k]['time_limit']=time_period($r['time_limit']);
                $R[$k]['stages'] = $db->AllRecords('
                    SELECT
                        S.*,
                        IF(S.date_edit=0,NULL,date_edit) as date_edit,
                        tts.name as state_name
                    FROM
                        tt_stages as S
                    INNER JOIN
                        tt_states'.($r["is_rollback"] ? "_rb" : "").' tts
                    ON
                        tts.id = state_id
                    WHERE
                        trouble_id='.$r['trouble_id'].'
                    ORDER BY
                        stage_id ASC
                ');

                $R[$k]['last_comment'] = $R[$k]['stages'] && isset($R[$k]['stages'][count($R[$k]['stages'])-2]) ? $R[$k]['stages'][count($R[$k]['stages'])-2]["comment"] : "";
                $R[$k]['add_info'] = $db->GetRow("select * from newbills_add_info where bill_no = '".$r["bill_no"]."'");
                $R[$k]['sms'] = $db->GetRow("select * from newbill_sms where bill_no = '".$r["bill_no"]."' limit 1");

                // humanreadable metro && logistic
                if($R[$k]['add_info'])
                {
                    $R[$k]['add_info']["metro_name"] = $R[$k]['add_info']["metro_id"] > 0 ? $lMetro[$R[$k]['add_info']["metro_id"]] : "";
                    if($R[$k]['add_info']["logistic"] == "")$R[$k]['add_info']["logistic"] = "none";
                    $R[$k]['add_info']["logistic_name"] = $lLogistic[$R[$k]['add_info']["logistic"]];
                }

            if ($r["service"] == "usage_voip")
            {
                $R[$k]["number"] = "";
                $usageVoip = UsageVoip::findOne($r["service_id"]);

                if ($usageVoip) {
                    $R[$k]["number"] = $usageVoip->E164;
                }
            }

            if ($r["server_id"] != 0) {

                $R[$k]["server"] = $R[$k]["datacenter_name"] = $R[$k]["datacenter_region"] = "";

                $res = $this->setServerForTT($r["server_id"]);
                if ($res)
                {
                    $R[$k]["server"] = $res["name"];
                    $R[$k]["datacenter_name"] = $res["datacenter_name"];
                    $R[$k]["datacenter_region"] = $res["datacenter_region"];
                }
            }
        }

        if(($flags&1)!=0){
            $design->assign('showStages',$showStages);
            if(!$client){
                $subj=0;
            }elseif($tt_design=='service' && $service){
                $subj=2;
            }else
                $subj=1;

            $design->assign('tt_subject',$subj);
            $design->assign('tt_design',$tt_design);
            $design->assign('tt_client',$client);
            $design->assign('tt_service',$service);
            $design->assign('tt_service_id',$service_id);
            $design->assign('tt_server_id',$server_id);
            if ($server_id && get_param_raw("show_add_form")) {
                $design->assign('tt_server',$this->setServerForTT($server_id));
            }

            $db->Query('select u.usergroup, user, name,g.comment as ugroup from user_users u , user_groups  g
                    where u.usergroup = g.usergroup and u.enabled="yes" order by usergroup,convert(name using koi8r)');
            $U=array();
            while($r=$db->NextRecord()){
                if(!isset($usergroup) || $usergroup!=$r['usergroup']){
                    $usergroup=$r['usergroup'];
                    $U[]=array('name'=>$r["ugroup"],'user'=>'');
                }
                if(strlen($r['user'])<15){
                    $r['user_pad'] = str_repeat("&nbsp;",15-strlen($r['user'])).$r['user'];
                }else
                    $r['user_pad']=$r['user'];
                $U[]=$r;
            }
            $design->assign('tt_users',$U);
        }

        foreach($R as $trouble_id=>$trouble){
            if(isset($trouble['stages']))
            foreach($trouble['stages'] as $stage_id=>$stage){
                /*if($stage['state_id']<>4)
                    continue;*/
                $query = "
                    SELECT
                        `td`.`doer_id`,
                        `cr`.`name`,
                        `cr`.`depart`
                    FROM
                        `tt_doers` `td`
                    LEFT JOIN
                        `courier` `cr`
                    ON
                        `cr`.`id` = `td`.`doer_id`
                    WHERE
                        `td`.`stage_id` = ".$stage['stage_id']."
                    ORDER BY
                        `cr`.`depart`,
                        `cr`.`name`
                ";
                $R[$trouble_id]['stages'][$stage_id]['doers']=$db->AllRecords($query,null,MYSQL_ASSOC);
            }
        }

        if(($flags&2)!=0){
            $design->assign('tt_troubles',$R);
        }

        if (isset($_SESSION['get_folders']) && $_SESSION['get_folders'])
        {
            $clientNick = ClientAccount::findOne($client)->client;
            if($clientNick)
                $W_folders[] = " T.client = \"$clientNick\" ";
            unset($_SESSION['get_folders']);
            $W_folders[] = "tf.pk & ".$this->curtype['folders'];
            if ($use_stages)
            {
                 $folders_join .= ' left join tt_stages as S ON T.id = S.trouble_id  ';
                 $W_folders[] = 'S.stage_id = T.cur_stage_id';
            }

            $folders = $db->AllRecords($q="
                SELECT  
                    tf.pk, tf.name, COUNT(DISTINCT(T.id)) as cnt
                FROM
                    tt_troubles as T
                LEFT JOIN 
                    tt_folders as tf ON T.folder & tf.pk 
                     ".$folders_join."
                WHERE 
                    ".MySQLDatabase::Generate($W_folders)."
                GROUP BY 
                    tf.pk 
                ORDER BY
                    `tf`.`order`",
                'pk',
                MYSQL_ASSOC
            );
            $all_folders = $db->AllRecords("
                select 
                    pk, name 
                from 
                    tt_folders 
                where 
                    pk & ".$this->curtype['folders']."
                order by 
                    `order`",
                'pk'
            );

            $design->assign('tt_all_folders',$all_folders);
            $design->assign('tt_folders',$folders);
            $design->assign('tt_folders_block',$design->fetch('tt/folders_list.html'));
         }

        if(($flags&4)!=0)
            return $R;
        return count($R);
    }

    //tt_design:
    //    full - при просмотре траблов
    //    client - из клиента
    //    service - из услуги
    //    top - сверху

    //mode:
    // 0 = все запросы, в т.ч. закрытые. состояние не открывать
    // 1 = все открытые
    // 2 = открытые, активные, ответственный - я.
    // 3 = открытые, активные, автор - я.
    // 4 = открытые, менеджер клиента - я.

    function    showTroubleList($mode,$tt_design = 'full',$fixclient = null,$service = null,$service_id = null,$t_id = null, $server_ids = null){

        $account = ClientAccount::findOne($fixclient);

        if($this->dont_again)
            return 0;
        global $db,$design;

        if($this->dont_filters || $tt_design != "full")// || isset($_REQUEST['filters_flag']))
            $R=$this->makeTroubleList($mode,$tt_design,5,$fixclient,$service,$service_id,$t_id, $server_ids);
        else{
            $R=$this->makeTroubleList($mode,$tt_design,5,$fixclient,$service,$service_id,$t_id, $server_ids);

            // фильтр по этапам
            $sql_select_users = " select `user` from user_users order by enabled, `user` ";
            $design->assign('users',$db->AllRecordsAssoc($sql_select_users, 'user', 'user'));
        }

        if ($mode == 6 && !$R) return false; // не показываем секцию с страблами если их нет


        $design->assign('tt_show_filter',$tt_design == "full");


        switch ($mode){
            case 0: $t='Все заявки'; break;
            case 1: $t='Открытые заявки'; break;
            case 2: $t='Активные заявки, порученные мне'; break;
            case 3: $t='Активные заявки, созданные мной'; break;
            case 4: $t='Запросы моих клиентов'; break;
            case 5: $t='Заявки с закрытым мною этапом, но не закрытые полностью'; break;
            case 6: $t='Серверные заявки'; break;
            default: trigger_error2("mode = ".$mode);
        }
        $design->assign('tt_header',$t);

        $design->assign('so',$so=get_param_integer ('so', 0));
        $design->assign('sort',$sort=get_param_integer('sort',1));
        $order = $so ? 'desc' : 'asc';
        $orderField = ($mode != 2 ? 'trouble_id' : 'date_start');
        provide_sort(
            $R,
            $sort,
            $so,
            [1 => $orderField, 2 => 'client', 3 => 'state_order', 4 => 'user_main'],
            $orderField
        );
        $design->assign('tt_troubles',$R);
        $design->assign('CUR','?module=tt&action=list&mode='.$mode);

        $v = 0;
        if ($tt_design == 'full') {
            $v = 2;
        } elseif ($tt_design) {
            if (count($R)) $v=1;
        }

        $design->assign("trouble_subtypes", $this->getTroubleSubTypes());
        $design->assign("trouble_subtypes_list", $this->getTroubleSubTypes(true));

        if($v>=2){
            if($this->curclient)
                $design->assign('bills',$db->AllRecords('select bill_no from newbills where is_payed=0 and client_id=(select id from clients where client="'.addcslashes($this->curclient, "\\\"").'") order by bill_date desc','bill_no',MYSQL_ASSOC));
            $design->assign('ttypes',$db->AllRecords('select * from tt_types','pk',MYSQL_ASSOC));

            $design->assign('curtype',$this->curtype);
            if(in_array($this->curtype['code'],array('trouble','task','support_welltime','connect'))){
                $design->assign('form',(new View())->render('@app/views/trouble/_form',[
                    'account' => $account,
                    'curtype' => $this->curtype,
                    'ttServer' => $design->get_template_vars('tt_server'),
                    'ttServerId' => $design->get_template_vars('tt_server_id'),
                    'ttService' => $design->get_template_vars('tt_service'),
                    'ttServiceId' => $design->get_template_vars('tt_service_id'),
                    'ttShowAdd' => $design->get_template_vars('tt_show_add'),
                    'troubleTypes' => \yii\helpers\ArrayHelper::map(
                        Yii::$app->db->createCommand('SELECT code, name FROM tt_types')->queryAll(PDO::FETCH_ASSOC),
                        'code', 'name'
                    ),
                    'troubleSubtypes' => $this->getTroubleSubTypes(),
                    'ttUsers' => \app\models\UserGroups::dao()->getListWithUsers(),
                    'billList' => $this->curtype && in_array($this->curtype['code'], ['shop_orders', 'mounting_orders', 'orders_kp'])
                        ? \yii\helpers\ArrayHelper::map(\app\models\Bill::find()
                            ->andWhere(['is_payed' => 0, 'client_id' => $account->id])
                            ->select('bill_no')
                            ->column(),
                            'bill_no', 'bill_no')
                        : [],
                ]));
                $design->AddMain('tt/trouble_form.tpl');
            }
            $this->showTimeTable();
        }

        /*
        if(!($mode>0 || isset($_REQUEST['filters_flag']))){
            $design->AddMain('tt/trouble_filters.tpl');
        }
        */

        if ($mode != 6)
            $design->AddMain('tt/trouble_filters.tpl');

        if ($v>=1) {
            if ($tt_design=='top') {
                $design->AddPreMain('tt/trouble_list.tpl');
            } else {
                $design->AddMain('tt/trouble_list'.(get_param_raw("type_pk","0") == 4 ? "_full_pk4" : "").'.tpl', ($mode == 6 ? 1 : 0));
            }
        }

        return count($R);
    }

    public function showServerTroubles($serverIds)
    {
        return $this->showTroubleList(6, 'full',null,null,null,null,$serverIds);
    }

    private function getTroubleSubTypes($isAll = false)
    {
        global $user;


        $typePk = get_param_raw("type_pk", 0);
        $a = array();

        if($typePk == 1 || $typePk == 3 || $isAll)
        {
            $a["trouble"] = "Трабл";
            $a["consultation"] = "Консультация";
            $a["monitoring"] = "Мониторинг";
        }

        if($typePk == 2 || $typePk == 5 || $isAll)
        {
            $a["task"] = "Задание";
            $a["reminder"] = "Напоминание";
        }

        if($typePk == 5 || $isAll)
            $a["prospecting"] = "Разведка";

        if($typePk == 7 || $isAll)
            $a["incomegoods"] = "Заказ поставщику";

        if($typePk == 8 || $isAll)
            $a["connect"] = "Подключение";


        if($isAll){
            $a["shop"] = "Заказ"; // possible: type_pk == 6
            $a[""] = "";
        }

        return $a;
    }

    function createStage($trouble_id, $data_to_open,$data_to_close = null,$user_edit=null)
    {
        global $db,$user;
        //state, user_main, comment, uspd,
        //date_start, date_finish

        if(is_array($data_to_close)){
            $R = array(
                'stage_id'=>$data_to_close['stage_id'],
                'comment'=>$data_to_close['comment'],
                'date_edit'=>array('NOW()'),
                'user_edit'=>$user_edit?$user_edit:$user->Get('user')
            );

            $db->QueryUpdate('tt_stages','stage_id',$R);


          $r = $db->GetRow('
                select
                    *
                from
                    tt_states
                where
                    id='.$data_to_open['state_id']);
            $T = $r['time_delta'];
            if(isset($data_to_open['date_start'])){
                if(!isset($data_to_open['date_finish_desired']))
                    $data_to_open['date_finish_desired'] = array(
                        '"'.$data_to_open['date_start'].'" + INTERVAL '.$T.' HOUR'
                    );
            }elseif(isset($_POST['doer_fix'])){
                $date = array_keys($_POST['doer_fix']);
                $date = $date[0];
                $time = array_keys($_POST['doer_fix'][$date]);
                $time = $time[0];
                $data_to_open['date_start'] = $date." ".$time.":00:00";
            }else{
                $r1 = $db->GetRow('
                    select
                        GREATEST(date_start,NOW()) as date_start
                    from
                        tt_stages
                    where
                        stage_id='.$data_to_close['stage_id']
                );

                $r2 = $db->GetRow('
                    select
                        GREATEST(date_finish_desired,NOW()+INTERVAL '.$T.' HOUR) as date_finish_desired
                    from
                        tt_stages
                    where
                        trouble_id='.$trouble_id.'
                    and
                        state_id!=4
                    order by
                        stage_id DESC
                    LIMIT 1
                ');
                $data_to_open['date_start'] = $r1['date_start'];
                if(!isset($data_to_open['date_finish_desired']))
                    $data_to_open['date_finish_desired'] = $r2['date_finish_desired'];
            }
        }
        if(in_array($data_to_open['state_id'],array(2,20,21,39,40,48))){
            $data_to_open['date_finish_desired'] = array('NOW()');
            $data_to_open['date_edit'] = array('NOW()');
            $data_to_open['user_edit'] = $user_edit?$user_edit:$user->Get('user');
        }
        $data_to_open['trouble_id']=$trouble_id;

        $id = $db->QueryInsert('tt_stages',$data_to_open);

        $db->Query('update tt_troubles set cur_stage_id = '.$id.', folder=(select folder from tt_states where id='.(int)$data_to_open['state_id'].') where id='.$trouble_id);

        if(in_array($data_to_open["state_id"], array(2,20,7,8,48))){
            // to close
            $db->Query("update tt_troubles set date_close=now() where id = '".$data_to_open["trouble_id"]."' and date_close='0000-00-00 00:00:00'");
        }

        if($id>0)
            $this->doers_action('fix_doers', $trouble_id, $id);


        TroubleDao::me()->updateSupportTicketByTrouble($trouble_id);

        $this->checkTroubleToSendToAll4geo($trouble_id);
        $this->checkTroubleToSend($trouble_id);

        return $id;
    }

    function createTrouble($R = array(), $user_main = null) {
        global $db,$user;
        if (!isset($R['user_author'])) $R['user_author']=$user->Get('user');
        if (!$user_main) {
            $user_main = $R['user_author'];
        } elseif ($r = $db->GetRow('select user,trouble_redirect from user_users where "'.$user_main.'" IN (`user`,`id`)')) {
            if ($r['trouble_redirect']) $user_main = $r['trouble_redirect']; else $user_main = $r['user'];
        } else {
            if (defined("YII_ENV") && YII_ENV == "test") {
                throw new Exception('неверный пользователь');
            } else {
                trigger_error2('неверный пользователь'); 
            }
            return;
        }

        $R2 = [];
        if (isset($R["first_comment"]))
        {
            $R2["comment"] = $R["first_comment"];
            unset($R["first_comment"]);
        }

        if(isset($R['date_finish_desired']))
            $R2['date_finish_desired']=$R['date_finish_desired'];
        else
            $R2['time']=$R['time'];

        if($R['trouble_type']=='trouble'){
            $R2['date_start'] = array('NOW()');
            $R2['state_id']=1;
        }else{
            if (!isset($R['folder'])) {
                $R['folder'] = array('(select pk from tt_folders where pk & (select folders from tt_types where code="'.addcslashes($R['trouble_type'],"\\\"").'") order by pk limit 1)');
            }
            $R2['date_start'] = isset($R['date_start'])?$R['date_start']:array('NOW()');

            $r = $db->GetRow("select id from tt_states where pk & (select states from tt_types where code='".addcslashes($R['trouble_type'], "\\'")."') order by ".(($R['trouble_type']=='shop_orders')?'`oso`':($R['trouble_type']=='mounting_orders'?'`omo`':'`order`'))." limit 1");
            $R2['state_id'] = $r['id'];
        }
        #unset($R['trouble_type']);
        unset($R['date_start']);
        unset($R['date_finish_desired']);
        unset($R['time']);

        $R['date_creation'] = array('NOW()');
        if(isset($R2['time'])){
            $R2['date_finish_desired'] = array((is_array($R2['date_start'])?'NOW()':'"'.$R2['date_start'].'"').' + INTERVAL '.$R2['time'].' HOUR');
            unset($R2['time']);
        }
        if(isset($R['bill_no']) && $R['bill_no']=='null')
            unset($R['bill_no']);
        $id = $db->QueryInsert('tt_troubles',$R);

        $trouble = \app\models\Trouble::findOne($id);
        $trouble->mediaManager->addFiles($files = 'tt_files', $custom_names = 'custom_name_tt_files');

        $R2['user_main'] = $user_main;
/*
    insert into tt_stages (date_start,state_id,date_finish_desired,user_main,trouble_id) values (NOW(),"1",NOW() + INTERVAL 1 HOUR,"nick","49583")
*/

        $this->createStage($id,$R2);
        if (isset($R2["comment"]) && $R2["comment"])
        {
            $R2["comment"] = "";
            $this->createStage($id,$R2);
        }

        return $id;
    }

    function checkTroubleAccess($trouble = null) {

        global $db,$design,$user;
        if (access('tt','admin')) return true;
        if (!access('tt','use')) return false;
        $u = $user->Get('user');
        if (in_array($trouble['state_id'], array(2, 20, 39, 46,47,48))) return false;
        if ($trouble['state_id']==7 && $u==$trouble['user_author']) return true;
        if ($trouble['is_editableByMe']) return true;
        if ($u==$trouble['user_main'] || $u==$trouble['user_author']) return true;
        if (in_array($trouble['client'],array('all4net','wellconnect')) && access('tt','shop_orders')) return true;
        if (in_array($trouble["user_author"], ["1c-vitrina", "AutoLK"])) return true;
        if ($trouble["user_main"] == "system") return true;
        return false;
    }


    /**
    * Установлен ли рейтинг на текущей стадии заявки
    */
    function isRated($trouble)
    {
        global $db;

        $trouble = Trouble::find($trouble);

        $stages = array();

        foreach ($trouble->stages as $stage)
        {
            $stages[] = $stage;
        }

        $stages = array_reverse($stages);

        $stateId = null;

        foreach ($stages as $stage)
        {
            if ($stateId === null)
            {
                $stateId = $stage->state->id;
            }

            if ($stateId != $stage->state->id)
            {
                return false;
            }else{
                if ($stage->rating > 0)
                {
                    return $stage->rating;
                }
            }
        }

        return false;
    }

    function tt_slist($fixclient){
        global $design,$db;
        $db->Query('select * from tt_states order by id');
        $R=array(); while ($r=$db->NextRecord()) $R[]=$r;
        $design->assign('tt_states',$R);
        $design->AddMain('tt/states_list.tpl');
    }
    function tt_sadd($fixclient){
        global $design,$db;
        $this->InitDbMap();
        $this->dbmap->ShowEditForm('tt_states','',array(),1);
        $design->AddMain('tt/states_add.tpl');
    }
    function tt_sedit($fixclient){
        global $design,$db;
        $this->InitDbMap();
        $id = get_param_protected('id' , '');
        $this->dbmap->ApplyChanges('tt_states');
        $this->dbmap->ShowEditForm('tt_states','tt_states.id="'.$id.'"',array(),1);
        $design->assign('id',$id);
        $design->AddMain('tt/states_edit.tpl');

    }
    function tt_sapply($fixclient){
        global $design,$db;
        $this->InitDbMap();
        if (($this->dbmap->ApplyChanges('tt_states')!="ok") && (get_param_protected('dbaction','')!='delete')) {
            $row=get_param_raw('row',array());
            $this->dbmap->ShowEditForm('tt_states','',$row);
            $design->AddMain('tt/states_add.tpl');
        } else {
            $this->tt_slist($fixclient);
        }
    }

    function showTimeTable($someone=null,$cast=false){
        global $design;
        $this->prepareTimeTable($someone, $cast);
        $design->AddMain('tt/timetable.tpl');
    }

    function prepareTimeTable($someone=null,$cast=false){
        global $db,$design;
        $tr_id = get_param_integer('id',$this->cur_trouble_id);
        if(is_null($someone) && $tr_id){
            $query = "
                SELECT
                    `ts`.`state_id`
                FROM
                    `tt_troubles` `tt`
                LEFT JOIN
                    `tt_stages` `ts`
                ON
                    `ts`.`stage_id` = `tt`.`cur_stage_id`
                WHERE
                    `tt`.`id` = ".$tr_id."
            ";

            $row = $db->GetRow($query);
            if($row['state_id'] == '4'){
                $someone = true;
            }
        }

        $time = param_load_date(
            'ttt_',
            array('mday'=>date('d'),'mon'=>date('m'),'year'=>date('Y')),
            false
        );
        $date = date('Y-m-d',$time);

        $db->Query($query = "
            SELECT
	    `cr`.`id`,
	    `cr`.`name`,
	    `tt`.bill_no,
	    `cc`.`name` `client`,
	    `ts_last`.`state_id` `state`,
	    `st`.name state_name,
	    `ts`.`trouble_id`,
	    `cr`.`depart`,
	    DATE(`ts`.`date_start`) `t_date`,
	    `ts`.`date_start` `tf_date`,
	    ".(($tr_id)?"IF(`ts`.`trouble_id`=".($tr_id).",'Y','N')":"'N'")." `its_here`,
	    IF(`ts`.`stage_id`=`tt`.`cur_stage_id`,'Y','N') `its_this`
	FROM `courier` `cr`
	LEFT JOIN `tt_doers` `td` ON `td`.`doer_id` = `cr`.`id`
	LEFT JOIN `tt_stages` `ts` ON `ts`.`stage_id` = `td`.`stage_id` AND `ts`.`date_start` BETWEEN DATE_ADD('".$date."',INTERVAL -1 DAY) AND DATE_ADD('".$date."',INTERVAL 2 DAY)
	LEFT JOIN `tt_troubles` `tt` ON `tt`.`id` = `ts`.`trouble_id`
	LEFT JOIN `clients` `cl` ON `cl`.`client` = CAST(`tt`.`client` AS CHAR)
	LEFT JOIN `client_contract` `ccc` ON `ccc`.`id` = `cl`.`contract_id`
	LEFT JOIN `client_contragent` `cc` ON `cc`.id = `ccc`.contragent_id
	LEFT JOIN `tt_stages` `ts_last` ON `ts_last`.`stage_id` = `tt`.`cur_stage_id`
	LEFT JOIN `tt_states` st ON `st`.id = `ts_last`.state_id
	LEFT JOIN `newbills` nb ON nb.bill_no = tt.bill_no
	LEFT JOIN `newbills_add_info` nai on nai.bill_no = nb.bill_no
	WHERE `cr`.`enabled` = 'yes'
	GROUP BY
	    `cr`.`id`,
	    `cr`.`name`,
	    `cr`.`depart`,
	    `ts`.`date_start`
	ORDER BY
	    `cr`.`depart`,
	    `cr`.`name`
        ");


        $flag_chck = false;
        $hours_tpl = array();
        for($i=0;$i<24;$i++){
            $hours_tpl[$i]=false;
        }
        $doers = array();
        while($row=$db->NextRecord()){
            if(!isset($doers[$row['depart']])){
                $doers[$row['depart']] = array();
            }
            if(!isset($doers[$row['depart']][$row['id']])){
                $doers[$row['depart']][$row['id']] = array(
                    'name'=>$row['name'],
                    'depart'=>$row['depart'],
                    'time'=>array()
                );
            }
            if(
                !is_null($row['t_date'])
            &&
                !isset($doers[$row['depart']][$row['id']]['time'][$row['t_date']])
            ){
                $doers[$row['depart']][$row['id']]['time'][$row['t_date']] = $hours_tpl;
            }

            if(!is_null($row['tf_date'])){
                $pod = explode(" ",$row['tf_date']);
                $pot = explode(":",$pod[1]);
                if(!isset($doers[$row['depart']][$row['id']]['time'][$row['t_date']][(int)$pot[0]]))
                {
                    $doers[$row['depart']][$row['id']]['time'][$row['t_date']][(int)$pot[0]] = array();
                }

                $doers[$row['depart']][$row['id']]['time'][$row['t_date']][(int)$pot[0]][] = array(
                    'state'=>$row['state'],
                    'client'=>$row['client'],
                    'bill_no' => $row["bill_no"],
                    'state_name' => $row["state_name"],
                    'trouble'=>$row['trouble_id'],
                    'is_wrong' => (int)$pot[1] != 0 ? $pot[0].":".$pot[1].":".$pot[2] : false,
                );

                if($row['its_here']=='Y'){
                    if($row['its_this']=='Y'){
                        $flag_chck = true;
                        $doers[$row['depart']][$row['id']]['time'][$row['t_date']]['here'] = (int)$pot[0];
                    }
                }elseif(!isset($doers[$row['depart']][$row['id']]['time'][$row['t_date']]['here']))
                    $doers[$row['depart']][$row['id']]['time'][$row['t_date']]['here'] = 'none';
            }
        }

        if($cast && $cast=='refix'){
            $design->assign('refix_flag',true);
        }else
            $design->assign('refix_flag',false);
        if($tr_id)
            $design->assign('tt_id',$tr_id);

        $design->assign('flag_chck',$flag_chck);
        $design->assign('tt_doers',$doers);
        $design->assign('dates',array(
            'yesterday'=>date('d-m-Y',$time - 60*60*24),
            'today'=>date('d-m-Y',$time),
            'tomorrow'=>date('d-m-Y',$time + 60*60*24),
            'key'=>array(
                'yesterday'=>date('Y-m-d',$time - 60*60*24),
                'today'=>date('Y-m-d',$time),
                'tomorrow'=>date('Y-m-d',$time + 60*60*24),
            )
        ));
        $design->assign('timetableShow',$someone);
    }

    function tt_courier_report()
    {
        global $db,$design;

        $design->assign("date_y", $y = get_param_integer("date_y", date("Y")));
        $design->assign("date_m", $m = get_param_integer("date_m", date("m")));

        $from = strtotime($y."-".$m."-01");
        $to = strtotime("+1 month", $from);

        $q = "select doer_id, depart, name, count,wm_count from (select doer_id, count(1) as count,sum(if(client='WiMaxComstar', 1,0)) as wm_count from (select  t.id as t_id, max(d.stage_id) as s_id from
            (SELECT distinct trouble_id as id FROM `tt_stages`
             where date_start between '".date("Y-m-d", $from)." 00:00:00' and '".date("Y-m-d", $to)." 00:00:00' and state_id in (2,20))  t, tt_stages s, tt_doers d
            where t.id = s.trouble_id and d.stage_id = s.stage_id group by t.id)a, tt_stages s, tt_doers d, tt_troubles t
            where a.s_id = s.stage_id and t.id = s.trouble_id and bill_no is not null and d.stage_id = s.stage_id
            group by doer_id)a
            left join courier c on c.id = doer_id
            where depart != 'Инженер'
            order by count desc";

        $design->assign("data", $db->AllRecords($q));
        $design->AddMain("tt/courier_report.tpl");
    }

    function tt_courier_report2()
    {
        global $db,$design;

        $design->assign("date_y", $y = get_param_integer("date_y", date("Y")));
        $design->assign("date_m", $m = get_param_integer("date_m", date("m")));

        $from = strtotime($y."-".$m."-01");
        $to = strtotime("+1 month", $from);

        $q = "select doer_id, depart, client, user_author,c.name, d.name as depart_name, sum(c) count from
            (select doer_id, client, user_author, count(1) as c from
             (select  t.id as t_id, max(d.stage_id) as s_id from
              (SELECT distinct trouble_id as id FROM `tt_stages`
               where date_start between '".date("Y-m-d", $from)." 00:00:00' and '".date("Y-m-d", $to)." 00:00:00' and state_id in (2,20))  t, tt_stages s, tt_doers d
              where t.id = s.trouble_id and d.stage_id = s.stage_id group by t.id)a, tt_stages s, tt_doers d, tt_troubles t
             where a.s_id = s.stage_id and t.id = s.trouble_id /*and bill_no is not null*/ and d.stage_id = s.stage_id
             group by doer_id, client, user_author)a

            left join courier c on c.id = doer_id
            left join user_users u on a.user_author = u.user
            left join user_departs d on u.depart_id = d.id
            where depart != 'Инженер'
            group by client, doer_id, user_author
            order by c.name, d.name desc
            ";

        $departs = array("Витрины" => array(), "Отделы" => array("-" => "-"));
        $doers = array();
        $d = array();
        $total = array();

        foreach($db->AllRecords($q) as $l)
        {
            $isVitrina = $l["user_author"] == "1c-vitrina" ? "Витрины" : "Отделы";

            if($l["user_author"] == "1c-vitrina" && stripos($l["client"], "id") === 0) $l["client"] = "WellTime";
            $l["depart_name"] = $l["user_author"] == "1c-vitrina" ? $l["client"] : $l["depart_name"];
            if($l["depart_name"] == "") $l["depart_name"] = "-";
            $departs[$isVitrina][$l["depart_name"]] = $l["depart_name"];

            $doers[$l["doer_id"]] = array("name" => $l["name"], "depart" => $l["depart"]);

            if(!isset($d[$l["doer_id"]]))$d[$l["doer_id"]] = array();
            if(!isset($d[$l["doer_id"]][$l["depart_name"]]))$d[$l["doer_id"]][$l["depart_name"]] = 0;
            $d[$l["doer_id"]][$l["depart_name"]] += $l["count"];

            if(!isset($total[$l["depart_name"]])) $total[$l["depart_name"]] = 0;
            $total[$l["depart_name"]] += $l["count"];
        }

        //printdbg($d);
        $design->assign("departs_info", array("Витрины" => count($departs["Витрины"]), "Отделы" => count($departs["Отделы"]), "all" => count($departs["Отделы"]) + count($departs["Витрины"])));

        $design->assign("data", $d);
        $design->assign("departs", $departs);
        $design->assign("doers", $doers);
        $design->assign("total", $total);
        $design->AddMain("tt/courier_report2.tpl");
    }
    function tt_doers_list($fixclient){
        global $db,$design;

        $dateFrom = new DatePickerValues('date_from', 'today');
        $dateTo = new DatePickerValues('date_to', 'today');
        $dateFrom->format = 'Y-m-d 00:00:00';$dateTo->format = 'Y-m-d 23:59:59';
        $date_begin = $dateFrom->getDay();
        $date_end = $dateTo->getDay();

        $ttype_filter = $ttype_filter_ = get_param_protected('ttype_filter','all');
        $design->assign('ttype_filter_selected',$ttype_filter);

        $doer_filter = $doer_filter_ = get_param_protected('doer_filter','null');
        $design->assign('doer_filter_selected',$doer_filter);

        // view bills without task
        $view_bwt = get_param_raw("do", "") ? get_param_raw("view_bwt", 0) : 1;
        $design->assign('view_bwt',$view_bwt);

        $view_calc = get_param_raw("view_calc", 0);
        $design->assign('view_calc',$view_calc);

        $doerId = 0;
        if($doer_filter == 'null'){
            $doer_filter = '';
        }else{
            $doerId = (int)$doer_filter;
            $doer_filter = '
                    AND
                    `cr`.`id` = '.((int)$doer_filter);
        }

        $state_filter = $state_filter_ = get_param_protected('state_filter', 'null');

        $design->assign("state_filter_selected", $state_filter);

        if($state_filter == "null")
        {
            $state_filter = " not in (2,20,21,39,40,48)";
        }elseif($state_filter == 2 || $state_filter == 20){
            $state_filter = " in (2,20,39,40,48)";
        }else{
            $state_filter = ' = "'.$state_filter.'"';
        }

        if($ttype_filter == 'all')
        {
            $query = "
                SELECT
                    DATE(`date`) `date`,
                    `courier_name`,
                    `company`,
                    `task`,
                    `cur_state`,
                    `tt_id`,
                    `client_id`,
                    `type`,
                    `trouble_cur_state`,
                    `bill_no`
                FROM
                    (
                        SELECT
                            `ln`.`created_at` `date`,
                            `cr`.`name` `courier_name`,
                            `cc`.`name` `company`,
                            ROUND(SUM(`nb`.`sum`)+SUM(IFNULL(`nbl`.`sum`,0)),2) `task`,
                            0 `cur_state`,
                            0 `tt_id`,
                            `cl`.`id` `client_id`,
                            `nb`.`currency` `type`,
                            0 `trouble_cur_state`,
                            `nb`.`bill_no`
                        FROM
                            `newbills` `nb`
                        INNER JOIN `courier` `cr` ON `cr`.`id` = `nb`.`courier_id`".$doer_filter."

                        INNER JOIN ( 
                            select model_id, max(created_at) created_at
                            from `history_changes`
                            where model='Bill' and data_json like '%" . ($doerId ? "\"courier_id\":\"{$doerId}\"":"\"courier_id\":") ."%'
                            group by model_id
                            
                        ) `ln` ON `ln`.`model_id` = `nb`.`id`

                        LEFT JOIN `newbill_lines` `nbl` ON `nbl`.`bill_no` = `nb`.`bill_no`
                            AND `nbl`.`type` = 'zadatok'
                        LEFT JOIN `clients` `cl` ON `cl`.`id` = `nb`.`client_id`
                        LEFT JOIN `client_contract` `ccc` ON `ccc`.`id` = `cl`.`contract_id`
                        LEFT JOIN `client_contragent` `cc` ON `cc`.id = `ccc`.contragent_id
                        WHERE `nb`.`courier_id` > 0
                            ".($view_bwt ? "" : " and false ")."
                        AND `ln`.`created_at` BETWEEN '".$date_begin."' AND '".$date_end."'
                        GROUP BY
                            `ln`.`created_at`,
                            `cr`.`name`,
                            `cc`.`name`,
                            `nb`.`currency`
                    UNION
                        SELECT distinct
                            `ts`.`date_start` `date`,
                            `cr`.`name` `courier_name`,
                            `cc`.`name` `company`,
                            `tt`.`problem` `task`,
                            `ts`.`state_id` `cur_state`,
                            `tt`.`id` `tt_id`,
                            `cl`.`id` `client_id`,
                            'ticket' `type`,
                            cts.state_id `trouble_cur_state`,
                            `tt`.`bill_no`
                        FROM
                            `tt_stages` `ts`
                        INNER JOIN `tt_doers` `td` ON `td`.`stage_id` = `ts`.`stage_id`
                        INNER JOIN `courier` `cr` ON `cr`.`id` = `td`.`doer_id`".$doer_filter."
                        LEFT JOIN  `tt_troubles` `tt` ON `tt`.`id` = `ts`.`trouble_id`
                        left join   tt_stages cts on cts.stage_id = tt.cur_stage_id
                        LEFT JOIN  `clients` `cl` ON `cl`.`client` = `tt`.`client`
                        LEFT JOIN `client_contract` `ccc` ON `ccc`.`id` = `cl`.`contract_id`
                        LEFT JOIN `client_contragent` `cc` ON `cc`.id = `ccc`.contragent_id
                        WHERE
                            /*`ts`.`state_id` = 4
                        AND */
                            cts.state_id ".$state_filter."
                        and
                            `ts`.`date_start` BETWEEN '".$date_begin."' AND '".$date_end."'
                    ) `tbl`
                ORDER BY
                    `date`,
                    `company`,
                    `courier_name`
            ";
        }elseif($ttype_filter == 'bills')
            $query = "
                SELECT
                    DATE(`date`) `date`,
                    `courier_name`,
                    `company`,
                    `task`,
                    0 `cur_state`,
                    0 `tt_id`,
                    `client_id`,
                    `type`,
                    0 trouble_cur_state,
                    bill_no
                FROM
                    (
                        SELECT
                            `ln`.`created_at` `date`,
                            `cr`.`name` `courier_name`,
                            `cc`.`name` `company`,
                            ROUND(SUM(`nb`.`sum`)+SUM(IFNULL(`nbl`.`sum`,0)),2) `task`,
                            `cl`.`id` `client_id`,
                            `nb`.`currency` `type`,
                            `nb`.`bill_no` bill_no
                        FROM `newbills` `nb`
                        INNER JOIN `courier` `cr` ON `cr`.`id` = `nb`.`courier_id`".$doer_filter."
                        INNER JOIN `history_changes` `ln` ON `ln`.model='Bill' and `ln`.`model_id` = `nb`.`id`
                                    and ln.data_json like concat('%\"courier_id\":\"',cr.id,'\"%')
                        LEFT JOIN `newbill_lines` `nbl` ON `nbl`.`bill_no` = `nb`.`bill_no`
                                    AND `nbl`.`type` = 'zadatok'
                        LEFT JOIN `clients` `cl` ON `cl`.`id` = `nb`.`client_id`
                        LEFT JOIN `client_contract` `ccc` ON `ccc`.`id` = `cl`.`contract_id`
                        LEFT JOIN `client_contragent` `cc` ON `cc`.id = `ccc`.contragent_id
                        WHERE `nb`.`courier_id` > 0
                            ".($view_bwt ? "" : " and false ")."
                        AND
                            `ln`.`created_at` BETWEEN '".$date_begin."' AND '".$date_end."'
                        AND
                            `ln`.`id` = (
                                    select id
                                    from `history_changes`
                                    where model='Bill' and model_id=nb.id and data_json like concat('%\"courier_id\":\"',cr.id,'\"%')
                                    order by created_at desc
                                    limit 1
                            )
                        GROUP BY
                            `ln`.`created_at`,
                            `cr`.`name`,
                            `cc`.`name`,
                            `nb`.`currency`
                    ) `tbl`
                ORDER BY
                    `date`,
                    `company`,
                    `courier_name`
            ";
        elseif($ttype_filter == "troubles")
            $query = "
                SELECT distinct
                    DATE(`date`) `date`,
                    `courier_name`,
                    `company`,
                    `task`,
                    `cur_state`,
                    `client_id`,
                    `tt_id`,
                    `type`,
                    trouble_cur_state,
                    `bill_no`
                FROM
                    (
                        SELECT distinct
                            `ts`.`date_start` `date`,
                            `cr`.`name` `courier_name`,
                            `cc`.`name` `company`,
                            `tt`.`problem` `task`,
                            `ts`.`state_id` `cur_state`,
                            `tt`.`id` `tt_id`,
                            `cl`.`id` `client_id`,
                            'ticket' `type`,
                            cts.state_id `trouble_cur_state`,
                            `tt`.`bill_no`
                        FROM `tt_stages` `ts`

                        INNER JOIN `tt_doers` `td` ON `td`.`stage_id` = `ts`.`stage_id`
                        INNER JOIN `courier` `cr` ON `cr`.`id` = `td`.`doer_id`".$doer_filter."

                        LEFT JOIN `tt_troubles` `tt`  ON `tt`.`id` = `ts`.`trouble_id`

                        LEFT JOIN `tt_stages`   `cts` ON cts.stage_id = tt.cur_stage_id
                        LEFT JOIN `clients`     `cl`  ON `cl`.`client` = `tt`.`client`
                        LEFT JOIN `client_contract` `ccc` ON `ccc`.`id` = `cl`.`contract_id`
                        LEFT JOIN `client_contragent` `cc` ON `cc`.id = `ccc`.contragent_id

                        WHERE
                            ".($view_bwt ? "" : "tt.id > 0 and ")."
                            /*`ts`.`state_id` = 4
                        AND */
                            cts.state_id ".$state_filter."
                        and
                            `ts`.`date_start` BETWEEN '".$date_begin."' AND '".$date_end."'
                    ) `tbl`
                ORDER BY
                    `date`,
                    `company`,
                    `courier_name`
            ";
        else
            $query = "
                SELECT
                    DATE(`date`) `date`,
                    `courier_name`,
                    `company`,
                    `task`,
                    `cur_state`,
                    `tt_id`,
                    `client_id`,
                    `type`,
                    `trouble_cur_state`,
                    `bill_no`
                FROM
                    (
                        SELECT
                            `ln`.`created_at` `date`,
                            `cr`.`name` `courier_name`,
                            `cc`.`name` `company`,
                            ROUND(SUM(`nb`.`sum`)+SUM(IFNULL(`nbl`.`sum`,0)),2) `task`,
                            0 `cur_state`,
                            0 `tt_id`,
                            `cl`.`id` `client_id`,
                            `nb`.`currency` `type`,
                            0 `trouble_cur_state`,
                            `nb`.`bill_no`
                        FROM
                            `newbills` `nb`
                        INNER JOIN `courier` `cr` ON `cr`.`id` = `nb`.`courier_id`".$doer_filter."
                        INNER JOIN `history_changes` `ln` ON nl.model='Bill' and `ln`.`model_id` = `nb`.`id`
                            and ln.data_json like concat('%\"courier_id\":\"',cr.id,'\"%')
                        LEFT JOIN `newbill_lines` `nbl` ON `nbl`.`bill_no` = `nb`.`bill_no`
                            AND `nbl`.`type` = 'zadatok'
                        LEFT JOIN `clients` `cl` ON `cl`.`id` = `nb`.`client_id`
                        LEFT JOIN `client_contract` `ccc` ON `ccc`.`id` = `cl`.`contract_id`
                        LEFT JOIN `client_contragent` `cc` ON `cc`.id = `ccc`.contragent_id
                        WHERE `nb`.`courier_id` > 0
                            ".($view_bwt ? "" : " and false ")."
                        AND `ln`.`created_at` BETWEEN '".$date_begin."' AND '".$date_end."'
                        AND
                            `ln`.`id` = (
                                    select id
                                    from `history_changes`
                                    where model='Bill' and model_id=nb.id and data_json like concat('%\"courier_id\":\"',cr.id,'\"%')
                                    order by created_at desc
                                    limit 1
                            )
                        GROUP BY
                            `ln`.`created_at`,
                            `cr`.`name`,
                            `cc`.`name`,
                            `nb`.`currency`
                    UNION
                    SELECT distinct
                            `s`.`date_start` `date`,
                            `cr`.`name` `courier_name`,
                            `cc`.`name` `company`,
                            `tt`.`problem` `task`,
                            `s`.`state_id` `cur_state`,
                            `tt`.`id` `tt_id`,
                            `cl`.`id` `client_id`,
                            'ticket' `type`,
                            cts.state_id `trouble_cur_state`,
                            `tt`.`bill_no`from
                                (select  max(s2.stage_id) as stage_id from tt_stages s
                                inner join tt_troubles t on (t.id = s.trouble_id)
                                inner join tt_stages s2 on (t.id = s2.trouble_id)
                                inner JOIN `tt_doers` `td` ON `td`.`stage_id` = `s2`.`stage_id` and doer_id = ".$doerId."
                                where s.`date_start` BETWEEN '".$date_begin."' AND '".$date_end."'
                                and s.state_id  in (2,20) group by t.id )a, tt_stages s
                        LEFT JOIN `tt_troubles` `tt`  ON `tt`.`id` = s.trouble_id
                        LEFT JOIN `tt_stages`   `cts` ON cts.stage_id = tt.cur_stage_id
                        LEFT JOIN `clients`     `cl`  ON `cl`.`client` = `tt`.`client`
                        LEFT JOIN `client_contract` `ccc` ON `ccc`.`id` = `cl`.`contract_id`
                        LEFT JOIN `client_contragent` `cc` ON `cc`.id = `ccc`.contragent_id
                        INNER JOIN `tt_doers` `td` ON `td`.`stage_id` = `s`.`stage_id`
                        INNER JOIN `courier` `cr` ON `cr`.`id` = `td`.`doer_id`
                        where s.stage_id = a.stage_id
                    ) `tbl`
                ORDER BY
                    `date`,
                    `company`,
                    `courier_name`
            ";

        if($view_calc)
        {

        $query = "SELECT tbl2.*, nb2.sum as bill_sum,
           if(tbl2.bill_no is not null and tbl2.bill_no != '',(SELECT round(SUM(IF(type='good', `sum`,0)),2) FROM newbill_lines nbl2 WHERE nbl2.bill_no = tbl2.bill_no),0) AS sum_good,

           if(tbl2.bill_no is not null and tbl2.bill_no != '',(SELECT SUM(IF(type='good', 1,0)) FROM newbill_lines nbl2 WHERE nbl2.bill_no = tbl2.bill_no),0) AS count_good,
           if(tbl2.bill_no is not null and tbl2.bill_no != '',(SELECT SUM(IF(type='service', 1,0)) FROM newbill_lines nbl2 WHERE nbl2.bill_no = tbl2.bill_no),0) AS count_service,

           if(tbl2.bill_no is not null and tbl2.bill_no != '',(SELECT round(SUM(IF(type='service', `sum`,0)),2) AS sum_good FROM newbill_lines nbl2 WHERE nbl2.bill_no = tbl2.bill_no),0) AS sum_service,


            (select  max(if(l.item_id is null || l.item_id = '',1,0)) from newbill_lines l where l.bill_no = nb2.bill_no ) is_stat,
            (select  max(if(group_id in (select id from g_groups where parent_id in (5049, 5003)) || group_id in (5049,5003),1,0)) from newbill_lines l, g_goods g where l.bill_no = nb2.bill_no and l.item_id = g.id ) is_25,
            (select  max(if(group_id in (select id from g_groups where parent_id in (5004)) || group_id = '5004',1,0)) from newbill_lines l, g_goods g where l.bill_no = nb2.bill_no and l.item_id = g.id ) is_250


            FROM (".$query.") tbl2
            LEFT JOIN newbills nb2 ON tbl2.bill_no = nb2.bill_no
            ";
        }


        $ret = array();

        $db->Query($query);
        $sumBonus = 0;
        $count = 0;
        while($row=$db->NextRecord(MYSQL_ASSOC)){
            if(!isset($ret[$row['date']])){
                $ret[$row['date']] = array('rowspan'=>0,'doers'=>array());
            }

            $ret[$row['date']]['rowspan']++;

            $bonus = 0;
            if($view_calc){
                $bonus = ($row["is_25"] ? $row["sum_service"]*0.25 : ($row["is_stat"] || $row["is_250"] || $row["sum_service"] || !$row["bill_no"]? 250 : 0));
                $sumBonus += $bonus;
            }else{
                $row["bill_sum"] = $row["sum_good"] = $row["sum_service"] = $row["count_service"] = $row["count_good"] = 0;
            }

            $ret[$row['date']]['doers'][] = array(
                'name'=>$row['courier_name'],
                'company'=>$row['company'],
                'task'=>stripslashes($row['task']),
                'cur_state'=>$row['cur_state'],
                'tt_id'=>$row['tt_id'],
                'client_id'=>$row['client_id'],
                'type'=>$row['type'],
                'trouble_cur_state'=>$row['trouble_cur_state'],
                'bill_no' => $row["bill_no"],
                'bill_sum' => $row["bill_sum"],
                'sum_good' => $row["sum_good"],
                'count_good' => $row["count_good"],
                'sum_service' => $row["sum_service"],
                'count_service' => $row["count_service"],
                'bonus' => $bonus
            );
            $count++;

        }

        $design->assign('sum_bonus',$sumBonus);
        $design->assign('count',$count);
        $design->assign_by_ref('report_data',$ret);
        if(get_param_protected('print',false)){
            $design->assign('print',true);
            $design->ProcessEx('tt/doers_report.tpl');
        }else{
            if(count($_POST)>0)
                $design->assign(
                    'print_report',
                    '?module=tt'.
                    '&action=doers_list'.
                    '&print=yes'.
                    '&date_begin_d='.$_POST['date_begin_d'].
                    '&date_begin_m='.$_POST['date_begin_m'].
                    '&date_begin_y='.$_POST['date_begin_y'].
                    '&date_end_d='.$_POST['date_end_d'].
                    '&date_end_m='.$_POST['date_end_m'].
                    '&date_end_y='.$_POST['date_end_y'].
                    (($doer_filter_<>'null')?'&doer_filter='.$doer_filter_:'').
                    (($ttype_filter_<>'null')?'&ttype_filter='.$ttype_filter_:'').
                    (($state_filter_<>'null')?'&state_filter='.$state_filter_:'')
                );

            $dDoers = array('null' => 'Все');
            foreach($db->AllRecords("
                        SELECT
                            `id`,
                            `depart`,
                            `name`
                        FROM
                            `courier`
                        WHERE
                            `enabled`='yes'
                        ORDER BY
                            `depart`,
                            `name`
                    ", null, MYSQL_ASSOC) as $id => $d)
            {
                $dDoers[$d["depart"]][$d["id"]] = $d["name"];
            }
            $design->assign('doer_filter', $dDoers);
            /*
            $design->assign(
                'doer_filter',
                array_merge(
                    array(array('id'=>'null','name'=>'Все','depart'=>'')),
                    $db->AllRecords("
                        SELECT
                            `id`,
                            `depart`,
                            `name`
                        FROM
                            `courier`
                        WHERE
                            `enabled`='yes'
                        ORDER BY
                            `depart`,
                            `name`
                    ", null, MYSQL_ASSOC)
                )
            );*/

            $design->assign(
                'l_state_filter',
                array_merge(
                    array(
                        array('id'=>'null','name'=>'Все (кроме: закрыт, отказ)'),
                    ),
                    $db->AllRecords("
                        SELECT
                            `id`,
                            `name`
                        FROM
                            `tt_states`
                        where
                            pk & 17703 #2047
                        ORDER BY
                            `name`
                    ", null, MYSQL_ASSOC)
                )
            );

            $design->assign('tt_states_list',$db->AllRecords('select * from tt_states','id',MYSQL_ASSOC));

            $design->AddMain('tt/doers_report.tpl');
        }
    }
    function tt_refix_doers($fixclient){
        if(count($_POST)>0){
            global $db,$design;
            $trouble_id = (int)$_POST['id'];

            $row = $db->GetRow($q="
                SELECT
                    `cur_stage_id` `stage_id`
                FROM
                    `tt_troubles`
                WHERE
                    `id` = ".$trouble_id
            );

            if(!$row['stage_id']){
                $design->assign('BIG_ERROR',true);
                $this->showTimeTable(true,'refix');
                return false;
            }

            if(isset($_POST['doer_fix'])){
                $date = array_keys($_POST['doer_fix']);
                $date = $date[0];
                $time = array_keys($_POST['doer_fix'][$date]);
                $time = $time[0];
                $date_start = $date." ".$time.":00:00";
                $db->Query("
                    UPDATE
                        `tt_stages`
                    SET
                        `date_start` = '".$date_start."'
                    WHERE
                        `stage_id` = ".$row['stage_id']
                );
            }
            //$db->Query("LOCK TABLES `tt_doers` WRITE");
            $db->Query('start transaction');
            $db->Query("DELETE FROM `tt_doers` WHERE `stage_id`=".$row['stage_id']);
            $this->doers_action('fix_doers', $trouble_id, $row['stage_id']);
            //$db->Query("UNLOCK TABLES");
            $db->Query('commit');
            $design->assign('BIG_VICTORY',true);
            $this->showTimeTable(true,'refix');
            return false;
        }elseif(count($_GET)>0){
            $_GET['id'] = (int)$_GET['tt_id'];
            $this->showTimeTable(true,'refix');
        }
        return false;
    }
    function doers_action($action,$trouble=null,$stage=null){
        global $db,$design;
        switch($action){
            case "show_panel":{
                break;
            }case "fix_doers":{
                $trouble = (int)$trouble;
                $stage = (int)$stage;
                if(!($stage > 0) || !isset($_POST['doer_fix']))
                    break;
                $doers = $_POST['doer_fix'];
                $date = array_keys($doers);
                $date = $date[0];
                $time = array_keys($doers[$date]);
                $time = $time[0];
                $doers = array_keys($doers[$date][$time]);

                $query = "
                    INSERT INTO `tt_doers`
                        (`stage_id`,`doer_id`)
                    VALUES
                        ";
                $dscnt = 0;
                $toSendDoer = array();
                foreach($doers as $doer){
                    $edoer = (int)$doer;
                    if(!($edoer > 0))
                        break;
                    $toSendDoer[] = $edoer;
                    $query .= "(".$stage.",".$edoer."),";
                    $dscnt++;
                }
                if($dscnt == 0)
                    break;
                $query = substr($query, 0, strlen($query)-1);

                $db->Query($query,0);

                if($toSendDoer) {
                    foreach($toSendDoer as $edoer)
                        $this->sendDoerToAll4geo($trouble, $edoer);
                }
                return true;
                break;
            }
        }
    }

    function checkTroubleToSend($tId)
    {
        global $db;

        $rs = $db->AllRecords("SELECT user_main,comment  FROM `tt_stages` where trouble_id='".$tId."' order by stage_id desc limit 2");

        $user = $userFrom = false;
        $comment = "";
        $user_login = Yii::$app->user->getIdentity();

        if(count($rs) == 1 && $user_login->user != $rs[0]["user_main"]) //create
        {
            $user = $rs[0]["user_main"];
            $comment = $rs[0]["comment"];
        }elseif(count($rs) == 2 && $rs[0]["user_main"] != $rs[1]["user_main"]){
            $user = $rs[0]["user_main"];
            $userFrom = $rs[1]["user_main"];
            $comment = $rs[1]["comment"];
        }

        $p = $db->GetRow("select user_author, client, problem from tt_troubles where id = ".$tId);

        if($user && $p["user_author"] != "1c-vitrina")
        {
            sender::sendICQMsg($user,
                    "Заявка #".$tId." (клиент ".$p["client"].") назначен: ".$user.($userFrom ? " (был ".$userFrom.")" : "")."\n".
                    "Создатель: ".$p["user_author"]."\n".
                    "Проблема: ".$p["problem"]."\n\n".
                    ($comment ? "Последний коментарий: ".$comment."\n\n" : "")
                    );
        }
    }

    function checkTroubleToSendToAll4geo($troubleId)
    {
        global $db;

        $doerId = $db->GetValue(" select d.doer_id from tt_stages s, tt_doers d where s.trouble_id ='".$troubleId."' and s.stage_id = d.stage_id order by s.stage_id desc limit 1");
        if($doerId)
        {
            $this->sendDoerToAll4geo($troubleId, $doerId);
        }
    }

    function sendDoerToAll4geo($troubleId, $doerId)
    {
        global $db;
        static $cach = array();

        if(isset($cach[$troubleId]))
            return;

        $cach[$troubleId] = 1;

        $billNo = $db->GetValue("select bill_no from tt_troubles where id = '".$troubleId."'");
        if($billNo) {
            all4geo::getId($billNo, $doerId, @$_POST["comment"]);
        }else{
            all4geo::getId($troubleId, $doerId, @$_POST["comment"], true);
        }
    }

    function tt_doers($fixclient){
        global $db,$design;
        $this->curclient = $fixclient;
        if((isset($_POST['change']) && $_POST['change']) || (isset($_POST['append'])) || isset($_GET['drop'])){
            if(isset($_GET['drop']) && is_numeric($_GET['drop']) && $_GET['drop']){
                $query = "
                    DELETE FROM
                        `courier`
                    WHERE
                        `id` = ".((int)$_GET['drop'])."
                ";
                $db->Query($query);
                Header("Location: ?module=tt&action=doers");
                exit();
            }elseif(isset($_POST['append'])){
                $query = "
                    INSERT INTO `courier`
                        (`name`,`depart`,`enabled`)
                    VALUES
                        ('".addslashes($_POST['doer_name'])."','".str_replace("'", "\'", str_replace("\\","\\\\",$_POST['doer_depart']))."','".((isset($_POST['doer_active']))?'yes':'no')."')
                ";
            }elseif($_POST['change']){
                $query = "
                    UPDATE
                        `courier`
                    SET
                        `name` = '".addslashes($_POST['doer_name'])."',
                        `depart` = '".str_replace("'", "\'", str_replace("\\","\\\\",$_POST['doer_depart']))."',
                        `enabled` = '".((isset($_POST['doer_active']))?'yes':'no')."'
                    WHERE
                        `id` = ".((int)$_POST['change'])."
                ";
            }
            $db->Query($query);
        }

        $query = "
            SELECT
                `id`,
                `name`,
                `depart`,
                `enabled`
            FROM
                `courier`
            ORDER BY
                `depart`,
                `enabled`,
                `name`
        ";
        $doers = array();
        $db->Query($query);
        while($row=$db->NextRecord(MYSQL_ASSOC)){
            if(!isset($doers[$row['depart']]))
                $doers[$row['depart']] = array();
            $doers[$row['depart']][] = $row;
        }
        $design->assign('doers',$doers);
        $design->assign('departs',array_keys($doers));
        $design->AddMain('tt/doers_edit.tpl');
    }

    function tt_rpc_setState1c(){
        global $db, $user;
        if(!isset($_POST['bill_no']) || !isset($_POST['state'])){
            header('Location: index.php?module=tt&action=view&id='.$_POST['id']);
            exit();
        }
        $bill = $_POST['bill_no'];
        $state = $_POST['state'];
        include_once(INCLUDE_PATH.'1c_integration.php');
        $bs = new \_1c\billMaker($db);
        $fault = null;
        $f = $bs->setOrderStatus($bill, $state, $fault);
        if(!$f){
            trigger_error2("Не удалось обновить статус заказа:<br /> ".\_1c\getFaultMessage($fault)."<br />");
            echo "<br /><br />";
            echo "<a href='index.php?module=tt&action=view&id=".$_POST['id']."'>Вернуться к заявке</a>";
            exit();
        }
        if($f){
            $oBill = \app\models\Bill::findOne(['bill_no' => $bill]);
            if ($oBill) {
                if (strcmp($state, 'Отказ') == 0) {
                    $oBill->sum = 0;
                    $oBill->sum_with_unapproved = 0;
                    event::setReject($bill, $state);
                }
                $oBill->state_1c = $_POST['state'];
                $oBill->save();
            }
        }
        header('Location: index.php?module=tt&action=view&id='.$_POST['id']);
        exit();
    }
    function tt_store_limit($fixclient)
    {
        include 'StoreLimitReport.php';
        StoreLimitReport::getData();

    }
    function tt_save_limits($fixclient)
    {
        include 'StoreLimitReport.php';
        StoreLimitReport::saveData();
    }
}
?>
