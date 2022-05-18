<?php
/**
 * MVNO-партнеры IMSI. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var ImsiPartner $imsiPartner
 */

use app\models\billing\Trunk;
use app\modules\sim\models\ImsiPartner;
use kartik\widgets\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'MVNO-партнеры IMSI', 'url' => $cancelUrl = '/sim/imsi-partner/'],
        $this->title = $imsiPartner->isNewRecord ? Yii::t('common', 'Create') : $imsiPartner->name
    ],
]) ?>

<div class="well">
    <?php $form = ActiveForm::begin() ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-3">
            <?= $form->field($imsiPartner, 'name')
                ->textInput() ?>
        </div>

        <?php // Терминационный транк ?>
        <div class="col-sm-3">
            <?= $form->field($imsiPartner, 'mvno_region_id')
                ->widget(Select2::class, [
                    'data' => \app\models\Region::getList($isWithEmpty = true),
                ]) ?>
        </div>

        <?php // Вкл ?>
        <div class="col-sm-1">
            <?= $form->field($imsiPartner, 'is_active')
                ->checkbox() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= '' ; $this->render('//layouts/_submitButton' . ($imsiPartner->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
