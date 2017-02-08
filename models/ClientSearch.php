<?php

namespace app\models;

use app\classes\uu\model\AccountTariff;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class ClientSearch extends ClientAccount
{

    public $manager, $account_manager, $email, $voip, $ip, $domain, $address, $adsl;

    protected $companyName, $inn, $contractNo;

    public function rules()
    {
        return [
            [['id', 'regionId'], 'integer'],
            [
                [
                    'companyName',
                    'inn',
                    'email',
                    'voip',
                    'contractNo',
                    'ip',
                    'domain',
                    'address',
                    'adsl',
                    'account_manager',
                    'manager'
                ],
                'string'
            ],
        ];
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() +
        [
            'id' => '#',
            'companyName' => 'Название компании',
            'inn' => 'ИНН',
            'managerName' => 'Менеджер',
            'channelName' => 'Канал продаж',
            'contractNo' => '№ договора',
            'status' => 'Статус',
            'lastComment' => 'Комментарий',
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

    public function getLastComment()
    {
        $lastComment =
            $this->contract->getComments()
                ->andWhere(['is_publish' => 1])
                ->orderBy('ts desc')
                ->limit(1)
                ->one();
        return isset($lastComment) ? $lastComment->comment : '';
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = parent::find();
        $query->alias('client');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        $this->setAttributes($params);

        $query
            ->innerJoin(['contract' => ClientContract::tableName()], 'contract.id = client.contract_id')
            ->innerJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
            ->innerJoin(['super_client' => ClientSuper::tableName()], 'super_client.id = contragent.super_id');

        $query
            ->orFilterWhere(['client.id' => $this->id])
            ->orFilterWhere(['contract.manager' => $this->manager])
            ->orFilterWhere(['contract.account_manager' => $this->account_manager])
            ->orFilterWhere(['LIKE', 'contragent.name_full', $this->companyName])
            ->orFilterWhere(['LIKE', 'contragent.name', $this->companyName])
            ->orFilterWhere(['LIKE', 'super_client.name', $this->companyName])
            ->orFilterWhere(['LIKE', 'inn', $this->inn])
            ->orFilterWhere(['LIKE', 'address_connect', $this->address]);

        if ($this->contractNo) {
            $query->orFilterWhere(['contract.number' => $this->contractNo]);
            if (!$dataProvider->getTotalCount()) {
                $query->orFilterWhere(['LIKE', 'contract.number', $this->contractNo]);
            }
        }

        if ($this->email) {
            $query->leftJoin(['contact' => ClientContact::tableName()], 'contact.client_id = client.id');
            $query
                ->andFilterWhere(['LIKE', 'contact.data', $this->email])
                ->andFilterWhere(['contact.type' => 'email']);
        }

        if ($this->voip) {
            $uuQuery = clone $query;

            $query->leftJoin(['base_voip' => UsageVoip::tableName()], 'base_voip.client = client.client');
            $query->andFilterWhere(['LIKE', 'base_voip.e164', $this->voip]);

            $uuQuery->leftJoin(['uu_voip' => AccountTariff::tableName()], 'uu_voip.client_account_id = client.id');
            $uuQuery->andFilterWhere(['LIKE', 'uu_voip.voip_number', $this->voip]);

            $query->union($uuQuery);
        }

        if ($this->ip) {
            $query
                ->leftJoin(['ip_ports' => UsageIpPorts::tableName()], 'ip_ports.client = client.client')
                ->leftJoin(['ip_routes' => UsageIpRoutes::tableName()], 'ip_routes.port_id = ip_ports.id');

            $query->andFilterWhere([
                'OR',
                [
                    'AND',
                    (new Expression('INET_ATON("' . $this->ip . '") >= INET_ATON(SUBSTRING_INDEX(ip_routes.net,"/",1))')),
                    (new Expression('INET_ATON("' . $this->ip . '") <  INET_ATON(SUBSTRING_INDEX(ip_routes.net,"/",1))' .
                        '+POW(2,32-SUBSTRING_INDEX(ip_routes.net,"/",-1))'))
                ],
                ['LIKE', 'ip_routes.net', $this->ip]
            ]);
        }

        if ($this->domain) {
            $query->leftJoin(['domain' => Domain::tableName()], 'domain.client = client.client');
            $query->andFilterWhere(['LIKE', 'domain.domain', $this->domain]);
        }

        if ($this->adsl) {
            $query
                ->leftJoin(['ip_ports' => UsageIpPorts::tableName()], 'ip_ports.client = client.client')
                ->leftJoin(['tech_port' => TechPort::tableName()], 'ip_ports.port_id = tech_port.id');

            $query->andFilterWhere(['LIKE', 'tech_port.node', $this->adsl]);
        }

        return $dataProvider;
    }
}
