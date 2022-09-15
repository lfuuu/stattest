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
use app\modules\nnp\models\PackagePricelistNnpSms;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\billing_uu\Pricelist as uuPricelist;
use app\modules\uu\models\ServiceType;
use kartik\editable\Editable;
use unclead\multipleinput\TabularColumn;
use app\widgets\TabularInput\TabularInput;


switch ($formModel->tariff->service_type_id) {

    case ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY:
        $pricelistServiceTypeId = uuPricelist::ID_SERVICE_TYPE_DATA;
        $packagePricelistModel = new PackagePricelistNnpInternet();
        $packagePricelistModels = $formModel->tariff->packagePricelistsNnpInternet;
        break;

    case ServiceType::ID_VOIP_PACKAGE_SMS:
    case ServiceType::ID_A2P_PACKAGE:
        $pricelistServiceTypeId = uuPricelist::ID_SERVICE_TYPE_SMS;
        $packagePricelistModel = new PackagePricelistNnpSms();
        $packagePricelistModels = $formModel->tariff->packagePricelistsNnpSms;
        break;

    default:
        throw new LogicException('Неизвестный тип услуги ' . $formModel->tariff->service_type_id);
}

$nnpPricelistList = uuPricelist::getList(
    $isWithEmpty = true,
    $isWithNullAndNotNull = false,
    $pricelistServiceTypeId
);

$isRemovePackagePricelistsV2 = false;

if (!$packagePricelistModels) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePricelistModels = [$packagePricelistModel];
    $isRemovePackagePricelistsV2 = true;
}


$this->registerJsVariable('isRemovePackagePricelistsV2', $isRemovePackagePricelistsV2);

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
    $btnOptions = ['class' => 'hide'];
} else {
    $btnOptions = $options = [];
}

if (\Yii::$app->user->can('tarifs.priceEdit')) {
    $options = $btnOptions = [];
}


$attributeLabels = $packagePricelistModel->attributeLabels();

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY));

$columns = [
    [
        'name' => 'nnp_pricelist_id',
        'title' => $attributeLabels['nnp_pricelist_id'],
        'type' => Editable::INPUT_SELECT2,
        'options' => $options + [
                'data' => $nnpPricelistList,
            ],
    ]
];

if ($formModel->tariff->service_type_id == ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY) {
    $columns[] = [
        'name' => 'bytes_amount',
        'title' => $attributeLabels['bytes_amount'],
        'value' => function($model) {
            return $model->bytes_amount / 1024 / 1024;
        },
        'type' => Editable::INPUT_TEXT,
        'options' => $options,
    ];
}

if ($formModel->tariff->service_type_id == ServiceType::ID_VOIP_PACKAGE_SMS) {
    $columns[] = [
        'name' => 'include_amount',
        'title' => $attributeLabels['include_amount'],
        'type' => Editable::INPUT_RANGE,
        'options' => $options + [
                'html5Container' => ['style' => 'width:500px'],
                'html5Options' => ['min' => 0, 'max' => 1000, 'step' => 10]
        ],
    ];
}

$columns[] = [
    'name' => 'id', // чтобы идентифицировать модель
    'type' => TabularColumn::TYPE_HIDDEN_INPUT,
];

?>

<div class="well package-pricelist">
    <h2>
        Прайслист v.2
        <?= $helpConfluence ?>
    </h2>

    <div>
        <?= TabularInput::widget([
                'models' => array_values($packagePricelistModels), // ключ должен быть автоинкрементный
                'allowEmptyList' => false,
                'addButtonOptions' => $btnOptions,
                'removeButtonOptions' => $btnOptions,

                'columns' => $columns,
            ]
        )
        ?>
    </div>
</div>