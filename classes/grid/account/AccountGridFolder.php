<?php
namespace app\classes\grid\account;

use app\classes\grid\account\telecom\maintenance\AutoBlockFolder;
use app\helpers\SetFieldTypeHelper;
use app\models\ClientAccount;
use app\models\BusinessProcessStatus;
use app\models\ClientContact;
use app\models\ClientContract;
use app\models\ContractType;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
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
    public $service;
    public $regionId;
    public $sale_channel;
    public $financial_type;
    public $contract_type;
    public $federal_district;

    public function getName()
    {
        return '';
    }

    public function getColumns()
    {
        return [];
    }

    public function rules()
    {
        return [
            [['id', 'regionId', 'sale_channel', 'contract_type'], 'integer'],
            [['companyName', 'createdDate', 'account_manager', 'manager', 'bill_date', 'currency',
                'service', 'block_date', 'financial_type', 'federal_district'], 'string'],
        ];
    }


    public function attributeLabels()
    {
        return (new ClientAccount())->attributeLabels() +
        [
            'id' => 'ID',
            'company' => 'Компания',
            'created' => 'Заведен',
            'inn' => 'ИНН',
            'managerName' => 'Менеджер',
            'channelName' => 'Канал продаж',
            'contractNo' => '№ договора',
            'status' => '#',
            'lastComment' => 'Комментарий',
            'service' => 'Услуга',
            'bill_date' => 'Дата платежа',
            'abon' => 'Абон.(пред.)',
            'over' => 'Прев.(пред.)',
            'total' => 'Всего',
            'abon1' => 'Абон.(тек.)',
            'over1' => 'Прев.(тек.)',
            'abondiff' => 'Абон.(diff)',
            'overdiff' => 'Прев.(diff)',
            'block_date' => 'Дата блокировки',
            'federal_district' => 'ФО',
            'contract_type' => 'Тип договора',
            'financial_type' => 'Финансовый тип договора',
        ];
    }

    public static function create(AccountGrid $grid)
    {
        return new static($grid);
    }

    public function __construct($grid = null)
    {
        if($grid && $grid instanceof AccountGrid) {
            $this->grid = $grid;
        }
        parent::__construct();
    }

    public function getId()
    {
        return md5(get_called_class());
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
            'mu.name as manager_name',
            'amu.name as account_manager_name',
            'c.support',
            'c.telemarketing',
            'c.sale_channel',
            'sh.name as sale_channel_name',
            'DATE(c.created) AS created',
            'c.currency',
            'c.region',
            'reg.name as region_name',
            'cr.federal_district',
            'ct.name as contract_type',
            'cr.financial_type',
        ]);

        $query->join('INNER JOIN', 'client_contract cr', 'c.contract_id = cr.id');
        $query->join('INNER JOIN', 'client_contragent cg', 'cr.contragent_id = cg.id');
        $query->join('LEFT JOIN', 'client_contract_type ct', 'ct.id = cr.contract_type_id');
        $query->join('LEFT JOIN', 'user_users amu', 'amu.user = cr.account_manager');
        $query->join('LEFT JOIN', 'user_users mu', 'mu.user = cr.manager');
        $query->join('LEFT JOIN', 'sale_channels sh', 'sh.id = c.sale_channel');
        $query->join('LEFT JOIN', 'regions reg', 'reg.id = c.region');
    }

    public function queryOrderBy()
    {
        return ['created' => SORT_DESC];
    }

    public function spawnDataProvider()
    {
        $query = new Query();

        $this->queryParams($query);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $this->queryOrderBy(),
                'attributes' => $this->getColumns()
            ]
        ]);

        $query->andFilterWhere(['c.id' => $this->id]);
        $query->andFilterWhere(['or', ['cg.name' => $this->companyName],['cg.name_full' => $this->companyName]]);
        $query->andFilterWhere(['cr.account_manager' => $this->account_manager]);
        $query->andFilterWhere(['cr.manager' => $this->manager]);
        $query->andFilterWhere(['c.sale_channel' => $this->sale_channel]);
        $query->andFilterWhere(['l.service' => $this->service]);
        $query->andFilterWhere(['c.region' => $this->regionId]);

        $query->andFilterWhere(['cr.financial_type' => $this->financial_type]);
        if($this->federal_district)
            $query->andWhere(SetFieldTypeHelper::generateCondition(new ClientContract(), 'federal_district', $this->federal_district));
        $query->andFilterWhere(['cr.contract_type_id' => $this->contract_type]);

        if ($this->currency) {
            $query->andWhere(['c.currency' => $this->currency]);
        }

        if ($this->bill_date && !empty($this->bill_date)) {
            $billDates = preg_split('/[\s+]\-[\s+]/', $this->bill_date);
            $query->andWhere(['between', 'b.bill_date', $billDates[0], $billDates[1]]);
        }

        if ($this->createdDate && !empty($this->createdDate)) {
            $createdDates = preg_split('/[\s+]\-[\s+]/', $this->createdDate);
            $query->andWhere(['between', 'c.created', $createdDates[0], $createdDates[1]]);
        }

        if (isset($this->block_date) && !empty($this->block_date)) {
            $blockDates = preg_split('/[\s+]\-[\s+]/', $this->block_date);
            $query->andWhere(['between', 'ab.block_date', $blockDates[0], $blockDates[1]]);
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

    public function getPreparedColumns()
    {
        $columns = [];
        foreach ($this->getColumns() as $column) {
            $columns[$column] = $this->getDefaultColumns()[$column];
            $columns[$column]['label'] = $this->getAttributeLabel($column);

            $callback =
                !is_array($columns[$column]['filter'])
                    ? $columns[$column]['filter']
                    : array_pop($columns[$column]['filter']);

            if ($callback instanceof \Closure)
                $columns[$column]['filter'] = $callback();

        }

        return $columns;
    }

    private function getDefaultColumns()
    {
        return [
            'status' => [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<span class="btn btn-grid" style="background:' . ClientAccount::$statuses[$data['status']]['color'] . '" title="' . ClientAccount::$statuses[$data['status']]['name'] . '">&nbsp;</span>';
                },
                'filterType' => \kartik\grid\GridView::FILTER_COLOR
            ],
            'id' => [
                'attribute' => 'id',
                'filter' => function(){
                    return '<input name="id" class="form-control" value="'.\Yii::$app->request->get('id').'" style="width:50px;" />';
                },
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data['id'] . '">' .$data['id'] . '</a>';
                }
            ],
            'company' => [
                'attribute' => 'company',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data['id'] . '">' . $data['company'] . '</a>';
                },
                'filter' => function() {
                    return '<input name="companyName"
                        id="searchByCompany" value="' . \Yii::$app->request->get('companyName') . '"
                        class="form-control" style="min-width:150px" />';
                },
            ],
            'created' => [
                'attribute' => 'created',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['created'];
                },
                'filter' => function () {
                    return \kartik\daterange\DateRangePicker::widget([
                        'name' => 'createdDate',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => \Yii::$app->request->get('created'),
                        'pluginOptions' => [
                            'format' => 'YYYY-MM-DD',
                        ],
                        'containerOptions' => [
                            'style' => 'width:50px;',
                            'class' => 'drp-container input-group',
                        ]
                    ]);
                }
            ],
            'block_date' => [
                'attribute' => 'block_date',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['block_date'];
                },
                'filter' => function () {
                    return \kartik\daterange\DateRangePicker::widget([
                        'name' => 'block_date',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => \Yii::$app->request->get('block_date'),
                        'pluginOptions' => [
                            'format' => 'YYYY-MM-DD',
                        ],
                        'containerOptions' => [
                            'style' => 'width:50px;',
                            'class' => 'drp-container input-group',
                        ]
                    ]);
                }
            ],
            'service' => [
                'attribute' => 'service',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['service'];
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
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-', 'style' => 'max-width:50px;',]
                    );
                },
            ],
            'abon' => [
                'attribute' => 'abon',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['abon'];
                }
            ],
            'over' => [
                'attribute' => 'over',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['over'];
                }
            ],
            'total' => [
                'attribute' => 'total',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['total'];
                }
            ],
            'abon1' => [
                'attribute' => 'abon1',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['abon1'];
                }
            ],
            'over1' => [
                'attribute' => 'over1',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['over1'];
                }
            ],
            'abondiff' => [
                'attribute' => 'abondiff',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['abondiff'];
                }
            ],
            'overdiff' => [
                'attribute' => 'overdiff',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['overdiff'];
                }
            ],
            'bill_date' => [
                'attribute' => 'bill_date',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['bill_date'];
                },
                'filter' => function () {
                    return \kartik\daterange\DateRangePicker::widget([
                        'name' => 'bill_date',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => \Yii::$app->request->get('bill_date'),
                        'pluginOptions' => [
                            'format' => 'YYYY-MM-DD',
                        ],
                        'containerOptions' => [
                            'style' => 'width:50px;',
                            'class' => 'drp-container input-group',
                        ]
                    ]);
                }
            ],
            'manager' => [
                'attribute' => 'manager',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['manager_name'];
                },
                'filter' => function () {
                    return \kartik\widgets\Select2::widget([
                        'name' => 'manager',
                        'data' => \app\models\User::getManagerList(),
                        'value' => \Yii::$app->request->get('manager'),
                        'options' => [
                            'placeholder' => 'Начните вводить фамилию',
                            'style' => 'width:100px;',
                        ],
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
                    return $data['account_manager_name'];
                },
                'filter' => function () {
                    return \kartik\widgets\Select2::widget([
                        'name' => 'account_manager',
                        'value' => \Yii::$app->request->get('account_manager'),
                        'data' => \app\models\User::getAccountManagerList(),
                        'options' => [
                            'placeholder' => 'Начните вводить фамилию',
                            'style' => 'width:100px;',
                        ],
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
                    return $data['currency'];
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'currency',
                        \Yii::$app->request->get('currency'),
                        \app\models\Currency::map(),
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-', 'style' => 'max-width:50px;']
                    );
                },
            ],
            'sale_channel' => [
                'attribute' => 'sale_channel',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/sale-channel/edit?id=' . $data['sale_channel'] . '">' . $data['sale_channel_name'] . '</a>';
                },
                'filter' => function () {
                    return \kartik\widgets\Select2::widget([
                        'name' => 'account_manager',
                        'data' => \app\models\SaleChannel::getList(),
                        'value' => \Yii::$app->request->get('sale_channel'),
                        'options' => ['placeholder' => 'Начните вводить название', 'style' => 'width:100px;',],
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
                    return $data['region_name'];
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'regionId',
                        \Yii::$app->request->get('regionId'),
                        \app\models\Region::getList(),
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-', 'style' => 'max-width:50px;']
                    );
                },
            ],
            'federal_district' => [
                'attribute' => 'federal_district',
                'format' => 'raw',
                'value' => function ($data) {
                    $arr = SetFieldTypeHelper::parseValue($data['federal_district']);
                    array_walk($arr, function(&$item){
                        $item = ClientContract::$districts[$item];
                    });
                    return implode('<br>', $arr);
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'federal_district',
                        \Yii::$app->request->get('federal_district'),
                        ClientContract::$districts,
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-', 'style' => 'width:50px;']
                    );
                },
            ],
            'contract_type' => [
                'attribute' => 'contract_type',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['contract_type'];
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'contract_type',
                        \Yii::$app->request->get('contract_type'),
                        ContractType::getList($this->grid->getBusinessProcessId()),
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-', 'style' => 'max-width:100px;']
                    );
                },
            ],
            'financial_type' => [
                'attribute' => 'financial_type',
                'format' => 'raw',
                'value' => function ($data) {
                    return ClientContract::$financialTypes[$data['financial_type']];
                },
                'filter' => function () {
                    return \yii\helpers\Html::dropDownList(
                        'financial_type',
                        \Yii::$app->request->get('financial_type'),
                        ClientContract::$financialTypes,
                        ['class' => 'form-control', 'style' => 'max-width:100px;']
                    );
                },
            ],
        ];
    }

}