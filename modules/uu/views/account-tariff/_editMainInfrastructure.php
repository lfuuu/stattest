<?php
/**
 * Свойства услуги для инфраструктуры
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\models\City;
use app\models\Datacenter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_INFRASTRUCTURE));
?>

<div class="row">

    <?php // город ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'city_id')
            ->widget(Select2::class, [
                'data' => City::getList(
                    $isWithEmpty = true,
                    $countryId = null,
                    $isWithNullAndNotNull = false,
                    $isUsedOnly = false,
                    $isShowInLk = false
                ),
            ])
            ->label($accountTariff->getAttributeLabel('city_id') . $helpConfluence)
        ?>
    </div>

    <?php // проект ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'infrastructure_project')
            ->widget(Select2::class, [
                'data' => AccountTariff::getInfrastructureProjectList($isWithEmpty = true),
            ])
            ->label($accountTariff->getAttributeLabel('infrastructure_project') . $helpConfluence)
        ?>
    </div>

    <?php // уровень ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'infrastructure_level')
            ->widget(Select2::class, [
                'data' => AccountTariff::getInfrastructureLevelList($isWithEmpty = true),
            ])
            ->label($accountTariff->getAttributeLabel('infrastructure_level') . $helpConfluence)
        ?>
    </div>

    <?php // тех. площадка ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'datacenter_id')
            ->widget(Select2::class, [
                'data' => Datacenter::getList($isWithEmpty = true),
            ])
            ->label($accountTariff->getAttributeLabel('datacenter_id') . $helpConfluence)
        ?>
    </div>

    <?php // цена ?>
    <div class="col-sm-4">
        <?= $form->field($accountTariff, 'price')
            ->input('number', ['step' => 0.01])
            ->label($accountTariff->getAttributeLabel('price') . $helpConfluence)
        ?>
    </div>
</div>

