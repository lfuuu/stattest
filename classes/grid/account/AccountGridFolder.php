<?php
namespace app\classes\grid\account;

use app\classes\grid\account\telecom\maintenance\AutoBlockFolder;
use app\models\ClientAccount;
use app\models\ClientBPStatuses;
use app\models\ClientContact;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ContractType;
use app\models\Domain;
use app\models\TechPort;
use app\models\UsageIpPorts;
use app\models\UsageIpRoutes;
use app\models\UsageVoip;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;


abstract class AccountGridFolder extends Model
{
    /** @var AccountGrid */
    public $grid;

    public $id;
    public $companyName;
    public $createdDate;
    public $currency;
    public $manager;
    public $account_manager;
    public $bill_date;
    public $email;
    public $voip;
    public $ip;
    public $domain;
    public $address;
    public $adsl;
    public $service;
    public $abon;
    public $over;
    public $total;
    public $abon1;
    public $over1;
    public $abondiff;
    public $overdiff;
    public $date_from;
    public $date_to;
    public $sum;
    public $regionId;
    public $inn;
    public $contractNo;
    public $sale_channel;

    public function getName()
    {
        return '';
    }

    public function getColumns()
    {
        return [];
    }

    public function hasFilters()
    {
        return true;
    }


    public function rules()
    {
        return [
            [['id', 'regionId', 'sale_channel'], 'integer'],
            [['companyName', 'inn', 'email', 'voip', 'contractNo', 'ip', 'domain', 'address', 'adsl',
                'account_manager', 'manager', 'bill_date', 'currency', 'service'], 'string'],
        ];
    }


    public function attributeLabels()
    {
        return parent::attributeLabels() +
        [
            'id' => '# ЛС',
            'companyName' => 'Название компании',
            'inn' => 'ИНН',
            'managerName' => 'Менеджер',
            'channelName' => 'Канал продаж',
            'contractNo' => '№ договора',
            'status' => 'Статус',
            'lastComment' => 'Комментарий',
        ];
    }

    public static function create(AccountGrid $grid)
    {
        return new static($grid);
    }

    public function __construct(AccountGrid $grid)
    {
        $this->grid = $grid;
        parent::__construct();
    }

    public function getId()
    {
        return md5(get_called_class());
    }

