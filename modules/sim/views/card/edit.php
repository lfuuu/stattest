<?php
/**
 * SIM-карты. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var CardSupport $cardSupport
 * @var Card $originCard
 * @var VirtualCard $virtualCard
 * @var Number $unassigned_number
 */

use app\models\Number;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\classes\Html;
use app\modules\sim\models\CardSupport;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\VirtualCard;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

// Инициализируем переменные из CardSupport, если сим-карта не создается, а обновляется
if (!$originCard->isNewRecord) {
    list($originCard, $virtualCard, $unassigned_number) = [
        $cardSupport->origin_card, $cardSupport->virtual_card, $cardSupport->unassigned_number,
    ];
}

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/card/'],
        $this->title = $originCard->isNewRecord ? Yii::t('common', 'Create') : $originCard->iccid
    ],
]) ?>

    <div class="well">
        <?= $this->render('forms/_edit', [
            'card' => $originCard,
            'activeFormId' => 'origin_card',
            'submitButtonId' => 'submitButtonOriginCard',
        ]) ?>
    </div>

<?php if (!$originCard->isNewRecord) { ?>
    <div class="well">
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-6 <?php if ($cardSupport->isLostCard()) : ?>sim-card_is-active-action<?php endif; ?>">
                    <h3>Замена утерянной симкарты</h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12 <?php if ($cardSupport->isBetweenCards() || $cardSupport->isUnassignedNumber()) : ?>sim-card_is-active-action<?php endif; ?>">
                    <h3>Обмен MSISDNs между сим-картами или свободным номером</h3>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 10px;">
            <!-- Выбрать SIM-карту для замены -->
            <div class="col-md-6">
                <?php ActiveForm::begin() ?>
                <div class="col-sm-6">
                    <?= Select2::widget([
                        'name' => 'status',
                        'data' => CardStatus::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
                        'value' => $virtualCard && $cardSupport->isLostCard() ? $virtualCard->status_id : null,
                    ]); ?>
                </div>
                <div class="col-sm-4">
                    <?= $this->render('//layouts/_submitButton', [
                        'text' => 'Выбрать',
                        'glyphicon' => 'glyphicon-search',
                        'params' => [
                            'id' => 'submitButtonFilter',
                            'class' => 'btn btn-primary',
                        ],
                    ]) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            <!-- Выбрать MSISDN для замены -->
            <div class="col-md-6">
                <?php $formFreeMsisdn = ActiveForm::begin() ?>
                <div class="col-sm-6">
                    <?php
                    $id = null; $value = null;
                    if ($cardSupport->isBetweenCards() && $imsies = $virtualCard->imsies) {
                        /** @var Imsi $imsi */
                        if ($imsies && $imsi = reset($imsies)) {
                            $value = $imsi->msisdn;
                        }
                    } else if ($cardSupport->isUnassignedNumber()) {
                        $value = $unassigned_number->number;
                        $id = 'unassigned_number';
                    }
                    echo Html::input('text', 'number', $value, ['class' => 'form-control','id' => $id]);
                    ?>
                </div>
                <div class="col-sm-4">
                    <?= $this->render('//layouts/_submitButton', [
                        'text' => 'Выбрать',
                        'glyphicon' => 'glyphicon-search',
                        'params' => [
                            'class' => 'btn btn-primary',
                        ],
                    ]) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <?php if ($cardSupport->isUnassignedNumber() || $cardSupport->isBetweenCards() || $cardSupport->isLostCard()) { ?>
        <div class="row">
            <!-- Обмен MSISDNs между сим-картами или замена потерянной SIM-карты-->
            <div class="col-sm-12 text-right">
                <?= $this->render('//layouts/_submitButton', [
                    'text' => CardSupport::AJAX_RESOLVER[$cardSupport->behaviour]['label'],
                    'glyphicon' => 'glyphicon-transfer',
                    'params' => [
                        'id' => CardSupport::AJAX_RESOLVER[$cardSupport->behaviour]['button'],
                        'class' => 'btn btn-primary',
                        'value' => CardSupport::AJAX_RESOLVER[$cardSupport->behaviour]['method'],
                    ],
                ]) ?>
            </div>
        </div>
        <?php if ($cardSupport->isUnassignedNumber()) { ?>
            <!-- Обмен MSISDN между SIM-картой и неназначенным номером -->
            <div class="well" style="margin-top: 20px;">
                <div class="row">
                    <div class="col-md-12">
                        <b>Неназначенным номером является номер, не привязанный к сим-карте: </b>
                        <div id='unassigned_number' style="color: #d43f3a; margin-top: 10px;">
                            <b><?= $unassigned_number->number; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="well" style="margin-top: 20px;">
                <?= $this->render('forms/_edit', [
                    'card' => $virtualCard,
                    'activeFormId' => 'virtual_card',
                    'submitButtonId' => 'submitButtonVirtualCard',
                ]) ?>
            </div>
        <?php } ?>
    <?php } elseif ($cardSupport->isErrorBehaviour()) { ?>
        <div class="alert alert-danger">
            <strong>Произошла ошибка!</strong> <?= $cardSupport->message; ?>
        </div>
    <?php }
}

$this->registerCssFile('@web/views/sim/card/edit.css');
$this->registerJsFile('@web/views/sim/card/edit.js');
?>