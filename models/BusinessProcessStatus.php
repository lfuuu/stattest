<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use yii\helpers\Url;

/**
 * Class BusinessProcessStatus
 *
 * @property int $id
 * @property int $business_process_id
 * @property string $name
 * @property int $sort
 * @property string $oldstatus
 * @property string $color
 * @property boolean $is_bill_send
 * @property boolean $is_off_stage
 * @property boolean $is_with_wizard
 */
class BusinessProcessStatus extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    // Общие
    const STATE_NEGOTIATIONS = 11; // Переговоры

    // Телеком b2b
    const TELEKOM_MAINTENANCE_ORDER_OF_SERVICES = 19; // Заказ услуг
    const TELEKOM_MAINTENANCE_CONNECTED = 8; // Подключаемые
    const TELEKOM_MAINTENANCE_WORK = 9; // Включенные
    const TELEKOM_MAINTENANCE_DISCONNECTED = 10; // Отключенные
    const TELEKOM_MAINTENANCE_DISCONNECTED_DEBT = 11; // Отключенные за долги
    const TELEKOM_MAINTENANCE_TRASH = 22; // Мусор
    const TELEKOM_MAINTENANCE_NOT_CONNECTED = 0; // Не привязанные
    const TELEKOM_MAINTENANCE_TECH_FAILURE = 27; // Тех. отказ
    const TELEKOM_MAINTENANCE_FAILURE = 28; // Отказ
    const TELEKOM_MAINTENANCE_DUPLICATE = 29; // Дубликат
    const TELEKOM_MAINTENANCE_WLINNONET = 152; // WL_Innonet
    const TELEKOM_MAINTENANCE_EXCEPTION_FROM_BOOK_OF_PROD = 157; // Исключения из Книги Прод.
    const TELEKOM_MAINTENANCE_PORTING_REQUEST_ACCEPTED = 169; // Заявка на портирование принята

    // Телеком b2c
    const TELEKOM_MAINTENANCE_B2C_ORDER_OF_SERVICES = 201; // Заказ услуг
    const TELEKOM_MAINTENANCE_B2C_CONNECTED = 198; // Подключаемые
    const TELEKOM_MAINTENANCE_B2C_WORK = 199; // Включенные
    const TELEKOM_MAINTENANCE_B2C_DISCONNECTED = 200; // Отключенные
    const TELEKOM_MAINTENANCE_B2C_TRASH = 202; // Мусор
    const TELEKOM_MAINTENANCE_B2C_TECH_FAILURE = 203; // Тех. отказ
    const TELEKOM_MAINTENANCE_B2C_FAILURE = 204; // Отказ
    const TELEKOM_MAINTENANCE_B2C_DUPLICATE = 205; // Дубликат
    const TELEKOM_MAINTENANCE_B2C_WLINNONET = 206; // WL_Innonet
    const TELEKOM_MAINTENANCE_B2C_EXCEPTION_FROM_BOOK_OF_PROD = 207; // Исключения из Книги Прод.
    const TELEKOM_MAINTENANCE_B2C_PORTING_REQUEST_ACCEPTED = 213; // Заявка на портирование принята


    // Статусы, в которых можно получать платежи с внешних систем
    const PAY_AVAILABLE_STATUSES_TELEKOM_MAINTENANCE = [
        self::TELEKOM_MAINTENANCE_WORK,
        self::TELEKOM_MAINTENANCE_B2C_WORK,
        self::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES,
        self::TELEKOM_MAINTENANCE_B2C_ORDER_OF_SERVICES,
        self::TELEKOM_MAINTENANCE_CONNECTED,
        self::TELEKOM_MAINTENANCE_B2C_CONNECTED,
        self::TELEKOM_MAINTENANCE_DISCONNECTED_DEBT,
    ];

    const TELEKOM_SALES_INCOMING = 1; // Входящие
    const TELEKOM_SALES_NEGOTIATIONS = 2; // В стадии переговоров
    const TELEKOM_SALES_TESTING = 3; // Тестируемые
    const TELEKOM_SALES_CONNECTING = 4; // Подключаемые
    const TELEKOM_SALES_TECH_FAILURE = 5; // Техотказ
    const TELEKOM_SALES_FAILURE = 6; // Отказ
    const TELEKOM_SALES_TRASH = 7; // Мусор

    // Интернет магазин
    const INTERNET_SHOP_ACTING = 16; // Сопровождение
    const INTERNET_SHOP_TRASH = 18; // Сопровождение

    // Поставщик
    const PROVIDER_ORDERS_ACTING = 32; // Заказы - Действущий
    const PROVIDER_ORDERS_NEGOTIATION_STAGE = 36; // Заказы - В стадии переговоров
    const PROVIDER_MAINTENANCE_TELESHOP = 108; // Сопровождение - Shop MCNTele.com
    const PROVIDER_MAINTENANCE_VOLS = 109; // Сопровождение - ВОЛС
    const PROVIDER_MAINTENANCE_SERVICE = 110; // Сопровождение - Сервисный
    const PROVIDER_MAINTENANCE_ACTING = 15; // Сопровождение - Действущий
    const PROVIDER_MAINTENANCE_CLOSED = 92; // Сопровождение - Закрытый
    const PROVIDER_MAINTENANCE_SELF_BUY = 93; // Сопровождение - Самозакупки
    const PROVIDER_MAINTENANCE_ONCE = 94; // Сопровождение - Разовый

    // Партнер
    const PARTNER_MAINTENANCE_NEGOTIATIONS = 126; // Сопровождение - Переговоры
    const PARTNER_MAINTENANCE_ACTING = 35; // Сопровождение - Действующий
    const PARTNER_MAINTENANCE_MANUAL_BILL = 127; // Сопровождение - Ручной счет
    const PARTNER_MAINTENANCE_SUSPENDED = 128; // Сопровождение - Приостановлен
    const PARTNER_MAINTENANCE_TERMINATED = 129; // Сопровождение - Расторгнут
    const PARTNER_MAINTENANCE_FAILURE = 130; // Сопровождение - Расторгнут
    const PARTNER_MAINTENANCE_TRASH = 131; // Сопровождение - Мусор
    const PARTNER_MAINTENANCE_WHITELABEL = 155; // Сопровождение - WhiteLabel

    // Внутренний офис
    const INTERNAL_OFFICE = 34; // Внутренний офис
    const INTERNAL_OFFICE_CLOSED = 111; // Закрытые

    // Оператор
    const OPERATOR_OPERATORS_INCOMING = 37; // Операторы - Входящий
    const OPERATOR_OPERATORS_NEGOTIATIONS = 38; // Операторы - Переговоры
    const OPERATOR_OPERATORS_TESTING = 39; // Операторы - Тестирование
    const OPERATOR_OPERATORS_ACTING = 40; // Операторы - Действующий
    const OPERATOR_OPERATORS_MANUAL_BILL = 107; // Операторы - Ручной счет
    const OPERATOR_OPERATORS_SUSPENDED = 41; // Операторы - Приостановлен
    const OPERATOR_OPERATORS_TERMINATED = 42; // Операторы - Расторгнут
    const OPERATOR_OPERATORS_BLOCKED = 43; // Операторы - Фрод блокировка
    const OPERATOR_OPERATORS_TECH_FAILURE = 44; // Операторы - Техотказ
    const OPERATOR_OPERATORS_AUTO_BLOCKED = 45; // Операторы - Автоблокировка
    const OPERATOR_OPERATORS_TRASH = 121; // Операторы - Мусор
    const OPERATOR_CLIENTS_INCOMING = 47; // Клиенты - Входящий
    const OPERATOR_CLIENTS_NEGOTIATIONS = 48; // Клиенты - Переговоры
    const OPERATOR_CLIENTS_TESTING = 49; // Клиенты - Тестирование
    const OPERATOR_CLIENTS_ACTING = 50; // Клиенты - Действующий
    const OPERATOR_CLIENTS_FORMAL = 125; // Клиенты - Формальные
    const OPERATOR_CLIENTS_JIRASOFT = 56; // Клиенты - JiraSoft
    const OPERATOR_CLIENTS_SUSPENDED = 51; // Клиенты - Приостановлен
    const OPERATOR_CLIENTS_TERMINATED = 52; // Клиенты - Расторгнут
    const OPERATOR_CLIENTS_BLOCKED = 53; // Клиенты - Фрод блокировка
    const OPERATOR_CLIENTS_TECH_FAILURE = 54; // Клиенты - Техотказ
    const OPERATOR_CLIENTS_TRASH = 122; // Клиенты - Мусор
    const OPERATOR_INFRASTRUCTURE_INCOMING = 62; // Инфраструктура - Входящий
    const OPERATOR_INFRASTRUCTURE_NEGOTIATIONS = 63; // Инфраструктура - Переговоры
    const OPERATOR_INFRASTRUCTURE_TESTING = 64; // Инфраструктура - Тестирование
    const OPERATOR_INFRASTRUCTURE_ACTING = 65; // Инфраструктура - Действующий
    const OPERATOR_INFRASTRUCTURE_FORMAL = 140; // Инфраструктура - Формальные
    const OPERATOR_INFRASTRUCTURE_SUSPENDED = 66; // Инфраструктура - Приостановлен
    const OPERATOR_INFRASTRUCTURE_TERMINATED = 67; // Инфраструктура - Расторгнут
    const OPERATOR_INFRASTRUCTURE_BLOCKED = 68; // Инфраструктура - Фрод блокировка
    const OPERATOR_INFRASTRUCTURE_TECH_FAILURE = 69; // Инфраструктура - Техотказ
    const OPERATOR_INFRASTRUCTURE_TRASH = 123; // Инфраструктура - Мусор
    const OPERATOR_INFRASTRUCTURE_MANUAL_BILL = 150; // Инфраструктура - Ручной счет
    const OPERATOR_INFRASTRUCTURE_ONE_TIME = 151; // Инфраструктура - Разовый

    const WELLTIME_MAINTENANCE_COMMISSIONING = 95; // Пуско-наладка
    const WELLTIME_MAINTENANCE_MAINTENANCE = 96; // Техобслуживание
    const WELLTIME_MAINTENANCE_MAINTENANCE_FREE = 97; // Без Техобслуживания
    const WELLTIME_MAINTENANCE_SUSPENDED = 98; // Приостановленные
    const WELLTIME_MAINTENANCE_FAILURE = 99; // Отказ
    const WELLTIME_MAINTENANCE_TRASH = 100; // Мусор


    const ITOUTSOURSING_MAINTENANCE_INCOMING = 132; // Входящие
    const ITOUTSOURSING_MAINTENANCE_NEGOTIATIONS = 133; // В стадии переговоров
    const ITOUTSOURSING_MAINTENANCE_VERIFICATION = 134; // Проверка документов
    const ITOUTSOURSING_MAINTENANCE_CONNECTING = 135; // Подключаемые
    const ITOUTSOURSING_MAINTENANCE_ONSERVICE = 136; // На обслуживании
    const ITOUTSOURSING_MAINTENANCE_SUSPENDED = 137; // Приостановленные
    const ITOUTSOURSING_MAINTENANCE_FAILURE = 138; // Отказ
    const ITOUTSOURSING_MAINTENANCE_TRASH = 139; // Мусор

    // OTT
    const OTT_MAINTENANCE_ORDER_OF_SERVICES = 141; // Заказ услуг
    const OTT_MAINTENANCE_CONNECTED = 142; // Подключаемые
    const OTT_MAINTENANCE_WORK = 143; // Включенные
    const OTT_MAINTENANCE_DISCONNECTED = 144; // Отключенные
    const OTT_MAINTENANCE_DISCONNECTED_DEBT = 145; // Отключенные за долги
    const OTT_MAINTENANCE_TRASH = 146; // Мусор
    const OTT_MAINTENANCE_TECH_FAILURE = 147; // Тех. отказ
    const OTT_MAINTENANCE_FAILURE = 148; // Отказ
    const OTT_MAINTENANCE_DUPLICATE = 149; // Дубликат


    const FOLDER_TELECOM_AUTOBLOCK = 21;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract_business_process_status';
    }

    public function rules()
    {
        return [
            [['id', 'business_process_id', 'is_bill_send', 'is_off_stage', 'is_with_wizard'], 'integer'],
            [['name', 'oldstatus', 'color'], 'string'],
            [['name', 'business_process_id'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'name' => 'Название статуса',
            'business_process_id' => 'Бизнес процесс',
            'sort' => 'Сортировка',
            'color' => 'Подсветка ЛС',
            'oldstatus' => 'Старый статус (для совместимости)',
            'is_bill_send' => 'Отправка счета',
            'is_off_stage' => 'Завершающий статус (ЛС считается выключеным)',
            'is_with_wizard' => 'Работает Wizard',
        ];
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int $businessProcessId
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $businessProcessId = null
    )
    {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['business_process_id' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC],
            $where = $businessProcessId ? ['business_process_id' => $businessProcessId] : []
        );
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/dictionary/business-process-status/edit', 'id' => $id]);
    }


    /**
     * Получение статуса по ID бизнес процесса
     *
     * @param integer $id
     * @return array|\app\classes\model\ActiveRecord[]
     */
    public static function getStatusesByBusinessId($id)
    {
        return
            self::find()
                ->leftJoin(BusinessProcess::tableName() . ' bp',
                    'bp.`id` = ' . self::tableName() . '.`business_process_id`')
                ->leftJoin(Business::tableName() . ' b', 'b.`id` = bp.`business_id`')
                ->where(['b.`id`' => $id])
                ->all();
    }

    /**
     * Получение дерева статусов
     *
     * @return array
     */
    public static function getTree()
    {
        $processes = [];
        $statuses = [];
        $businessProcesses = BusinessProcess::find()
            ->joinWith('businessProcessStatuses')
            ->andWhere([BusinessProcess::tableName() . '.show_as_status' => '1'])
            ->orderBy([
                BusinessProcess::tableName() . '.sort' => SORT_ASC,
                BusinessProcessStatus::tableName() . '.business_process_id' => SORT_ASC,
                BusinessProcessStatus::tableName() . '.sort' => SORT_ASC,
                BusinessProcessStatus::tableName() . '.id' => SORT_ASC,
            ])
            ->all();
        foreach ($businessProcesses as $businessProcess) {
            $processes[] = [
                'id' => $businessProcess->id,
                'up_id' => $businessProcess->business_id,
                'name' => $businessProcess->name
            ];
            foreach ($businessProcess->businessProcessStatuses as $status) {
                $statuses[] = [
                    'id' => $status->id,
                    'name' => $status->name,
                    'up_id' => $status->business_process_id
                ];
            }
        }

        return [
            "processes" => $processes,
            "statuses" => $statuses
        ];
    }

    /**
     * @param bool $isInsert
     * @return bool
     */
    public function beforeSave($isInsert)
    {
        $this->sort = $this->sort ?: self::find()
                ->where([
                    'business_process_id' => $this->business_process_id
                ])
                ->max('sort') + 1;

        return parent::beforeSave($isInsert);
    }

    /**
     * Сохранение сортировки из грида
     *
     * @param integer $elementId
     * @param integer $nextElementId
     * @return bool|string
     * @throws ModelValidationException
     */
    public function gridSort($elementId, $nextElementId)
    {
        $transaction = self::getDb()->beginTransaction();

        try {
            $movedElement = self::findOne(['id' => $elementId]);

            if ((int)$nextElementId) {
                $nextElement = self::findOne(['id' => $nextElementId]);

                if ($nextElement->business_process_id != $movedElement->business_process_id) {
                    throw new \LogicException('Сортировка статусов возможно только внутри одного бизнес процесса');
                }

                $isMoveDown = $movedElement->sort < $nextElement->sort;

                self::updateAllCounters(
                    [
                        'sort' => $isMoveDown ? -1 : 1
                    ],
                    [
                        'AND',
                        ['!=', 'sort', 0],
                        ['>', 'sort', $isMoveDown ? $movedElement->sort : $nextElement->sort - 1],
                        ['<', 'sort', $isMoveDown ? $nextElement->sort : $movedElement->sort],
                        ['business_process_id' => $movedElement->business_process_id]
                    ]);

                $movedElement->sort = $nextElement->sort - ($isMoveDown ? 1 : 0) ?: 1;
                if (!$movedElement->save()) {
                    throw new ModelValidationException($movedElement);
                }

            } else {
                $maxSequence = self::find()->max('`sort`');
                $movedElement->sort = (int)$maxSequence + 1;
                if (!$movedElement->save()) {
                    throw new ModelValidationException($movedElement);
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $e->getMessage();
        }

        return true;
    }
}
