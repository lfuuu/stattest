<?php

namespace app\classes\monitoring;

use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\models\ClientAccount;
use app\models\ClientContract;

class MissingManager extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'missing_manager';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Договора без надзора менеджера';
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return new ArrayDataProvider([
            'allModels' =>
                ClientContract::find()
                    ->from(ClientContract::tableName() . ' cc')
                    ->leftJoin(ClientAccount::tableName() . ' c', 'c.contract_id = cc.id')
                    ->where(['manager' => ''])
                    ->groupBy('cc.id')
                    ->all(),
        ]);
    }

}