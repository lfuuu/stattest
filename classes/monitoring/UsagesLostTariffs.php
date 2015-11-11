<?php

namespace app\classes\monitoring;

use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVoipPackage;
use app\models\UsageExtra;
use app\models\UsageWelltime;

class UsagesLostTariffs extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'usages_lost_tariffs';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Активные услуги без тарифа';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Активные услуги без тарифа';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            MonitorGridColumns::getStatusColumn(
                $combineChainsValue = ['clientAccount']
            ),
            MonitorGridColumns::getIdColumn(
                $combineChainsValue = ['clientAccount']
            ),
            MonitorGridColumns::getCompanyColumn(
                $combineChainsValue = ['clientAccount', 'contract', 'contragent'],
                $combineClientId = ['clientAccount']
            ),
            [
                'attribute' => 'id',
                'label' => 'ID услуги',
            ],
            MonitorGridColumns::getUsageTitle(),
            MonitorGridColumns::getUsageRelevance(),
            MonitorGridColumns::getUsageDescription(),
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $usages = [
            UsageVoip::className(),
            UsageVirtpbx::className(),
            UsageIpPorts::className(),
            UsageSms::className(),
            UsageVoipPackage::className(),
            UsageExtra::className(),
            UsageWelltime::className(),
        ];

        $result = [];
        foreach ($usages as $usage) {
            $result = array_merge($result, (array) $usage::getMissingTariffs());
        }

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

    /**
     * @return array
     */
    public static function intoLogTariff($usage)
    {
        return
            $usage::find()
                ->from([$usage::tableName() . ' uv'])
                ->select('uv.*')
                ->where(new Expression('uv.actual_from <= CAST(NOW() AS DATE)'))
                ->andWhere(new Expression('uv.actual_to > CAST(NOW() AS DATE)'))
                ->andWhere(
                    new Expression('
                        (
                            SELECT id_tarif
                            FROM log_tarif
                            WHERE
                                service = "' . $usage::tableName() . '"
                                AND id_service = uv.id
                                AND date_activation <= CAST(NOW() AS DATE)
                                AND id_tarif != 0
                            ORDER BY date_activation DESC, id DESC
                            LIMIT 1
                        ) IS NULL'
                    )
                )
                ->all();
    }

    /**
     * @return array
     */
    public static function intoTariffTable($usage, $tariffsTable, $tariffField = 'tarif_id')
    {
        return
            $usage::find()
                ->select($usage::tableName() . '.*')
                ->leftJoin($tariffsTable . ' ts', 'ts.id = ' . $tariffField)
                ->where(new Expression('actual_from <= CAST(NOW() AS DATE)'))
                ->andWhere(new Expression('actual_to > CAST(NOW() AS DATE)'))
                ->andWhere('ts.id IS NULL')
                ->all();
    }

}