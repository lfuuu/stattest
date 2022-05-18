<?php

use app\assets\AppAsset;
use app\classes\Html;
use kartik\tabs\TabsX;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\helpers\MediaFileHelper;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\forms\person\PersonForm;
use app\models\Language;

/** @var PersonForm $model */
/** @var \app\models\Person $person */

$this->registerCssFile('@web/css/behaviors/image-preview-select.css', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/image-preview-select.js', ['depends' => [AppAsset::class]]);

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);

echo Html::formLabel('Редактирование ответственного лица');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Ответственные лица', 'url' => Url::toRoute(['/person'])],
        'Редактирование ответственного лица',
    ],
]);

$tabs = [];
foreach(Language::getList() as $languageCode => $languageTitle) {
    $language =
        !is_file(__DIR__  . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . $languageCode . '.php')
            ? Language::LANGUAGE_DEFAULT
            : $languageCode;

    $tabs[] = [
        'label' =>
            Html::tag(
                'div', '',
                ['title' => $languageTitle, 'class' => 'flag flag-' . explode('-', $languageCode)[0]]
            ) . $languageTitle,
        'content' => $this->render('i18n/' . $language, [
            'form' => $form,
            'person' => $person,
            'lang' => $language,
        ]),
        'headerOptions' => [],
        'options' => ['style' => 'white-space: nowrap;'],
    ];
}
?>

<div class="container well" style="width: 100%; padding-top: 20px;">
    <fieldset style="width: 100%;">
        <?= TabsX::widget([
            'id' => 'tabs-person-i18n',
            'items' => $tabs,
            'position' => TabsX::POS_ABOVE,
            'bordered' => false,
            'encodeLabels' => false,
            'containerOptions' => [
                'class' => 'i18n-tabs',
            ],
        ]);
        ?>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'signature_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('SIGNATURE_DIR', 'images', 'assoc'),
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