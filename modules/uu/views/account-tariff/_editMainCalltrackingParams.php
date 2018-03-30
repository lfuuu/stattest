<?php
/**
 * Свойства услуги для Calltracking
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;
?>

<div class="row">
    <div class="col-sm-4">
        <?= $form
                ->field($accountTariff, 'calltracking_params')
                ->textarea()
                ->render()
        ?>
    </div>
</div>