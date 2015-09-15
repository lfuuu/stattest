<?php

use app\classes\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\helpers\MediaFileHelper;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\forms\person\PersonForm;

/** @var $model PersonForm */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);

echo Html::formLabel('Редактирование ответственного лица');
echo Breadcrumbs::widget([
    'links' => [
        [
            'label' => 'Ответственные лица',
            'url' => Url::toRoute(['/person'])
        ],
        'Редактирование ответственного лица'
    ],
]);
?>

<link href="/css/behaviors/image-preview-select.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="/js/behaviors/image-preview-select.js"></script>

<div class="container well" style="width: 100%; padding-top: 20px;">
    <fieldset style="width: 100%;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'name_nominative');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'name_genitive');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_nominative');
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_genitive');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'signature_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('SIGNATURE_DIR', '*.{gif,png,jpg,jpeg}', 'assoc'),
                            [
                                'prompt' => 'Выбрать подпись',
                                'data-source' => Yii::$app->params['SIGNATURE_DIR'],
                                'data-target' => '#full_signature_file_name',
                                'class' => 'image_preview_select',
                            ]
                        )
                        ->label('Подпись');
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label>Предпросмотр подписи</label>
                        <div id="full_signature_file_name" class="image_preview"></div>
                    </div>
                </div>
            </div>
        </div>

        <div style="height: 25px;">&nbsp;</div>
    </fieldset>

    <?php
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link modal-form-close',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['/person']) . '";',
                        ]) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);
    ActiveForm::end();
    ?>
</div>