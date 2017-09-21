<?php
/**
 * Список универсальных услуг с пакетами. Форма. Ресурсы
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariff $accountTariffFirst
 * @var AccountTariff[][] $row
 * @var \kartik\form\ActiveForm $form
 */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use kartik\widgets\DatePicker;

$resources = $accountTariffFirst->resources;
if (!$resources) {
    return;
}
$dateTimeNow = $accountTariffFirst->clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента

?>
<br>
<div class="well">

    <?php
    foreach ($resources as $resource) :
        if (!$resource->isEditable()) {
            continue;
        }

        $unit = $resource->getUnit();
        ?>
        <div class="row">

            <div class="col-sm-4">
                <?= Html::encode($resource->name) ?><?= $unit ? Html::encode(', ' . $unit) : '' ?>:
            </div>

            <div class="col-sm-4">
                <?php

                /** @var AccountTariffResourceLog $accountTariffResourceLog */
                $accountTariffResourceLogsQuery = $accountTariffFirst->getAccountTariffResourceLogs($resource->id);
                $accountTariffResourceLog = null;
                foreach ($accountTariffResourceLogsQuery->each() as $accountTariffResourceLogTmp) {

                    if (!$accountTariffResourceLog) {
                        $accountTariffResourceLog = $accountTariffResourceLogTmp;
                    }
                    ?>
                    <div>
                        <b><?= $accountTariffResourceLogTmp->getAmount() ?></b>
                        <span class="account-tariff-log-actual-from">
                        (с <?= Yii::$app->formatter->asDate($accountTariffResourceLogTmp->actual_from, DateTimeZoneHelper::HUMAN_DATE_FORMAT) ?>)
                    </span>
                    </div>
                    <?php

                    if ($accountTariffResourceLogTmp->actual_from < $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT)) {
                        // совсем старые не нужно выводить здесь
                        break;
                    }
                }

                ?>
            </div>

            <div class="col-sm-4">

                <?=
                // отменить
                $accountTariffFirst->isResourceCancelable($resource) ?
                    Html::a(
                        Html::tag('i', '', [
                            'class' => 'glyphicon glyphicon-erase',
                            'aria-hidden' => 'true',
                        ]) . ' ' .
                        Yii::t('common', 'Reject'),
                        [
                            '/uu/account-tariff/resource-cancel',
                            'ids' => array_keys($row),
                            'resourceId' => $resource->id,
                        ],
                        [
                            'class' => 'btn btn-danger account-tariff-voip-button account-tariff-resource-button-cancel btn-xs',
                            'title' => 'Отклонить смену количества ресурса',
                        ]
                    ) :
                    ''
                ?>

                <?php
                // сменить
                if ($accountTariffFirst->isResourceEditable($resource) && $accountTariffResourceLog) : ?>

                    <?= Html::button(
                        Html::tag('i', '', [
                            'class' => 'glyphicon glyphicon-edit',
                            'aria-hidden' => 'true',
                        ]) . ' ' .
                        Yii::t('common', 'Change'),
                        [
                            'class' => 'btn btn-primary account-tariff-voip-button account-tariff-voip-resource-button-edit btn-xs',
                            'title' => 'Сменить количество ресурса',
                            'data-id' => $accountTariffFirst->id,
                            'data-resourceId' => $resource->id,
                        ]
                    ) ?>

                    <div class="account-tariff-voip-resource-div collapse">
                        <?php
                        $field = $form->field($accountTariffResourceLog, "[{$resource->id}]amount");

                        if ($resource->isNumber()) {
                            $params = ['type' => 'number', 'step' => 1, 'min' => $resource->min_value];
                            if ($resource->max_value) {
                                $params['max'] = $resource->max_value;
                            }

                            $field->textInput($params);
                        } else {
                            $field->checkbox(['value' => 1], $enclosedByLabel = false);
                        }

                        echo $field->label(false);
                        ?>
                    </div>

                <?php else : ?>

                    <div class="account-tariff-voip-resource-div collapse">
                        Нельзя сменить
                    </div>

                <?php endif ?>

            </div>

        </div>
    <?php endforeach ?>

    <?php
    if (!$accountTariffFirst->isNewRecord) :

        // оплачено до
        $actualTo = $accountTariffFirst->getDefaultActualFrom();

        $currentDate = date(DateTimeZoneHelper::DATE_FORMAT);

        $accountTariffResourceLog = new AccountTariffResourceLog(); // пустышка
        $accountTariffResourceLog->actual_from = $currentDate;
        ?>
        <div class="row account-tariff-voip-resource-div collapse">

            <div class="col-sm-4">
                <?= Html::submitButton(
                    Html::tag('i', '', [
                        'class' => 'glyphicon glyphicon-edit',
                        'aria-hidden' => 'true',
                    ]) . ' ' .
                    Yii::t('common', 'Save'),
                    [
                        'class' => 'btn btn-primary',
                    ]
                ) ?>
            </div>

            <div class="col-sm-4">
                <?= $form->field($accountTariffResourceLog, 'actual_from')
                    ->widget(DatePicker::className(), [
                        'removeButton' => false,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'startDate' => $currentDate,
                            'todayHighlight' => true,
                        ],
                    ])
                    ->label(false)
                ?>
            </div>

            <div class="col-sm-4">
                оплачено до <a class="pointer setActualFrom" data-date="<?= $actualTo ?>"><?= Yii::$app->formatter->asDate($actualTo) ?></a>
                <?= $this->render('//layouts/_help', [
                    'message' => 'Указанное количество ресурса уже оплачено (или входит в тариф) до указанной даты (не включительно).' . PHP_EOL .
                        'Уменьшение кол-ва ресурса до этой даты не повлияет на баланс.' . PHP_EOL .
                        'При увеличении кол-ва будут списаны деньги со счета за весь период до указанной даты.',
                ]); ?>
            </div>

        </div>
    <?php endif ?>

</div>
