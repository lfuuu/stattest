<?php

use app\models\HistoryVersion;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiStatus;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipNdcType;

/**
 * Class m171102_120606_clear_history_version
 */
class m171102_120606_clear_history_version extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        HistoryVersion::deleteAll([
            'model' => [
                AccountTariff::className(),
                AccountTariffLog::className(),
                Card::className(),
                CardStatus::className(),
                Imsi::className(),
                ImsiStatus::className(),
                Tariff::className(),
                TariffOrganization::className(),
                TariffPeriod::className(),
                TariffResource::className(),
                TariffVoipCity::className(),
                TariffVoipNdcType::className(),
            ],
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
