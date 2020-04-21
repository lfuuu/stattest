<?php
/**
 * SIM-карты. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var Card $card
 * @var string $activeFormId
 * @var string $submitButtonId
 */

use app\classes\Html;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

?>

<?php $form = ActiveForm::begin(['id' => $activeFormId]) ?>

<?php
$isAllowEdit = \Yii::$app->user->can('sim.write');

$optionDisable = $isAllowEdit ? [] : ['disabled' => true];
?>
<div class="row">

    <div class="col-sm-3">
        <?= $form->field($card, 'iccid')->textInput($optionDisable) ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($card, 'imei')->textInput($optionDisable) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($card, 'client_account_id')->textInput($optionDisable) ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($card, 'status_id')->widget(Select2::class, [
                'data' => CardStatus::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
            ] + $optionDisable) ?>
    </div>

    <div class="col-sm-1">
        <?= $form->field($card, 'is_active')->checkbox($optionDisable) ?>
    </div>
</div>

<?php if (!$card->isNewRecord) {
    echo $this->render('//layouts/_showHistory', ['model' => $card, 'idField' => 'iccid']);
} ?>

<?= $this->render('../_editImsi', ['card' => $card, 'optionDisable' => $optionDisable]) ?>

<div class="form-group text-right">
    <?= $this->render('//layouts/_buttonCancel', ['url' => '/sim/card/']) ?>
    <?= $isAllowEdit ? Html::tag('div', $card->isNewRecord ? 'Создать' : 'Обновить', [
        'id' => $card->isNewRecord ? 'submitButtonCreateCard' : $submitButtonId,
        'class' => ['btn', 'btn-primary'],
    ]) : '' ?>
</div>

<?php ActiveForm::end(); ?>
