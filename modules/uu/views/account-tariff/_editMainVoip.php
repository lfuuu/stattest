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
use app\models\mtt_raw\MttRaw;
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
                <?php if ($accountTariff->mtt_balance) : ?>
                    <?= sprintf('%.2f', $accountTariff->mtt_balance) ?> руб.<br>
                    <?= sprintf('%.2f',$accountTariff->mtt_balance / \app\modules\mtt\Module::MEGABYTE_COST) ?> МБ
                <?php endif ?>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute(['/uu/mtt/update-balance', 'accountTariffId' => $accountTariff->id]),
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
                    'url' => Url::toRoute(['/uu/mtt/update-number', 'accountTariffId' => $accountTariff->id]),
                    'text' => '',
                    'title' => 'Обновить номер МТТ',
                    'glyphicon' => 'glyphicon-refresh',
                ]) ?>
            </div>
        </div>

        <!-- MTT статистика Internet -->
        <div class="col-sm-3">
            <label>Статистика MTT</label>
            <div>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute([
                        '/uu/mtt/',
                        'MttRawFilter[number_service_id]' => $accountTariff->id,
                        'MttRawFilter[serviceid][0]' => MttRaw::SERVICE_ID_SMS_IN_HOMENETWORK,
                        'MttRawFilter[serviceid][1]' => MttRaw::SERVICE_ID_SMS_IN_ROAMING,
                    ]),
                    'text' => 'SMS',
                ]) ?>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute([
                        '/uu/mtt/',
                        'MttRawFilter[number_service_id]' => $accountTariff->id,
                        'MttRawFilter[serviceid][0]' => MttRaw::SERVICE_ID_INET_IN_HOMENETWORK,
                        'MttRawFilter[serviceid][1]' => MttRaw::SERVICE_ID_INET_IN_ROAMING,
                    ]),
                    'text' => 'Интернет',
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
                $statsNnpPackageMinutes = StatsAccount::getStatsNnpPackageMinute($accountTariff->client_account_id,
                    $accountTariff->id);
                foreach ($statsNnpPackageMinutes as $statsNnpPackageMinute) : ?>
                    <div>
                        <b><?= $statsNnpPackageMinute['name'] ?></b>:
                        <?= round($statsNnpPackageMinute['used_seconds'] / 60, 2) . ' / ' . round($statsNnpPackageMinute['total_seconds'] / 60, 2) ?>
                    </div>
                <?php
                endforeach;
            } catch (\Exception $e) {
            }
            ?>
        </div>
    </div>
</div>