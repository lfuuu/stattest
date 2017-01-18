<?php

/**
 * @var \app\forms\client\ContragentEditForm $model
 * @var $this \app\classes\BaseView
 */

use app\classes\Html;
use app\classes\Language;
use app\helpers\DateTimeZoneHelper;
use app\models\UserGroups;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;

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
            <a href="/contragent/transfer?id=<?= $model->id; ?>" onClick="return showIframePopup(this)" data-height="500">Переместить</a>
        <?php endif; ?>

        <?php $f = ActiveForm::begin(); ?>
        <?= $this->render($this->getFormPath('contragent', $language), ['model' => $model, 'f' => $f]); ?>
        <div class="row" style="width: 1100px;">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-6">
                        <div type="textInput">
                            <label class="control-label" for="historyVersionStoredDate">Сохранить на</label>
                            <?= Html::dropDownList('ContragentEditForm[historyVersionStoredDate]', null, $model->getContragentModel()->getDateList(),
                                ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'id' => 'historyVersionStoredDate']); ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div type="textInput">
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

<script>
    showLastChanges = <?= $showLastChanges ? 'true' : 'false' ?>;

    $(function () {
        $('#deferred-date-input').parent().parent().hide();
    });

    $('#buttonSave').closest('form').on('submit', function (e) {
        $('#type-select .btn').not('.btn-primary').each(function () {
            $($(this).data('tab')).remove();
        });
        if ($("#historyVersionStoredDate option:selected").val() == '')
            $('#historyVersionStoredDate option:selected').val($('#deferred-date-input').val()).select();
        return true;
    });

    $('#historyVersionStoredDate').on('change', function () {
        var datepicker = $('#deferred-date-input');
        if ($("option:selected", this).val() == '') {
            datepicker.parent().parent().show();
        }
        else {
            datepicker.parent().parent().hide();
        }
    });
</script>