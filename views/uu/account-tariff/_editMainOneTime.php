<?php
/**
 * свойства разовой услуги
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\classes\uu\model\AccountLogResource;
use kartik\form\ActiveForm;

$accountTariff = $formModel->accountTariff;
?>

<div class="row">

    <?php // стоимость ?>
    <div class="col-sm-8">
        <div class="form-group field-tariff-resource-one-time required">
            <label for="tariff-resource-one-time" class="control-label">Стоимость, ¤</label> (списывается со счета; если надо добавить на счет - указывайте отрицательное значение)

            <div>
                <?php
                if ($accountTariff->isNewRecord) {
                    echo Html::input('number', 'resourceOneTimeCost', '', ['class' => 'form-control', 'step' => 0.01]);
                } else {
                    $accountLogResource = AccountLogResource::findOne(['account_tariff_id' => $accountTariff->id]);
                    $accountLogResource && printf('%.2f', $accountLogResource->price);
                }
                ?>
            </div>

        </div>
    </div>

</div>