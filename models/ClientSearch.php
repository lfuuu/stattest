<?php

namespace app\models;

use app\classes\grid\FilterDataProvider;

class ClientSearch extends ClientAccount
{
    public $channel, $manager, $email, $voip, $ip, $domain, $address, $adsl;

    protected $companyName, $inn, $contractNo;

    public function rules()
    {
        return [
            [['id', 'channel', 'manager'], 'integer'],
            [['companyName', 'inn', 'email', 'voip', 'contractNo', 'ip' ,'domain', 'address', 'adsl'], 'string'],
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
            'contractNo' => '№ договора',
            'status' => 'Статус',
        ];
    }

    public function getContractNo()
    {
        return $this->contract->number;
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
        $query->orFilterWhere(['like', 'address_connect', $this->address]);

        if($this->contractNo) {
            $query->orFilterWhere(['number' => $this->contractNo]);
            if(!$dataProvider->getTotalCount())
                $query->orFilterWhere(['like', 'number', $this->contractNo]);
        }

        if($this->email){
            $query->leftJoin(ClientContact::tableName(), ClientContact::tableName() . '.client_id=' . ClientAccount::tableName() . '.id');
            $query->andFilterWhere(['like', ClientContact::tableName().'.data', $this->email]);
            $query->andFilterWhere([ClientContact::tableName().'.type' => 'email']);
        }

        if($this->voip){
            $query->leftJoin(UsageVoip::tableName(), UsageVoip::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->andFilterWhere(['like', UsageVoip::tableName().'.e164', $this->voip]);
        }

        if($this->ip){
            $query->leftJoin(UsageIpPorts::tableName(), UsageIpPorts::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->leftJoin(UsageIpRoutes::tableName(), UsageIpRoutes::tableName() . '.port_id=' . UsageIpPorts::tableName() . '.id');
            $query->andFilterWhere(['like', UsageIpRoutes::tableName().'.net', $this->ip]);
        }

        if($this->domain){
            $query->leftJoin(Domain::tableName(), Domain::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->andFilterWhere(['like', Domain::tableName().'.domain', $this->domain]);
        }

        if($this->adsl){
            $query->leftJoin(UsageIpPorts::tableName(), UsageIpPorts::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->leftJoin(TechPort::tableName(), UsageIpPorts::tableName() . '.port_id=' . TechPort::tableName() . '.id');
            $query->andFilterWhere(['like', TechPort::tableName().'.node', $this->adsl]);
        }

        return $dataProvider;
    }
}