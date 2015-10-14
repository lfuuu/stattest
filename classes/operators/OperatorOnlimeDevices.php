<?php

namespace app\classes\operators;

use Yii;
use yii\db\Query;
use app\forms\external_operators\RequestOnlimeForm;
use app\forms\external_operators\RequestOnlimeStateForm;
use app\models\Bill;
use app\models\TroubleState;

if (!defined('PATH_TO_ROOT'))
    define('PATH_TO_ROOT', Yii::$app->basePath . '/stat/');
if (!defined('NO_WEB'))
    define('NO_WEB', 1);

require_once PATH_TO_ROOT . 'conf.php';

class OperatorOnlimeDevices extends Operators
{

    const OPERATOR = 'onlime';
    const OPERATOR_CLIENT = 'id36001';

    protected static $requestProducts = [
        [
            'id' => 17473,
            'name' => 'D-Link DIR-825/AC',
            'nameFull' => 'Маршрутизатор, офисный D-Link DIR-825/AC (066.5600.7859)',
            'id_1c' => '032a1ea7-4fc6-11e5-953a-00155d881200',
        ],
        [
            'id' => 17474,
            'name' => 'HD mini',
            'nameFull' => 'IPTV-приставка, HD mini (065.0002.0056)',
            'id_1c' => '3f3dcbc4-4fc6-11e5-953a-00155d881200',
        ],
        [
            'id' => 17475,
            'name' => 'SML-482 HD Base',
            'nameFull' => 'IPTV-приставка, SML-482 HD Base (065.0002.0055)',
            'id_1c' => '82922c6d-4fc6-11e5-953a-00155d881200',
        ],
        [
            'id' => 17476,
            'name' => 'RT STB HD',
            'nameFull' => 'IPTV-приставка, RT STB HD (065.0002.0057)',
            'id_1c' => 'a4933f17-4fc6-11e5-953a-00155d881200',
        ],
        [
            'id' => 17471,
            'name' => 'Upvel UR-825AC',
            'nameFull' => 'Маршрутизатор, офисный Upvel UR-825AC (066.5600.7460)',
            'id_1c' => 'd57357e7-4fc4-11e5-953a-00155d881200',
        ],
        [
            'id' => 17472,
            'name' => 'NetGear JNR3210-1NNRUS',
            'nameFull' => 'Маршрутизатор, офисный NetGear JNR3210-1NNRUS (066.5600.6394)',
            'id_1c' => 'db24efd7-4fc5-11e5-953a-00155d881200',
        ],
    ];

    protected static $requestModes = [
        'new' => [
            'title' => 'Новый',
            'queryModify' => 'modeNewModify',
        ],
        'work' => [
            'title' => 'В работе',
            'queryModify' => 'modeWorkModify',
        ],
        'deferred' => [
            'title' => 'Отложенный',
            'queryModify' => 'modeDeferredModify',
        ],
        'close' => [
            'title' => 'Закрыт',
            'queryModify' => 'modeCloseModify',
        ],
        'done' => [
            'sql' => '',
            'title' => 'Выполнен',
            'queryModify' => 'modeDoneModify',
        ],
        'reject' => [
            'title' => 'Отказ',
            'queryModify' => 'modeRejectModify',
        ],
    ];

    protected static $reportFields = [
        'Оператор'                                      => 'fio_oper',
        'Номер счета OnLime'                            => 'req_no',
        'Номер счета Маркомнет Сервис'                  => 'bill_no',
        'Дата создания заказа'                          => 'date_creation',
        'Кол-во'                                        => 'products',
        'Серийный номер'                                => 'serials',
        'Номер купона'                                  => 'coupon',
        'ФИО клиента'                                   => 'fio',
        'Телефон клиента'                               => 'phone',
        'Адрес'                                         => 'address',
        'Дата доставки желаемая'                        => 'date_deliv',
        'Дата доставки фактическая'                     => 'date_delivered',
        'Этап'                                          => 'stages_text',
    ];

    protected static $availableRequestStatuses = [
        17 => 'В работе',
        21 => 'Отказ',
    ];

    public $isRollback = true;

    public function getRequestForm()
    {
        return new RequestOnlimeForm;
    }

