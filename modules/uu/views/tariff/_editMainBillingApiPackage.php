<?php
/**
 * Биллинг API. Пакет.
 *
 * @var BaseView $this
 * @var TariffForm $formModel
 * @var ActiveForm $form
 * @var int $editableType
 */

use app\classes\BaseView;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\forms\TariffForm;
use app\modules\uu\models\billing_uu\PricelistApi;
use app\widgets\TabularInput\TabularColumn;
use app\widgets\TabularInput\TabularInput;
use kartik\editable\Editable;
use yii\widgets\ActiveForm;

$packageApi = $formModel->packageApi;

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

?>


<div class="well package-minute">
    <h2>
        Pricelist API
    </h2>
    <?= TabularInput::widget([
            'models' => $packageApi, // ключ должен быть автоинкрементный
            'allowEmptyList' => true,
            'columns' => [
                [
                    'name' => 'api_pricelist_id',
                    'title' => (new PricelistApi())->attributeLabels()['api_pricelist_id'],
                    'type' => Editable::INPUT_SELECT2,
                    'options' => $options + [
                            'data' => PricelistApi::getList(true),
                        ],
                    'headerOptions' => [
                        'class' => 'col-sm-12',
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