<?php

namespace app\modules\sorm\filters;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\modules\uu\models\AccountTariff;

class ClientsFilter extends ActiveRecord
{
    public $type = ClientContragent::PERSON_TYPE;
    public $isB2c = true;

    private $legalMap = [
        ClientContragent::PERSON_TYPE => 0,
        ClientContragent::LEGAL_TYPE => 1,
    ];

    public function attributes()
    {
        return [
            'id' => 'Ид',
            'name_jur' => 'Название / ФИО',
        ];
    }

    public function search()
    {
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $this->getSubscribers(),
        ]);

        return $dataProvider;
    }

    private function _getSubscribers()
    {
        $subscribers = \Yii::$app->dbPg->createCommand('select * from sorm_itgrad.subscribers_v1 where not is_active and legal_type_id = :legal_type_id', [':legal_type_id' => $this->getLegalTypeId()])->queryAll();

        if (!$subscribers) {
            return [];
        }

        $accountIds = array_map(fn($sub) => $sub['id'], $subscribers);
        $accountIdsB2c = ClientAccount::find()->where(['id' => $accountIds, 'price_level' => ClientAccount::PRICE_LEVEL_B2C])->select('id')->column();

        if (!$accountIdsB2c) {
            return [];
        }

        $accountIdsB2c = array_fill_keys($accountIdsB2c, 1); // array values to keys
        return array_filter($subscribers, function ($sub) use ($accountIdsB2c) {
            $isSet = isset($accountIdsB2c[$sub['id']]);
            return $this->isB2c ? $isSet : !$isSet;
        });

    }

    private function getSubscribers()
    {
        $subscribers = \Yii::$app->dbPg->createCommand($q = 'select * from sorm_itgrad.subscribers_v1 where (not is_active and legal_type_id = :legal_type_id)', [':legal_type_id' => $this->getLegalTypeId()])->queryAll();

        if (!$subscribers) {
            return [];
        }

        $accountIds = array_filter(array_map(fn($sub) => $sub['region_id'] != 99 ? $sub['id'] : null, $subscribers));
        $accountIds = array_combine($accountIds, $accountIds);

        $accountTariffIds = array_filter(array_map(fn($sub) => $sub['region_id'] == 99 ? $sub['id'] : null, $subscribers));
        if ($accountTariffIds) {
            $accountIds += AccountTariff::find()->where(['id' => $accountTariffIds])->select('client_account_id')->distinct()->indexBy('id')->column();
        }

        $priceLevelWhere = $this->isB2c ? ['price_level' => ClientAccount::PRICE_LEVEL_B2C] : ['not', ['price_level' => ClientAccount::PRICE_LEVEL_B2C]];

        $qq = ClientAccount::find()->where(['id' => array_keys($accountIds)])->andWhere($priceLevelWhere)->select('id');
//        echo $qq->createCommand()->rawSql;
        $accountIdsB2c = $qq->column();

        if (!$accountIdsB2c) {
            return [];
        }

        array_walk($subscribers, function(&$sub) use ($accountIds) {
            $sub['account_id'] = $accountIds[$sub['id']];
        });

        $accountIdsB2c = array_fill_keys($accountIdsB2c, 1); // array values to keys
        $subscribers = array_filter($subscribers, function ($sub) use ($accountIdsB2c) {
            $isSet = isset($accountIdsB2c[$sub['account_id']]);
//            return $this->isB2c ? $isSet : !$isSet;
            return $isSet;
        });

        return $subscribers;
    }

    private function getLegalTypeId()
    {
        return $this->legalMap[$this->type] ?? 1;
    }
}