    public function getRequestStateForm()
    {
        return new RequestOnlimeStateForm;
    }

    public function modeNewModify(Query $query, $dao)
    {
        $query->andWhere('s.stage_id = t.cur_stage_id');
        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['in', 'state_id', [15, 32]]);
    }

    public function modeWorkModify(Query $query, $dao) {
        $query->andWhere('s.stage_id = t.cur_stage_id');
        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['not in', 'state_id', [15, 32, 24, 31, 2, 20, 4, 18, 28, 21]]);
    }

    public function modeDeferredModify(Query $query, $dao)
    {
        $query->andWhere('s.stage_id = t.cur_stage_id');
        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['in', 'state_id', [24, 31]]);
    }

    public function modeCloseModify(Query $query, $dao) {
        $query->andWhere('s.trouble_id = t.id');
        $query->andWhere(['between', 's.date_start', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['in', 'state_id', [2, 20]]);
    }

    public function modeDoneModify(Query $query, $dao)
    {
        $query->andWhere('s.stage_id = t.cur_stage_id');
        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['in', 'state_id', [4, 18, 28]]);
    }

    public function modeRejectModify(Query $query, $dao) {
        $query->andWhere('s.stage_id = t.cur_stage_id');
        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['state_id' => 21]);
    }

    public function saveOrder1C(array $data)
    {
        $soap = $this->initSoap1C();

        $items_list = array();
        if ($data['items_list'] !== false) {
            foreach ($data['items_list'] as $item) {
                list($item['id'], $item['descr_id']) = explode(':', $item['id']);

                $items_list[] = [
                    'КодНоменклатура1С'     => $item['id'],
                    'КодХарактеристика1С'   => $item['descr_id'] ?: '00000000-0000-0000-0000-000000000000',
                    'Количество'            => $item['quantity'],
                    'КодСтроки'             => (int) $item['code_1c'],
                    'Цена'                  => $item['price'],
                ];
            }
        }

        $query = [
            'НомерЗаказа'           => $data['order_number'],
            'ИдКарточкиКлиентаСтат' => $data['client_tid'],
            'Комментарий'           => $data['order_comment'],
            'ЭтоВозврат'            => (bool) $data['is_rollback'],
            'Пользователь'          => (Yii::$app->user->identity ? Yii::$app->user->identity->user : 'system'),
            'ДопИнформацияЗаказа'   => $data['add_info'],
            'КодСклад1С'            => $data['store_id'],
        ];

        if ($items_list !== false)
            $query['СписокПозиций']= ['Список' => $items_list];

        $response = $soap->utSaveOrder($query);
        $result = $response->return;

        if (!$result) {
            throw new \Exception($response->{'СообщениеОбОшибке'}, 1000);
        }
        $response = $response->{'ЗаказТовара'};

        if (!isset($response->{'ДопИнформацияЗаказа'}))
            $response->{'ДопИнформацияЗаказа'} = null;

        return $response;
    }

    public function saveOrderState1C(Bill $bill, TroubleState $state)
    {
        $soap = $this->initSoap1C();

        try {
            $soap->utSetOrderStatus([
                'НомерЗаказа' => $bill->bill_no,
                'Статус' => $state->state_1c,
                'Пользователь' => (Yii::$app->user->identity ? Yii::$app->user->identity->user : 'system'),
                'ЭтоВозврат' => (bool) $bill->is_rollback
            ])->return;
        }
        catch (\SoapFault $e) {
            throw new \Exception('Не удалось обновить статус заказа:', 1000);
        }
    }

    private function initSoap1C()
    {
        if (!defined('SYNC1C_UT_SOAP_URL') || !SYNC1C_UT_SOAP_URL)
            return false;

        $wsdl = \Sync1C::me()->utWsdlUrl;
        $login = \Sync1C::me()->utLogin;
        $pass = \Sync1C::me()->utPassword;
        $params = [
            'encoding' => 'UTF-8',
            'trace' => 1,
        ];
        if ($login && $pass) {
            $params['login'] = $login;
            $params['password'] = $pass;
        }
        return new \SoapClient($wsdl,$params);
    }



}