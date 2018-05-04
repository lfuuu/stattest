<?php
/**
 * Свойства услуги для Calltracking
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\modules\uu\models\ServiceType;
use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;
$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_CALLTRACKING));
?>

<div class="row">
    <div class="col-sm-4">
        <?= $form
            ->field($accountTariff, 'calltracking_params')
            ->textarea()
            ->label($accountTariff->getAttributeLabel('calltracking_params') . $helpConfluence)
        ?>
    </div>
</div>