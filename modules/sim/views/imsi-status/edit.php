<?php
/**
 * Статусы IMSI. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var ImsiStatus $imsiStatus
 */

use app\modules\sim\models\ImsiStatus;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'Статусы IMSI', 'url' => $cancelUrl = '/sim/imsi-status/'],
        $this->title = $imsiStatus->isNewRecord ? Yii::t('common', 'Create') : $imsiStatus->name
    ],
]) ?>

<div class="well">
    <?php $form = ActiveForm::begin() ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-6">
            <?= $form->field($imsiStatus, 'name')->textInput() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($imsiStatus->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
