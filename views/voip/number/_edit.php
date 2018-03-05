<?php
/**
 * Редактирование номера
 *
 * @var \app\classes\BaseView $this
 * @var app\models\Number $number
 */

use app\models\DidGroup;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    ?>

    <div class="row">

        <?php // Красивость ?>
        <div class="col-sm-2">
            <?= $form->field($number, 'beauty_level')->widget(Select2::className(), [
                'data' => DidGroup::$beautyLevelNames,
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
