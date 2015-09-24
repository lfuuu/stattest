<?php
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

?>

<?php if (Yii::$app->session->hasFlash('success')) : ?>
    <script>
        jQuery(document).ready(function () {
            if (window.parent.currentTplId != <?= $model->id; ?>)
                window.parent.location.search = 'id=' + <?= $model->id; ?>;
            window.parent.currentTplId = <?= $model->id; ?>;
            window.parent.templates = <?= json_encode(\app\dao\ClientDocumentDao::templateList()) ?>;
            window.parent.generateList();
            window.parent.$dialog.dialog('close');
        });
    </script>
<?php else : ?>
    <legend>
        Изменение параметров
    </legend>

    <div class="well">

        <?php $form = ActiveForm::begin([]); ?>
        <div id="dialog-form" title="Параметры">
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'type')->dropDownList(\app\models\ClientDocument::$types) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-9">
                    <?= $form->field($model, 'folder_id')->dropDownList(\app\models\document\DocumentFolder::getList()) ?>
                </div>
                <div class="col-xs-3" style="padding-top: 20px;">
                    <?= \yii\helpers\Html::button('<i class="glyphicon glyphicon-plus"></i> Добавить', [
                        'class' => 'btn btn-success',
                        'onclick' => '$(this).closest(".row").hide().next().show(); return false;',
                    ]) ?>
                </div>
            </div>
            <div class="row" style="display: none;">
                <div class="col-xs-9">
                    <div class="form-group field-documenttemplate-folder-name has-success">
                        <label class="control-label" for="documenttemplate-name">Папка</label>
                        <input type="text" id="documenttemplate-folder-name" class="form-control"
                               name="DocumentTemplate[folder_name]">
                        <div class="help-block"></div>
                    </div>
                </div>
                <div class="col-xs-3" style="padding-top: 20px;">
                    <?= \yii\helpers\Html::button('<i class="glyphicon glyphicon-remove"></i> Отмена', [
                        'class' => 'btn btn-danger',
                        'onclick' => '$(this).closest(".row").hide().prev().show(); return false;',
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($model, 'name')->textInput() ?>
                </div>
            </div>
            <?= $form->field($model, 'id', ['options' => ['style' => 'display:none;']])->hiddenInput() ?>
        </div>

        <div style="position: fixed; bottom: 0; right: 0;">
            <?php
            echo Form::widget([
                'model' => $model,
                'form' => $form,
                'attributes' => [
                    'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
                    'actions' => [
                        'type' => Form::INPUT_RAW,
                        'value' =>
                            '<div class="col-md-12" style="text-align: right; padding-right: 0px;">' .
                            Html::button('Отмена', [
                                'class' => 'btn btn-link',
                                'id' => 'dialog-close',
                                'style' => 'width: 100px; margin-right: 15px;',
                            ]) .
                            Html::submitButton('Сохранить', [
                                'class' => 'btn btn-primary',
                                'style' => 'width: 100px;',
                            ])
                            .
                            '</div>'
                    ],
                ],
            ]);
            ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function () {
            $('#dialog-close').click(function () {
                window.parent.$dialog.dialog('close');
            });
        });
    </script>
<?php endif; ?>