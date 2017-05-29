<?php

namespace app\modules\uu\models;

use app\classes\model\HistoryActiveRecord;
use app\modules\uu\models\traits\AccountTariffBillerPeriodTrait;
use app\modules\uu\models\traits\AccountTariffBillerResourceTrait;
use app\modules\uu\models\traits\AccountTariffBillerSetupTrait;
use app\modules\uu\models\traits\AccountTariffBillerTrait;
use app\modules\uu\models\traits\AccountTariffBoolTrait;
use app\modules\uu\models\traits\AccountTariffGroupTrait;
use app\modules\uu\models\traits\AccountTariffLinkTrait;
use app\modules\uu\models\traits\AccountTariffPackageTrait;
use app\modules\uu\models\traits\AccountTariffRelationsTrait;
use app\modules\uu\models\traits\AccountTariffValidatorTrait;

/**
 * Универсальная услуга
 *
 * @property int $id
 * @property int $client_account_id
 * @property int $service_type_id
 * @property int $region_id
 * @property int $city_id
 * @property int $prev_account_tariff_id   Основная услуга
 * @property int $tariff_period_id   Если null, то закрыто. Кэш AccountTariffLog->TariffPeriod
 * @property string $comment
 * @property int $voip_number номер линии (если 4-5 символов) или телефона (fk на voip_numbers)
 * @property int $vm_elid_id ID VM collocation
 */
class AccountTariff extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

    use AccountTariffRelationsTrait;
    use AccountTariffBillerTrait;
    use AccountTariffBillerSetupTrait;
    use AccountTariffBillerResourceTrait;
    use AccountTariffBillerPeriodTrait;
    use AccountTariffValidatorTrait;
    use AccountTariffBoolTrait;
    use AccountTariffGroupTrait;
    use AccountTariffPackageTrait;
    use AccountTariffLinkTrait;

    const DELTA = 100000;

    // Ошибки ЛС
    const ERROR_CODE_ACCOUNT_EMPTY = 1; // ЛС не указан
    const ERROR_CODE_ACCOUNT_BLOCKED_PERMANENT = 2; // ЛС заблокирован
    const ERROR_CODE_ACCOUNT_BLOCKED_TEMPORARY = 3; // ЛС заблокирован из-за превышения лимитов
    const ERROR_CODE_ACCOUNT_BLOCKED_FINANCE = 4; // ЛС в финансовой блокировке
    const ERROR_CODE_ACCOUNT_IS_NOT_UU = 5; // Универсальную услугу можно добавить только ЛС, тарифицируемому универсально
    const ERROR_CODE_ACCOUNT_IS_UU = 6; // Неуниверсальную услугу можно добавить только ЛС, тарифицируемому неуниверсально
    const ERROR_CODE_ACCOUNT_CURRENCY = 7; // Валюта акаунта и тарифа не совпадают
    const ERROR_CODE_ACCOUNT_MONEY = 8; // На ЛС даже с учетом кредита меньше первичного платежа по тарифу
    const ERROR_CODE_ACCOUNT_TRUNK = 9; // Универсальную услугу транка можно добавить только ЛС с договором Межоператорка
    const ERROR_CODE_ACCOUNT_TRUNK_SINGLE = 10; // Для ЛС можно создать только одну базовую услугу транка. Зато можно добавить несколько пакетов.
    const ERROR_CODE_ACCOUNT_POSTPAID = 11; // ЛС и тариф должны быть либо оба предоплатные, либо оба постоплатные

    // Ошибки даты
    const ERROR_CODE_DATE_PREV = 21; // Нельзя менять задним числом
    const ERROR_CODE_DATE_TODAY = 22; // Сегодня уже меняли. Теперь можно сменить его не ранее завтрашнего дня
    const ERROR_CODE_DATE_FUTURE = 23; // Уже назначена смена в будущем. Если вы хотите установить новое значение - сначала отмените эту смену
    const ERROR_CODE_DATE_TARIFF = 24; // Пакет телефонии может начать действовать только после начала действия основной услуги телефонии
    const ERROR_CODE_DATE_PAID = 25; // Нельзя менять раньше уже оплаченного периода

    // Ошибки тарифа
    const ERROR_CODE_SERVICE_TYPE = 31; // Нельзя менять тип услуги
    const ERROR_CODE_TARIFF_EMPTY = 32; // Не указан тариф/период
    const ERROR_CODE_TARIFF_WRONG = 33; // Неправильный тариф/период
    const ERROR_CODE_TARIFF_SERVICE_TYPE = 34; // Тариф/период не соответствует типу услуги
    const ERROR_CODE_TARIFF_SAME = 35; // Нет смысла менять на то же самое значение. Выберите другое значение

    // Ошибки услуги
    const ERROR_CODE_USAGE_EMPTY = 41; // Услуга не указана
    const ERROR_CODE_USAGE_MAIN = 42; // Не указана основная услуга телефонии для пакета телефонии
    const ERROR_CODE_USAGE_DOUBLE_PREV = 43; // Этот пакет уже подключен на эту же базовую услугу. Повторное подключение не имеет смысла.
    const ERROR_CODE_USAGE_DOUBLE_FUTURE = 44; // Этот пакет уже запланирован на подключение на эту же базовую услугу. Повторное подключение не имеет смысла
    const ERROR_CODE_USAGE_CANCELABLE = 45; // Нельзя отменить уже примененный тариф
    const ERROR_CODE_USAGE_DEFAULT = 46; // Нельзя подключить второй базовый пакет на ту же услугу.
    const ERROR_CODE_USAGE_NOT_EDITABLE = 47; // Услуга нередактируемая
    const ERROR_CODE_USAGE_NUMBER_NOT_IN_STOCK = 48; // Этот телефонный номер нельзя подключить

    // Ошибки ресурса
    const ERROR_CODE_RESOURCE_WRONG = 51; // Указан несуществующий ресурс
    const ERROR_CODE_RESOURCE_TYPE_WRONG = 52; // Этот ресурс от другого типа услуги
    const ERROR_CODE_RESOURCE_TRAFFIC = 54; // Этот ресурс - трафик, а не опция. Его нельзя установить заранее.
    const ERROR_CODE_RESOURCE_AMOUNT_MIN = 55; // Значение ресурса меньше минимально допустимого значения.
    const ERROR_CODE_RESOURCE_AMOUNT_MAX = 56; // Значение ресурса больше максимально допустимого значения.

    // Ошибка телефонии
    const ERROR_CODE_VOIP_WRONG_NUMBER = 71; // Не указан телефонный номер
    const ERROR_CODE_VOIP_WRONG_STATUS = 72; // Статус тарифа не совпадает со статусом телефонного номера

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_account_id', 'service_type_id'], 'required'],
            [
                [
                    'client_account_id',
                    'service_type_id',
                    'region_id',
                    'city_id',
                    'prev_account_tariff_id',
                    'tariff_period_id',
                ],
                'integer'
            ],
            [['comment'], 'string'],
            ['voip_number', 'match', 'pattern' => '/^\d{4,15}$/'],
            ['service_type_id', 'validatorServiceType'],
            ['client_account_id', 'validatorTrunk', 'skipOnEmpty' => false],
            ['tariff_period_id', 'validatorTariffPeriod'],
            ['voip_number', 'validatorVoipNumber', 'skipOnEmpty' => true],
            [
                ['city_id', 'voip_number'],
                'required',
                'when' => function (AccountTariff $accountTariff) {
                    return $accountTariff->service_type_id == ServiceType::ID_VOIP;
                }
            ],
        ];
    }
}