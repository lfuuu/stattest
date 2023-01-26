<?php

namespace app\classes\grid\account;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientBlockedComment;
use app\models\BusinessProcess;
use app\models\Currency;
use app\models\Good;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\SaleChannelOld;
use app\models\TariffExtra;
use app\models\usages\UsageFactory;
use app\models\User;
use kartik\daterange\DateRangePicker;
use kartik\grid\GridView;
use kartik\widgets\Select2;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Form;
use app\classes\Html;
use app\helpers\SetFieldTypeHelper;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ContractType;
use app\models\Organization;
use yii\helpers\Json;

abstract class AccountGridFolder extends Model
{
    const SERVICE_FILTER_EXTRA = 'extra';
    const SERVICE_FILTER_GOODS = 'goods';

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
    public $partner_clients_service;
    public $sale_channel;
    public $financial_type;
    public $contract_type;
    public $federal_district;
    public $contractNo;
    public $contract_created;
    public $legal_entity;

    private $_organizationList = [];

    public $_isGenericFolder = true;

    public $is_group_by_contract = true;

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'sale_channel', 'contract_type', 'legal_entity', 'service_type', 'is_payed'], 'integer'],
            [
                [
                    'companyName',
                    'createdDate',
                    'account_manager',
                    'manager',
                    'bill_date',
                    'currency',
                    'service',
                    'partner_clients_service',
                    'block_date',
                    'financial_type',
                    'federal_district',
                    'contractNo',
                    'contract_created'
                ],
                'string'
            ],
        ];
    }


    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(
            (new ClientAccount())->attributeLabels(),
            [
                'id' => 'ID',
                'company' => 'Контрагент',
                'created' => 'Заведен',
                'inn' => 'ИНН',
                'managerName' => 'Менеджер',
                'channelName' => 'Канал продаж',
                'sale_channel' => 'Откуда узнали о нас',
                'contractNo' => '№ договора',
                'contract_created' => 'Дата договора',
                'status' => '#',
                'lastComment' => 'Комментарий',
                'service' => 'Услуга',
                'partner_clients_service' => 'Услуга',
                'bill_date' => 'Дата счёта',
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
                'legal_entity' => 'Юр. лицо',
            ]);
    }

    /**
     * @param AccountGrid $grid
     * @return static
     */
    public static function create(AccountGrid $grid)
    {
        return new static($grid);
    }

    /**
     * AccountGridFolder constructor.
     *
     * @param null $grid
     */
    public function __construct($grid = null)
    {
        if ($grid && $grid instanceof AccountGrid) {
            $this->grid = $grid;
        }

        $this->_organizationList = Organization::dao()->getList();

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return md5(get_called_class());
    }

    /**
     * @param Query $query
     */
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
            'cr.number AS contractNo',
            'cr.organization_id',
            'mu.name as manager_name',
            'amu.name as account_manager_name',
            'c.support',
            'c.telemarketing',
            'c.sale_channel',
            'sh.name as sale_channel_name',
            'DATE(c.created) AS created',
            'c.currency',
            'cr.federal_district',
            'ct.name as contract_type',
            'cr.financial_type',
            'MAX(doc.contract_date) as contract_created'
        ]);

        $query->join('INNER JOIN', 'client_contract cr', 'c.contract_id = cr.id');
        $query->join('INNER JOIN', 'client_contragent cg', 'cr.contragent_id = cg.id');
        $query->join('LEFT JOIN', 'client_contract_type ct', 'ct.id = cr.contract_type_id');
        $query->join('LEFT JOIN', 'user_users amu', 'amu.user = cr.account_manager');
        $query->join('LEFT JOIN', 'user_users mu', 'mu.user = cr.manager');
        $query->join('LEFT JOIN', 'sale_channels_old sh', 'sh.id = c.sale_channel');
        $query->join('LEFT JOIN', 'client_document doc',
            'doc.id = (select max(id) from client_document where contract_id = cr.id and is_active = 1 and type ="contract")');
        $query->groupBy(($this->is_group_by_contract && $this->isGenericFolder() ? 'cr' : 'c') . '.id');
    }

    /**
     * @return array
     */
    public function queryOrderBy()
    {
        return ['id' => SORT_DESC];
    }

    /**
     * @return ActiveDataProvider
     */
    public function spawnDataProvider()
    {
        $query = new Query();

        $this->queryParams($query);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $this->queryOrderBy(),
                'attributes' => $this->getColumns()
            ],
            'pagination' => [
                'pageSize' => Form::PAGE_SIZE,
            ],
        ]);

        $query->andFilterWhere(['c.id' => $this->id]);
        $query->andFilterWhere(['or', ['cg.name' => $this->companyName], ['cg.name_full' => $this->companyName]]);
        $query->andFilterWhere(['cr.account_manager' => $this->account_manager]);
        $query->andFilterWhere(['cr.manager' => $this->manager]);
        $query->andFilterWhere(['cr.number' => $this->contractNo]);
        $query->andFilterWhere(['c.sale_channel' => $this->sale_channel]);
        // Для отчета "Выручка Welltime" добавить товары из 1С
        if ($this->grid->isCurrentReport(BusinessProcess::TELECOM_REPORTS)) {
            if ($this->hasServiceSignature(self::SERVICE_FILTER_GOODS)) {
                $item = explode('_', $this->service);
                $query->andFilterWhere([
                    'l.item_id' => $item ? array_pop($item) : null
                ]);
            } elseif ($this->hasServiceSignature(self::SERVICE_FILTER_EXTRA)) {
                $item = explode('_', $this->service);
                $query->andFilterWhere([
                    'l.id_service' => $item ? array_pop($item) : null,
                    'l.service' => UsageFactory::USAGE_EXTRA,
                ]);
            } else {
                $query->andFilterWhere(['l.service' => $this->service]);
            }
        } else {
            $query->andFilterWhere(['l.service' => $this->service]);
        }

        $query->andFilterWhere(['cr.financial_type' => $this->financial_type]);
        if ($this->federal_district) {
            $query->andWhere(SetFieldTypeHelper::generateCondition(new ClientContract(), 'federal_district',
                $this->federal_district));
        }

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
            $query->andWhere(['between', 'c.created', $createdDates[0] . ' 00:00:00', $createdDates[1] . ' 23:59:59']);
        }

        if ($this->contract_created && !empty($this->contract_created)) {
            $createdDates = preg_split('/[\s+]\-[\s+]/', $this->contract_created);
            $query->andWhere(['between', 'doc.contract_date', $createdDates[0], $createdDates[1]]);
        }

        if (isset($this->block_date) && !empty($this->block_date)) {
            $blockDates = preg_split('/[\s+]\-[\s+]/', $this->block_date);
            $query->andHaving(['between', 'block_date', $blockDates[0], $blockDates[1]]);
        }

        if (isset($this->legal_entity) && !empty($this->legal_entity)) {
            $query->andFilterWhere(['cr.organization_id' => $this->legal_entity]);
        }

        return $dataProvider;
    }

    /**
     * Получение кол-ва строк результата выборки
     *
     * @return int
     */
    public function getCount()
    {
        $query = new Query();
        $this->queryParams($query);
        $query->orderBy = null;
        return $query->count();
    }

    /**
     * @return array
     */
    public function getPreparedColumns()
    {
        $columns = [];
        foreach ($this->getColumns() as $column) {
            $columns[$column] = $this->getDefaultColumns()[$column];
            $columns[$column]['label'] = $this->getAttributeLabel($column);

            $callback = null;
            if (isset($columns[$column]['filter'])) {
                $callback = !is_array($columns[$column]['filter']) ? $columns[$column]['filter'] : array_pop($columns[$column]['filter']);
            }

            if ($callback instanceof \Closure) {
                $columns[$column]['filter'] = $callback();
            }
        }

        return $columns;
    }

    /**
     * Подзапрос получения даты блокировки
     *
     * @return Query
     */
    protected function getBlockDateQuery()
    {
        $now = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));

        $tz = new \DateTimeZone(Yii::$app->user->identity->timezone_name);
        $tzOffset = $tz->getOffset($now);

        return (new Query())
            ->select(new Expression('MAX(l.date) ' . ($tzOffset > 0 ? "+" : "-") . ' INTERVAL ' . abs($tzOffset) . ' SECOND'))
            ->from(['l' => ImportantEvents::tableName()])
            ->where('l.client_id = c.id')
            ->andWhere(['l.event' => ImportantEventsNames::ZERO_BALANCE])
            ->groupBy(['l.client_id']);
    }

    /**
     * @return array
     */
    protected function getDefaultColumns()
    {
        return [
            'status' => [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<span class="btn btn-grid" style="background:' . ClientAccount::$statuses[$data['status']]['color'] . '" title="' . ClientAccount::$statuses[$data['status']]['name'] . '">&nbsp;</span>';
                },
                'filterType' => GridView::FILTER_COLOR
            ],
            'id' => [
                'attribute' => 'id',
                'filter' => function () {
                    return '<input name="id" class="form-control" value="' . Yii::$app->request->get('id') . '" />';
                },
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data['id'] . '">' . $data['id'] . '</a>';
                },
                'width' => '120px',
            ],
            'company' => [
                'attribute' => 'company',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data['id'] . '">' . $data['company'] . '</a>';
                },
                'filter' => function () {
                    return '<input name="companyName"
                        id="searchByCompany" value="' . Yii::$app->request->get('companyName') . '"
                        class="form-control" style="min-width:150px" />';
                },
            ],
            'contractNo' => [
                'attribute' => 'contractNo',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/client/view?id=' . $data['id'] . '">' . $data['contractNo'] . '</a>';
                },
                'filter' => function () {
                    return '<input name="contractNo"
                        id="searchByContractNo" value="' . Yii::$app->request->get('contractNo') . '"
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
                    return DateRangePicker::widget([
                        'name' => 'createdDate',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => Yii::$app->request->get('created'),
                        'pluginOptions' => [
                            'locale' => [
                                'format' => 'YYYY-MM-DD',
                            ],
                        ],
                        'containerOptions' => [
                            'style' => 'width:50px; overflow: hidden;',
                            'class' => 'drp-container input-group',
                        ]
                    ]);
                }
            ],
            'contract_created' => [
                'attribute' => 'created',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['contract_created'];
                },
                'filter' => function () {
                    return DateRangePicker::widget([
                        'name' => 'contract_created',
                        'value' => Yii::$app->request->get('contract_created'),
                        'presetDropdown' => true,
                        'pluginOptions' => [
                            'locale' => [
                                'format' => 'YYYY-MM-DD',
                            ],
                        ],
                        'containerOptions' => [
                            'style' => 'overflow: hidden;',
                            'class' => 'drp-container input-group',
                        ],
                        'pluginEvents' => [
                            'cancel.daterangepicker' => 'function(e, picker) { picker.element.find("input").val("").trigger("change"); }',
                        ],
                        'options' => [
                            'style' => 'font-size: 10px;',
                        ],
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
                    return DateRangePicker::widget([
                        'name' => 'block_date',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => Yii::$app->request->get('block_date'),
                        'pluginOptions' => [
                            'locale' => [
                                'format' => 'YYYY-MM-DD',
                            ],
                        ],
                        'containerOptions' => [
                            'style' => 'width:50px; overflow: hidden;',
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
                    $items = [
                        'emails' => 'Email',
                        'usage_tech_cpe' => 'Tech CPE',
                        'usage_extra' => 'Extra',
                        'usage_ip_ports' => 'IP Ports',
                        'usage_sms' => 'SMS',
                        'usage_virtpbx' => 'ВАТС',
                        'usage_voip' => 'Телефония',
                        'usage_welltime' => 'Welltime',
                    ];
                    // Добавление к услугам дополнительных параметров
                    if ($this->grid->isCurrentReport(BusinessProcess::TELECOM_REPORTS)) {
                        foreach (['FromCustomers', 'FromManagersAndUsages', 'Different'] as $name) {
                            if (md5('app\classes\grid\account\telecom\reports\Income' . $name . 'Folder') === md5(static::class)) {
                                // Для отчета "Выручка Welltime" добавить товары из 1С
                                $goods = Good::find()
                                    ->where(['num_id' => [Good::GOOD_HASP_HL_PRO_USB, Good::GOOD_WELLTIME_IP_ATC]])
                                    ->asArray();
                                foreach ($goods->each() as $good) {
                                    $items += ['goods_' . $good['id'] => $good['name']];
                                }

                                // Для отчета "Выручка Welltime" добавить услуги с кодом "welltime", публичный статус, период - одноразовый
                                $tarifsExtra = TariffExtra::find()
                                    ->select(['id', 'description'])
                                    ->where(['code' => 'welltime', 'status' => 'public', 'period' => 'once'])
                                    ->asArray();
                                foreach ($tarifsExtra->each() as $tarifExtra) {
                                    $items += ['extra_' . $tarifExtra['id'] => $tarifExtra['description']];
                                }
                                break;
                            }
                        }
                    }
                    return Html::dropDownList(
                        'service',
                        Yii::$app->request->get('service'),
                        $items,
                        ['class' => 'form-control', 'prompt' => '-Не выбрано-', 'style' => 'max-width:150px;',]
                    );
                },
            ],
            'partner_clients_service' => [
                'attribute' => 'service',
                'format' => 'raw',
                'value' => function ($data) {
                    $usages = [];
                    if ($data['usage_voip'] > 0) {
                        $usages[] = 'Телефония';
                    }

                    if ($data['usage_virtpbx'] > 0) {
                        $usages[] = 'ВАТС';
                    }

                    return implode(', ', $usages);
                },
                'filter' => function () {
                    return Html::dropDownList(
                        'service',
                        Yii::$app->request->get('service'),
                        [
                            'emails' => 'Email',
                            'tech_cpe' => 'Tech CPE',
                            'usage_extra' => 'Extra',
                            'usage_ip_ports' => 'IP Ports',
                            'usage_sms' => 'SMS',
                            'usage_virtpbx' => 'ВАТС',
                            'usage_voip' => 'Телефония',
                            'usage_welltime' => 'Welltime',
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
                    return DateRangePicker::widget([
                        'name' => 'bill_date',
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'value' => Yii::$app->request->get('bill_date'),
                        'pluginOptions' => [
                            'locale' => [
                                'format' => 'YYYY-MM-DD',
                            ],
                        ],
                        'containerOptions' => [
                            'style' => 'width:50px; overflow: hidden;',
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
                    return Select2::widget([
                        'name' => 'manager',
                        'data' => User::getManagerList(),
                        'value' => Yii::$app->request->get('manager'),
                        'options' => [
                            'placeholder' => 'Начните вводить фамилию',
                            'style' => 'width: 150px;',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
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
                    return Select2::widget([
                        'name' => 'account_manager',
                        'value' => Yii::$app->request->get('account_manager'),
                        'data' => User::getAccountManagerList(),
                        'options' => [
                            'placeholder' => 'Начните вводить фамилию',
                            'style' => 'width: 150px;',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
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
                    return Html::dropDownList(
                        'currency',
                        Yii::$app->request->get('currency'),
                        Currency::map(),
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
                    return Select2::widget([
                        'name' => 'account_manager',
                        'data' => SaleChannelOld::getList(),
                        'value' => Yii::$app->request->get('sale_channel'),
                        'options' => ['placeholder' => 'Начните вводить название', 'style' => 'width:100%;',],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]);
                },
            ],
            'federal_district' => [
                'attribute' => 'federal_district',
                'format' => 'raw',
                'value' => function ($data) {
                    $arr = SetFieldTypeHelper::parseValue($data['federal_district']);
                    array_walk($arr, function (&$item) {
                        $item = ClientContract::$districts[$item];
                    });
                    return implode('<br>', $arr);
                },
                'filter' => function () {
                    return Html::dropDownList(
                        'federal_district',
                        Yii::$app->request->get('federal_district'),
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
                    return Html::dropDownList(
                        'contract_type',
                        Yii::$app->request->get('contract_type'),
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
                    return Html::dropDownList(
                        'financial_type',
                        Yii::$app->request->get('financial_type'),
                        ClientContract::$financialTypes,
                        ['class' => 'form-control', 'style' => 'max-width:100px;']
                    );
                },
            ],
            'legal_entity' => [
                'attribute' => 'legal_entity',
                'format' => 'raw',
                'value' => function ($data) {
                    return isset($this->_organizationList[$data['organization_id']]) ?
                        $this->_organizationList[$data['organization_id']] : '';
                },
                'filter' => function () {
                    $items = ['' => '- Все -'] + Organization::dao()->getList($isWithEmpty = false);
                    return Html::dropDownList(
                        'legal_entity',
                        Yii::$app->request->get('legal_entity'),
                        $items,
                        ['class' => 'form-control', 'style' => 'min-width:80px;']
                    );
                },
            ],
            'comment' => [
                'attribute' => 'comment',
                'format' => 'raw',
                'value' => function ($data) {
                    return
                        '<span>' . $data['comment'] . '</span>' .
                        '<img src="/images/icons/edit.gif" role="button" data-id=' . $data["id"] . ' class="edit pull-right" alt="Редактировать" />';
                },
                'width' => '20%',
            ],
        ];
    }

    /**
     * Возврат ассоциативного массива с указанными полями для выборки.
     * В противном случае будет возвращен пустой массив
     *
     * @return array
     */
    public function getQuerySummarySelect()
    {
        return [];
    }

    /**
     * Возврат ассоциативного массива с суммами по условиям, указанным в методе
     * getQuerySummarySelect. В противном случае будет возвращен пустой массив
     *
     * @return array
     */
    public function getSummary()
    {
        return [];
    }

    /**
     * Возврат количества colspan, необходимого при генерации отчета в дополнительной
     * строке в afterHeader
     *
     * @return int
     */
    public function getColspan()
    {
        return 0;
    }

    /**
     * Определение сигнатуры услуги
     *
     * @param string $signature
     * @return bool
     */
    public function hasServiceSignature($signature)
    {
        return strpos($this->service, $signature) === 0;
    }

    public function isGenericFolder()
    {
        return $this->_isGenericFolder;
    }
    public function initExtraValues()
    {
        if (isset($_COOKIE['Form' . $this->formName() . 'Data'])) {
            $data = Json::decode($_COOKIE['Form' . $this->formName() . 'Data']);

            if (isset($data['is_group_by_contract'])) {
                $this->is_group_by_contract = $data['is_group_by_contract'];
            }
        }
    }

}