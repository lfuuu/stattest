<?php
/**
 * Пакеты. Предоплаченные минуты
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\modules\nnp\forms\package\Form;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\PackageMinute;
use kartik\editable\Editable;
use unclead\widgets\TabularInput;

$packageMinute = new PackageMinute;
$attributeLabels = $packageMinute->attributeLabels();

$packageMinutes = $formModel->package->packageMinutes;
if (!$packageMinutes) {
    // нет моделей, но виджет для рендеринга их обяхательно требует
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
                    'options' => [
                        'data' => $destinationList,
                    ],
                    'headerOptions' => [
                        'class' => 'col-sm-4',
                    ],
                ],
                [
                    'name' => 'minute',
                    'title' => $attributeLabels['minute'],
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
