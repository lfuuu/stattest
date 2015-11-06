<?php

namespace app\classes\monitoring;

use app\models\ClientAccount;
use yii\base\Component;
use yii\data\ArrayDataProvider;

class ClientAccountWODayLimit extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'client_account_wo_day_limit';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Л/С без дневного лимита';
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        return new ArrayDataProvider([
            'allModels' =>
                ClientAccount::find()
                    ->where(['voip_credit_limit_day' => 0])
                    ->all()
        ]);
    }

}