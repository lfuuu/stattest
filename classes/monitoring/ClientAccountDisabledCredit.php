<?php

namespace app\classes\monitoring;

use app\models\ClientAccount;
use yii\base\Component;
use yii\data\ArrayDataProvider;

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
        return 'Л/С с отключенным кредитом';
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        return new ArrayDataProvider([
            'allModels' =>
                ClientAccount::find()
                    ->where(['<', 'credit', 0])
                    ->all()
        ]);
    }

}