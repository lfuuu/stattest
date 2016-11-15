<?php

use app\dao\OrganizationDao;
use app\models\Currency;
use app\models\LkWizardState;
use app\models\Region;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use app\classes\Html;
use app\models\Country;

/** @var \app\models\EntryPoint $model */
/** @var ActiveForm $form */
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);

echo Html::formLabel('Точки подключения');
echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Точки подключения', 'url' => $cancelUrl = '/dictionary/entry-point'],
        ($model->id ? 'Редактирование' : 'Добавление')
    ],
]);

echo $form->field($model, 'id')->hiddenInput()->label('');

?>

<div class="container well col-sm-12">
    <fieldset class="col-sm-12">

        <div class="row">
            <div class="col-sm-3">
                <?= $form
                    ->field($model, 'code')
                    ->textInput($model->id ? ['readonly' => true] : [])
                ?>
            </div>
            <div class="col-sm-7">
                <?= $form
                    ->field($model, 'name')
                ?>
            </div>

            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'is_postpaid')
                    ->checkbox()
                ?>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'super_client_prefix')
                ?>
            </div>

            <div class="col-sm-6">
                <?= $form
                    ->field($model, 'organization_id')
                    ->dropDownList(OrganizationDao::me()->getList())
                ?>
            </div>

            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'country_id')
                    ->dropDownList(Country::getList())
                ?>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'client_contract_business_id')
                    ->dropDownList(\app\models\Business::getList())
                ?>
            </div>

            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'credit')
                ?>
            </div>

            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'account_version')->dropDownList(\app\models\ClientAccount::$versions)
                ?>
            </div>

            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'region_id')
                    ->dropDownList(Region::dao()->getList(false, $model->country_id))
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'client_contract_business_process_id')
                    ->dropDownList(
                        \app\models\BusinessProcess::find()
                            ->where(['business_id' => $model->client_contract_business_id])
                            ->andWhere(['show_as_status' => '1'])
                            ->indexBy('id')
                            ->all()
                    )
                ?>
            </div>

            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'voip_credit_limit_day')
                ?>
            </div>

            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'wizard_type')
                    ->dropDownList(LkWizardState::$name)
                ?>
            </div>

            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'currency_id')
                    ->dropDownList(Currency::getList())
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'client_contract_business_process_status_id')
                    ->dropDownList(
                        \app\models\BusinessProcessStatus::find()
                            ->where(['business_process_id' => $model->client_contract_business_process_id])
                            ->indexBy('id')
                            ->all()
                    )
                ?>
            </div>

            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'voip_limit_mn_day')
                ?>
            </div>

            <div class="col-sm-2">
                <?= $form
                    ->field($model, 'is_default')
                    ->checkbox()
                ?>
            </div>

            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'timezone_name')
                    ->dropDownList(Region::getTimezoneList())
                ?>
            </div>
        </div>

    </fieldset>

    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($model->id ? 'Save' : 'Create')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>
<script>
    var statuses = <?=json_encode(\app\models\BusinessProcessStatus::getTree())?>;

    $('#entrypoint-client_contract_business_id').on('change', function (event) {
        redrawProccess(event.target.value);
    });

    $('#entrypoint-client_contract_business_process_id').on('change', function (event) {
        redrawStatuses(event.target.value);
    });

    function redrawProccess(businessId) {
        var businessProcessObj = $("#entrypoint-client_contract_business_process_id");

        var processId = 0;

        businessProcessObj.empty();
        $.each(statuses.processes, function (key, value) {
            if (businessId == value['up_id']) {
                if (!processId) {
                    processId = value['id'];
                }
                businessProcessObj.append($('<option>').val(value['id']).text(value['name']));
            }
        });

        redrawStatuses(processId);
    }

    function redrawStatuses(processId) {
        var businessProcessStatusObj = $("#entrypoint-client_contract_business_process_status_id");

        businessProcessStatusObj.empty();
        $.each(statuses.statuses, function (key, value) {
            if (processId == value['up_id']) {
                businessProcessStatusObj.append($('<option>').val(value['id']).text(value['name']));
            }
        });
    }

    var regions = <?= json_encode(Region::find()
        ->select(['id', 'name', 'country_id'])
        ->orderBy(['id' => SORT_DESC])
        ->asArray()
        ->all())
        ?>;
    $('#entrypoint-country_id').on('change', function (event) {
        redrawRegions(event.target.value);
    });

    function redrawRegions(countryId) {
        var regionObj = $("#entrypoint-region_id");

        regionObj.empty();
        $.each(regions, function (key, value) {
            if (countryId == value['country_id']) {
                regionObj.append($('<option>').val(value['id']).text(value['name']));
            }
        });
    }

</script>