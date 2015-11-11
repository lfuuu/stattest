<?php

namespace app\classes\monitoring;

use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\BusinessProcessStatus;

class ClientAccountDisabledCredit extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'client_account_disabled_credit';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Л/С без лимита кредита';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Лицевые счета без лимита кредита';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            MonitorGridColumns::getStatusColumn(),
            MonitorGridColumns::getIdColumn(),
            MonitorGridColumns::getCompanyColumn(
                $combineChainsValue = ['contract', 'contragent']
            ),
            MonitorGridColumns::getCreatedColumn(),
            MonitorGridColumns::getManagerColumn(),
            MonitorGridColumns::getRegionColumn(),
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $params = [
            'region' => Yii::$app->request->get('region'),
            'manager' => Yii::$app->request->get('manager'),
            'created' => Yii::$app->request->get('created'),
        ];

        $query =
            ClientAccount::find()
                ->from(ClientAccount::tableName() . ' c')
                ->leftJoin(ClientContract::tableName() . ' cc', 'cc.id = c.contract_id')
                ->innerJoin(ClientContragent::tableName() . ' cg', 'cc.contragent_id = cg.id')
                ->where(['<', 'c.credit', 0])
                ->andWhere([
                    'in',
                    'cc.business_process_status_id',
                    [
                        BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED,
                        BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
                        BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES
                    ]
                ])
                ->andFilterWhere(['c.region' => $params['region']])
                ->andFilterWhere(['cc.manager' => $params['manager']]);

        if ($params['created'] && !empty($params['created'])) {
            $createdDates = preg_split('/[\s+]\-[\s+]/', $params['created']);
            $query->andWhere(['between', 'c.created', $createdDates[0], $createdDates[1]]);
        }

        return new ArrayDataProvider([
            'allModels' => $query->all(),
        ]);
    }

}