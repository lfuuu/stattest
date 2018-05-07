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

<div class="row">

    <div class="col-sm-3">
        <?= $form->field($card, 'iccid')->textInput() ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($card, 'imei')->textInput() ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($card, 'client_account_id')->textInput() ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($card, 'status_id')->widget(Select2::className(), [
            'data' => CardStatus::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
        ]) ?>
    </div>

    <div class="col-sm-1">
        <?= $form->field($card, 'is_active')->checkbox() ?>
    </div>
</div>

<?php if (!$card->isNewRecord) {
    echo $this->render('//layouts/_showHistory', ['model' => $card, 'idField' => 'iccid']);
} ?>

<?= $this->render('../_editImsi', ['card' => $card]) ?>

<div class="form-group text-right">
    <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    <?= Html::tag('div', $card->isNewRecord ? 'Создать' : 'Обновить', [
            'id' => $card->isNewRecord ? 'submitButtonCreateCard' : $submitButtonId,
            'class' => ['btn', 'btn-primary'],
    ]) ?>
</div>

<?php ActiveForm::end(); ?>