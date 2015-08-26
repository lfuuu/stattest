<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\file\FileInput;
use yii\helpers\Html;
use app\helpers\MediaFileHelper;
use app\models\Language;
use app\models\City;

/** @var User $model */

$cities = ['' => '-- Выберите город --'];
foreach (City::find()->orderBy('country_id desc')->all() as $city) {
    $cities[ $city->id ] = $city->country->name . ' / ' . $city->name;
}

$photoPreview = [];
if (!empty($model->photo) && MediaFileHelper::checkExists('USER_PHOTO_DIR', $model->id . '.' . $model->photo)) {
    $photoPreview = [
        Html::img(
            Yii::$app->params['USER_PHOTO_DIR'] . $model->id . '.' . $model->photo . '?rnd=' . mt_rand(0, 99),
            [
                'class' => 'file-preview-image',
            ]
        ),
    ];
}
?>

<link href="/css/behaviors/media-manager.css" rel="stylesheet" />

<legend>
    Профайл пользователя
</legend>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
        'options' => ['enctype'=>'multipart/form-data'],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'user' => ['type' => Form::INPUT_TEXT, 'options' => ['readonly' => 'readonly']],
            'name' => ['type' => Form::INPUT_TEXT],
            'city_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $cities,
                'options' => [
                    'class' => 'select2',
                ],
            ],
            'language' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => ['' => '-- Выберите язык --'] + Language::getList(),
                'options' => [
                    'class' => 'select2',
                ],
            ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'email' => ['type' => Form::INPUT_TEXT],
            'icq' => ['type' => Form::INPUT_TEXT],
            'phone_work' => ['type' => Form::INPUT_TEXT],
            'phone_mobile' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'photo' => [
                'type' => Form::INPUT_WIDGET,
                'widgetClass' => FileInput::className(),
                'options' => [
                    'options' => [
                        'multiple' => false,
                        'accept' => 'image/*',
                    ],
                    'pluginOptions' => [
                        'showCaption' => false,
                        'showRemove' => false,
                        'showUpload' => false,
                        'browseClass' => 'btn btn-default btn-block',
                        'browseIcon' => '<i></i> ',
                        'browseLabel' =>  'Выбрать файл',
                        'initialPreview' => $photoPreview,
                    ],
                ],
            ],
            'show_troubles_on_every_page' => [
                'type' => Form::INPUT_CHECKBOX,
                'options' => [
                    'container' => ['style' => 'margin-top: 25px;'],
                ],
            ],
            'empty_1' => ['type' => Form::INPUT_RAW],
            'empty_2' => ['type' => Form::INPUT_RAW],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-12" style="text-align: right; padding-right: 0px;">' .
                        Html::submitButton('Изменить', ['class' => 'btn btn-primary']) .
                    '</div>'
            ],
        ],
    ]);

    ActiveForm::end();
    ?>

</div>