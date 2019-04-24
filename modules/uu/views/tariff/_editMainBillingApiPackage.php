<?php
/**
 * Биллинг API. Пакет.
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\billing_uu\PricelistApi;
use kartik\select2\Select2;

$packageApi = $formModel->tariff->packageApi;

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

?>
<div class="well">
    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($packageApi, 'api_pricelist_id')
                ->widget(Select2::class, [
                    'data' => PricelistApi::getList(true),
                    'options' => $options,
                ]) ?>
        </div>
    </div>
</div>

