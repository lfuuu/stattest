<?php

namespace app\classes\uu\model;

use app\classes\Html;
use app\classes\model\HistoryActiveRecord;
use app\classes\uu\forms\AccountLogFromToTariff;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Business;
use app\models\City;
use app\models\ClientAccount;
use app\models\Region;
use DateTime;
use DateTimeImmutable;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

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
 * @property int vm_elid_id ID VM collocation
 *
 * @property ClientAccount $clientAccount
 * @property ServiceType $serviceType
 * @property Region $region
 * @property City $city
 * @property \app\models\Number $number
 * @property AccountTariff $prevAccountTariff  Основная услуга
 * @property AccountTariff[] $nextAccountTariffs   Пакеты
 * @property AccountTariffLog[] $accountTariffLogs
 * @property TariffPeriod $tariffPeriod
 *
 * @property AccountLogSetup[] $accountLogSetups
 * @property AccountLogPeriod[] $accountLogPeriods
 * @property AccountLogResource[] $accountLogResources
 */
class AccountTariff extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

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
    const ERROR_CODE_DATE_PREV = 21; // Нельзя менять тариф задним числом
    const ERROR_CODE_DATE_TODAY = 22; // Сегодня тариф уже меняли. Теперь можно сменить его не ранее завтрашнего дня
    const ERROR_CODE_DATE_FUTURE = 23; // Уже назначена смена тарифа в будущем. Если вы хотите установить новый тариф - сначала отмените эту смену
    const ERROR_CODE_DATE_TARIFF = 24; // Пакет телефонии может начать действовать только после начала действия основной услуги телефонии
    const ERROR_CODE_DATE_PAID = 25; // Нельзя закрыть услугу раньше уже оплаченного периода

    // Ошибки тарифа
    const ERROR_CODE_SERVICE_TYPE = 31; // Нельзя менять тип услуги
    const ERROR_CODE_TARIFF_EMPTY = 32; // Не указан тариф/период
    const ERROR_CODE_TARIFF_WRONG = 33; // Неправильный тариф/период
    const ERROR_CODE_TARIFF_SERVICE_TYPE = 34; // Тариф/период не соответствует типу услуги
    const ERROR_CODE_TARIFF_SAME = 35; // Нет смысла менять период/тариф на тот же самый. Выберите другой период/тариф

    // Ошибки услуги
    const ERROR_CODE_USAGE_EMPTY = 41; // Услуга не указана
    const ERROR_CODE_USAGE_MAIN = 42; // Не указана основная услуга телефонии для пакета телефонии
    const ERROR_CODE_USAGE_DOUBLE_PREV = 43; // Этот пакет уже подключен на эту же базовую услугу. Повторное подключение не имеет смысла.
    const ERROR_CODE_USAGE_DOUBLE_FUTURE = 44; // Этот пакет уже запланирован на подключение на эту же базовую услугу. Повторное подключение не имеет смысла
    const ERROR_CODE_USAGE_CANCELABLE = 45; // Нельзя отменить уже примененный тариф
    const ERROR_CODE_USAGE_DEFAULT = 46; // Нельзя подключить второй базовый пакет на ту же услугу.

    /** @var array Код ошибки для АПИ */
    public $errorCode = null;

    /** @var int */
    protected $serviceTypeIdOld = null;

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
            [
                ['city_id', 'voip_number'],
                'required',
                'when' => function (AccountTariff $accountTariff) {
                    return $accountTariff->service_type_id == ServiceType::ID_VOIP;
                }
            ],
        ];
    }

    /**
     * @param bool $isWithAccount
     * @return string
     */
    public function getName($isWithAccount = true)
    {
        $names = [];

        if ($isWithAccount) {
            $names[] = $this->clientAccount->client;
        }

        if ($this->service_type_id == ServiceType::ID_VOIP && $this->voip_number) {
            // телефония
            $names[] = Yii::t('uu', 'Number {number}', ['number' => $this->voip_number]);
        } elseif ($this->service_type_id == ServiceType::ID_VOIP_PACKAGE && $this->prevAccountTariff->voip_number) {
            // пакет телефонии. Номер взять от телефонии
            $names[] = Yii::t('uu', 'Number {number}', ['number' => $this->prevAccountTariff->voip_number]);
        }

        $names[] = $this->tariff_period_id ? $this->tariffPeriod->getName() : Yii::t('common', 'Switched off');

        return implode('. ', $names);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/account-tariff/edit', 'id' => $this->id]);
    }

    /**
     * Вернуть html: имя + ссылка на услугу
     *
     * @param bool $isWithAccount
     * @return string
     */
    public function getAccountTariffLink($isWithAccount = true)
    {
        return $this->getLink($isWithAccount, false);
    }

    /**
     * Вернуть html: имя + ссылка на тариф
     *
     * @param bool $isWithAccount
     * @return string
     */
    public function getTariffPeriodLink($isWithAccount = true)
    {
        return $this->getLink($isWithAccount, true);
    }

    /**
     * Вернуть html: имя + ссылка на тариф
     *
     * @param bool $isWithAccount
     * @param bool $isTariffPeriodLink
     * @return string
     */
    public function getLink($isWithAccount = true, $isTariffPeriodLink = false)
    {
        return $this->tariff_period_id ?
            Html::a(
                Html::encode($this->getName($isWithAccount)),
                $isTariffPeriodLink ? $this->tariffPeriod->getUrl() : $this->getUrl()
            ) :
            Yii::t('common', 'Switched off');
    }

    /**
     * @param int $serviceTypeId
     * @return string
     */
    public static function getUrlNew($serviceTypeId)
    {
        return Url::to(['uu/account-tariff/new', 'serviceTypeId' => $serviceTypeId]);
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffPeriod()
    {
        return $this->hasOne(TariffPeriod::className(), ['id' => 'tariff_period_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrevAccountTariff()
    {
        return $this->hasOne(self::className(), ['id' => 'prev_account_tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNextAccountTariffs()
    {
        return $this->hasMany(self::className(), ['prev_account_tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumber()
    {
        return $this->hasOne(\app\models\Number::className(), ['number' => 'voip_number']);
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::className(), ['id' => 'service_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogSetups()
    {
        return $this->hasMany(AccountLogSetup::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogPeriods()
    {
        return $this->hasMany(AccountLogPeriod::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogResources()
    {
        return $this->hasMany(AccountLogResource::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffLogs()
    {
        return $this->hasMany(AccountTariffLog::className(), ['account_tariff_id' => 'id'])
            ->orderBy(['actual_from_utc' => SORT_DESC, 'id' => SORT_DESC])
            ->indexBy('id');
    }

    /**
     * Вернуть лог уникальных тарифов
     * В отличие от $this->accountTariffLogs
     *  - только те, которые не переопределены другим до наступления этой даты
     *  - в порядке возрастания
     *  - только активные на данный момент
     *
     * @param bool $isWithFuture
     * @return AccountTariffLog[]
     */
    public function getUniqueAccountTariffLogs($isWithFuture = false)
    {
        $accountTariffLogs = [];
        /** @var AccountTariffLog $accountTariffLogPrev */
        $accountTariffLogPrev = null;

        $accountTariffLogsTmp = $this->accountTariffLogs;
        $clientDate = reset($accountTariffLogsTmp)
            ->getClientDateTime()
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        foreach ($this->accountTariffLogs as $accountTariffLog) {
            if ($accountTariffLogPrev &&
                $accountTariffLogPrev->actual_from == $accountTariffLog->actual_from &&
                $accountTariffLogPrev->actual_from_utc > $accountTariffLogPrev->insert_time // строго раньше наступления даты
            ) {
                // неактивный тариф, потому что переопределен другим до наступления этой даты
                // если переопределен в тот же день, то списываем за оба
                continue;
            }

            if (!$isWithFuture && $accountTariffLog->actual_from > $clientDate) {
                // еще не наступил
                continue;
            }

            $accountTariffLogs[$accountTariffLog->getUniqueId()] = $accountTariffLogPrev = $accountTariffLog;
        }

        $accountTariffLogs = array_reverse($accountTariffLogs, true); // по возрастанию. Это важно для расчета периодов и цен
        return $accountTariffLogs;
    }

    /**
     * Вернуть большие периоды, разбитые только по смене тарифов
     * У последнего тарифа dateTo может быть null (не ограничен по времени)
     *
     * @param bool $isWithFuture
     * @param Period $chargePeriodMain если указано, то использовать указанное, а не из tariffPeriod
     * @return AccountLogFromToTariff[]
     */
    public function getAccountLogHugeFromToTariffs($isWithFuture = false, $chargePeriodMain = null)
    {
        /** @var AccountLogFromToTariff[] $accountLogPeriods */
        $accountLogPeriods = [];
        $uniqueAccountTariffLogs = $this->getUniqueAccountTariffLogs($isWithFuture);
        foreach ($uniqueAccountTariffLogs as $uniqueAccountTariffLog) {

            // начало нового периода
            $dateActualFrom = new DateTimeImmutable($uniqueAccountTariffLog->actual_from);

            if (($count = count($accountLogPeriods)) > 0) {

                // закончить предыдущий период
                $prevAccountTariffLog = $accountLogPeriods[($count - 1)];
                $prevTariffPeriodChargePeriod = $chargePeriodMain ?: $prevAccountTariffLog->tariffPeriod->chargePeriod;

                // старый тариф должен закончиться не раньше этой даты
                $dateActualFromYmd = $dateActualFrom->format(DateTimeZoneHelper::DATE_FORMAT);
                $insertTimeYmd = (new DateTimeImmutable($uniqueAccountTariffLog->insert_time))->format(DateTimeZoneHelper::DATE_FORMAT);
                if ($dateActualFromYmd < $insertTimeYmd) {
                    // $insertTimeYmd = UsageInterface::MIN_DATE; // ну, надо же хоть как-нибудь посчитать этот идиотизм, когда тариф меняют задним числом
                    throw new \LogicException('Тариф нельзя менять задним числом: ' . $uniqueAccountTariffLog->id);
                }

                /** @var DateTimeImmutable $dateFromNext дата теоретического начала (продолжения) старого тарифа. Из нее -1day получается дата окончания его прошлого периода */
                if ($dateActualFromYmd == $insertTimeYmd) {
                    // если смена произошла в тот же день, то этот день билингуется дважды: по старому тарифу (с полуночи до insert_time, но не менее периода списания) и по новому (с insert_time)
                    $dateTimeMin = $dateActualFrom;
                } else {
                    $dateTimeMin = $dateActualFrom->modify('-1 day');
                }

                unset($dateActualFromYmd, $insertTimeYmd);

                $prevAccountTariffLog->dateTo = $prevTariffPeriodChargePeriod->getMaxDateTo($prevAccountTariffLog->dateFrom, $dateTimeMin);
            }

            if (!$uniqueAccountTariffLog->tariffPeriod) {
                // услуга закрыта
                break;
            }

            // начать новый период
            $accountLogPeriods[] = new AccountLogFromToTariff();

            $count = count($accountLogPeriods);
            $accountLogPeriods[($count - 1)]->dateFrom = $dateActualFrom;
            $accountLogPeriods[($count - 1)]->tariffPeriod = $uniqueAccountTariffLog->tariffPeriod;
        }

        return $accountLogPeriods;
    }

    /**
     * Вернуть периоды, разбитые не более периода списания
     * Разбиты по логу тарифов, периодам списания, 1-м числам месяца.
     *
     * @param Period $chargePeriodMain если указано, то использовать указанное, а не из getAccountLogHugeFromToTariffs
     * @param bool $isWithCurrent возвращать ли незаконченный (длится еще) тариф? Для предоплаты надо, для постоплаты нет
     * @return AccountLogFromToTariff[]
     */
    public function getAccountLogFromToTariffs($chargePeriodMain = null, $isWithCurrent = true)
    {
        /** @var AccountLogFromToTariff[] $accountLogPeriods */
        $accountLogPeriods = [];
        $dateTo = $dateFrom = null;
        $minLogDatetime = self::getMinLogDatetime();
        $dateTimeNow = $this->clientAccount->getDatetimeWithTimezone();

        // взять большие периоды, разбитые только по смене тарифов
        // и разбить по периодам списания и первым числам
        $accountLogHugePeriods = $this->getAccountLogHugeFromToTariffs($isWithFuture = false, $chargePeriodMain);
        foreach ($accountLogHugePeriods as $accountLogHugePeriod) {

            $dateTo = $accountLogHugePeriod->dateTo;
            if ($dateTo && $dateTo < $minLogDatetime) {
                // слишком старый. Для оптимизации считать не будем
                continue;
            }

            $tariffPeriod = $accountLogHugePeriod->tariffPeriod;
            $chargePeriod = $chargePeriodMain ?: $tariffPeriod->chargePeriod;
            $dateFrom = $accountLogHugePeriod->dateFrom;
            if ($dateTo) {
                $dateToLimited = $dateTo;
            } else {
                // текущий день по таймзоне ЛС
                $dateToLimited = $chargePeriod->getMaxDateTo($dateFrom, $dateTimeNow);
                unset($timezoneName, $timezone);
            }

            do {
                $accountLogPeriod = new AccountLogFromToTariff();
                $accountLogPeriod->tariffPeriod = $tariffPeriod;
                $accountLogPeriod->dateFrom = $dateFrom;
                $accountLogPeriod->dateTo = $chargePeriod->monthscount ? $dateFrom->modify('last day of this month') : $dateFrom;

                if ($accountLogPeriod->dateTo >= $minLogDatetime) {
                    // Для оптимизации считаем только нестарые
                    $accountLogPeriods[] = $accountLogPeriod;
                }

                // начать новый период
                /** @var DateTimeImmutable $dateFrom */
                $dateFrom = $accountLogPeriod->dateTo->modify('+1 day');

            } while ($dateFrom->format(DateTimeZoneHelper::DATE_FORMAT) <= $dateToLimited->format(DateTimeZoneHelper::DATE_FORMAT));

        }

        if (!$isWithCurrent &&
            ($count = count($accountLogPeriods)) &&
            !$dateTo && $dateFrom > (new DateTimeImmutable())
        ) {
            // если count, то $dateTo и $dateFrom определены
            // если тариф действующий (!$dateTo) и следующий должен начаться не сегодня ($dateFrom > (new DateTimeImmutable()))
            // значит, последний период еще длится - удалить из расчета
            unset($accountLogPeriods[($count - 1)]);
            return $accountLogPeriods;
        }

        return $accountLogPeriods;
    }

    /**
     * Вернуть даты периодов, по которым не произведен расчет платы за подключение
     * В отличии от getUntarificatedPeriodPeriods - в периоде учитывается только начало, а не регулярное списание
     *
     * @param AccountLogSetup[] $accountLogs уже обработанные
     * @return AccountLogFromToTariff[]
     */
    public function getUntarificatedSetupPeriods($accountLogs)
    {
        $untarificatedPeriods = [];
        $minLogDatetime = self::getMinLogDatetime();
        $accountLogFromToTariffs = $this->getAccountLogHugeFromToTariffs(); // все

        $i = 0; // Порядковый номер нетестового тарифа
        // вычитанием получим необработанные
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

            // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
            // @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
            $isTest = $accountLogFromToTariff->tariffPeriod->tariff->getIsTest();
            !$isTest && $i++;

            if ($accountLogFromToTariff->dateFrom < $minLogDatetime) {
                // слишком старый. Для оптимизации считать не будем
                continue;
            }

            $uniqueId = $accountLogFromToTariff->getUniqueId();
            if (isset($accountLogs[$uniqueId])) {
                unset($accountLogs[$uniqueId]);
            } else {
                // этот период не рассчитан
                $accountLogFromToTariff->isFirst = !$isTest && ($i === 1);
                $untarificatedPeriods[] = $accountLogFromToTariff;
            }
        }

        if (count($accountLogs)) {
            // остался неизвестный период, который уже рассчитан
            // Иногда менеджеры меняются тариф задним числом. Почему - это другой вопрос. Надо решить, как это билинговать
            // Решили пока игнорировать
            printf(PHP_EOL . 'Error. There are unknown calculated accountLogSetup for accountTariffId %d: %s' . PHP_EOL, $this->id, implode(', ', array_keys($accountLogs)));
        }

        return $untarificatedPeriods;
    }

    /**
     * Вернуть даты периодов, по которым не произведен расчет абонентки
     *
     * @param AccountLogPeriod[] $accountLogs уже обработанные
     * @return AccountLogFromToTariff[]
     * @throws \LogicException
     * @throws ModelValidationException
     */
    public function getUntarificatedPeriodPeriods($accountLogs)
    {
        $untarificatedPeriods = [];
        $accountLogFromToTariffs = $this->getAccountLogFromToTariffs(null, true); // все

        // вычитанием получим необработанные
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

            $uniqueId = $accountLogFromToTariff->getUniqueId();
            if (isset($accountLogs[$uniqueId])) {
                // такой период рассчитан
                // проверим, все ли корректно
                $accountLog = $accountLogs[$uniqueId];
                $dateToTmp = $accountLogFromToTariff->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);
                if ($accountLog->date_to !== $dateToTmp) {
                    throw new \LogicException(sprintf('Error. Calculated accountLogPeriod date %s is not equal %s for accountTariffId %d', $accountLog->date_to, $dateToTmp, $this->id));
                }

                $tariffPeriodId = $accountLogFromToTariff->tariffPeriod->id;
                if ($accountLog->tariff_period_id !== $tariffPeriodId) {
                    throw new \LogicException(sprintf('Error. Calculated accountLogPeriod %s is not equal %s for accountTariffId %d', $accountLog->tariff_period_id, $tariffPeriodId, $this->id));
                }

                unset($accountLogs[$uniqueId]);

            } else {
                // этот период не рассчитан
                $untarificatedPeriods[] = $accountLogFromToTariff;
            }
        }

        if (count($accountLogs)) {
            // остался неизвестный период, который уже рассчитан
            printf(PHP_EOL . 'Error. There are unknown calculated accountLogPeriod for accountTariffId %d: %s' . PHP_EOL, $this->id, implode(', ', array_keys($accountLogs)));

            /*
                foreach ($accountLogs as $accountLog) {
                    if (!$accountLog->delete()) {
                        throw new ModelValidationException($accountLog);
                    };
                }
            */

        }

        return $untarificatedPeriods;
    }

    /**
     * Вернуть даты периодов, по которым не произведен расчет по ресурсам
     *
     * @param AccountLogResource[][] $accountLogss уже обработанные. AccountLogResource[$dateYmd][$resourceId]
     * @return AccountLogFromToTariff[][]
     */
    public function getUntarificatedResourcePeriods($accountLogss)
    {
        $untarificatedPeriodss = [];
        $chargePeriod = Period::findOne(['id' => Period::ID_DAY]);
        $accountLogFromToTariffs = $this->getAccountLogFromToTariffs($chargePeriod, false); // все

        // по всем периодам
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

            $dateYmd = $accountLogFromToTariff->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
            $tariffResources = $accountLogFromToTariff->tariffPeriod->tariff->tariffResources;

            // по всем ресурсам тарифа
            foreach ($tariffResources as $tariffResource) {

                $resourceId = $tariffResource->resource_id;
                if (array_key_exists($dateYmd, $accountLogss) && array_key_exists($resourceId, $accountLogss[$dateYmd])) {

                    // такой ресурс-период рассчитан. unset нельзя, иначе потом ресурс добавится заново от другого пересекающегося периода
                    $accountLogss[$dateYmd][$resourceId] = null;

                } else {

                    // этот ресурс-период не рассчитан
                    // если в середине месяца сменили тариф, то за этот день будет две абонентки, но ресурс надо рассчитать только один раз (по последнему тарифу), поэтому используем хэш $dateYmd
                    $untarificatedPeriodss[$dateYmd][$resourceId] = $accountLogFromToTariff;

                }
            }
        }

        if (count($accountLogss)) {
            foreach ($accountLogss as $dateYmd => $accountLogs) {
                foreach ($accountLogs as $resourceId => $accountLog) {
                    if (!$accountLog) {
                        continue;
                    }

                    // остался неизвестный период, который уже рассчитан
                    printf(PHP_EOL . 'Error. There are unknown calculated accountLogResource for accountTariffId = %d, date = %s, resource = %d' . PHP_EOL, $this->id, $dateYmd, $resourceId);
                }
            }
        }

        return $untarificatedPeriodss;
    }

    /**
     * @return string
     */
    public function getNextAccountTariffsAsString()
    {
        if ($this->nextAccountTariffs) {
            $strings = array_map(
                function (AccountTariff $nextAccountTariff) {
                    return Html::a(
                        Html::encode($nextAccountTariff->getName(false)),
                        $nextAccountTariff->getUrl()
                    );
                },
                $this->nextAccountTariffs
            );
            return implode('<br />', $strings);
        } else {
            return Yii::t('common', '(not set)');
        }
    }

    /**
     * Можно ли отменить последнюю смену тарифа
     *
     * @return bool
     */
    public function isCancelable()
    {
        if (!$this->tariff_period_id) {
            // уже закрытый
            return false;
        }

        if ($this->tariffPeriod->tariff->is_default) {
            // дефолтный нельзя отменять. Он должен отмениться автоматически при отмене базового тарифа
            return false;
        }

        $accountTariffLogs = $this->accountTariffLogs;
        $accountTariffLog = reset($accountTariffLogs);
        if (!$accountTariffLog) {
            return false;
        }

        $dateTimeNow = $this->clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента
        return $accountTariffLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * Можно ли сменить тариф или закрыть услугу
     *
     * @return bool
     */
    public function isEditable()
    {
        if (!$this->tariff_period_id) {
            // уже закрытый
            return false;
        }

        if ($this->tariffPeriod->tariff->is_default) {
            // дефолтный нельзя редактировать. Он должен закрыться автоматически при закрытии базового тарифа
            return false;
        }

        return true;
    }

    /**
     * Сгруппировать одинаковые город-тариф-пакеты по строчкам
     *
     * @param ActiveQuery $query
     * @return AccountTariff[][]
     */
    public static function getGroupedObjects(ActiveQuery $query)
    {
        $rows = [];

        /** @var AccountTariff $accountTariff */
        foreach ($query->each() as $accountTariff) {

            $hash = $accountTariff->getHash();
            !isset($rows[$hash]) && $rows[$hash] = [];
            $rows[$hash][$accountTariff->id] = $accountTariff;
        }

        return $rows;
    }

    /**
     * Вернуть хеш услуги. Нужно для группировки похожих услуг телефонии по разным номерам.
     *
     * @return string
     */
    public function getHash()
    {
        $dateTimeUtc = DateTimeZoneHelper::getUtcDateTime()
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $hashes = [];

        // город
        $hashes[] = $this->city_id;

        // лог тарифа и даты
        foreach ($this->accountTariffLogs as $accountTariffLog) {
            $hashes[] = $accountTariffLog->tariff_period_id ?: '';
            $hashes[] = $accountTariffLog->actual_from;

            if ($accountTariffLog->actual_from_utc < $dateTimeUtc) {
                // показываем только текущий. Старье не нужно
                break;
            }
        }

        // Пакет. Лог тарифа  и даты
        foreach ($this->nextAccountTariffs as $accountTariffPackage) {
            foreach ($accountTariffPackage->accountTariffLogs as $accountTariffPackageLog) {
                // лог тарифа
                $hashes[] = $accountTariffPackageLog->tariff_period_id ?: '';
                $hashes[] = $accountTariffPackageLog->actual_from;

                if ($accountTariffPackageLog->actual_from_utc < $dateTimeUtc) {
                    // показываем только текущий. Старье не нужно
                    break;
                }
            }
        }

        return md5(implode('_', $hashes));
    }

    /**
     * Вернуть дату, с которой рассчитываем лог. Если date_from строго меньше этой даты, то этот период не нужен в расчете
     * Фактически расчитываем за этот и предыдущий месяц
     * Это нужно для оптимизации, чтобы не хранить много лишних данных, которые не нужны, а только тормозят расчет новых
     *
     * @return DateTime
     */
    public static function getMinLogDatetime()
    {
        return (new DateTime())->setTime(0, 0, 0)->modify('first day of this month -6 months');
    }

    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->serviceTypeIdOld = $this->service_type_id;
    }

    /**
     * Валидировать, что нельзя менять service_type_id
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorServiceType($attribute, $params)
    {
        if (!$this->isNewRecord && $this->serviceTypeIdOld != $this->service_type_id) {
            $this->addError($attribute, 'Нельзя менять тип услуги');
            $this->errorCode = AccountTariff::ERROR_CODE_SERVICE_TYPE;
            return;
        }
    }

    /**
     * Валидировать, что задан транк для соответствующей услуги
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorTrunk($attribute, $params)
    {
        if ($this->service_type_id != ServiceType::ID_TRUNK) {
            return;
        }

        if (
            $this->isNewRecord
            && AccountTariff::find()
                ->where(
                    [
                        'client_account_id' => $this->client_account_id,
                        'service_type_id' => $this->service_type_id,
                    ]
                )
                ->count()
        ) {
            $this->addError($attribute, 'Для ЛС можно создать только одну базовую услугу транка. Зато можно добавить несколько пакетов.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_TRUNK_SINGLE;
            return;
        }

        if (!in_array($this->clientAccount->contract->business_id, [Business::OPERATOR, Business::OTT])) {
            $this->addError($attribute, 'Универсальную услугу транка можно добавить только ЛС с договором Межоператорка или ОТТ.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_TRUNK;
            return;
        }
    }

    /**
     * Валидировать, что tariff_period_id соответствует service_type_id
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorTariffPeriod($attribute, $params)
    {
        if (!$this->tariff_period_id) {
            return;
        }

        $tariffPeriod = $this->tariffPeriod;
        if (!$tariffPeriod) {
            $this->addError($attribute, 'Неправильный тариф/период ' . $this->tariff_period_id);
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_WRONG;
            return;
        }

        if ($tariffPeriod->tariff->service_type_id != $this->service_type_id) {
            $this->addError($attribute, 'Тариф/период ' . $tariffPeriod->tariff->service_type_id . ' не соответствует типу услуги ' . $this->service_type_id);
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_SERVICE_TYPE;
            return;
        }
    }

    /**
     * УУ-ЛС?
     *
     * @param int $clientAccountId
     * @return bool|null null - нет клиента, false - 4 (старый), true - 5 (УУ)
     */
    public static function isUuAccount($clientAccountId = null)
    {
        if (!$clientAccountId) {
            global $fixclient_data;
            if (isset($fixclient_data['id']) && $fixclient_data['id'] > 0) {
                $clientAccountId = (int)$fixclient_data['id'];

            }
        }

        if (!$clientAccountId) {
            return null;
        }

        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        if ($clientAccount->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            Yii::$app->session->setFlash('error', 'Неуниверсальную услугу можно добавить только ЛС, тарифицируемому неуниверсально.');
            return true;
        }

        return false;
    }

    /**
     * Дата по умолчанию для подключить/сменить/закрыть
     *
     * Подключить и закрыть - строго после этой даты.
     * Сменить тариф - с любой даты, но эта по умолчанию.
     *
     * @return string
     */
    public function getDefaultActualFrom()
    {
        if (!$this->isNewRecord && count($accountLogPeriods = $this->accountLogPeriods)) {
            // следующий после завершения оплаченного
            $accountLogPeriod = end($accountLogPeriods);
            return (new DateTime($accountLogPeriod->date_to))
                ->modify('+1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT);
        }

        // ничего не оплачено - хоть с сегодня
        return date(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * Если эта услуга активна - подключить базовый пакет. Если неактивна - закрыть все пакеты.
     *
     * @throws \Exception
     */
    public function addOrCloseDefaultPackage()
    {
        if ($this->service_type_id != ServiceType::ID_VOIP) {
            return;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if ($this->tariff_period_id) {
                // подключить базовый пакет
                $this->_addDefaultPackage();
            } else {
                // выключить все пакеты
                $this->_closeAllPackages();
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            throw $e;
        }

    }

    /**
     * Подключить базовый пакет.
     *
     * @throws \app\exceptions\ModelValidationException
     */
    private function _addDefaultPackage()
    {
        if ($this->_hasDefaultPackage()) {
            // базовый пакет уже подключен
            return;
        }

        $defaultPackage = $this->tariffPeriod->tariff->findDefaultPackage($this->city_id);
        if (!$defaultPackage) {
            Yii::error('Не найден базовый пакет для услуги ' . $this->id, 'uu');
            return;
        }

        $tariffPeriods = $defaultPackage->tariffPeriods;
        $tariffPeriod = reset($tariffPeriods);

        $accountTariffLogs = $this->accountTariffLogs;
        $accountTariffLog = end($accountTariffLogs); // базовый пакет должен быть подключен с самого начала (конца desc-списка)

        // подключить базовый пакет
        $accountTariffPackage = new AccountTariff();
        $accountTariffPackage->client_account_id = $this->client_account_id;
        $accountTariffPackage->service_type_id = ServiceType::ID_VOIP_PACKAGE;
        $accountTariffPackage->region_id = $this->region_id;
        $accountTariffPackage->city_id = $this->city_id;
        $accountTariffPackage->prev_account_tariff_id = $this->id;
        if (!$accountTariffPackage->save()) {
            throw new ModelValidationException($accountTariffPackage);
        }

        $accountTariffPackageLog = new AccountTariffLog();
        $accountTariffPackageLog->account_tariff_id = $accountTariffPackage->id;
        $accountTariffPackageLog->tariff_period_id = $tariffPeriod->id;
        $accountTariffPackageLog->actual_from_utc = $accountTariffLog->actual_from_utc;
        $accountTariffPackageLog->insert_time = $accountTariffLog->actual_from_utc; // чтобы не было лишнего списания
        if (!$accountTariffPackageLog->save()) {
            throw new ModelValidationException($accountTariffPackageLog);
        }
    }

    /**
     * Вернуть существующий базовый пакет.
     *
     * @return bool
     */
    private function _hasDefaultPackage()
    {
        $nextAccountTariffs = $this->nextAccountTariffs;
        foreach ($nextAccountTariffs as $nextAccountTariff) {

            if (!$nextAccountTariff->tariff_period_id) {
                // закрытый
                continue;
            }

            if ($nextAccountTariff->tariffPeriod->tariff->is_default) {
                return true;
            }
        }

        return null;
    }

    /**
     * Закрыть все пакеты.
     *
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
     */
    private function _closeAllPackages()
    {
        $accountTariffLogs = $this->accountTariffLogs;
        $accountTariffLog = reset($accountTariffLogs); // пакет должен быть закрыт с даты закрытия самого тарифа (то есть начала desc-списка)
        if ($accountTariffLog->tariff_period_id) {
            Yii::error('Услуга ' . $this->id . ' закрыта, хотя не должна', 'uu');
            return;
        }

        // закрыть все пакеты
        $nextAccountTariffs = $this->nextAccountTariffs;
        foreach ($nextAccountTariffs as $nextAccountTariff) {

            if (!$nextAccountTariff->tariff_period_id) {
                // уже закрыт
                continue;
            }

            $nextAccountTariffLogs = $nextAccountTariff->accountTariffLogs;
            $nextAccountTariffLog = reset($nextAccountTariffLogs);  // последняя смена тарифа (в начале desc-списка)
            if ($nextAccountTariffLog->actual_from_utc > $accountTariffLog->actual_from_utc) {
                // что-то есть в будущем - отменить и закрыть
                if (!$nextAccountTariffLog->delete()) {
                    throw new ModelValidationException($nextAccountTariffLog);
                }
            } elseif ($nextAccountTariffLog->actual_from_utc == $accountTariffLog->actual_from_utc) {
                if (!$nextAccountTariffLog->tariff_period_id) {
                    // и так должно быть закрытие. Ничего не делаем
                    continue;
                }

                // что?! смена на другой тариф?! отменить и закрыть
                if (!$nextAccountTariffLog->delete()) {
                    throw new ModelValidationException($nextAccountTariffLog);
                }
            }

            // закрыть
            $nextAccountTariffLog = new AccountTariffLog();
            $nextAccountTariffLog->account_tariff_id = $nextAccountTariff->id;
            $nextAccountTariffLog->tariff_period_id = null;
            $nextAccountTariffLog->actual_from_utc = $accountTariffLog->actual_from_utc;
            $nextAccountTariffLog->insert_time = $accountTariffLog->actual_from_utc; // чтобы не было лишнего списания
            if (!$nextAccountTariffLog->save($runValidation = false)) { // пакет не может работать без основной услуги. Поэтому закрыть и точка, что бы там проверки не говорили "уже оплачено" и прочее!
                throw new ModelValidationException($nextAccountTariffLog);
            }
        }
    }
}