<?php
/**
 * Статусы SIM-карт. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var CardStatus $cardStatus
 */

use app\classes\Html;
use app\modules\sim\models\CardStatus;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'Статусы SIM-карт', 'url' => $cancelUrl = '/sim/card-status/'],
        $this->title = $cardStatus->isNewRecord ? Yii::t('common', 'Create') : $cardStatus->name
    ],
]) ?>

<div class="well">
    <?php $form = ActiveForm::begin() ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-6">
            <?= $form->field($cardStatus, 'name')->textInput() ?>
        </div>
        <?php // Виртальный? ?>
        <div class="col-sm-6">
            <?= $form->field($cardStatus, 'is_virtual')->checkbox() ?>
        </div>

        <?php // Сим-карты ?>
        <div class="col-sm-6">
            <?= Html::a(
                Yii::t('common', 'Show'),
                Url::to(['/sim/card/', 'CardFilter[card_status_id]' => $cardStatus->id])
            ) ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($cardStatus->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
