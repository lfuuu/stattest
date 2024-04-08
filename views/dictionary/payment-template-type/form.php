<?php

/**
 * @var \app\classes\BaseView $this
 * @var PaymentTemplateType $model
 */

use app\classes\Html;
use app\models\document\PaymentTemplateType;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

if (!$model->isNewRecord) {
    $this->title = $model->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>


<?php

echo Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Типы для документов', 'url' => $cancelUrl = '/dictionary/payment-template-type'],
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
            <?= $form->field($model, 'name')->textInput() ?>
        </div>

        <div class="col-sm-6">
            <?= $form->field($model, 'note')->textInput() ?>
        </div>

        <div class="col-sm-6">
            <?= $form->field($model, 'is_portrait')->dropDownList(PaymentTemplateType::$typeList) ?>
        </div>

        <div class="col-sm-6">
            <?= $form->field($model, 'data_source')->dropDownList(PaymentTemplateType::$dataSourceList) ?>
        </div>

        <div class="col-sm-6">
            <?= $form->field($model, 'is_enabled')->checkbox() ?>
        </div>

    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($model->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
