<?php
/**
 * Групповое редактирование
 *
 * @var app\classes\BaseView $this
 * @var int $city_id
 */
use app\models\DidGroup;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

?>

<?php
$number = new \app\models\Number;
$form = ActiveForm::begin();
$viewParams = [
    'form' => $form,
];
?>

<div class="row">

    <?php // Статус ?>
    <div class="col-sm-3">
        <?= $form->field($number, 'status')
            ->widget(Select2::className(), [
                'data' => \app\models\Number::dao()->getStatusList($isWithEmpty = true),
            ]) ?>
    </div>

    <?php // Красивость ?>
    <div class="col-sm-3">
        <?= $form->field($number, 'beauty_level')
            ->widget(Select2::className(), [
                'data' => DidGroup::dao()->getBeautyLevelList($isWithEmpty = true),
            ]) ?>
    </div>

    <?php // DID-группа ?>
    <div class="col-sm-3">
        <?= $form->field($number, 'did_group_id')
            ->widget(Select2::className(), [
                'data' => DidGroup::dao()->getList($isWithEmpty = true, $city_id),
            ]) ?>
    </div>

    <div class="col-sm-3">
        <?= Yii::t('common', 'Set new value to all filtered entries') ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
