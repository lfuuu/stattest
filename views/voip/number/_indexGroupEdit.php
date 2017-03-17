<?php
/**
 * Групповое редактирование
 *
 * @var app\classes\BaseView $this
 * @var int $city_id
 * @var int $currentClientAccountId
 */

use app\classes\traits\GetListTrait;
use app\models\DidGroup;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$number = new \app\models\Number;
$form = ActiveForm::begin([
    'id' => 'group-form',
]);
$viewParams = [
    'form' => $form,
];

$this->registerJsVariables([
    'clientAccountId' => $currentClientAccountId,
    'groupEditStatus' => \app\models\Number::STATUS_NOTACTIVE_RESERVED,
    'groupEditIsNull' => GetListTrait::$isNull,
]);
?>

<div class="row">

    <?php // Клиент ?>
    <div class="col-sm-2">
        <?= $form->field($number, 'client_id')->input('number', [
            'title' => Yii::t('common', 'Enter {nullValue} for empty value, {notNullValue} for not empty value', ['nullValue' => GetListTrait::$isNull, 'notNullValue' => GetListTrait::$isNotNull])
        ]) ?>
    </div>

    <?php // Статус ?>
    <div class="col-sm-2">
        <?= $form->field($number, 'status')
            ->widget(Select2::className(), [
                'data' => \app\models\Number::dao()->getStatusList($isWithEmpty = true),
            ]) ?>
    </div>

    <?php // Красивость ?>
    <div class="col-sm-2">
        <?= $form->field($number, 'beauty_level')
            ->widget(Select2::className(), [
                'data' => DidGroup::dao()->getBeautyLevelList($isWithEmpty = true),
            ]) ?>
    </div>

    <?php // DID-группа ?>
    <div class="col-sm-2">
        <?= $city_id ?
            $form->field($number, 'did_group_id')
                ->widget(Select2::className(), [
                    'data' => DidGroup::getList($isWithEmpty = true, $city_id),
                ]) :
            'DID-группу можно установить только для конкретного города' ?>
    </div>

    <div class="col-sm-4">
        <?= Yii::t('common', 'Set new value to all filtered entries') ?>
        <?= $this->render('//layouts/_submitButtonSave', ['class' => 'group-submit-button']) ?>
    </div>
</div>

<?php ActiveForm::end();
