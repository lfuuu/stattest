<?php

use app\classes\BaseView;
use app\models\Test;
use app\classes\Html;
use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

/** @var Test $model */
/** @var ActiveForm $form */
/** @var BaseView $this */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);

echo Html::formLabel('Редактирование уровня цен');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => $this->title = 'Уровни цен', 'url' => $cancelUrl = '/dictionary/price-level'],
        ($model->id ? 'Редактирование' : 'Добавление'),
    ],
]);
?>

<div class="container well col-sm-12">
    <fieldset class="col-sm-12">
        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($model, 'name') ?>
            </div>
        </div>
    </fieldset>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($model->id ? 'Save' : 'Create')) ?>
    </div>

    <?php ActiveForm::end() ?>
</div> 