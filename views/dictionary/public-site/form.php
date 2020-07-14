<?php

/**
 * @var \app\classes\BaseView $this
 * @var PublicSite $model
 */

use app\classes\Html;
use app\models\City;
use app\models\Country;
use app\models\dictionary\PublicSite;
use app\widgets\MultipleInput\MultipleInput;
use kartik\editable\Editable;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

if (!$model->isNewRecord) {
    $this->title = $model->title;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>
<?= Html::formLabel('Редактирование публичного сайта'); ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Публичные сайты', 'url' => $cancelUrl = '/dictionary/public-site'],
        $this->title,
    ],
]) ?>

<?php if ($model->hasErrors()) : ?>
    <?php $errors = $model->getErrors(); ?>
    <div class="alert alert-danger text-center error-block">
        <?php foreach ($errors as $errorGroup) : ?>
            <?php foreach ($errorGroup as $error) : ?>
                <?= $error; ?><br />
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    echo Html::activeHiddenInput($model, 'id');
    ?>

    <div class="row">

        <div class="col-sm-6">
            <?= $form->field($model, 'title')->textInput() ?>
        </div>

        <div class="col-sm-6">
            <?= $form->field($model, 'domain')->textInput() ?>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'data')->widget(MultipleInput::class, [
                    'allowEmptyList' => false,
                    'enableGuessTitle' => true,
                    'addButtonPosition' => MultipleInput::POS_HEADER,
                    'colgroup' => [
                        '5%',
                        '25%',
                        '45%',
                        '25%',
                    ],
                    'columns' => [
                        [
                            'name' => 'order',
                            'title' => 'Позиция',
                            'type' => Editable::INPUT_TEXT,
                        ],
                        [
                            'name' => 'country_code',
                            'title' => 'Страна',
                            'type' => Editable::INPUT_SELECT2,
                            'options' => [
                                'data' => Country::getList(),
                            ],
                        ],
                        [
                            'name' => 'city_ids',
                            'title' => 'Города',
                            'type' => Editable::INPUT_SELECT2,
                            'options' => [
                                'data' => City::getList(),
                                'options' => [
                                    'multiple' => true,
                                ],
                            ],
                        ],
                        [
                            'name' => 'ndc_type_ids',
                            'title' => 'NDC',
                            'type' => Editable::INPUT_SELECT2,
                            'options' => [
                                'data' => \app\modules\nnp\models\NdcType::getList(),
                                'options' => [
                                    'multiple' => true,
                                ],
                            ],
                        ],
                    ],
                ]
            );
            ?>
        </div>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($model->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>