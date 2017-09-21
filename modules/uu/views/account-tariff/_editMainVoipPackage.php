<?php
/**
 * свойства услуги для телефонии. Пакеты
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\models\City;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
$number = $accountTariffParent ? $accountTariffParent->number : null;
?>

<div class="row">

    <?php // город ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'city_id')
            ->widget(Select2::className(), [
                'data' => City::getList($isWithEmpty = true, $number ? $number->country_code : null),
            ]) ?>
    </div>

</div>

