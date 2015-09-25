<?php

namespace app\forms\external_operators;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Form;
use app\classes\StatModule;
use app\classes\operators\Operators;
use app\models\ClientAccount;

require_once Yii::$app->basePath . '/stat/include/1c_integration.php';

class RequestOnlimeForm extends Form
{

    public
        $fullname,
        $address,
        $phone,
        $time_interval,
        $account_id,
        $comment,
        $products = [],
        $products_counts = [];

    public $bill_no;

    private static $time_intervals = [
        '10-16' => 'с 10 до 16 часов',
        '16-21' => 'с 16 до 21 часа',
    ];

    private static $fields1C = [
        'ФИО'                       => 'fio',
        'Адрес'                     => 'address',
        'НомерЗаявки'               => 'req_no',
        'ЛицевойСчет'               => 'acc_no',
        'НомерПодключения'          => 'connum',
        'Комментарий1'              => 'comment1',
        'Комментарий2'              => 'comment2',
        'ПаспортСерия'              => 'passp_series',
        'ПаспортНомер'              => 'passp_num',
        'ПаспортКемВыдан'           => 'passp_whos_given',
        'ПаспортКогдаВыдан'         => 'passp_when_given',
        'ПаспортКодПодразделения'   => 'passp_code',
        'ПаспортДатаРождения'       => 'passp_birthday',
        'ПаспортГород'              => 'reg_city',
        'ПаспортУлица'              => 'reg_street',
        'ПаспортДом'                => 'reg_house',
        'ПаспортКорпус'             => 'reg_housing',
        'ПаспортСтроение'           => 'reg_build',
        'ПаспортКвартира'           => 'reg_flat',
        'Email'                     => 'email',
        'ПроисхождениеЗаказа'       => 'order_given',
        'КонтактныйТелефон'         => 'phone',
        'Метро'                     => 'metro_id',
        'Логистика'                 => 'logistic',
        'ВладелецЛинии'             => 'line_owner',
    ];

    public function rules()
    {
        return [
            [['fullname', 'address', 'phone', 'account_id'], 'required'],
            [['fullname', 'address', 'phone', 'comment'], 'string'],
            [['account_id'], 'integer'],
            ['time_interval', 'in', 'range' => array_keys(self::getTimeIntervals())],
            ['products', 'required', 'message' => 'Выберите хотя бы один товар'],
            ['products_counts', 'required', 'message' => 'Выберите хотя бы один товар'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fullname' => 'Ф.И.О.',
            'address' => 'Адрес доставки',
            'phone' => 'Контактный телефон',
            'time_interval' => 'Временной интервал',
            'account_id' => 'Лицевой счет',
            'comment' => 'Комментарии к заявке',
        ];
    }

    public function getTimeIntervals()
    {
        return self::$time_intervals;
    }

    public function save(Operators $operator)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $account = ClientAccount::findOne(['client' => $operator->operatorClient]);

            $positions = [
                'bill_no' => '',
                'client_id' => $account->id,
                'list' => [],
                'sum' => 0,
                'number' => '',
                'comment' => $this->comment,
                'is_rollback' => 1,
            ];

            for ($i = 0, $s = count($this->products); $i < $s; $i++) {
                $product = $operator->getProductById($this->products[$i]);
                $positions['list'][] = [
                    'id' => $product['id_1c'] . ':',
                    'code_1c' => 0,
                    'quantity_saved' => $this->products_counts[$i],
                    'name' => $product['nameFull'],
                    'quantity' => $this->products_counts[$i],
                    'price' => 0,
                    'sum' => 0,
                ];
            }

            $addInfo = [];
            foreach (self::$fields1C as $field => $field_key) {
                $addInfo[$field] = $this->convertField($field_key);
            }

            try {
                $response = $operator->saveOrder1C([
                    'client_tid' => $account->client,
                    'order_number' => $positions['bill_no'],
                    'items_list' => (isset($positions['list']) ? $positions['list'] : false),
                    'order_comment' => $positions['comment'],
                    'is_rollback' => $positions['is_rollback'],
                    'add_info' => $addInfo,
                    'store_id' => $operator->storeId,
                ]);
            }
            catch (\Exception $e) {
                $this->addError('1C_error', str_replace('|||', '', $e->getMessage()));
                $this->addError('1C_order', 'Не удалось создать заказ в 1С');
                return false;
            }

            $transaction->commit();

            $class = new \stdClass;
            $class->order = $response;
            $class->isRollback = $positions['is_rollback'];

            $error = '';
            $this->bill_no = $response->{'Номер'};

            $soap = new \_1c\SoapHandler;
            $soap->statSaveOrder($class, $this->bill_no, $error, []);

            StatModule::tt()->createTrouble([
                'user_author' => 'system',
                'trouble_type' => 'shop_orders',
                'trouble_subtype' => 'shop',
                'client' => $account->client,
                'problem' => $positions['comment'],
                'bill_no' => $this->bill_no,
                'time' => 0,
                'folder' => $operator::OPERATOR_TROUBLE_DEFAULT_FOLDER,
            ]);
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->hasErrors() ? false : true;
    }

    private function convertField($field)
    {
        switch ($field) {
            case 'fio':
                return $this->fullname;
            case 'comment1':
                return self::$time_intervals[ $this->time_interval ];
            case 'acc_no':
                return $this->account_id;
            default:
                return isset($this->{$field}) ? $this->{$field}  : '';
        }
    }

}
