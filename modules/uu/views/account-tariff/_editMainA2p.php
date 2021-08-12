<?php
/**
 * Свойства услуги для A2P
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
?>

<div class="row">

    <?php // ROUTE_NAME
        $a2pRoutes = \app\models\dictionary\A2pSmsRoute::getList(false, false, 'name','name');
        $a2pRoutes = [$accountTariff->route_name_default => $accountTariff->route_name_default] + $a2pRoutes;
    ?>
    <div class="col-sm-4">
        <?= $form->field($accountTariff, 'route_name')
            ->widget(\kartik\widgets\Select2::class, [
                'data' => $a2pRoutes,
            ])
            ->label($accountTariff->getAttributeLabel('route_name'))
        ?>
    </div>
</div>

