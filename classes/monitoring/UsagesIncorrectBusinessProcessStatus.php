<?php

namespace app\classes\monitoring;

use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\classes\Html;
use app\models\User;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVoipPackage;
use app\models\UsageExtra;
use app\models\UsageWelltime;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\BusinessProcessStatus;

class UsagesIncorrectBusinessProcessStatus extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'usages_incorrect_business_process_status';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Л/C с некорректным бизнес-процесс статусом';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return
            'Лицевые счета с активными услугами и бизнес-процесс статусом не ' .
            '"Включенные", "Подключаемые", "Заказ услуг"';
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
                $combineChainsValue = ['clientAccount', 'contract', 'contragent'],
                $combineClientId = ['clientAccount']
            ),
            MonitorGridColumns::getManagerColumn(
                $combineChainsValue = ['clientAccount']
            ),
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $params = [
            'manager' => Yii::$app->request->get('manager'),
        ];

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
            $result = array_merge(
                $result,
                (array) $usage::find()
                    ->select('u.*')
                    ->from($usage::tableName() . ' u')
                    ->leftJoin(ClientAccount::tableName() . ' c', 'c.client = u.client')
                    ->leftJoin(ClientContract::tableName() . ' cc', 'cc.id = c.contract_id')
                    ->where(new Expression('actual_from <= CAST(NOW() AS DATE)'))
                    ->andWhere(new Expression('actual_to > CAST(NOW() AS DATE)'))
                    ->andWhere([
                        'not in',
                        'cc.business_process_status_id',
                        [
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_CONNECTED,
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
                            BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES
                        ]
                    ])
                    ->andFilterWhere(['cc.manager' => $params['manager']])
                    ->groupBy('u.client')
                    ->all()

            );
        }

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}