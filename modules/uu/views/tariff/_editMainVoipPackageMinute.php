<?php
/**
 * Пакеты. Предоплаченные минуты
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\nnp\models\Destination;
use app\modules\nnp\models\PackageMinute;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\ServiceType;
use kartik\editable\Editable;
use unclead\multipleinput\TabularColumn;
use app\widgets\TabularInput\TabularInput;

$packageMinute = new PackageMinute;
$attributeLabels = $packageMinute->attributeLabels();

$packageMinutes = $formModel->tariff->packageMinutes;
if (!$packageMinutes) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $packageMinutes = [$packageMinute];
    $this->registerJsVariable('isRemovePackageMinutes', true);
}

$destinationList = Destination::getList(true);

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
    $btnOptions = ['class' => 'hide'];
} else {
    $btnOptions = $options = [];
}

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_CALLS));
?>

<div class="well package-minute">
    <h2>
        Предоплаченные минуты
        <?= $helpConfluence ?>
    </h2>
    <?= TabularInput::widget([
            'models' => array_values($packageMinutes), // ключ должен быть автоинкрементный
            'allowEmptyList' => true,
            'addButtonOptions' => $btnOptions,
            'removeButtonOptions' => $btnOptions,

            'columns' => [
                [
                    'name' => 'destination_id',
                    'title' => $attributeLabels['destination_id'] . $helpConfluence,
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => $destinationList,
                        ],
                    'headerOptions' => [
                        'class' => 'col-sm-9',
                    ],
                ],
                [
                    'name' => 'minute',
                    'title' => $attributeLabels['minute'] . $helpConfluence,
                    'options' => $options + [
                            'type' => 'number',
                        ],
                    'type' => Editable::INPUT_TEXT,
                    'headerOptions' => [
                        'class' => 'col-sm-3',
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
