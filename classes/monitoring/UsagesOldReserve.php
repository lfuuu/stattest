<?php

namespace app\classes\monitoring;

use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageExtra;
use app\models\UsageWelltime;
use app\models\ClientAccount;
use app\models\ClientContract;

class UsagesOldReserve extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'usages_old_reserve';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Старый резерв';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Список услуг в "старом" резерве или попытка удалить услугу';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            MonitorGridColumns::getUsageId(),
            MonitorGridColumns::getUsageDescription(),
            MonitorGridColumns::getIdColumn(
                $combineChainsValue = ['clientAccount']
            ),
            MonitorGridColumns::getCompanyColumn(
                $combineChainsValue = ['clientAccount', 'contract', 'contragent'],
                $combineClientId = ['clientAccount']
            ),
            MonitorGridColumns::getManagerColumn(
                $combineChainsValue = ['clientAccount']
            ),
            MonitorGridColumns::getTelecomClientBusinessProcessStatuses(),

        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $params = [
            'manager' => Yii::$app->request->get('manager'),
            'business_process_status_id' => Yii::$app->request->get('business_process_status_id'),
        ];

        $usages = [
            UsageVoip::class,
        ];

        $result = [];
        foreach ($usages as $usage) {
            $result = array_merge(
                $result,
                (array)$usage::find()
                    ->select('u.*')
                    ->from($usage::tableName() . ' u')
                    ->leftJoin(ClientAccount::tableName() . ' c', 'c.client = u.client')
                    ->leftJoin(ClientContract::tableName() . ' cc', 'cc.id = c.contract_id')
                    ->where(new Expression("u.actual_from > '3000-01-01'"))
                    ->andWhere(new Expression("u.actual_to > '3000-01-01'"))
                    ->andFilterWhere(['cc.manager' => $params['manager']])
                    ->andFilterWhere(['cc.business_process_status_id' => $params['business_process_status_id']])
                    ->all()
            );
        }

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}