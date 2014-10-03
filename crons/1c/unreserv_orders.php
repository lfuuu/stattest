<?php


define("PATH_TO_ROOT", "../../");

include PATH_TO_ROOT."conf.php";


echo "\n".date("r").": ";

$newStateId = 21;

foreach(Trouble::find_by_sql(
    "select id, bill_no, user_main, cur_stage_id from tt_troubles
    inner join tt_stages s on cur_stage_id = s.stage_id and state_id = 16 and date_start < (now() - INTERVAL 7 day)
	where user_main in ('belyaev', 'li')"
    ) as $t)
{
    echo "\n".$t->bill_no." -> ".$t->user_main;

    $b = NewBill::find($t->bill_no);

    if($b->bill_no && preg_match("/\d{6}\/\d{4}/", $b->bill_no))
    {
        $bill = $db->GetRow("select * from newbills where bill_no='".addcslashes($t->bill_no,"\\'")."'");
        $newstate = $db->GetRow("select * from tt_states where id=".$newStateId);
        if($newstate['state_1c']<>$b->state_1c){
            require_once(INCLUDE_PATH.'1c_integration.php');
            $bs = new \_1c\billMaker($db);
            $fault = null;
            $f = $bs->setOrderStatus($b->bill_no, $newstate['state_1c'], $fault);
            if(!$f){
                echo "\nНе удалось обновить статус заказа:\n ".\_1c\getFaultMessage($fault)."\n";
                exit();
            }
            if($f){
                if (strcmp($newstate['state_1c'],'Отказ') == 0){
                    $db->Query($q="update newbills set sum=0, cleared_sum=0, state_1c='".$newstate['state_1c']."' where bill_no='".addcslashes($b->bill_no, "\\'")."'");
                    event::setReject($bill, $newstate);
                }else{
                    $db->Query($q="update newbills set state_1c='".$newstate['state_1c']."' where bill_no='".addcslashes($b->bill_no, "\\'")."'");
                }
            }
        }

        $GLOBALS['module_tt']->createStage(
                $t->id,
                array(
                    "user_main" => $t->user_main,
                    "state_id" => $newStateId
                    ),
                array(
                    'comment'=> "Заказ снят автоматически с резерва по истечении 7 дней",
                    'stage_id'=>$t->cur_stage_id
                    ),
                "sys"
                );
    }
}
