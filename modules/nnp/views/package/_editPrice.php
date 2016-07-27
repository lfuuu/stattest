<?php
/**
 * Пакеты. Цена по направлениям
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\modules\nnp\forms\package\Form;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\PackagePrice;
use kartik\editable\Editable;
use unclead\widgets\TabularInput;

$packagePrice = new PackagePrice;
$attributeLabels = $packagePrice->attributeLabels();

$packagePrices = $formModel->package->packagePrices;
if (!$packagePrices) {
    // нет моделей, но виджет для рендеринга их обяхательно требует
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
                    'options' => [
                        'data' => $destinationList,
                    ],
                    'headerOptions' => [
                        'class' => 'col-sm-4',
                    ],
                ],
                [
                    'name' => 'price',
                    'title' => $attributeLabels['price'],
                    'headerOptions' => [
                        'class' => 'col-sm-1',
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
