<?php


define("PATH_TO_ROOT", "../../");

include PATH_TO_ROOT."conf.php";

$b = NewBill::find("201309/0093");


print_r($b->trouble->current_stage->state->name);

$newStateId = 21;

foreach(Trouble::find('all', array(
    "select" => "bill_no",
    "joins" => "inner join tt_stages s on cur_stage_id = s.stage_id and state_id = 16 and date_start < (now() - INTERVAL 7 day)",
    "conditions" => array("id" => "147313")
    )) as $t)
{
    echo $t->bill_no."<br>";

    continue;

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
                echo "Не удалось обновить статус заказа:<br /> ".\_1c\getFaultMessage($fault)."<br />";
                echo "<br /><br />";
                echo "<a href='index.php?module=tt&action=view&id=".$t->id."'>Вернуться к заявке</a>";
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
                    "user_main" => $t->current_stage->user_main,
                    "state_id" => 21
                    ),
                array(
                    'comment'=> Encoding::toKoi8r("Заказ снят с резерва по истечении 7 дней"),
                    'stage_id'=>$t->cur_stage_id
                    ),
                "sys"
                );
    }
}
