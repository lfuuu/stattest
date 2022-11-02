<?php
/**
 * Пакеты. Цена по направлениям
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\Html;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\PackagePrice;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\ServiceType;
use kartik\editable\Editable;
use unclead\multipleinput\TabularColumn;
use app\widgets\TabularInput\TabularInput;

$packagePrice = new PackagePrice;
$attributeLabels = $packagePrice->attributeLabels();

$packagePrices = $formModel->tariff->packagePrices;
$packagePricesCount = count($packagePrices);
if (!$packagePrices) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packagePrices = [$packagePrice];
    $this->registerJsVariable('isRemovePackagePrices', true);
}

$destinationList = Destination::getList(true);

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
    $hideOpt = ['class' => 'hide'];
} else {
    $hideOpt = $options = [];
}

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_CALLS));
?>

<div class="well package-price">
    <h2>
        Цена по направлениям
        <?= $helpConfluence ?>
    </h2>
    <?php
    if ($packagePricesCount) {
        echo Html::a('Скачать все префиксы номеров с ценами', ['/uu/tariff/download', 'id' => $formModel->tariff->id]);
    }
    ?>

    <?= TabularInput::widget([
            'models' => array_values($packagePrices), // ключ должен быть автоинкрементный
            'allowEmptyList' => true,
            'addButtonOptions' => $hideOpt,
            'removeButtonOptions' => $hideOpt,
            'columns' => [
                [
                    'name' => 'destination_id',
                    'title' => $attributeLabels['destination_id'] . $helpConfluence,
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => $destinationList,
                        ],
                    'headerOptions' => [
                        'class' => 'col-sm-5',
                    ],
                ],
                [
                    'name' => 'price',
                    'title' => $attributeLabels['price'] . $helpConfluence,
                    'options' => $options,
                    'headerOptions' => [
                        'class' => 'col-sm-2',
                    ],
                ],
                [
                    'name' => 'interconnect_price',
                    'title' => $attributeLabels['interconnect_price'] . $helpConfluence,
                    'options' => $options,
                    'headerOptions' => [
                        'class' => 'col-sm-2',
                    ],
                ],
                [
                    'name' => 'connect_price',
                    'title' => $attributeLabels['connect_price'] . $helpConfluence,
                    'options' => $options,
                    'headerOptions' => [
                        'class' => 'col-sm-2',
                    ],
                ],
                [
                    'name' => 'weight',
                    'title' => $attributeLabels['weight'] . $helpConfluence,
                    'options' => [], // всегда можно редактировать
                    'headerOptions' => [
                        'class' => 'col-sm-1',
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
