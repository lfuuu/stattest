<?php

/** @var $number app\models\Number */
/** @var \app\classes\BaseView $this */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Number;
use app\models\NumberLog;
use kartik\widgets\ActiveForm;

echo app\classes\Html::formLabel($this->title = 'Номер '. $number->number);

echo \yii\widgets\Breadcrumbs::widget([
    'links' => [
        'Телефония',
        ['label' => 'Номера', 'url' => $cancelUrl = '/voip/number'],
        ['label' => $number->number, 'url' => $number->getUrl()]
    ],
]);

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
                <?php if ($number->status == Number::STATUS_NOTACTIVE_HOLD): ?>
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

            $this->registerJsVariable('numberFormId', $form->getId());

            echo Html::activeHiddenInput($actionForm, 'scenario', ['id' => 'scenario']);
            echo Html::activeHiddenInput($actionForm, 'did');
            echo Html::activeHiddenInput($actionForm, 'client_account_id');
            echo Html::activeHiddenInput($actionForm, 'hold_month', ['id' => 'hold_month']);

            switch ($number->status) {
                case Number::STATUS_INSTOCK:

                    if ($actionForm->client_account_id) {
                        $clientAccount = ClientAccount::findOne($actionForm->client_account_id);
                        echo Html::button('Зарезервировать за клиентом ' . $clientAccount->id . ' ' . $clientAccount->company, ['class' => 'btn btn-primary', 'onclick' => "numberSubmitForm('startReserve')"]) . "<br>";
                    }

                    echo Html::button('Поместить в отстойник (6 месяцев)', ['class' => 'btn btn-primary col-sm-12', 'onclick' => "numberHoldSubmitForm('6')"]);
                    echo Html::button('Поместить в отстойник (3 месяца)', ['class' => 'btn btn-primary col-sm-12', 'onclick' => "numberHoldSubmitForm('3')"]);
                    echo Html::button('Поместить в отстойник (1 месяц)', ['class' => 'btn btn-primary col-sm-12', 'onclick' => "numberHoldSubmitForm('1')"]);

                    echo Html::button('Номер не продается', ['class' => 'btn btn-primary col-sm-12', 'onclick' => "numberSubmitForm('startNotSell')"]);

                    echo "<br>" . Html::button('Высвободить номер', ['class' => 'btn btn-danger btn-sm col-sm-3', 'style' => 'margin-top: 200px;', 'onclick' => "numberSubmitForm('toRelease')"]);
                    break;

                case Number::STATUS_NOTACTIVE_RESERVED:
                    echo Html::button('Снять с резерва', ['class' => 'btn btn-primary col-sm-12', 'onclick' => "numberSubmitForm('stopReserve')"]);
                    break;

                case Number::STATUS_NOTACTIVE_HOLD:
                    echo Html::button('Убрать из отстойника', ['class' => 'btn btn-primary col-sm-12', 'onclick' => "numberSubmitForm('stopHold')"]);
                    break;

                case Number::STATUS_NOTSALE:
                    echo Html::button('Номер продается', ['class' => 'btn btn-primary col-sm-12', 'onclick' => "numberSubmitForm('stopNotSell')"]);
                    break;
            }

            if ($number->is7800()) {
                echo "<br />";

                ?>
                <br />
                <div class="well" style="width: 500px;">
                    <fieldset>
                        <label>Тех номер</label>
                        <div class="col-sm-12">
                            <div class="col-sm-6">
                                <?php
                                echo Html::activeTextInput($actionForm, 'number_tech', ['class' => 'form-control']);
                                ?>
                            </div>
                            <div class="col-sm-6">
                                <?= Html::button('Установить тех номер',
                                    ['class' => 'btn btn-info', 'onclick' => "saveTechNumber()"]) ?>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <?php
            }

            $form->end();
            ?>
        </td>
        <td valign="top" width="50%" style="padding: 10px">
            <?php if (!empty($logList)) : ?>
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
                                        <?=Html::a($log['client'], ['/client/view', 'id' => $log['client']], ['style' => 'text-decoration:none;font-weight:bold']) ?>
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
                                        ?><b>Номер не продается</b><?php break;
                                    case NumberLog::ACTION_SALE:
                                        ?><b>Номер продается</b><?php break;
                                    case NumberLog::ACTION_INVERTRESERVED:
                                        if ($log['addition'] == 'Y') {
                                            ?><b>Зарезервирован.</b><?php
                                        } else {
                                            ?><b>Снят резерв.</b><?php
                                        }
                                        if ($log['client_id']) {
                                            ?>, Л/С: <a href='/client/view?id=<?= $log['client_id'] ?>'
                                                     style='text-decoration:none;font-weight:bold'><?= $log['client_id'] ?></a>
                                            <?php
                                        };
                                        break;
                                    case NumberLog::ACTION_ACTIVE:
                                        if ($log['addition'] == NumberLog::ACTION_ADDITION_TESTED) {
                                            ?><b><?= Number::$statusList[Number::STATUS_ACTIVE_TESTED] ?></b><?php
                                        } else
                                        if ($log['addition'] == NumberLog::ACTION_ADDITION_COMMERCIAL) {
                                            ?><b><?= Number::$statusList[Number::STATUS_ACTIVE_COMMERCIAL] ?></b><?php
                                        } else {
                                            ?><b>Используется</b><?php
                                        }
                                        if ($log['client_id']) {
                                            ?>, Л/С: <a href='/client/view?id=<?= $log['client_id'] ?>'
                                                     style='text-decoration:none;font-weight:bold'><?= $log['client_id'] ?></a>
                                            <?php
                                        };

                                        break;
                                    case NumberLog::ACTION_CREATE:
                                        if ($log['addition'] == "N") {
                                            ?><b>Номер высвобожден</b><?
                                        } else {
                                            ?><b>Номер создан</b><?
                                        }
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