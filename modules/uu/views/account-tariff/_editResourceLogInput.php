<?php
/**
 * Создание/редактирование универсальной услуги. Сменить количество ресурсов с определенной даты
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 * @var bool $isReadOnly
 */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\TariffResource;
use kartik\widgets\DatePicker;
use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;
$resources = $accountTariff->resources;
if (!$resources) {
    return;
}

$tariffPeriod = $accountTariff->tariffPeriod;

/** @var TariffResource[] $tariffResources */
if ($tariffPeriod) {
    $tariffResources = $tariffPeriod->tariff->tariffResourcesIndexedByResourceId;
} else {
    $tariffResources = [];
}

list($dateFrom,) = $accountTariff->getLastLogPeriod();
$currentDate = date(DateTimeZoneHelper::DATE_FORMAT);

$accountTariffResourceLog = new AccountTariffResourceLog(); // пустышка
$accountTariffResourceLog->resource_id = $formModel->serviceTypeId;
$accountTariffResourceLog->actual_from = $currentDate;

// оплачено до
$actualTo = $accountTariff->getDefaultActualFrom();

$accountTariffResourceLogTableName = AccountTariffResourceLog::tableName();
$tariffResourceTableName = TariffResource::tableName();
$resourceTableName = Resource::tableName();
?>

<div class="well tariffResources">
    <div class="row">
        <div class="col-sm-3"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'resource_id')) ?></label></div>
        <div class="col-sm-2"><label>История изменений</label></div>
        <div class="col-sm-1"><label>Диапазон значений</label></div>
        <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $accountTariffResourceLogTableName, 'amount')) ?></label></div>
        <div class="col-sm-1">
            <label>
                Оплачено, ед.
                <?= $this->render('//layouts/_help', [
                    'message' => 'Указанное количество ресурса уже оплачено (или входит в тариф) до указанной даты (не включительно).' . PHP_EOL .
                        'Уменьшение кол-ва ресурса до этой даты не повлияет на баланс.' . PHP_EOL .
                        'При увеличении кол-ва будут списаны деньги со счета за весь период до указанной даты.',
                ]); ?>
            </label>
        </div>
        <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'amount')) ?></label></div>
        <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_per_unit')) ?></label></div>
        <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_min')) ?></label></div>
    </div>

    <?php
    foreach ($resources as $resource) :
        $tariffResource = isset($tariffResources[$resource->id]) ? $tariffResources[$resource->id] : null;
        ?>

        <div class="row">

            <div class="col-sm-3">
                <label for="accounttariffresourcelog-<?= $resource->id ?>-amount"><?= Html::encode($resource->name) ?></label>
            </div>

            <div class="col-sm-2">
                <?= $this->render('_editResourceLogGrid', ['accountTariff' => $accountTariff, 'resource' => $resource]) ?>
            </div>

            <div class="col-sm-1">
                <?= $resource->getValueRange() ?>
            </div>

            <div class="col-sm-1">
                <?php
                $accountTariffResourceLog->amount = $accountTariff->getResourceValue($resource->id);

                if ($isReadOnly || !$accountTariff->isResourceEditable($resource)) {

                    // нельзя редактировать
                    echo $accountTariffResourceLog->getAmount();

                } else {

                    // можно редактировать
                    $field = $form->field($accountTariffResourceLog, "[{$resource->id}]amount");

                    if ($resource->isNumber()) {
                        $field->textInput(['type' => 'number', 'step' => 1]);
                    } else {
                        $field->checkbox(['value' => 1], $enclosedByLabel = false);
                    }

                    echo $field->label(false);
                }
                ?>
            </div>

            <div class="col-sm-1">
                <?php
                $maxPaidAmount = $tariffResource ? $tariffResource->amount : 0;

                /** @var AccountTariffResourceLog $accountTariffResourceLogTmp */
                $accountTariffResourceLogsQuery = $accountTariff->getAccountTariffResourceLogs($resource->id);
                foreach ($accountTariffResourceLogsQuery->each() as $accountTariffResourceLogTmp) {
                    if ($accountTariffResourceLogTmp->actual_from > $currentDate) {
                        // еще не действует
                        continue;
                    }

                    $maxPaidAmount = max($maxPaidAmount, $accountTariffResourceLogTmp->amount);

                    if ($accountTariffResourceLogTmp->actual_from < $dateFrom) {
                        // все старые уже не действуют
                        break;
                    }
                }

                unset($accountTariffResourceLogsQuery, $accountTariffResourceLogTmp);

                echo $resource->isNumber() ?
                    $maxPaidAmount :
                    ($maxPaidAmount ? '+' : '-');
                ?>
            </div>

            <div class="col-sm-1">
                <?= $tariffResource ? $tariffResource->getAmount() : '' ?>
            </div>

            <div class="col-sm-1">
                <?= $tariffResource ? $tariffResource->price_per_unit : '' ?>
            </div>

            <div class="col-sm-1">
                <?= $tariffResource ? $tariffResource->price_min : '' ?>
            </div>

        </div>

    <?php endforeach ?>

    <?php if (!$isReadOnly && !$accountTariff->isNewRecord) : ?>
        <div class="row">

            <div class="col-sm-3">
                <?= Html::submitButton(
                    Html::tag('i', '', [
                        'class' => 'glyphicon glyphicon-edit',
                        'aria-hidden' => 'true',
                    ]) . '  Установить новые значения',
                    [
                        'class' => 'btn btn-primary',
                    ]
                ) ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($accountTariffResourceLog, 'actual_from')
                    ->widget(DatePicker::className(), [
                        'removeButton' => false,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'startDate' => date(DateTimeZoneHelper::DATE_FORMAT),
                            'todayHighlight' => true,
                        ],
                    ])
                    ->label(false)
                ?>
            </div>

            <div class="col-sm-offset-2 col-sm-2">
                до <a class="pointer setActualFrom" data-date="<?= $actualTo ?>"><?= Yii::$app->formatter->asDate($actualTo) ?></a>
            </div>

        </div>
    <?php endif ?>

</div>