    public function search($params)
    {
        $query = ClientAccount::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        $this->setAttributes($params);

        $query->innerJoin(ClientContract::tableName(), ClientContract::tableName() . '.id=' . ClientAccount::tableName() . '.contract_id');
        $query->innerJoin(ClientContragent::tableName(), ClientContragent::tableName() . '.id=' . ClientContract::tableName() . '.contragent_id');

        $query->orFilterWhere([ClientAccount::tableName() . '.id' => $this->id]);
        $query->orFilterWhere([ClientContract::tableName() . '.manager' => $this->manager]);
        $query->orFilterWhere([ClientContract::tableName() . '.account_manager' => $this->account_manager]);
        $query->orFilterWhere(['like', 'name_full', $this->companyName]);
        $query->orFilterWhere(['like', 'name', $this->companyName]);
        $query->orFilterWhere(['like', 'inn', $this->inn]);
        $query->orFilterWhere(['like', 'address_connect', $this->address]);


        if ($this->contractNo) {
            $query->orFilterWhere(['number' => $this->contractNo]);
            if (!$dataProvider->getTotalCount())
                $query->orFilterWhere(['like', 'number', $this->contractNo]);
        }

        if ($this->email) {
            $query->leftJoin(ClientContact::tableName(), ClientContact::tableName() . '.client_id=' . ClientAccount::tableName() . '.id');
            $query->andFilterWhere(['like', ClientContact::tableName() . '.data', $this->email]);
            $query->andFilterWhere([ClientContact::tableName() . '.type' => 'email']);
        }

        if ($this->voip) {
            $query->leftJoin(UsageVoip::tableName(), UsageVoip::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->andFilterWhere(['like', UsageVoip::tableName() . '.e164', $this->voip]);
        }

        if ($this->ip) {
            $query->leftJoin(UsageIpPorts::tableName(), UsageIpPorts::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->leftJoin(UsageIpRoutes::tableName(), UsageIpRoutes::tableName() . '.port_id=' . UsageIpPorts::tableName() . '.id');
            $query->andFilterWhere(['like', UsageIpRoutes::tableName() . '.net', $this->ip]);
        }

        if ($this->domain) {
            $query->leftJoin(Domain::tableName(), Domain::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->andFilterWhere(['like', Domain::tableName() . '.domain', $this->domain]);
        }

        if ($this->adsl) {
            $query->leftJoin(UsageIpPorts::tableName(), UsageIpPorts::tableName() . '.client=' . ClientAccount::tableName() . '.client');
            $query->leftJoin(TechPort::tableName(), UsageIpPorts::tableName() . '.port_id=' . TechPort::tableName() . '.id');
            $query->andFilterWhere(['like', TechPort::tableName() . '.node', $this->adsl]);
        }

        return $dataProvider;
    }

    public function queryParams(Query $query)
    {
        $query->from(['clients c']);
        $query->select([
            'c.status',
            'c.id',
            'c.contract_id',
            'cg.name AS company',
            'cr.manager',
            'cr.account_manager',
            'c.support',
            'c.telemarketing',
            'c.sale_channel',
            'DATE(c.created) AS created',
            'c.currency',
            'c.region',
        ]);

        $query->join('INNER JOIN', 'client_contract cr', 'c.contract_id = cr.id');
        $query->join('INNER JOIN', 'client_contragent cg', 'cr.contragent_id = cg.id');

        $query->orderBy(['c.created' => SORT_DESC]);
    }

    public function spawnDataProvider()
    {
        $query = new Query();

        $this->queryParams($query);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => $orderBy
            ]
        ]);

        $query->andFilterWhere(['c.id' => $this->id]);
        $query->andFilterWhere(['or', ['cg.name' => $this->companyName],['cg.name_full' => $this->companyName]]);
        $query->andFilterWhere(['cr.account_manager' => $this->account_manager]);
        $query->andFilterWhere(['cr.manager' => $this->manager]);
        $query->andFilterWhere(['c.sale_channel' => $this->sale_channel]);
        $query->andFilterWhere(['l.service' => $this->service]);
        $query->andFilterWhere(['c.region' => $this->regionId]);

        if ($this->currency)
            $query->andFilterWhere(['c.currency' => $this->currency]);

        if ($this->bill_date) {
            $billDates = explode('+-+', $this->bill_date);
            $query->andFilterWhere(['between', 'b.bill_date', $billDates[0], $billDates[1]]);
        }

        if ($this->createdDate) {
            $createdDates = explode('+-+', $this->createdDate);
            $query->andFilterWhere(['between', 'c.created', $createdDates[0], $createdDates[1]]);
        }

        if ($this->grid == ClientBPStatuses::FOLDER_TELECOM_AUTOBLOCK) {
            $pg_query = new Query();

            $pg_query->select('client_id')->from('billing.locks')->where('voip_auto_disabled=true');

            $ids = $pg_query->column(\Yii::$app->dbPg);
            if (!empty($ids)) {
                $query->andFilterWhere(['in', 'c.id', $ids]);
            }
        }

        if ($query->params) {
            $params = [];
            foreach ($query->params as $paramKey => $paramValue)
                $params[':' . $paramKey] = $this->$paramKey ? $this->$paramKey : $paramValue;
            $query->addParams($params);
        }

        return $dataProvider;
    }

    public function getCount()
    {
        $query = new Query();

        $this->queryParams($query);
        $query->orderBy = null;

        if ($this instanceof AutoBlockFolder) {
            $pg_query = new Query();
            $pg_query->select('client_id')->from('billing.locks')->where('voip_auto_disabled=true');

            $ids = $pg_query->column(\Yii::$app->dbPg);
            if (!empty($ids)) {
                $query->andFilterWhere(['in', 'c.id', $ids]);
            }
        }
        return $query->count();
    }

    private function getPreparedColumns()
    {
        foreach ($this->getColumns() as $k => $column) {
            $columnName = is_string($column) ? $column : $k;
            $columnParams = is_array($column) ? $column : [];
            $columns[$columnName] =
                isset($this->grids['defaultColumnsParams'][$columnName])
                    ? array_merge_recursive($this->grids['defaultColumnsParams'][$columnName], $columnParams)
                    : $columnParams;

            $columns[$columnName]['label'] = $this->spawnColumnLabel($column, $columnName);

            if ($genFilters && $columns[$columnName]['filter']) {
                $callback =
                    !is_array($columns[$columnName]['filter'])
                        ? $columns[$columnName]['filter']
                        : array_pop($columns[$columnName]['filter']);

                if ($callback  instanceof \Closure)
                    $columns[$columnName]['filter'] = $callback();
            }
        }
        //var_dump($columns); die;
        $gridSettings['columns'] = $columns;
    }

