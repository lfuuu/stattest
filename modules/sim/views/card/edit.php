<?php

use app\modules\sim\models\CardStatus;
use app\classes\Html;
use app\modules\sim\models\Dsm;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

/**
 * SIM-карты. Карточка
 *
 * @var \app\classes\BaseView $this
 * @var Dsm $dsm
 */
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/card/'],
        $this->title = $dsm->origin->isNewRecord ? Yii::t('common', 'Create') : $dsm->origin->iccid
    ],
]) ?>
    <!-- Форма редактирования OriginCard -->
    <div class="well">
        <?= $this->render('forms/_edit', [
            'card' => $dsm->origin,
            'regionName' => $dsm->regionName,
            'activeFormId' => 'origin_card',
            'submitButtonId' => 'submitButtonOriginCard',
        ]) ?>
    </div>

<?php
$imsies = $dsm->origin->imsies;
foreach ($imsies as $imsiO) {
    echo $this->render('forms/_imsiExternalStatusLog', ['imsi' => $imsiO, 'count' => count($imsies)]);
}
?>

<?php if (!$dsm->origin->isNewRecord && \Yii::$app->user->can('sim.write')) { ?>
    <!-- Блок, отображающий методы для выбора необходимой сим-карты или непривязанного номера -->
    <div class="well">
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <h3>Выберите склад или введите номер телефона</h3>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 10px;">
            <div class="col-md-6">
                <?php $form = ActiveForm::begin() ?>
                <!-- Поле выбора склада сим-карты -->
                <div class="col-sm-4">
                    <?= Select2::widget([
                        'id' => Dsm::ENV_WITH_WAREHOUSE,
                        'name' => Dsm::ENV_WITH_WAREHOUSE,
                        'data' => CardStatus::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
                        'value' => $dsm->warehouseId ?: null,
                    ]); ?>
                </div>
                <!-- Поле для ввода номера -->
                <div class="col-sm-5">
                    <?= Html::input('text', Dsm::ENV_WITH_RAW_NUMBER,
                        $dsm->isSynchronizable() ? $dsm->rawNumber : null,
                        ['class' => 'form-control', 'id' => Dsm::ENV_WITH_RAW_NUMBER]); ?>
                </div>
                <!-- Кнопка синхронизации -->
                <div class="col-sm-3">
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
    <!-- Блок, отображающий выбранную сим-карту или непривязанный номер -->
    <?php if ($dsm->isSynchronizable()) : ?>
        <div class="row">
            <div class="col-sm-12 text-right">
                <?= $this->render('//layouts/_submitButton', [
                    'text' => 'Заменить',
                    'glyphicon' => 'glyphicon-transfer',
                    'params' => [
                        'id' => sprintf('button%s', $dsm->virtual ? 'BetweenCards' : 'UnassignedNumber'),
                        'class' => 'btn btn-primary',
                        'value' => sprintf('/sim/card/change-%s', $dsm->virtual ? 'msisdn' : 'unassigned-number'),
                    ],
                ]) ?>
            </div>
        </div>
        <div class="well" style="margin-top: 20px;">
            <?php if ($dsm->virtual) : ?>
                <?= $this->render('forms/_edit', [
                    'card' => $dsm->virtual,
                    'activeFormId' => 'virtual_card',
                    'submitButtonId' => 'submitButtonVirtualCard',
                ]) ?>
            <?php elseif ($dsm->unassignedNumber) : ?>
                <b>Непривязанный к сим-карте мобильный номер, находящийся в продаже: </b>
                <div id='virtual_number' class="sim-card_is-unassigned_number" style="margin-top: 10px;">
                    <?= $dsm->unassignedNumber->number; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif ($dsm->errorMessages) : ?>
        <div class="alert alert-danger">
            <strong>Произошла ошибка!</strong> <?= implode('\r\n', $dsm->errorMessages); ?>
        </div>
    <?php endif; ?>
<?php }
$this->registerJsFile('@web/views/sim/card/edit.js');

