<?php
/**
 * Свойства услуги для VPS
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VPS));
?>

<div class="row">

    <?php // ID VPS ?>
    <div class="col-sm-4">
        <?= $form->field($accountTariff, 'vm_elid_id')
            ->input('number')
            ->label($accountTariff->getAttributeLabel('vm_elid_id') . $helpConfluence)
        ?>
    </div>
</div>

