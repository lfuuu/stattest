<link href="/css/behaviors/media-manager.css" rel="stylesheet" />

<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use app\models\Language;
use app\models\City;

/** @var User $model */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);

$cities = ['' => '-- Выберите город --'];
foreach (City::find()->orderBy('country_id desc')->all() as $city) {
    $cities[ $city->id ] = $city->country->name . ' / ' . $city->name;
}
?>

<div class="row">
    <div class="col-sm-12">
        <h2>Профайл пользователя</h2>

        <?php
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
                'photo_file_name' => [
                    'type' => Form::INPUT_FILE,
                    'value' =>
                        '<div class="file_upload form-control input-sm">
                            Выбрать файл<input class="media-manager" type="file" name="tt_files[]" />
                        </div>'
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
        ?>

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
                            Html::submitButton('Изменить', ['class' => 'btn btn-primary']) .
                        '</div>'
                ],
            ],
        ]);
        ?>
    </div>
</div>

<?php
ActiveForm::end();
?>