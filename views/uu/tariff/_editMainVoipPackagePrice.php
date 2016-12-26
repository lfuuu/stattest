<?php
/**
 * Пакеты. Цена по направлениям
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\controllers\uu\TariffController;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\PackagePrice;
use kartik\editable\Editable;
use unclead\widgets\TabularInput;

$packagePrice = new PackagePrice;
$attributeLabels = $packagePrice->attributeLabels();

$packagePrices = $formModel->tariff->packagePrices;
if (!$packagePrices) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePrices = [$packagePrice];
    ?>
    <script type='text/javascript'>
        $(function () {
            $(".package-price .multiple-input")
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

<div class="well package-price">
    <h2>Цена по направлениям</h2>
    <?= TabularInput::widget([
            'models' => array_values($packagePrices), // ключ должен быть автоинкрементный
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
                        'class' => 'col-sm-6',
                    ],
                ],
                [
                    'name' => 'price',
                    'title' => $attributeLabels['price'],
                    'options' => $options,
                    'headerOptions' => [
                        'class' => 'col-sm-3',
                    ],
                ],
                [
                    'name' => 'interconnect_price',
                    'title' => $attributeLabels['interconnect_price'],
                    'options' => $options,
                    'headerOptions' => [
                        'class' => 'col-sm-3',
                    ],
                ],
                [
                    'name' => 'id', // чтобы идентифицировать модель
                    'options' => [
                        'class' => 'hidden',
                    ],
                ],
            ],
        ]
    )
    ?>
</div>
