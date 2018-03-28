<?php
/**
 * Свойства услуги для VPS
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\models\City;
use app\models\Datacenter;
use app\modules\uu\models\AccountTariff;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
?>

<div class="row">

    <?php // ID VPS ?>
    <div class="col-sm-4">
        <?= $form->field($accountTariff, 'vm_elid_id')
            ->input('number') ?>
    </div>
</div>

