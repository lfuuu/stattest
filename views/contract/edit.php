<?php

/**
 * @var ContractEditForm $model
 * @var \app\classes\BaseView $this
 */

use app\assets\AppAsset;
use app\classes\Html;
use app\classes\Language;
use app\dao\ClientDocumentDao;
use app\forms\client\ContractEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContractReward;
use app\models\ClientContragent;
use app\models\ClientDocument;
use app\models\UserGroups;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$this->registerJsFile('@web/js/behaviors/managers_by_contract_type.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/organization_by_legal_type.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/show-last-changes.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/change-doc-template.js', ['depends' => [AppAsset::className()]]);

$contragents = ClientContragent::find()->andWhere(['super_id' => $model->getModel()->getContragent()->super_id])->all();
$contragentsOptions = [];

foreach ($contragents as $contragent) {
    $contragentsOptions[$contragent->id] = [
        'data-legal-type' => $contragent->legal_type,
    ];
}
$language = Language::getLanguageByCountryId($contragents[0]->country_id ?: \app\models\Country::RUSSIA);
$contragents = ArrayHelper::map($contragents, 'id', 'name');

if (!$model->id) {
    $model->organization_id = ClientContragent::$defaultOrganization[$model->contract->contragent->legal_type];
}

$docs = $model->model->allDocuments;
?>

<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> договора</h2>

        <?php $f = ActiveForm::begin(); ?>

        <?= $this->render($this->getFormPath('contract', $language), ['model' => $model, 'f' => $f, 'contragents' => $contragents, 'contragentsOptions' => $contragentsOptions]); ?>

        <div class="row" style="width: 1100px;">
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
                        <label class="control-label" for="historyVersionStoredDate">Сохранить на</label>
                        <?= Html::dropDownList('ContractEditForm[historyVersionStoredDate]', null, $model->getModel()->getDateList(),
                            ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'id' => 'historyVersionStoredDate']); ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
                        <label class="control-label" for="deferred-date-input">Выберите дату</label>
                        <?= DatePicker::widget(
                            [
                                'name' => 'kartik-date-3',
                                'value' => Yii::$app->request->get('date') ? Yii::$app->request->get('date') : date(DateTimeZoneHelper::DATE_FORMAT),
                                'removeButton' => false,
                                'options' => ['class' => 'form-control input-sm'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'startDate' => '-5y',
                                ],
                                'id' => 'deferred-date-input'
                            ]
                        ); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']); ?>
            </div>

            <?php if (!$model->isNewRecord) : ?>
                <div class="col-sm-12 form-group">
                    <?= $this->render('//layouts/_showVersion', ['model' => $model->contract]) ?>
                    <?= $this->render('//layouts/_showHistory', ['model' => $model->contract]) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php ActiveForm::end(); ?>

        <script>
            $(function () {
                $('#deferred-date-input').parent().parent().hide();
            });

            $('#buttonSave').closest('form').on('submit', function (e) {
                if ($("#historyVersionStoredDate option:selected").val() == '')
                    $('#historyVersionStoredDate option:selected').val($('#deferred-date-input').val()).select();
                return true;
            });

            $('#historyVersionStoredDate').on('change', function () {
                console.log(this);
                var datepicker = $('#deferred-date-input');
                if ($("option:selected", this).val() == '') {
                    console.log('picker show');
                    datepicker.parent().parent().show();
                }
                else {
                    datepicker.parent().parent().hide();
                }
            });
        </script>
    </div>

    <?= $this->render('contract/grid', [
        'contract' => $model,
        'docs' => array_filter($docs, function ($row) {
            return $row->type === ClientDocument::DOCUMENT_CONTRACT_TYPE;
        }),
    ]) ?>

    <?= $this->render('agreements/grid', [
        'contract' => $model,
        'docs' => array_filter($docs, function ($row) {
            return $row->type === ClientDocument::DOCUMENT_AGREEMENT_TYPE;
        }),
    ]) ?>

    <?php if ($model->business_id == \app\models\Business::PARTNER) : ?>
        <a name="rewards"></a>
        <div class="col-sm-12">
            <div class="row" style="padding:5px 0; color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
                <div class="col-sm-12">Параметры вознаграждения</div>
            </div>

            <?php
            foreach (ClientContractReward::$usages as $usageType => $usageTitle) {
                echo $this->render('rewards/' . $usageType,
                    [
                        'contract' => $model,
                        'model' => new ClientContractReward,
                        'usageType' => $usageType,
                    ]
                );
            }
            ?>
        </div>
    <?php endif; ?>

    <?= $this->render('files/grid', [
        'contract' => $model->getModel(),
    ]) ?>
</div>

<script type="text/javascript">
    var
        documentFolders = <?= Json::encode(ClientDocumentDao::getFoldersByDocumentType([ClientDocument::DOCUMENT_AGREEMENT_TYPE])) ?>,
        documentTemplates = <?= Json::encode(ClientDocumentDao::getTemplates()) ?>;

    jQuery(document).ready(function () {
        var $businessIdField = $('#contracteditform-business_id'),
            $changeExternal = $('#change-external');

        var $businessIdField = $('#contracteditform-business_id'),
            $changeExternal = $('#change-external');

        if ($businessIdField.val() == 3) {
            $changeExternal.val('external');
        } else {
            $changeExternal.val('internal');
        }

        $businessIdField.on('change', function () {
            if ($businessIdField.val() == 3) {
                $changeExternal.val('external');
            } else {
                $changeExternal.val('internal');
            }

            $changeExternal.trigger('change');
        });

        $changeExternal.on('change', function () {
            var fields = $('.tmpl-group[data-type="contract"], .tmpl[data-type="contract"], #agreement-block');

            if ($(this).val() == 'internal') {
                fields.show();
            } else {
                fields.hide();
            }
        }).trigger('change');

        $('a.show-all').on('click', function () {
            $(this).parents('table').find('tbody > tr.show-all').toggleClass('hidden');
            $(this).toggleClass('label-success');
            return false;
        });

        $('tr.editable').find('a').on('click', function () {
            var $fields = $(this).parents('tr').find('td[data-field]')
            $form = $(this).parents('form');

            $fields.each(function () {
                var $field = $form.find('[name*="' + $(this).data('field') + '"]'),
                    $value = $(this).data('value') ? $(this).data('value') : $(this).text();

                $field.val($value).trigger('change');
            });
            $form.find('input:eq(2)').trigger('focus');

            return false;
        });

        $('select[name*="period_type"]').on('change', function () {
            var $nextInput = $(this).parents('td').next().find('input');
            if ($(this).val() == 'month') {
                $nextInput.show();
            } else {
                $nextInput.hide();
            }
        });

    });

</script>