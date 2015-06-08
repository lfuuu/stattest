<?php

namespace app\models;

use app\classes\grid\FilterDataProvider;

class ClientSearch extends ClientAccount
{
    public $channel, $manager, $email, $voip;

    protected $companyName, $inn;

    public function rules()
    {
        return [
            [['id', 'channel', 'manager'], 'integer'],
            [['companyName', 'inn', 'email', 'voip'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '# ЛС',
            'companyName' => 'Название компании',
            'inn' => 'ИНН',
            'managerName' => 'Менеджер',
            'channelName' => 'Канал продаж',
        ];
    }

    public function getCompanyName()
    {
        return $this->contract->contragent->name_full;
    }

    public function getInn()
    {
        return $this->contract->contragent->inn;
    }

    public function getManagerName()
    {
        return $this->contract->managerName;
    }

    public function getChannelName()
    {
        return $this->sale_channel ? SaleChannel::getList()[$this->sale_channel] : '';
    }

    public function search($params)
    {
        $query = parent::find();

        $dataProvider = new FilterDataProvider([
            'query' => $query,
        ]);

        $this->setAttributes($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->innerJoin(ClientContract::tableName(), ClientContract::tableName() . '.id=' . ClientAccount::tableName() . '.contract_id');
        $query->innerJoin(ClientContragent::tableName(), ClientContragent::tableName() . '.id=' . ClientAccount::tableName() . '.contragent_id');

        $query->orFilterWhere([ClientAccount::tableName() . '.id' => $this->id]);
        $query->orFilterWhere(['like', 'name_full', $this->companyName]);
        $query->orFilterWhere(['like', 'inn', $this->inn]);

        if($this->email){
            $query->leftJoin(ClientContact::tableName(), ClientContact::tableName() . '.client_id=' . ClientAccount::tableName() . '.id');
            $query->andFilterWhere(['like', ClientContact::tableName().'.data', $this->email]);
            $query->andFilterWhere([ClientContact::tableName().'.type' => 'email']);
        }

        if($this->voip){
            $query->leftJoin(UsageVoip::tableName(), UsageVoip::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->andFilterWhere(['like', UsageVoip::tableName().'.e164', $this->voip]);        }

        return $dataProvider;
    }
}