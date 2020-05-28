<?php

namespace app\models\filter;

use app\models\ClientContract;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\User;
use yii\data\ActiveDataProvider;

class UsageVoipFilter extends UsageVoip
{
    public $id = '';
    public $account_manager = '';
    public $is_active_client_account = '';

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['account_manager', 'string'],
            ['is_active_client_account', 'integer'],
        ]);
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $usageVoipTableName = UsageVoip::tableName();

        $query = UsageVoip::find()
            ->joinWith('regionName')
            ->with('regionName')
        ;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(["{$usageVoipTableName}.id" => $this->id]);

        if ($this->account_manager) {
            $query->joinWith('clientAccount.clientContractModel');
            $query->andWhere([ClientContract::tableName() . '.account_manager' => $this->account_manager]);
        }

        if ($this->is_active_client_account) {
            // if not included
            if (!$this->account_manager) {
                $query->joinWith('clientAccount');
            }
            $query->andWhere([ClientAccount::tableName().'.is_active' => 1]);
        }

        return $dataProvider;
    }
}