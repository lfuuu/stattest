<?php
/**
 * Пакеты. Прайслист с МГП (минимальный гарантированный платеж)
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\Html;
use app\models\billing\Pricelist;
use app\modules\nnp\models\PackagePricelist;
use app\modules\nnp\models\PackagePricelistNnp;
use app\modules\nnp\models\Pricelist as nnpPricelist;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\ServiceType;
use kartik\editable\Editable;
use unclead\multipleinput\TabularColumn;
use app\widgets\TabularInput\TabularInput;

$packagePricelist = new PackagePricelist;
$packagePricelistNnp = new PackagePricelistNnp();
$attributeLabels = $packagePricelist->attributeLabels();

$packagePricelists = $formModel->tariff->packagePricelists;
$packagePricelistsNnp = $formModel->tariff->packagePricelistsNnp;

$isRemovePackagePricelistsV1 = $isRemovePackagePricelistsV2 = false;
if (!$packagePricelists) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePricelists = [$packagePricelist];
    $isRemovePackagePricelistsV1 = true;
    $this->registerJsVariable('isRemovePackagePricelistsV1', true);
}

if (!$packagePricelistsNnp) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePricelistsNnp = [$packagePricelistNnp];
    $isRemovePackagePricelistsV2 = true;

}

$this->registerJsVariable('isRemovePackagePricelistsV1', $isRemovePackagePricelistsV1);
$this->registerJsVariable('isRemovePackagePricelistsV2', $isRemovePackagePricelistsV2);


switch ($tariff = $formModel->tariff->service_type_id) {

    case ServiceType::ID_VOIP_PACKAGE_CALLS:
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

$nnpPricelistList = nnpPricelist::getList($isWithEmpty = true, $isWithNullAndNotNull = false);

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
    $btnOptions = ['class' => 'hide'];
} else {
    $btnOptions = $options = [];
}

$optionsPricelist = $options;

if (\Yii::$app->user->can('tarifs.priceEdit')) {
    $optionsPricelist = $btnOptions = [];
}

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_CALLS));

$isPriceListV2Checked = $isRemovePackagePricelistsV1 && $isRemovePackagePricelistsV2 ? null : $isRemovePackagePricelistsV1;
?>

<div class="well package-pricelist">
    <h2>
        Прайс-лист с МГП
        <?= $helpConfluence ?>
    </h2>

    <div>
        <?= Html::checkbox('is_pricelist_v2', $isPriceListV2Checked,
            [
                'id' => 'is_pricelist_v2',
                'disabled' => $isPriceListV2Checked !== null]
            + ['label' => $attributeLabels['nnp_pricelist_id']]
        ) ?>
    </div>

    <div id="pricelist_v1" class="hide">
        <?= TabularInput::widget([
                'models' => array_values($packagePricelists), // ключ должен быть автоинкрементный
                'allowEmptyList' => true,
                'addButtonOptions' => $btnOptions,
                'removeButtonOptions' => $btnOptions,

                'columns' => [
                    [
                        'name' => 'pricelist_id',
                        'title' => $attributeLabels['pricelist_id'] . $helpConfluence,
                        'type' => Editable::INPUT_SELECT2,
                        'options' => $options + [
                                'data' => $pricelistList,
                            ],
                    ],
                    [
                        'name' => 'minute',
                        'title' => $attributeLabels['minute'] . $helpConfluence,
                        'type' => Editable::INPUT_TEXT,
                        'options' => $options + [
                                'type' => 'number',
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

    <div id="pricelist_v2" class="hide">
        <?= TabularInput::widget([
                'models' => array_values($packagePricelistsNnp), // ключ должен быть автоинкрементный
                'allowEmptyList' => true,
                'addButtonOptions' => $btnOptions,
                'removeButtonOptions' => $btnOptions,

                'columns' => [
                    [
                        'name' => 'nnp_pricelist_id',
                        'title' => $attributeLabels['nnp_pricelist_id'],
                        'type' => Editable::INPUT_SELECT2,
                        'options' => $optionsPricelist + [
                                'data' => $nnpPricelistList,
                            ],
                    ],
                    [
                        'name' => 'minute',
                        'title' => $attributeLabels['minute'] . $helpConfluence,
                        'type' => Editable::INPUT_RANGE,
                        'options' => $optionsPricelist + [
                                'html5Options' => ['min' => 0, 'max' => 10000, 'step' => 100],
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

    <script>
        $('#is_pricelist_v2').on('change',)
    </script>

</div>