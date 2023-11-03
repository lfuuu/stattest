<?php

namespace app\modules\sim\commands;

use app\helpers\DateTimeZoneHelper;
use app\models\Country;
use app\models\EventQueue;
use app\modules\nnp\models\NdcType;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\RegionSettings;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\console\Controller;

class OperateController extends Controller
{
    /**
     * Проверям на подключение IMSI к моб. номеру
     */
    public function actionCheckMobNumbers($accountId = false)
    {
        $accountTariffQuery = AccountTariff::find()
            ->where([
                'service_type_id' => ServiceType::ID_VOIP,
                'n.ndc_type_id' => NdcType::ID_MOBILE,
                'n.imsi' => null,
                'c.country_id' => Country::RUSSIA,
            ])
            ->andWhere([
                'not', [
                    'tariff_period_id' => null,
                ]
            ])
            ->joinWith('number n', true, 'INNER JOIN')
            ->joinWith('number.city c', true, 'INNER JOIN')
            ->with('number');

        if ($accountId) {
            $accountTariffQuery->andWhere(['client_account_id' => $accountId]);
        }


        static $cache = [];
        /** @var AccountTariff $accountTariff */
        foreach ($accountTariffQuery->each() as $accountTariff) {

            echo PHP_EOL . $accountTariff->id . ': ' . $accountTariff->voip_number;
            $region = $accountTariff->number->region;

            if (!isset($cache[$region])) {
                $regionSettings = RegionSettings::findOne(['region_id' => $region]);
                $sipWarehouseId = $regionSettings ? $regionSettings->sip_warehouse_status_id : false;
                $cache[$region] = $sipWarehouseId;
            }

            echo ' region: ' . $region;
            $sipWarehouseId = $cache[$region];

            if (!$sipWarehouseId) {
                echo PHP_EOL . 'in region: ' . $region . ' SIP warehouse not found';
                continue;
            }

            echo ' sip warehouse: ' . $sipWarehouseId;

            EventQueue::go(EventQueue::SYNC_TELE2_GET_IMSI, [
                'account_tariff_id' => $accountTariff->id,
                'voip_number' => $accountTariff->voip_number,
                'voip_numbers_warehouse_status' => $sipWarehouseId,
            ]);

            echo "+";
        }
    }

    /**
     * Запускаем цикл получения статусов IMSI в Tele2
     */
    public function actionRunGetImsiStatusLoop()
    {
        $countError = 0;
        do {
            $imsies = Imsi::dao()->getImsiesForGetStatus();

            foreach ($imsies as $imsi) {
                echo PHP_EOL . date(DateTimeZoneHelper::DATETIME_FORMAT) . ': ' . $imsi . ': ';
                try {
                    Imsi::dao()->getSubscriberStatus($imsi, $isSaveResultToLog = true, $isSilentWhenSaving = false);

                    $imsi = Imsi::findOne(['imsi' => $imsi]);
                    $log = $imsi->getExternalStatusLog()->orderBy(['id' => SORT_DESC])->one();
                    if ($log) {
                        echo $log;
                    }

                } catch (\Exception $e) {
                    \Yii::error($e);
                    $countError++;
                    echo PHP_EOL . 'Error: ' . $countError . ' => ' . $e->getMessage();

                    if ($countError >= 3) {
                        break 2;
                    }
                }
                sleep(3);
            }
        } while (true);
        echo PHP_EOL . 'exit';
    }
}
