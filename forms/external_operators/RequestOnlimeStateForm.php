<?php

namespace app\forms\external_operators;

use Yii;
use app\classes\Form;
use app\models\Trouble;
use app\models\Bill;

class RequestOnlimeStateForm extends Form
{

    public
        $comment,
        $state_id;

    public function rules()
    {
        return [
            [['state_id'], 'required'],
            [['comment'], 'string'],
            [['state_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'state_id' => 'Состояние',
            'comment' => 'Комментарии',
        ];
    }

    public function save(Trouble $trouble)
    {
        if ($trouble->bill_no && preg_match("#\d{6}\/\d{4}#", $trouble->bill_no)) {
            $bill = Bill::findOne(['bill_no' => $trouble->bill_no]);
            $newstate = TroubleState::findOne($R['state_id']);

            if ($newstate->state_1c <> $bill->state_1c) {
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

        $this->createStage(
            $trouble['id'],
            $R,
            array(
                'comment'=>$comment,
                'stage_id'=>$trouble['stage_id']
            )
        );

        // todo: переделать на bill::getDocumentType
        if($trouble["bill_no"] && $trouble["trouble_type"] == "shop_orders")
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

        return false;
    }

}