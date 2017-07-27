<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class BusinessProcessStatus
 *
 * @property int $id
 * @property int $business_process_id
 * @property string $name
 * @property int $sort
 * @property string $oldstatus
 * @property string $color
 */
class BusinessProcessStatus extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    // Общие
    const STATE_NEGOTIATIONS = 11; // Переговоры

    // Телеком
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

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['business_process_id' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC],
            $where = []
        );
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
}
