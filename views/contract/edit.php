<?php

/**
 * @var ContractEditForm $model
 * @var \app\classes\BaseView $this
 */

use app\assets\AppAsset;
use app\classes\Html;
use app\classes\Language;
use app\dao\ClientDocumentDao;
use app\models\ClientContractReward;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContragent;
use app\models\ClientDocument;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$this->registerJsFile('@web/js/behaviors/managers_by_contract_type.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/show-last-changes.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/change-doc-template.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/history-version.js', ['depends' => [AppAsset::class]]);

/** @var ClientContragent[] $contragents */
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

$viewParams = [
    'formModel' => $model,
];

?>

<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> договора</h2>

        <?php $f = ActiveForm::begin(); ?>

        <?= $this->render($this->getFormPath('contract', $language), ['model' => $model, 'f' => $f, 'contragents' => $contragents, 'contragentsOptions' => $contragentsOptions]); ?>

        <div class="row max-screen">
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-12">
                        <label class="control-label" for="historyVersionStoredDate">Сохранить на</label>
                        <?= Html::dropDownList(
                            'ContractEditForm[historyVersionStoredDate]',
                            null,
                            $model->getModel()->getDateList(),
                            ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'id' => 'historyVersionStoredDate']
                        ); ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="col-sm-12">
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
        <div class="col-sm-12 extend-block">
            <div class="row title">
                <div class="col-sm-12">Параметры вознаграждения v3</div>
            </div>
            <?= $this->render('//rewards/reward-client-contract/view', $viewParams) ?>
        </div>
        <div class="col-sm-12 extend-block">
            <div class="row title">
                <div class="col-sm-12">Параметры вознаграждения</div>

            </div>

            <?php
            foreach (ClientContractReward::$usages as $usageType => $usageTitle) {
                echo $this->render(
                    'rewards/' . $usageType,
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
</script>