    private function getDefaultFilters()
    {
        return [
            'status' => [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<span class="btn btn-grid" style="background:' . $data->statusColor . '" title="' . $data->statusName . '">&nbsp;</span>';
                },
                'filterType' => \kartik\grid\GridView::FILTER_COLOR
            ],
            'id' => [
                'attribute' => 'id',
                'filter' => function(){
                    return '<input name="id" class="form-control" value="'.\Yii::$app->request->get('companyName').'" />';
                },
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data->id . '">' . $data->id . '</a>';
                }
            ],
            'company' => [
                'attribute' => 'company',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data->id . '">' . $data->contract->contragent->name . '</a>';
                },
                'filter' => function() {
                    return '<input name="companyName" id="searchByCompany" value="' . \Yii::$app->request->get('companyName') . '" class="form-control" />';
                },
            ],
            'created' => [
                'attribute' => 'created',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->created;
                },
                'filter' => function () {
                    return \kartik\daterange\DateRangePicker::widget([
                        'name' => 'createdDate',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => \Yii::$app->request->get('created'),
                        'containerOptions' => [
                            'style' => 'width:300px;',
                            'class' => 'drp-container input-group',
                        ]
                    ]);
                }
            ],/*
    'block_date' => [
        'attribute' => 'block_date',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->block_date;
        }
    ],*/
            'service' => [
                'attribute' => 'service',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->service;
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'service',
                        \Yii::$app->request->get('service'),
                        [
                            'emails' => 'emails',
                            'tech_cpe' => 'tech_cpe',
                            'usage_extra' => 'usage_extra',
                            'usage_ip_ports' => 'usage_ip_ports',
                            'usage_sms' => 'usage_sms',
                            'usage_virtpbx' => 'usage_virtpbx',
                            'usage_voip' => 'usage_voip',
                            'usage_welltime' => 'usage_welltime',
                        ],
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-']
                    );
                },
            ],
            'abon' => [
                'attribute' => 'abon',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->abon;
                }
            ],
            'over' => [
                'attribute' => 'over',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->over;
                }
            ],
            'total' => [
                'attribute' => 'total',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->total;
                }
            ],
            'abon1' => [
                'attribute' => 'abon1',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->abon1;
                }
            ],
            'over1' => [
                'attribute' => 'over1',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->over1;
                }
            ],
            'abondiff' => [
                'attribute' => 'abondiff',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->abondiff;
                }
            ],
            'overdiff' => [
                'attribute' => 'overdiff',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->overdiff;
                }
            ],
            'bill_date' => [
                'attribute' => 'bill_date',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->bill_date;
                },
                'filter' => function () {
                    return \kartik\daterange\DateRangePicker::widget([
                        'name' => 'bill_date',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => \Yii::$app->request->get('bill_date'),
                        'containerOptions' => [
                            'style' => 'width:300px;',
                            'class' => 'drp-container input-group',
                        ]
                    ]);
                }
            ],
            'manager' => [
                'attribute' => 'manager',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="index.php?module=users&m=user&id=' . $data->userManager->user . '">' . $data->userManager->name . '</a>';
                },
                'filter' => function () {
                    return \kartik\widgets\Select2::widget([
                        'name' => 'manager',
                        'data' => \app\models\User::getManagerList(),
                        'value' => \Yii::$app->request->get('manager'),
                        'options' => ['placeholder' => 'Начните вводить фамилию'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);
                },
            ],
            'account_manager' => [
                'attribute' => 'account_manager',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="index.php?module=users&m=user&id=' . $data->userAccountManager->user . '">' . $data->userAccountManager->name . '</a>';
                },
                'filter' => function () {
                    return \kartik\widgets\Select2::widget([
                        'name' => 'account_manager',
                        'value' => \Yii::$app->request->get('account_manager'),
                        'data' => \app\models\User::getAccountManagerList(),
                        'options' => ['placeholder' => 'Начните вводить фамилию'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);
                },
            ],
            'currency' => [
                'attribute' => 'currency',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->currency;
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'currency',
                        \Yii::$app->request->get('currency'),
                        \app\models\Currency::map(),
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-']
                    );
                },
            ],
            'sale_channel' => [
                'attribute' => 'sale_channel',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/sale-channel/edit?id=' . $data->sale_channel . '">' . $data->channelName . '</a>';
                },
                'filter' => function () {
                    return \kartik\widgets\Select2::widget([
                        'name' => 'account_manager',
                        'data' => \app\models\SaleChannel::getList(),
                        'value' => \Yii::$app->request->get('sale_channel'),
                        'options' => ['placeholder' => 'Начните вводить название'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);
                },
            ],
            'region' => [
                'attribute' => 'region',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->accountRegion->name;
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'regionId',
                        \Yii::$app->request->get('regionId'),
                        \app\models\Region::getList(),
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-']
                    );
                },
            ],
        ];
    }

}