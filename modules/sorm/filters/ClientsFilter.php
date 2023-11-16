<?php

namespace app\modules\sorm\filters;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\modules\uu\models\AccountTariff;

class ClientsFilter extends ActiveRecord
{
    public $filter_region_id = '';
    public $type = ClientContragent::PERSON_TYPE;
    public $is_b2c = 1;
    public $account_manager = '';

    private $legalMap = [
        ClientContragent::PERSON_TYPE => 0,
        ClientContragent::LEGAL_TYPE => 1,
    ];

    public function attributeLabels()
    {
        return [
            'id' => 'Ид',
            'name_jur' => 'Название / ФИО',
            'is_b2c' => 'B2C',
            'account_manager' => 'Акк. менеджер',
            'filter_region_id' => 'Регион',
        ];
    }

    public function rules()
    {
        return [
            [['filter_region_id', 'is_b2c'], 'integer'],
            [['account_manager',], 'string'],
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
            return $this->is_b2c ? $isSet : !$isSet;
        });

    }

    private function getSubscribers()
    {
        $andWhere = '';
        $params = [':legal_type_id' => $this->getLegalTypeId()];

        if ($this->filter_region_id !== '') {
            $params[':region_id'] = $this->filter_region_id;
            $andWhere .= ' AND region_id = :region_id';
        }

        $fList = 'region_id, f, i, o, legal_type_id, name_jur, _state_address_nostruct, _address_nostruct, _state_address_device_nostruct, _address_device_nostruct ';
        $subscribersQuery = \Yii::$app->dbPg->createCommand($q = 'select max(id::int) as id, count(*) as cnt, ' . $fList . ' from sorm_itgrad.subscribers_v1 where (not is_active and legal_type_id = :legal_type_id)' . $andWhere . ' group by ' . $fList, $params);

        $subscribers = $subscribersQuery->queryAll();
        if (!$subscribers) {
            return [];
        }

        $accountIds = array_filter(array_map(fn($sub) => $sub['region_id'] != 99 ? $sub['id'] : AccountTariff::find()->where(['id' => $sub['id']])->select('client_account_id')->scalar(), $subscribers));
        $accountIds = array_combine($accountIds, $accountIds);

        $accountTariffIds = array_filter(array_map(fn($sub) => $sub['region_id'] == 99 ? $sub['id'] : null, $subscribers));
        if ($accountTariffIds) {
            $accountIds += AccountTariff::find()->where(['id' => $accountTariffIds])->select('client_account_id')->distinct()->indexBy('id')->column();
        }

        $priceLevelWhere = $this->is_b2c !== '' ? ($this->is_b2c ? ['price_level' => ClientAccount::PRICE_LEVEL_B2C] : ['not', ['price_level' => ClientAccount::PRICE_LEVEL_B2C]]) : [];

        $qq = ClientAccount::find()->alias('c')->where(['c.id' => array_keys($accountIds)])->andWhere($priceLevelWhere)->select('c.id');
        if ($this->account_manager !== '') {
            $qq->joinWith('clientContractModel cc')->andWhere(['cc.account_manager' => $this->account_manager]);
        }
//        echo $qq->createCommand()->rawSql;
        $accountIdsB2c = $qq->column();

        if (!$accountIdsB2c) {
            return [];
        }

        array_walk($subscribers, function (&$sub) use ($accountIds) {
            $sub['account_id'] = $accountIds[$sub['id']];
        });

        $accountIdsB2c = array_fill_keys($accountIdsB2c, 1); // array values to keys
        $subscribers = array_filter($subscribers, function ($sub) use ($accountIdsB2c) {
            $isSet = isset($accountIdsB2c[$sub['account_id']]);
//            return $this->is_b2c ? $isSet : !$isSet;
            return $isSet;
        });

        return $subscribers;
    }

    private function getLegalTypeId()
    {
        return $this->legalMap[$this->type] ?? 1;
    }
}
