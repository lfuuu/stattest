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

class UsagesActiveConnecting extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'usages_active_connecting';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Активные услуги с некорректным статусом';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Список активных услуг со статусом "подключаемые"';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
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
        $params = [
            'manager' => Yii::$app->request->get('manager'),
            'business_process_status_id' => Yii::$app->request->get('business_process_status_id'),
        ];

        $usages = [
            UsageVoip::className(),
            UsageVirtpbx::className(),
            UsageIpPorts::className(),
            UsageSms::className(),
            UsageExtra::className(),
            UsageWelltime::className(),
        ];

        $result = [];
        foreach ($usages as $usage) {
            $result = array_merge(
                $result,
                (array) $usage::find()
                    ->select('u.*')
                    ->from($usage::tableName() . ' u')
                    ->leftJoin(ClientAccount::tableName() . ' c', 'c.client = u.client')
                    ->leftJoin(ClientContract::tableName() . ' cc', 'cc.id = c.contract_id')
                    ->where(new Expression('u.actual_from <= CAST(NOW() AS DATE)'))
                    ->andWhere(new Expression('u.actual_to > CAST(NOW() AS DATE)'))
                    ->andWhere(['u.status' => 'connecting'])
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