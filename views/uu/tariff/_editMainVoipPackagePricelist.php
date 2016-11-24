<?php
/**
 * Пакеты. Прайслист с МГП (минимальный гарантированный платеж)
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\controllers\uu\TariffController;
use app\models\billing\Pricelist;
use app\modules\nnp\models\PackagePricelist;
use kartik\editable\Editable;
use unclead\widgets\TabularInput;

$packagePricelist = new PackagePricelist;
$attributeLabels = $packagePricelist->attributeLabels();

$packagePricelists = $formModel->tariff->packagePricelists;
if (!$packagePricelists) {
    // нет моделей, но виджет для рендеринга их обязательно требует
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

switch ($tariff = $formModel->tariff->service_type_id) {

    case \app\classes\uu\model\ServiceType::ID_VOIP_PACKAGE:
        $pricelistList = Pricelist::getList(true, $isWithNullAndNotNull = false, $type = 'client', $orig = true);
        break;

    case \app\classes\uu\model\ServiceType::ID_TRUNK_PACKAGE_ORIG:
        $pricelistList = Pricelist::getList(true, $isWithNullAndNotNull = false, $type = 'operator', $orig = true);
        break;

    case \app\classes\uu\model\ServiceType::ID_TRUNK_PACKAGE_TERM:
        $pricelistList = Pricelist::getList(true, $isWithNullAndNotNull = false, $type = 'operator', $orig = false);
        break;

    default:
        throw new LogicException('Неизвестный тип услуги ' . $formModel->tariff->service_type_id);
}

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}
?>

<div class="well package-pricelist">
    <h2>Прайслист с МГП</h2>
    <?= TabularInput::widget([
            'models' => array_values($packagePricelists), // ключ должен быть автоинкрементный
            'allowEmptyList' => true,
            'columns' => [
                [
                    'name' => 'pricelist_id',
                    'title' => $attributeLabels['pricelist_id'],
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => $pricelistList,
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
