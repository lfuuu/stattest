<?php
/**
 * SIM-карты. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var Card $card
 */

use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => $cancelUrl = '/sim/card/'],
        $this->title = $card->isNewRecord ? Yii::t('common', 'Create') : $card->iccid
    ],
]) ?>

<div class="well">
    <?php $form = ActiveForm::begin() ?>

    <div class="row">

        <?php // iccid ?>
        <div class="col-sm-3">
            <?= $form->field($card, 'iccid')->textInput() ?>
        </div>

        <?php // imei ?>
        <div class="col-sm-3">
            <?= $form->field($card, 'imei')->textInput() ?>
        </div>

        <?php // ЛС ?>
        <div class="col-sm-2">
            <?= $form->field($card, 'client_account_id')->textInput() ?>
        </div>

        <?php // Статус ?>
        <div class="col-sm-3">
            <?= $form->field($card, 'status_id')->widget(Select2::className(), [
                'data' => CardStatus::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
            ]) ?>
        </div>

        <?php // Вкл ?>
        <div class="col-sm-1">
            <?= $form->field($card, 'is_active')->checkbox() ?>
        </div>

    </div>
    <?php
    if (!$card->isNewRecord) {
        echo $this->render('//layouts/_showHistory', ['model' => $card, 'idField' => 'iccid']);
    }
    ?>

    <?= $this->render('_editImsi', ['card' => $card]) ?>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($card->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
