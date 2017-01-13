<?php
/**
 * свойства тарифа (периоды)
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\uu\model\Period;
use app\classes\uu\model\TariffPeriod;
use app\controllers\uu\TariffController;
use kartik\editable\Editable;
use unclead\widgets\TabularInput;

$tariff = $formModel->tariff;
$tariffPeriods = $formModel->tariffPeriods;
$tariffPeriodTableName = TariffPeriod::tableName();

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

if (!$tariff->isNewRecord) {
    // это нужно сделать ДО TabularInput, иначе он попортит данные $tariffPeriods
    $showHistory = $this->render('//layouts/_showHistory', ['model' => $tariffPeriods, 'deleteModel' => [new TariffPeriod(), 'tariff_id', $tariff->id]]);
} else {
    $showHistory = '';
}

?>

<div class="well chargePeriod">
    <?php
    $periodList = Period::getList();
    echo TabularInput::widget([
            'models' => array_values($tariffPeriods), // ключ должен быть автоинкрементный
            'allowEmptyList' => false,
            'columns' => [
                [
                    'name' => 'period_id',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'period_id'),
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => $periodList,
                        ],
                ],
                [
                    'name' => 'charge_period_id',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'charge_period_id'),
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => $periodList,
                        ],
                ],
                [
                    'name' => 'price_setup',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_setup'),
                    'options' => $options,
                ],
                [
                    'name' => 'price_per_period',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_per_period'),
                    'options' => $options,
                ],
                [
                    'name' => 'price_min',
                    'title' => Yii::t('models/' . $tariffPeriodTableName, 'price_min'),
                    'options' => $options,
                ],
                [
                    'name' => 'id', // чтобы идентифицировать модель
                    'options' => [
                        'class' => 'hidden',
                    ],
                ],
            ],
        ]
    );
    ?>

    <?= $showHistory ?>

</div>

<script type='text/javascript'>
    $(function () {
        $(".chargePeriod .multiple-input")
            .on("afterInit afterAddRow afterDeleteRow onChangePeriod", function () {
                setTimeout(function () {
                    var periods = $(".chargePeriod .list-cell__period_id select");
                    periods.val(periods.first().val()); // всем периодам установить значение, как у первого. текст при этом остается старым, но он все равно будет скрыт

                    periods = $(".chargePeriod .list-cell__period_id .select2-container");
                    periods.addClass('hidden'); // все периоды выключить...
                    periods.first().removeClass('hidden'); // ... кроме первого

                    // пустым строчкам установить 0
                    $(".chargePeriod .list-cell__price_setup input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });
                    $(".chargePeriod .list-cell__price_min input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });
                }, 400); // потому что select2 рендерится чуть позже
            })
            .on("change", "#tariffperiod-0-period_id", function (e, item) {
                $(".chargePeriod .multiple-input").trigger("onChangePeriod"); // при изменении первого периода - менять все
            });
    });
</script>
