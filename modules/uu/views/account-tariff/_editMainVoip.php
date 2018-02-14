<?php
/**
 * свойства услуги для телефонии
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\models\billing\Locks;
use app\models\billing\StatsAccount;
use app\models\City;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

$accountTariff = $formModel->accountTariff;
$number = $accountTariff->number;
?>

<div class="row">

    <?php // город ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'city_id')
            ->widget(Select2::className(), [
                'data' => City::getList($isWithEmpty = true, $number ? $number->country_code : null),
                'disabled' => true,
            ]) ?>
    </div>

    <?php // номер ?>
    <div class="col-sm-2">
        <label><?= $accountTariff->getAttributeLabel('voip_number') ?></label>
        <div>
            <?= $number ?
                Html::a($accountTariff->voip_number, $number->getUrl()) :
                $accountTariff->voip_number ?>
        </div>
    </div>

    <?php if ($number && $number->isMobileMcn()) : ?>

        <?php // баланс MTT ?>
        <div class="col-sm-2">
            <label><?= $accountTariff->getAttributeLabel('mtt_balance') ?></label>
            <div>
                <?= $accountTariff->mtt_balance ?>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute(['/uu/account-tariff/update-mtt-balance', 'id' => $accountTariff->id]),
                    'text' => '',
                    'title' => 'Обновить баланс МТТ',
                    'glyphicon' => 'glyphicon-refresh',
                ]) ?>
            </div>
        </div>

        <?php // номер MTT ?>
        <div class="col-sm-3">
            <label><?= $accountTariff->getAttributeLabel('mtt_number') ?></label>
            <div>
                <?= $accountTariff->mtt_number ?>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute(['/uu/account-tariff/update-mtt-number', 'id' => $accountTariff->id]),
                    'text' => '',
                    'title' => 'Обновить номер МТТ',
                    'glyphicon' => 'glyphicon-refresh',
                ]) ?>
            </div>
        </div>

    <?php endif ?>

</div>

<div class="row">
    <div class="col-sm-6">
        <?= $form->field($accountTariff, 'device_address')
            ->textInput()
        ?>
    </div>

    <div class="col-sm-3">
        <label>Остатки секунд по пакетам</label>
        <div>
            <?php
            try {
                StatsAccount::setPgTimeout(Locks::PG_ACCOUNT_TIMEOUT);
                $statsNnpPackageMinutes = StatsAccount::getStatsNnpPackageMinute($accountTariff->client_account_id, $accountTariff->id);
                foreach ($statsNnpPackageMinutes as $statsNnpPackageMinute) :
                    ?>
                    <div>
                        <b><?= $statsNnpPackageMinute['name'] ?></b>:
                        <?= $statsNnpPackageMinute['used_seconds'] ?> / <?= $statsNnpPackageMinute['total_seconds'] ?>
                    </div>
                <?php
                endforeach;
            } catch (\Exception $e) {
            }
            ?>
        </div>
    </div>
</div>