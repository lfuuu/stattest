<?php

/**
 * @var \yii\web\View $this
 * @var PublicSite $model
 */

use kartik\editable\Editable;
use app\widgets\MultipleInput\MultipleInput;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use app\classes\Html;
use app\models\Country;
use app\models\dictionary\PublicSite;
use app\dao\CityDao;

if (!$model->isNewRecord) {
    $this->title = $city->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Публичные сайты', 'url' => $cancelUrl = '/dictionary/public-site'],
        $this->title
    ],
]) ?>

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
            <?= $form->field($model, 'data')->widget(MultipleInput::className(), [
                'allowEmptyList' => false,
                'enableGuessTitle' => true,
                'addButtonPosition' => MultipleInput::POS_HEADER,
                'colgroup' => [
                    '5%',
                    '25%',
                    '70%',
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
                            'data' => CityDao::me()->getList(),
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