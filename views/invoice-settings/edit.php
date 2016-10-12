<?php

use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use app\classes\Html;
use app\models\Country;
use app\models\InvoiceSettings;
use app\models\OrganizationSettlementAccount;

/** @var InvoiceSettings $model */
/** @var \app\models\Person $person */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);

echo Html::formLabel('Редактирование настроек платежных документов');
echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Настройки платежных документов', 'url' => $cancelUrl = '/invoice-settings'],
        'Редактирование настроек платежных документов'
    ],
]);
?>

<div class="container well col-sm-12">
    <fieldset class="col-sm-12">
        <div class="row">
            <div class="col-sm-6">
                <?= $form
                    ->field($model, 'customer_country_code')
                    ->dropDownList(Country::getList())
                ?>
            </div>
            <div class="col-sm-6">
                <?= $form
                    ->field($model, 'doer_country_code')
                    ->dropDownList(Country::getList())
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'settlement_account_type_id')
                    ->dropDownList(OrganizationSettlementAccount::$typesList)
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'contragent_type')
                    ->dropDownList(InvoiceSettings::$contragentTypes)
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'vat_rate') ?>
            </div>
        </div>

    </fieldset>

    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($city->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>