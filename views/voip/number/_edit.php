<?php

/**
 * Редактирование номера
 */

use app\modules\nnp\models\Operator;
use app\models\DidGroup;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\models\Region;

/**
 * @var \app\classes\BaseView $this
 * @var app\models\Number $number
 */

?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    ?>

    <div class="row">

        <?php // Красивость ?>
        <div class="col-sm-2">
            <?= $form->field($number, 'beauty_level')->widget(Select2::class, [
                'data' => DidGroup::$beautyLevelNames,
            ]) ?>
        </div>

        <?php // ННП-оператор пользователя ?>
        <div class="col-sm-2">
            <?= $form->field($number, 'nnp_operator_id')->widget(Select2::class, [
                'data' => Operator::getList(true, false, $number->country_code ?: null, 0)
            ]) ?>
        </div>

        <?php // источник номера ?>
        <div class="col-sm-2">
            <?= $form->field($number, 'source')->widget(Select2::class, [
                'data' => \app\models\voip\Source::getList(),
            ]) ?>
        </div>

        <?php // регион ?>
        <div class="col-sm-2">
            <?= $form->field($number, 'region')->widget(Select2::class, [
                'data' => Region::getList(true)
            ]) ?>
        </div>

        <?php // кнопки ?>
        <div class="col-sm-2">
            <label></label>
            <div>
                <?= $this->render('//layouts/_submitButtonSave') ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
