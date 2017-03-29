<?php
/**
 * свойства услуги для телефонии
 *
 * @var \app\classes\BaseView $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use app\models\City;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
?>

<div class="row">

    <?php // город ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'city_id')
            ->widget(Select2::className(), [
                'data' => City::getList($isWithEmpty = true, $formModel->accountTariff->clientAccount->country_id),
            ]) ?>
    </div>

    <?php // номер ?>
    <div class="col-sm-2">
        <label><?= $accountTariff->getAttributeLabel('voip_number') ?></label>
        <div><?= $accountTariff->voip_number ?></div>
    </div>

</div>

