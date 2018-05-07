<?php
/**
 * SIM-карты. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var Card $originCard
 * @var Card $virtualCard
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
        $this->title = $originCard->isNewRecord ? Yii::t('common', 'Create') : $originCard->iccid
    ],
]) ?>
<div class="well">
    <?=  $this->render('forms/_edit', [
        'card' => $originCard,
        'activeFormId' => 'origin_card',
        'submitButtonId' => 'submitButtonOriginCard',
    ]) ?>
</div>
<?php if (!$originCard->isNewRecord) { ?>
    <div class="well">
        <div class="row">
            <?php $statusForm = ActiveForm::begin() ?>
            <div class="col-sm-3">
                <?= Select2::widget([
                    'name' => 'status',
                    'data' => CardStatus::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
                    'value' => $virtualCard ? $virtualCard->status_id : null,
                ]); ?>
            </div>
            <div class="col-sm-3">
                <?= $this->render('//layouts/_submitButton', [
                    'text' => 'Выбрать SIM-карту для замены',
                    'glyphicon' => 'glyphicon-refresh',
                    'params' => [
                        'id' => 'submitButtonFilter',
                        'class' => 'btn btn-primary',
                    ],
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if ($virtualCard) { ?>
        <div class="row">
            <div class="col-sm-6">
                <div class="text-muted">Замена номера на SIM-карте <?= $virtualCard->iccid; ?></div>
            </div>
            <div class="col-sm-6 text-right">
                <?= $this->render('//layouts/_submitButton', [
                    'text' => 'Заменить номер на SIM-карте',
                    'glyphicon' => 'glyphicon-transfer',
                    'params' => [
                        'id' => 'submitButtonChangeMSISDN',
                        'class' => 'btn btn-primary',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="well" style="margin-top: 20px;">
            <?= $this->render('forms/_edit', [
                'card' => $virtualCard,
                'activeFormId' => 'virtual_card',
                'submitButtonId' => 'submitButtonVirtualCard',
            ]) ?>
        </div>
    <?php }
}
$this->registerJsFile('@web/views/sim/card/edit.js');
?>