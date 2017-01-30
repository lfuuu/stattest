<?php
/**
 * Пакеты. Предоплаченные минуты
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\controllers\uu\TariffController;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\PackageMinute;
use kartik\editable\Editable;
use unclead\widgets\TabularColumn;
use unclead\widgets\TabularInput;

$packageMinute = new PackageMinute;
$attributeLabels = $packageMinute->attributeLabels();

$packageMinutes = $formModel->tariff->packageMinutes;
if (!$packageMinutes) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packageMinutes = [$packageMinute];
    ?>
    <script type='text/javascript'>
        $(function () {
            $(".package-minute .multiple-input")
                .on("afterInit", function () {
                    $(this).multipleInput('remove');
                });
        });
    </script>
    <?php
}
$destinationList = Destination::getList(true);

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}
?>

<div class="well package-minute">
    <h2>Предоплаченные минуты</h2>
    <?= TabularInput::widget([
            'models' => array_values($packageMinutes), // ключ должен быть автоинкрементный
            'allowEmptyList' => true,
            'columns' => [
                [
                    'name' => 'destination_id',
                    'title' => $attributeLabels['destination_id'],
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                        'data' => $destinationList,
                    ],
                    'headerOptions' => [
                        'class' => 'col-sm-9',
                    ],
                ],
                [
                    'name' => 'minute',
                    'title' => $attributeLabels['minute'],
                    'options' => $options,
                    'headerOptions' => [
                        'class' => 'col-sm-3',
                    ],
                ],
                [
                    'name' => 'id', // чтобы идентифицировать модель
                    'type' => TabularColumn::TYPE_HIDDEN_INPUT,
                ],
            ],
        ]
    )
    ?>
</div>
