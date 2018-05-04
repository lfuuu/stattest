<?php
/**
 * свойства разовой услуги
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;

$accountTariff = $formModel->accountTariff;
$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_ONE_TIME));
?>

<div class="row">

    <?php // стоимость ?>
    <div class="col-sm-8">
        <div class="form-group field-tariff-resource-one-time required">
            <label for="tariff-resource-one-time" class="control-label">Стоимость, ¤ <?= $helpConfluence ?></label> (если это скидка, то указывайте отрицательное значение)

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