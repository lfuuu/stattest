<?php
/**
 * Список универсальных услуг с пакетами. Форма
 *
 * @var \app\classes\BaseView $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var int[] $packageServiceTypeIds
 * @var AccountTariff[][] $row
 * @var ServiceType $serviceType
 * @var AccountTariff[][] $packagesList
 */

use app\classes\Html;
use app\modules\uu\forms\AccountTariffEditForm;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['action' => '/uu/account-tariff/save-voip']);

$isEditable = $accountTariffFirst->isLogEditable();
$isCancelable = $accountTariffFirst->isLogCancelable();
$isShowAddPackage = $isEditable || $isCancelable;

$params = [
    'accountTariffFirst' => $accountTariffFirst,
    'packageServiceTypeIds' => $packageServiceTypeIds,
    'row' => $row,
    'serviceType' => $serviceType,
    'packagesList' => $packagesList,
    'isShowAddPackage' => $isShowAddPackage,
    'form' => $form,
    'tagName' => 'div',
];
$panelBodyId = 'panel-body-' . $accountTariffFirst->id;
?>

<?= Html::hiddenInput('serviceTypeId', $serviceType->id) ?>

    <div class="panel panel-info account-tariff-voip">
        <div class="panel-heading">
            <?php
            $formModel = new AccountTariffEditForm([
                'id' => $accountTariffFirst->id,
            ]);
            ?>
            <?php // город ?>
            <h2 class="panel-title">
                <?= Html::checkbox(null, $checked = true, ['class' => 'check-all collapse', 'title' => 'Отметить всё']) ?>

                <?= $accountTariffFirst->serviceType ? $accountTariffFirst->serviceType->name : '' ?>
                <?= $accountTariffFirst->region ? ' (' . $accountTariffFirst->region->name . ')' : '' ?>
                <?= $accountTariffFirst->city ? ' (' . $accountTariffFirst->city->name . ')' : '' ?>

                <?= $accountTariffFirst->tariff_period_id ?
                    $this->render('//layouts/_buttonCancel', ['url' => '#', 'class' => 'collapse']) :
                    $this->render('//layouts/_toggleButton', ['divSelector' => '#' . $panelBodyId])
                ?>
            </h2>

        </div>

        <div class="panel-body<?= $accountTariffFirst->tariff_period_id ? '' : ' collapse' ?>" id="<?= $panelBodyId ?>">
            <div class="row">

                <div class="col-sm-2 account-tariff-voip-numbers">

                    <?php // номера ?>
                    <?= $this->render('_indexVoipFormNumber', $params) ?>

                </div>

                <?php // тариф и базовый пакет ?>
                <div class="col-sm-<?= count($packageServiceTypeIds) ? 5 : 10 ?>">

                    <?= $this->render('_indexVoipFormTariffs', $params) ?>

                    <?= isset($packagesList[0]) ?
                        $this->render('_indexVoipFormTariffs', [
                            'accountTariffFirst' => reset($packagesList[0]),
                            'packageServiceTypeIds' => $packageServiceTypeIds,
                            'row' => $row,
                            'serviceType' => $serviceType,
                            'packagesList' => $packagesList,
                            'isShowAddPackage' => false,
                            'form' => $form,
                        ]) :
                        '' ?>

                    <?php // ресурсы ?>
                    <?= $this->render('_indexVoipFormResource', $params) ?>

                </div>

                <?php // пакеты ?>
                <?php if (count($packageServiceTypeIds)) : ?>
                    <div class="col-sm-5">
                        <?= $this->render('_indexVoipFormPackages', $params) ?>
                    </div>
                <?php endif ?>
            </div>


        </div>
    </div>

<?php ActiveForm::end(); ?>