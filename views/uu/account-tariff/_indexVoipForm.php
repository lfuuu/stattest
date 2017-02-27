<?php
/**
 * Список универсальных услуг с пакетами. Форма
 *
 * @var \yii\web\View $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var int[] $packageServiceTypeIds
 * @var AccountTariff[][] $row
 * @var ServiceType $serviceType
 * @var AccountTariff[][] $packagesList
 */

use app\classes\Html;
use app\classes\uu\forms\AccountTariffEditForm;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['action' => 'uu/account-tariff/save-voip']);

$isEditable = $accountTariffFirst->tariff_period_id;
$isCancelable = $accountTariffFirst->isCancelable();
$isShowAddPackage = $isEditable || $isCancelable;

$params = [
    'accountTariffFirst' => $accountTariffFirst,
    'packageServiceTypeIds' => $packageServiceTypeIds,
    'row' => $row,
    'serviceType' => $serviceType,
    'packagesList' => $packagesList,
    'isShowAddPackage' => $isShowAddPackage,
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
                <?= Html::checkbox(null, $checked = true, ['class' => 'check-all', 'style' => 'display: none;', 'title' => 'Отметить всё']) ?>

                <?= $accountTariffFirst->serviceType ? $accountTariffFirst->serviceType->name : '' ?>
                <?= $accountTariffFirst->region ? ' (' . $accountTariffFirst->region->name . ')' : '' ?>
                <?= $accountTariffFirst->city ? ' (' . $accountTariffFirst->city->name . ')' : '' ?>

                <?= $accountTariffFirst->tariff_period_id ?
                    '' :
                    $this->render('//layouts/_toggleButton', ['divSelector' => '#' . $panelBodyId])
                ?>
            </h2>

        </div>

        <div class="panel-body" id="<?= $panelBodyId ?>"
            <?php if (!$accountTariffFirst->tariff_period_id) : ?>
                style="display: none;"
            <?php endif ?>
        >
            <div class="row">

                <?php // номера ?>
                <div class="col-sm-2 account-tariff-voip-numbers">
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
                        ]) :
                        '' ?>

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