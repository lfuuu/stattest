<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use app\classes\Language;

$language = Language::getLanguageByCountryId($model->country_id?:643);
$formFolderName = Language::getLanguageExtension($language);
$model->formLang = $language;
?>
<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> контрагента</h2>

        <?php $f = ActiveForm::begin(); ?>
        <?= $this->render($formFolderName.'/form', ['model' => $model, 'f' => $f]); ?>
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
                                    'value' => Yii::$app->request->get('date') ? Yii::$app->request->get('date') : date('Y-m-d', time()),
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

    <?php if (!$model->isNewRecord): ?>
        <div class="col-sm-12 form-group">
            <a href="#" onclick="return showVersion({ClientContragent:<?= $model->id ?>}, true);">Версии</a><br/>
            <?= Html::button('∨', ['style' => 'border-radius: 22px;', 'class' => 'btn btn-default showhistorybutton', 'onclick' => 'showHistory({ClientContragent:' . $model->id . ', ClientContragentPerson:' . $model->getPersonId() . '})']); ?>
            <span>История изменений</span>
        </div>
    <?php endif; ?>
</div>

<script>
    $(function(){
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
<script type="text/javascript" src="/js/behaviors/show-last-changes.js"></script>
