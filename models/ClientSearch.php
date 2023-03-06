<?php

namespace app\models;

use app\classes\HttpClient;
use app\modules\uu\models\AccountTariff;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class ClientSearch extends ClientAccount
{

    public $manager, $account_manager, $email, $voip, $ip, $domain, $address, $adsl, $sip;

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
                    'manager',
                    'sip',
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
        $query->alias('client')
            ->distinct();

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


        if ($this->sip) {
            $result = (new HttpClient())
                ->createJsonRequest()
                ->setMethod('get')
                ->setUrl('https://vpbx.mcn.ru/phone/api/get_account_id_by_sip_name/')
                ->setData(['sip_name' => $this->sip])
                ->getResponseDataWithCheck();

            $this->id = $result && isset($result['result']) && $result['result'] ? $result['result'] : 0;
        }

        if ($this->inn) {
            $contragents = ClientContragent::find()->where(['inn' => $this->inn])->with('contractsActiveQuery.clientAccountModels')->limit(100)->all();

            $accountIds = [];
            $super = [];
            /** @var ClientContragent $contragent */
            foreach ($contragents as $contragent) {
                if (!isset($super[$contragent->super_id])) {
                    $super[$contragent->super_id] = $super[$contragent->super];
                }
                foreach ($contragent->contractsActiveQuery as $contract) {
                    foreach ($contract->clientAccountModels as $clientAccountModel) {
                        $accountIds[] = $clientAccountModel->id;
                    }
                }
            }

            if (!$accountIds) {
                $query->andFilterWhere(['super_client.id' => $super ? array_keys($super) : -1]);
            } else {
                $this->id = $accountIds;
            }
        }

        $query
            ->orFilterWhere(['client.id' => $this->id])
            ->orFilterWhere(['contract.manager' => $this->manager])
            ->orFilterWhere(['contract.account_manager' => $this->account_manager])
            ->orFilterWhere(['contract.number' => $this->contractNo])
            ->orFilterWhere(['LIKE', 'address_connect', $this->address]);

        if ($this->companyName) {
//            if (\Yii::$app->request->isAjax) {
                $query->orWhere(new Expression("match(contragent.name_full) against (:searchStr IN BOOLEAN MODE)", ['searchStr' => '*'.preg_replace('/\s+/', '*', $this->companyName).'*']));
//            } else {
//                $query->orFilterWhere(['LIKE', 'contragent.name_full', $this->companyName])
//                    ->orFilterWhere(['LIKE', 'contragent.name', $this->companyName])
//                    ->orFilterWhere(['LIKE', 'super_client.name', $this->companyName]);
//            }
            $dataProvider->setSort(false);
        }

        if ($this->email) {
            $query->leftJoin(['contact' => ClientContact::tableName()], 'contact.client_id = client.id');
            $query->andFilterWhere(['LIKE', 'contact.data', $this->email]);
        }

        if ($this->voip) {

            // search direct number
            $isDirect = AccountTariff::find()->where(['voip_number' => $this->voip])->exists();
            if (!$isDirect) {
                $isDirect = UsageVoip::find()->where(['e164' => $this->voip])->exists();
            }

            if ($isDirect) {
                $queryNumbers = UsageVoip::find()
                    ->alias('uv')
                    ->where(['uv.e164' => $this->voip])
                    ->select([
                        'client_id' => 'c.id',
                        'actual_from',
                    ])
                    ->joinWith('clientAccount c', true, 'INNER JOIN')
                    ->union(
                        AccountTariff::find()
                            ->where(['voip_number' => $this->voip])
                            ->select([
                                'client_id' => 'client_account_id',
                                'actual_from' => 'insert_time',
                            ])
                    );
                $query->innerJoin(['a' => $queryNumbers], 'a.client_id = client.id');
            } else {
                $query->leftJoin(['base_voip' => UsageVoip::tableName()], 'base_voip.client = client.client');
                $query->leftJoin(['uu_voip' => AccountTariff::tableName()], 'uu_voip.client_account_id = client.id');

                $query->andFilterWhere(['OR',
                    ['LIKE', 'base_voip.e164', $this->voip],
                    ['LIKE', 'uu_voip.voip_number', $this->voip]
                ]);

                $query->orderBy(new Expression('coalesce(base_voip.actual_from, uu_voip.insert_time) desc'));
            }
        }

        if ($this->ip) {
            $query
                ->leftJoin(['ip_ports' => UsageIpPorts::tableName()], 'ip_ports.client = client.client')
                ->leftJoin(['ip_routes' => UsageIpRoutes::tableName()], 'ip_routes.port_id = ip_ports.id');

            $query
                ->andWhere(
                    [
                        'BETWEEN',
                        new Expression('INET_ATON("' . $this->ip . '")'),
                        new Expression('INET_ATON(SUBSTRING_INDEX(ip_routes.net, "/", 1))'),
                        new Expression('INET_ATON(SUBSTRING_INDEX(ip_routes.net, "/", 1)) | POW(2, 32-SUBSTRING_INDEX(ip_routes.net, "/", -1))-1')
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
