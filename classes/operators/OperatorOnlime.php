<?php

namespace app\classes\operators;

use Yii;
use yii\base\Object;
use app\dao\reports\ReportOnlimeDao;
use app\forms\external_operators\RequestOnlimeForm;
use app\forms\external_operators\RequestOnlimeStateForm;
use app\models\Bill;
use app\models\TroubleState;

if (!defined('PATH_TO_ROOT'))
    define('PATH_TO_ROOT', Yii::$app->basePath . '/stat/');
if (!defined('NO_WEB'))
    define('NO_WEB', 1);

require_once PATH_TO_ROOT . 'conf.php';

class OperatorOnlime extends Object
{

    const OPERATOR_CLIENT = 'onlime';

    public static $requestProducts = [
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

    public static $requestModes = [
        'new' => [
            'sql' => '`state_id` IN (15,35,32,33)',
            'title' => 'Новый',
        ],
        'work' => [
            'sql' => '`state_id` NOT IN (2,20,21)',
            'title' => 'В работе',
        ],
        'deferred' => [
            'sql' => '`state_id` IN (24,31)',
            'title' => 'Отложенный',
        ],
        'close' => [
            'sql' => '`state_id` IN (2,20)',
            'title' => 'Закрыт',
        ],
        'done' => [
            'sql' => '`state_id` IN (4,18,28)',
            'title' => 'Выполнен',
        ],
        'reject' => [
            'sql' => '`state_id` = 21',
            'title' => 'Отказ',
        ],
    ];

    public static $reportFields = [
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

    public static $availableRequestStatuses = [
        15 => 'Новый',
        17 => 'В работе',
        24 => 'Отложен',
        20 => 'Закрыт',
        18 => 'Выполнен',
        21 => 'Отказ',
    ];

    public function getOperator()
    {
        return self::OPERATOR_CLIENT;
    }

    public function getRequestForm()
    {
        return new RequestOnlimeForm;
    }

    public function getRequestStateForm()
    {
        return new RequestOnlimeStateForm;
    }

    public function getReport()
    {
        return ReportOnlimeDao::me();
    }

    public function downloadReport($dateFrom, $dateTo, $filter = [])
    {
        $list = $this->report->getList($dateFrom, $dateTo, $filter);
        $sTypes = self::$requestModes;

        $reportName =
            'OnLime__' .
            str_replace(' ', '_', $sTypes[ $filter['mode'] ]['title']) .
            '__' . $dateFrom .
            '__' . $dateTo;

        Yii::$app->response->sendContentAsFile(
            $this->GenerateExcel(self::$reportFields, $list),
            $reportName . '.xls'
        );
        Yii::$app->end();
    }

    public function getProducts()
    {
        return self::$requestProducts;
    }

    public static function getProductById($id)
    {
        foreach (self::$requestProducts as $product) {
            if ($id == $product['id'])
                return $product;
        }
    }

    public function getModes()
    {
        return self::$requestModes;
    }

    public function getAvailableRequestStatuses()
    {
        return self::$availableRequestStatuses;
    }

    public static function saveOrder1C(array $data)
    {
        $soap = self::initSoap1C();

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

    public static function saveOrderState1C(Bill $bill, TroubleState $state)
    {
        $soap = self::initSoap1C();

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

    private static function initSoap1C()
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

    private function GenerateExcel($head, $list)
    {
        $objPHPExcel = new \PHPExcel;
        $objPHPExcel->setActiveSheetIndex(0);

        $sheet = $objPHPExcel->getActiveSheet();

        foreach ([10, 12, 21, 11, 29, 35, 33, 14, 14, 88] as $columnIndex => $width) {
            $sheet->getColumnDimensionByColumn($columnIndex + 1)->setWidth($width);
        }

        $idx = 0;
        foreach ($head as $title => $field) {
            if ($field == 'products') {
                foreach (self::$requestProducts as $product) {
                    $sheet->setCellValueByColumnAndRow($idx++, 2, $product['name']);
                }
            }
            else {
                $sheet->setCellValueByColumnAndRow($idx++, 2, $title);
            }
        }

        foreach ($list as $rowIdx => $item) {
            $colIdx = 0;
            foreach($head as $title => $field) {
                if ($field == 'products') {
                    foreach (self::$requestProducts as $i => $product) {
                        $sheet->setCellValueByColumnAndRow(
                            $colIdx++,
                            3 + $rowIdx,
                            isset($item['group_' . ($i + 1)]) ? strip_tags($item['group_' . ($i + 1)]) : ''
                        );
                    }
                }
                else if ($field == 'stages_text') {
                    $last_stage = array_pop($item['stages']);

                    $sheet->setCellValueByColumnAndRow(
                        $colIdx++,
                        3 + $rowIdx,
                        $last_stage['date_finish_desired'] . "\n" .
                        $last_stage['state_name'] . "\n" .
                        $last_stage['user_edit'] . "\n" .
                        $last_stage['comment']
                    );
                }
                else {
                    $sheet->setCellValueByColumnAndRow(
                        $colIdx++,
                        3 + $rowIdx,
                        isset($item[$field]) ? strip_tags($item[$field]) : ''
                    );
                }
            }
        }

        $oeWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        ob_start();
        $oeWriter->save('php://output');
        $content = ob_get_contents();
        ob_clean();

        return $content;
    }

}