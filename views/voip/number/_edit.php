<?php
/**
 * Редактирование номера
 *
 * @var \app\classes\BaseView $this
 * @var app\models\Number $number
 */

use app\modules\nnp\models\Operator;
use app\models\DidGroup;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\models\Region;

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
            <?= $form->field($number, 'usr_operator_id')->widget(Select2::className(), [
                'data' => Operator::getList(true, false, $number->country_code ?: null, 0)
            ]) ?>
        </div>

        <?php // источник номера ?>
        <div class="col-sm-2">
            <?= $form->field($number, 'source')->widget(Select2::className(), [
                'data' =>\app\classes\enum\VoipRegistrySourceEnum::$names,
            ]) ?>
        </div>

        <?php // регион ?>
        <div class="col-sm-2">
            <?= $form->field($number, 'region')->widget(Select2::className(), [
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
