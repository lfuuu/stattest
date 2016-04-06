<?php
use app\models\ClientAccount;
use app\models\Number;
use app\models\NumberLog;
use kartik\widgets\ActiveForm;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
/** @var $number app\models\Number */
?>

<table width="100%">
    <tr>
        <td valign="top" width="50%" style="padding: 10px">
            <table class="table table-bordered table-striped table-condensed">
                <tr>
                    <td>Номер</td>
                    <th><?= $number->number ?></th>
                </tr>
                <tr>
                    <td>Город</td>
                    <th><?= $number->city->name ?></th>
                </tr>
                <tr>
                    <td>DID группа</td>
                    <th><?= $number->didGroup->name ?></th>
                </tr>
                <tr>
                    <td>Статус</td>
                    <th><?= Number::$statusList[$number->status] ?></th>
                </tr>
                <?php if ($number->client_id): ?>
                <tr>
                    <td>Лицевой счет</td>
                    <th>
                        <?php
                        $clientAccount = ClientAccount::findOne($number->client_id);
                        echo Html::a($clientAccount->id . ' ' . $clientAccount->company, '/client/view?id=' . $clientAccount->id);
                        ?>
                    </th>
                </tr>
                <?php endif; ?>
                <?php if ($number->status == Number::STATUS_HOLD): ?>
                <tr>
                    <td>В остойнике до:</td>
                    <th>
                    <?php
                        echo DateTimeZoneHelper::getDateTime($number->hold_to);
                    ?>
                    </th>
                </tr>
                <?php endif; ?>
            </table>

            <?php
            $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);
            echo Html::activeHiddenInput($actionForm, 'scenario', ['id' => 'scenario']);
            echo Html::activeHiddenInput($actionForm, 'did');
            echo Html::activeHiddenInput($actionForm, 'client_account_id');
            echo Html::activeHiddenInput($actionForm, 'hold_month', ['id' => 'hold_month']);

            if ($number->status == Number::STATUS_INSTOCK) {
                if ($actionForm->client_account_id) {
                    $clientAccount = ClientAccount::findOne($actionForm->client_account_id);
                    echo Html::button('Зарезервировать за клиентом ' . $clientAccount->id . ' ' . $clientAccount->company, ['class' => 'btn btn-primary', 'onclick' => "numberSubmitForm('startReserve')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                } else {
                    echo Html::button('Зарезервировать без указания клиента', ['class' => 'btn btn-primary', 'onclick' => "numberSubmitForm('startReserve')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }

                echo "<br>".Html::button('Поместить в отстойник (6 месяцев)', ['class' => 'btn btn-primary', 'onclick' => "numberHoldSubmitForm('6')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                echo "<br>".Html::button('Поместить в отстойник (3 месяца)', ['class' => 'btn btn-primary', 'onclick' => "numberHoldSubmitForm('3')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                echo "<br>".Html::button('Поместить в отстойник (1 месяц)', ['class' => 'btn btn-primary', 'onclick' => "numberHoldSubmitForm('1')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

                echo Html::button('Номер не продается', ['class' => 'btn btn-primary', 'onclick' => "numberSubmitForm('startNotSell')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if ($number->status == Number::STATUS_RESERVED) {
                echo Html::button('Снять с резерва', ['class' => 'btn btn-primary', 'onclick' => "numberSubmitForm('stopReserve')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if ($number->status == Number::STATUS_HOLD) {
                echo Html::button('Убрать из отстойника', ['class' => 'btn btn-primary', 'onclick' => "numberSubmitForm('stopHold')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if ($number->status == Number::STATUS_NOTSELL) {
                echo Html::button('Номер продается', ['class' => 'btn btn-primary', 'onclick' => "numberSubmitForm('stopNotSell')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            $form->end();
            ?>
        </td>
        <td valign="top" width="50%" style="padding: 10px">
            <?php if (!empty($logList)): ?>
                <table class="table table-bordered table-striped table-condensed table-hover">
                    <tr>
                        <th colspan='2'>Операции с номером</th>
                    </tr>
                    <?php foreach($logList as $log): ?>
                        <tr>
                            <td style='text-align:center;font-weight:bolder;color:#555'><?= DateTimeZoneHelper::getDateTime($log['human_time']) ?></td>
                            <td>
                                <a style='text-decoration:none;font-weight:bold' href='?module=employeers&user=<?= $log['user'] ?>'><?= $log['user'] ?></a>

                                <?php

                                switch ($log['action']) {
                                    case NumberLog::ACTION_FIX:
                                        ?>
                                        <b>зафиксирован</b> за клиентом
                                        <a style='text-decoration:none;font-weight:bold'
                                           href='?module=clients&id=<?= $log['client_id'] ?>'><?= $log['client'] ?></a>
                                        <?php break;
                                    case NumberLog::ACTION_UNFIX:
                                        ?>
                                        <b>снят</b> с клиента
                                        <a href='?module=clients&id=<?= $log['client_id'] ?>'
                                           style='text-decoration:none;font-weight:bold'><?= $log['client'] ?></a>
                                        <?php break;
                                    case NumberLog::ACTION_HOLD:
                                        ?><b>помещен в отстойник</b><?php break;
                                    case NumberLog::ACTION_UNHOLD:
                                        ?><b>убран из отстойника</b><?php break;
                                    case NumberLog::ACTION_NOTSALE:
                                        ?><b>Номер не продется</b><?php break;
                                    case NumberLog::ACTION_SALE:
                                        ?><b>Номер продется</b><?php break;
                                    case NumberLog::ACTION_INVERTRESERVED:
                                        if ($log['addition'] == 'Y') {
                                            ?><b>Зарезервирован.</b><?php
                                        } else {
                                            ?><b>Снят резерв.</b><?php
                                        }
                                        if ($log['client_id']) {
                                            ?>ЛС: <a href='/client/view?id=<?= $log['client_id'] ?>'
                                                     style='text-decoration:none;font-weight:bold'><?= $log['client_id'] ?></a>
                                            <?php
                                        };
                                        break;
                                        ?>
                                    <?php } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </td>
    </tr>
</table>



<script>
    function numberSubmitForm(scenario) {
        $('#scenario').val(scenario);
        $('#<?=$form->getId()?>').submit();
    }

    function numberHoldSubmitForm(hold_month) {
        $('#scenario').val("startHold");
        $('#hold_month').val(hold_month);
        $('#<?=$form->getId()?>').submit();
    }
</script>
