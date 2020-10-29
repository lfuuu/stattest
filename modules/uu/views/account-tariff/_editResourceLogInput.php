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
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
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

$currentDate = date(DateTimeZoneHelper::DATE_FORMAT);

$accountTariffResourceLog = new AccountTariffResourceLog(); // пустышка
$accountTariffResourceLog->resource_id = $formModel->serviceTypeId;
$accountTariffResourceLog->actual_from = $currentDate;

// оплачено до
$actualTo = $accountTariff->getDefaultActualFrom();

$accountTariffResourceLogTableName = AccountTariffResourceLog::tableName();
$tariffResourceTableName = TariffResource::tableName();
$resourceTableName = ResourceModel::tableName();
?>

<div class="well tariffResources">
    <h2>Ресурс <?= $helpConfluence = $this->render('//layouts/_helpConfluence', AccountLogResource::getHelpConfluence()) ?></h2>
    <div class="row">
        <div class="col-sm-2 col-sm-offset-2">
            <label>
                Лог
                <?= $helpConfluence ?>
            </label>
        </div>
        <div class="col-sm-1">
            <label>
                Диапазон значений
                <?= $helpConfluence ?>
            </label>
        </div>
        <div class="col-sm-2">
            <label>
                <?= Html::encode(Yii::t('models/' . $accountTariffResourceLogTableName, 'amount')) ?>
                <?= $helpConfluence ?>
            </label>
        </div>
        <div class="col-sm-1">
            <label>
                Оплачено, ед.
                <?= $this->render('//layouts/_help', [
                    'message' => 'Указанное количество ресурса уже оплачено (или входит в тариф) до указанной даты (не включительно).' . PHP_EOL .
                        'Уменьшение кол-ва ресурса до этой даты не повлияет на баланс.' . PHP_EOL .
                        'При увеличении кол-ва будут списаны деньги со счета за весь период до указанной даты.',
                ]); ?>
                <?= $helpConfluence ?>
            </label>
        </div>
        <div class="col-sm-1">
            <label>
                <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'amount')) ?>
                <?= $helpConfluence ?>
            </label>
        </div>
        <div class="col-sm-1">
            <label>
                <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_per_unit')) ?>
                <?= $helpConfluence ?>
            </label>
        </div>
        <div class="col-sm-1">
            <label>
                <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_min')) ?>
                <?= $helpConfluence ?>
            </label>
        </div>
        <div class="col-sm-1">
            <label>
                <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'is_can_manage')) ?>
                <?= $helpConfluence ?>
            </label>
        </div>
    </div>

    <?php
    $helpConfluence = $this->render('//layouts/_helpConfluence', $accountTariff->serviceType->getHelpConfluence());

    $nResourceOptions = 0;
    /** @var ResourceModel $resource */
    foreach ($resources as $resource) :
        $nResourceOptions += (int)$resource->isOption();
        $tariffResource = isset($tariffResources[$resource->id]) ? $tariffResources[$resource->id] : null;
        ?>

        <div class="row">

            <div class="col-sm-2">
                <label for="accounttariffresourcelog-<?= $resource->id ?>-amount"><?= Html::encode($resource->name) ?> <?= $helpConfluence ?></label>
            </div>

            <div class="col-sm-2">
                <?= $this->render('_editResourceLogGrid', ['accountTariff' => $accountTariff, 'resource' => $resource]) ?>
            </div>

            <div class="col-sm-1">
                <?= $resource->getValueRange() ?>
            </div>

            <div class="col-sm-2">
                <?php
                $accountTariffResourceLog->amount = $accountTariff->getResourceValue($resource->id);

                if ($isReadOnly || !$accountTariff->isResourceEditable($resource)) {

                    // нельзя редактировать
                    echo $accountTariffResourceLog->getAmount();

                } else {

                    // можно редактировать
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
                }
                ?>
            </div>

            <div class="col-sm-1">
                <?php
                $maxPaidAmount = $tariffPeriod ?
                    $accountTariff->getMaxPaidAmount($tariffPeriod->tariff, $resource->id) :
                    0;

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

            <div class="col-sm-1">
                <?= $tariffResource ? \Yii::t('common', $tariffResource->is_can_manage ? 'Yes' : 'No') : '-' ?>
            </div>

        </div>

    <?php endforeach ?>

    <?php if ($nResourceOptions && !$isReadOnly && !$accountTariff->isNewRecord) : ?>
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
                    ->widget(DatePicker::class, [
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

            <div class="col-sm-offset-2 col-sm-2">
                до <a class="pointer setActualFrom" data-date="<?= $actualTo ?>"><?= Yii::$app->formatter->asDate($actualTo) ?></a>
            </div>

        </div>
    <?php endif ?>

</div>
