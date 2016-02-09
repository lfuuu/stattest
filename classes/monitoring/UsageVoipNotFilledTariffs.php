<?php

namespace app\classes\monitoring;

use app\models\LogTarif;
use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVoipPackage;
use app\models\UsageExtra;
use app\models\UsageWelltime;

class UsageVoipNotFilledTariffs extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'usages_not_filled_tariffs';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Телефония с неполным набором тарифов';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Телефония с неполным набором тарифов';
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
            MonitorGridColumns::getUsageId(),
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
        $result =
            UsageVoip::find()
                ->from([UsageVoip::tableName() . ' uv'])
                ->select('uv.*')
                ->leftJoin(LogTarif::tableName() . ' t', 't.id_service = uv.id AND t.service="usage_voip"')
                ->where(new Expression('uv.actual_from <= CAST(NOW() AS DATE)'))
                ->andWhere(new Expression('uv.actual_to > CAST(NOW() AS DATE)'))
                ->orWhere(new Expression('uv.actual_to > DATE_ADD(NOW(), INTERVAL -1 MONTH)'))
                ->andWhere(new Expression('!t.id_tarif OR !t.id_tarif_local_mob OR !t.id_tarif_russia OR !t.id_tarif_russia_mob OR !t.id_tarif_intern'))
                ->all();

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}