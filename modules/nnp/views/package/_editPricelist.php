<?php
/**
 * Пакеты. Прайслист с МГП (минимальный гарантированный платеж)
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\models\billing\Pricelist;
use app\modules\nnp\forms\package\Form;
use app\modules\nnp\models\PackagePricelist;
use kartik\editable\Editable;
use unclead\widgets\TabularInput;

$packagePricelist = new PackagePricelist;
$attributeLabels = $packagePricelist->attributeLabels();

$packagePricelists = $formModel->package->packagePricelists;
if (!$packagePricelists) {
    // нет моделей, но виджет для рендеринга их обяхательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePricelists = [$packagePricelist];
    ?>
    <script type='text/javascript'>
        $(function () {
            $(".package-pricelist .multiple-input")
                .on("afterInit", function () {
                    $(this).multipleInput('remove');
                });
        });
    </script>
    <?php
}
$pricelistList = Pricelist::getList(true, $isWithNullAndNotNull = false, $type = 'client', $orig = true);
?>

<div class="well package-pricelist">
    <h2>Прайслист с МГП (минимальный гарантированный платеж)</h2>
    <?= TabularInput::widget([
            'models' => array_values($packagePricelists), // ключ должен быть автоинкрементный
            'allowEmptyList' => true,
            'columns' => [
                [
                    'name' => 'pricelist_id',
                    'title' => $attributeLabels['pricelist_id'],
                    'type' => Editable::INPUT_SELECT2,
                    'options' => [
                        'data' => $pricelistList,
                    ],
                    'headerOptions' => [
                        'class' => 'col-sm-4',
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
