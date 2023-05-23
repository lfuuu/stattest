<?php

use app\dao\OrganizationDao;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
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
        'Словари',
        ['label' => $this->title = 'Настройки платежных документов', 'url' => $cancelUrl = '/dictionary/invoice-settings'],
        'Редактирование настроек платежных документов'
    ],
]);
?>

<div class="container well col-sm-12">
    <fieldset class="col-sm-12">
        <div class="row">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'doer_organization_id')
                    ->dropDownList(OrganizationDao::me()->getList(), [
                        'disabled' => !$model->isNewRecord,
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?php if ($model->isNewRecord) {
                    $countryList = Country::getList() + [null => 'Прочие страны'];
                } else {
                    $countryList = [null => 'Прочие страны'] + Country::getList();
                } ?>
                <?= $form
                    ->field($model, 'customer_country_code')
                    ->dropDownList($countryList, [
                        'disabled' => !$model->isNewRecord,
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'at_account_code')
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'settlement_account_type_id')
                    ->dropDownList(OrganizationSettlementAccount::$typesList, [
                        'disabled' => !$model->isNewRecord,
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'vat_apply_scheme')
                    ->dropDownList(InvoiceSettings::$vatApplySchemes, [
                        'disabled' => !$model->isNewRecord,
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'vat_rate') ?>
            </div>
        </div>

    </fieldset>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>

    <?php ActiveForm::end() ?>
</div>