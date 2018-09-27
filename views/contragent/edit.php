<?php

use app\assets\AppAsset;
use app\classes\BaseView;
use app\classes\Html;
use app\classes\Language;
use app\forms\client\ContragentEditForm;
use app\helpers\DateTimeZoneHelper;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Url;

/** @var ContragentEditForm $model */
/** @var BaseView $this */

$this->registerJsFile('@web/js/behaviors/history-version.js', ['depends' => [AppAsset::class]]);

$language = Language::getLanguageByCountryId($model->country_id ?: \app\models\Country::RUSSIA);
$model->formLang = $language;
if ($model->isNewRecord) {
    $model->lang_code = $model->formLang;
}
?>
<div class="row">
    <div class="col-sm-12">

        <h2 style="display: inline-block; width: 62%;"><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> контрагента</h2>
        <?php if (!$model->isNewRecord) : ?>
            <a href="<?= Url::toRoute(['/transfer/contragent', 'contragentId' => $model->id]) ?>" onClick="return showIframePopup(this)" data-height="500">Переместить</a>
        <?php endif; ?>

        <?php $f = ActiveForm::begin(); ?>
        <?= $this->render($this->getFormPath('contragent', $language), ['model' => $model, 'f' => $f]); ?>
        <div class="row max-screen">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-6">
                        <label class="control-label" for="historyVersionStoredDate">Сохранить на</label>
                        <?= Html::dropDownList('ContragentEditForm[historyVersionStoredDate]', null, $model->getContragentModel()->getDateList(),
                            ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'id' => 'historyVersionStoredDate']); ?>
                    </div>
                    <div class="col-sm-6">
                        <div>
                            <label class="control-label" for="deferred-date-input">Выберите дату</label>
                            <?= DatePicker::widget(
                                [
                                    'name' => 'kartik-date-3',
                                    'value' => Yii::$app->request->get('date') ? Yii::$app->request->get('date') : date(DateTimeZoneHelper::DATE_FORMAT),
                                    'removeButton' => false,
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
            </div>

            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!$model->isNewRecord) : ?>
        <div class="col-sm-12 form-group">
            <?= $this->render('//layouts/_showVersion', ['model' => [$model->contragent, $model->person]]) ?>
            <?= $this->render('//layouts/_showHistory', ['model' => [$model->contragent, $model->person]]) ?>
        </div>
    <?php endif; ?>
</div>