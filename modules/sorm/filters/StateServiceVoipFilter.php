<?php

namespace app\modules\sorm\filters;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\voip\StateServiceVoip;
use app\modules\uu\models\AccountTariff;
use yii\data\ActiveDataProvider;

class StateServiceVoipFilter extends StateServiceVoip
{
    public $region = '';
    public $is_b2c = '';
    public $account_manager = '';

    public function rules()
    {
        return [
            [['region', 'is_b2c'], 'integer'],
            [['account_manager',], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'is_b2c' => 'B2C',
                'account_manager' => 'Акк. менеджер',
            ];
    }

    public function search()
    {
        $query = self::find()
            ->alias('s')
            ->where(['>', 's.lines_amount', 0])
            ->andWhere(['s.device_address' => ''])
            ->andWhere(['not', ['like', 'e164', '79%', false]]) // no mob
            ->andWhere(['not', ['like', 'e164', '77%', false]]) // no KZ
            ->andWhere(['not', ['like', 'e164', '780%', false]]) // no free phones
            ->andWhere(['like', 'e164', '7%', false])
            ->andWhere(['OR', ['actual_to' => null], ['>=', 'actual_to', (new \DateTime('now'))->modify('-3 year')->format(DateTimeZoneHelper::DATE_FORMAT)]])
            ->joinWith('clientAccount.clientContractModel cc')
            ->joinWith('clientAccount.clientContractModel.clientContragent cg')
            ->with('clientAccount')
            ->With('clientAccount.clientContractModel.clientContragent')
        ;

        $query->andWhere(['not', ['cc.state' => 'unchecked']]);
        $query->andWhere(['not', ['cc.business_process_status_id' => [22, 187]]]);

        $this->region !== '' && $query->andWhere(['s.region' => $this->region]);
        $this->account_manager != '' && $query->andWhere(['cc.account_manager' => $this->account_manager]);
        if ($this->is_b2c !== '') {
            $b2cWhere = ['price_level' => ClientAccount::PRICE_LEVEL_B2C];
            $query->andWhere($this->is_b2c ? $b2cWhere : ['not', $b2cWhere]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    /*
        private function getSubscribers()
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
    */
    private function getSubscribers()
    {
        $subscribers = \Yii::$app->dbPg->createCommand($q = 'select * from sorm_itgrad.subscribers_v1 where /* (not is_active and legal_type_id = :legal_type_id) or */ id = \'2511220\' limit 3 ', [/*':legal_type_id' => $this->getLegalTypeId()*/])->queryAll();

        echo $this->getLegalTypeId();

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
        echo $qq->createCommand()->rawSql;
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
            return $this->isB2c ? $isSet : !$isSet;
        });

        return $subscribers;
    }

    private function getLegalTypeId()
    {
        return $this->legalMap[$this->type] ?? 1;
    }
}
