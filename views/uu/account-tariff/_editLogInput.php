<?php
/**
 * Строчка
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\classes\uu\model\ServiceType;
use app\helpers\DateTimeZoneHelper;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;
$accountTariffLog = $formModel->accountTariffLog;
?>

<div class="row">
    <?php
    $tariffPeriods = $formModel->getAvailableTariffPeriods($defaultTariffPeriodId, true, $accountTariff->service_type_id, $accountTariff->city_id);

    $accountTariffLog->tariff_period_id = $accountTariff->tariff_period_id; // текущий тариф
    !$accountTariffLog->tariff_period_id && $defaultTariffPeriodId && $accountTariffLog->tariff_period_id = $defaultTariffPeriodId; // иначе (при создании) дефолтный

    $id = mt_rand(0, 1000000); // чтобы на одной странице можно было несколько объектов показывать

    ?>
    <?php
    $isPackage = in_array($accountTariff->service_type_id, ServiceType::PACKAGES);
    if ($accountTariff->isNewRecord || !$isPackage) :
        ?>
        <div class="col-sm-6">
            <?= $form->field($accountTariffLog, 'tariff_period_id')
                ->widget(Select2::className(), [
                    'data' => $tariffPeriods,
                    'options' => [
                        'id' => 'accountTariffTariffPeriod' . $id,
                        'class' => 'accountTariffTariffPeriod',
                    ],
                ])
                ->label(false) // ($isPackage ? 'Пакет' : 'Тариф') . '/период'
            ?>
        </div>
    <?php else : ?>
        <?= $form->field($accountTariffLog, 'tariff_period_id')->hiddenInput() ?>
    <?php endif; ?>


    <div class="col-sm-6">
        <?= $form->field($accountTariffLog, 'actual_from')
            ->widget(DatePicker::className(), [
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'startDate' => date(DateTimeZoneHelper::DATE_FORMAT),
                    'todayHighlight' => true,
                ],
                'options' => [
                    'style' => 'width: 100px;',
                ],
            ])
            ->label(false) // $accountTariffLog->getAttributeLabel('actual_from_utc')
        // <div class="text-danger">Если сегодня, то отменить нельзя!</div>
        ?>
    </div>

    <?php if (!$accountTariff->isNewRecord) : ?>

        <?php if (!$isPackage) : ?>

        <div class="col-sm-6">
            <?= Html::submitButton(
                Html::tag('i', '', [
                    'class' => 'glyphicon glyphicon-edit',
                    'aria-hidden' => 'true',
                ]) . ' ' .
                Yii::t('tariff', 'Change tariff'),
                [
                    'class' => 'btn btn-primary',
                    'data-old-tariff-period-id' => $accountTariff->tariff_period_id,
                    'id' => 'changeTariffButton' . $id,
                ]
            ) ?>
        </div>

    <?php endif ?>

        <div class="col-sm-6">
            <?= Html::submitButton(
                Html::tag('i', '', [
                    'class' => 'glyphicon glyphicon-trash',
                    'aria-hidden' => 'true',
                ]) . ' ' .
                Yii::t('tariff', 'Close tariff'),
                [
                    'class' => 'btn btn-danger closeTariff',
                    'name' => 'closeTariff',
                    'id' => 'closeTariffButton' . $id,
                ]
            ) ?>
        </div>
        <script type='text/javascript'>
            $(function () {
                $("#changeTariffButton<?= $id ?>").on("click", function (e, item) {
                    // @todo еще надо отлавливать сабмит формы enter, но не путать с closeTariffButton. Можно и не делать здесь - достаточно в контроллере
                    if ($("#accountTariffTariffPeriod<?= $id ?>").val() == $(this).data('old-tariff-period-id')) {
                        alert("Нет смысла менять тариф на тот же самый. Выберите другой тариф.");
                        return false;
                    }
                });
                $("#closeTariffButton<?= $id ?>").on("click", function (e, item) {
                    return confirm("<?= Html::encode(Yii::t('tariff', 'Are you sure you want to close this tariff?')) ?>");
                });
            });
        </script>
    <?php endif ?>

</div>

<style>
    table.editLogInput td {
        vertical-align: top;
        padding: 0 5px;
    }
</style>