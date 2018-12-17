<?php
/**
 * Пакеты. Прайслист с МГП (минимальный гарантированный платеж)
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\nnp\models\PackagePricelistNnpInternet;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\billing_uu\Pricelist as uuPricelist;
use app\modules\uu\models\ServiceType;
use kartik\editable\Editable;
use unclead\multipleinput\TabularColumn;
use app\widgets\TabularInput\TabularInput;

$packagePricelistNnpInternet = new PackagePricelistNnpInternet();
$attributeLabels = $packagePricelistNnpInternet->attributeLabels();

$packagePricelistsNnpInternet = $formModel->tariff->packagePricelistsNnpInternet;

$isRemovePackagePricelistsV2 = false;

if (!$packagePricelistsNnpInternet) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePricelistsNnpInternet = [$packagePricelistNnpInternet];
    $isRemovePackagePricelistsV2 = true;
}


$this->registerJsVariable('isRemovePackagePricelistsV2', $isRemovePackagePricelistsV2);


switch ($tariff = $formModel->tariff->service_type_id) {

    case ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY:
        $nnpPricelistList = uuPricelist::getList($isWithEmpty = true, $isWithNullAndNotNull = false, uuPricelist::ID_SERVICE_TYPE_DATA);
        break;

    default:
        throw new LogicException('Неизвестный тип услуги ' . $formModel->tariff->service_type_id);
}

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY));

?>

<div class="well package-pricelist">
    <h2>
        Прайслист v.2
        <?= $helpConfluence ?>
    </h2>

    <div>
        <?= TabularInput::widget([
                'models' => array_values($packagePricelistsNnpInternet), // ключ должен быть автоинкрементный
                'allowEmptyList' => true,
                'columns' => [
                    [
                        'name' => 'nnp_pricelist_id',
                        'title' => $attributeLabels['nnp_pricelist_id'],
                        'type' => Editable::INPUT_SELECT2,
                        'options' => $options + [
                                'data' => $nnpPricelistList,
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
</div>