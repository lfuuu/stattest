<?php
/**
 * Строчка
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;
$accountTariffLog = $formModel->accountTariffLog;
?>

<div class="row">
    <div class="col-sm-6">
        <?php
        $tariffPeriods = $formModel->getAvailableTariffPeriods($defaultTariffPeriodId, true, $accountTariff->service_type_id, $accountTariff->city_id);

        $accountTariffLog->tariff_period_id = $accountTariff->tariff_period_id; // текущий тариф
        !$accountTariffLog->tariff_period_id && $defaultTariffPeriodId && $accountTariffLog->tariff_period_id = $defaultTariffPeriodId; // иначе (при создании) дефолтный

        $id = mt_rand(0, 1000000); // чтобы на одной странице можно было несколько объектов показывать
        ?>
        <?= $form->field($accountTariffLog, 'tariff_period_id')
            ->widget(Select2::className(), [
                'data' => $tariffPeriods,
                'options' => [
                    'id' => 'accountTariffTariffPeriod' . $id,
                    'class' => 'accountTariffTariffPeriod',
                ],
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($accountTariffLog, 'actual_from')->widget(DatePicker::className(), [
            'removeButton' => false,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'startDate' => (new DateTime())->modify('+1 day')->format('Y-m-d'),
                'todayHighlight' => true,
            ]
        ]) ?>
    </div>

    <?php if (!$accountTariff->isNewRecord) : ?>
        <div class="col-sm-4">
            <label class="control-label"></label> <?php // чтобы позиционировать аналогично другим полям ?>
            <div>

                <?= Html::submitButton(Yii::t('tariff', 'Change tariff'), [
                    'class' => 'btn btn-primary glyphicon glyphicon-edit',
                    'data-old-tariff-period-id' => $accountTariff->tariff_period_id,
                    'id' => 'changeTariffButton' . $id,
                ]) ?>

                <?= Html::submitButton(Yii::t('tariff', 'Close tariff'), [
                    'class' => 'btn btn-danger glyphicon glyphicon-trash closeTariff',
                    'name' => 'closeTariff',
                    'id' => 'closeTariffButton' . $id,
                ]) ?>

            </div>
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
