<?php
/**
 * Пакеты. Прайслист с МГП (минимальный гарантированный платеж)
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\models\billing\Pricelist;
use app\modules\nnp\models\PackagePricelist;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\ServiceType;
use kartik\editable\Editable;
use unclead\widgets\TabularColumn;
use unclead\widgets\TabularInput;

$packagePricelist = new PackagePricelist;
$attributeLabels = $packagePricelist->attributeLabels();

$packagePricelists = $formModel->tariff->packagePricelists;
if (!$packagePricelists) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePricelists = [$packagePricelist];
    $this->registerJsVariable('isRemovePackagePricelists', true);
}

switch ($tariff = $formModel->tariff->service_type_id) {

    case ServiceType::ID_VOIP_PACKAGE:
        $pricelistList = Pricelist::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $type = 'client', $orig = true);
        break;

    case ServiceType::ID_TRUNK_PACKAGE_ORIG:
        $pricelistList = Pricelist::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $type = 'operator', $orig = true);
        break;

    case ServiceType::ID_TRUNK_PACKAGE_TERM:
        $pricelistList = Pricelist::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $type = 'operator', $orig = false);
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
                    'type' => TabularColumn::TYPE_HIDDEN_INPUT,
                ],
            ],
        ]
    )
    ?>
</div>