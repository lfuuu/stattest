<?php

namespace app\classes\uu\model;

use app\classes\Html;
use app\classes\uu\forms\AccountLogFromToTariff;
use app\models\City;
use app\models\ClientAccount;
use app\models\Region;
use app\models\usages\UsageInterface;
use DateTime;
use DateTimeImmutable;
use RangeException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
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
class AccountTariff extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

    // на сколько сдвинуть id при конвертации
    const DELTA_VPBX = 0;
    const DELTA_VOIP = 10000;
    const DELTA_VOIP_PACKAGE = 50000;

    const DELTA_INTERNET = 51000;
    const DELTA_COLLOCATION = 51000;
    const DELTA_VPN = 51000;

    const DELTA_IT_PARK = 70000;
    const DELTA_DOMAIN = 70000;
    const DELTA_MAILSERVER = 70000;
    const DELTA_ATS = 70000;
    const DELTA_SITE = 70000;
    const DELTA_USPD = 70000;
    const DELTA_WELLSYSTEM = 70000;
    const DELTA_WELLTIME_PRODUCT = 70000;
    const DELTA_EXTRA = 70000;
    const DELTA_SMS_GATE = 70000;

    const DELTA_SMS = 80000;

    const DELTA_WELLTIME_SAAS = 90000;

    const DELTA_CALL_CHAT = 95000;

    const DELTA = 100000;

    public $serviceIdToDelta = [
        ServiceType::ID_VPBX => self::DELTA_VPBX,
        ServiceType::ID_VOIP => self::DELTA_VOIP,
        ServiceType::ID_VOIP_PACKAGE => self::DELTA_VOIP_PACKAGE,

        ServiceType::ID_INTERNET => self::DELTA_INTERNET,
        ServiceType::ID_COLLOCATION => self::DELTA_COLLOCATION,
        ServiceType::ID_VPN => self::DELTA_VPN,

        ServiceType::ID_IT_PARK => self::DELTA_IT_PARK,
        ServiceType::ID_DOMAIN => self::DELTA_DOMAIN,
        ServiceType::ID_MAILSERVER => self::DELTA_MAILSERVER,
        ServiceType::ID_ATS => self::DELTA_ATS,
        ServiceType::ID_SITE => self::DELTA_SITE,
        ServiceType::ID_USPD => self::DELTA_USPD,
        ServiceType::ID_WELLSYSTEM => self::DELTA_WELLSYSTEM,
        ServiceType::ID_WELLTIME_PRODUCT => self::DELTA_WELLTIME_PRODUCT,
        ServiceType::ID_EXTRA => self::DELTA_EXTRA,
        ServiceType::ID_SMS_GATE => self::DELTA_SMS_GATE,

        ServiceType::ID_SMS => self::DELTA_SMS,

        ServiceType::ID_WELLTIME_SAAS => self::DELTA_WELLTIME_SAAS,

        ServiceType::ID_CALL_CHAT => self::DELTA_CALL_CHAT,
    ];

    public $serviceIdToUrl = [
        ServiceType::ID_VPBX => '/pop_services.php?table=usage_virtpbx&id=%d',
        ServiceType::ID_VOIP => '/usage/voip/edit?id=%d',
        ServiceType::ID_VOIP_PACKAGE => '',

        ServiceType::ID_INTERNET => '/pop_services.php?table=usage_ip_ports&id=%d',
        ServiceType::ID_COLLOCATION => '/pop_services.php?table=usage_ip_ports&id=%d',
        ServiceType::ID_VPN => '/pop_services.php?table=usage_ip_ports&id=%d',

        ServiceType::ID_IT_PARK => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_DOMAIN => '/pop_services.php?table=usage_extra&id=%d', // /pop_services.php?id=%d&table=domains
        ServiceType::ID_MAILSERVER => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_ATS => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_SITE => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_USPD => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_WELLSYSTEM => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_WELLTIME_PRODUCT => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_EXTRA => '/pop_services.php?table=usage_extra&id=%d',
        ServiceType::ID_SMS_GATE => '/pop_services.php?table=usage_extra&id=%d',

        ServiceType::ID_SMS => '/pop_services.php?table=usage_sms&id=%d',

        ServiceType::ID_WELLTIME_SAAS => '/pop_services.php?table=usage_welltime&id=%d',

        ServiceType::ID_CALL_CHAT => '/usage/call-chat/edit?id=%d',
    ];

    /** @var int */
    protected $serviceTypeIdOld = null;

    public static function tableName()
    {
        return 'uu_account_tariff';
    }

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
                    'tariff_period_id'
                ],
                'integer'
            ],
            [['comment'], 'string'],
            ['voip_number', 'match', 'pattern' => '/^\d{4,15}$/'],
            ['service_type_id', 'validatorServiceType'],
            ['tariff_period_id', 'validatorTariffPeriod'],
        ];
    }

    /**
     * @return string
     */
    public function getName($isWithAccount = true)
    {
        $tariffPeriodName = $this->tariff_period_id ? $this->tariffPeriod->getName() : Yii::t('common', 'Switched off');
        return $isWithAccount ?
            sprintf('%s %s', $this->clientAccount->client, $tariffPeriodName) :
            $tariffPeriodName;
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
     * @param bool $isWithAccount
     * @return string
     */
    public function getAccountTariffLink($isWithAccount = true)
    {
        return $this->getLink($isWithAccount, false);
    }

    /**
     * Вернуть html: имя + ссылка на тариф
     * @param bool $isWithAccount
     * @return string
     */
    public function getTariffPeriodLink($isWithAccount = true)
    {
        return $this->getLink($isWithAccount, true);
    }

    /**
     * Вернуть html: имя + ссылка на тариф
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
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogPeriods()
    {
        return $this->hasMany(AccountLogPeriod::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogResources()
    {
        return $this->hasMany(AccountLogResource::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffLogs()
    {
        return $this->hasMany(AccountTariffLog::className(), ['account_tariff_id' => 'id'])
            ->orderBy(['actual_from' => SORT_DESC, 'id' => SORT_DESC])
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
        foreach ($this->accountTariffLogs as $accountTariffLog) {
            if ($accountTariffLogPrev &&
                $accountTariffLogPrev->actual_from == $accountTariffLog->actual_from &&
                $accountTariffLogPrev->actual_from > $accountTariffLogPrev->insert_time // строго раньше наступления даты @todo таймзона клиента
            ) {
                // неактивный тариф, потому что переопределен другим до наступления этой даты
                // если переопределен в тот же день, то списываем за оба
                continue;
            }
            if (!$isWithFuture && $accountTariffLog->actual_from > date('Y-m-d')) {
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
     * @return \app\classes\uu\forms\AccountLogFromToTariff[]
     */
    public function getAccountLogHugeFromToTariffs($isWithFuture = false)
    {
        /** @var AccountLogFromToTariff[] $accountLogPeriods */
        $accountLogPeriods = [];
        $uniqueAccountTariffLogs = $this->getUniqueAccountTariffLogs($isWithFuture);
        foreach ($uniqueAccountTariffLogs as $uniqueAccountTariffLog) {

            // начало нового периода
            $dateActualFrom = new DateTimeImmutable($uniqueAccountTariffLog->actual_from);

            if (($count = count($accountLogPeriods)) > 0) {

                // закончить предыдущий период
                $prevAccountTariffLog = $accountLogPeriods[$count - 1];
                $prevTariffPeriodChargePeriod = $prevAccountTariffLog->tariffPeriod->chargePeriod;

                // старый тариф должен закончиться не раньше этой даты
                $dateActualFromYmd = $dateActualFrom->format('Y-m-d');
                $insertTimeYmd = (new DateTimeImmutable($uniqueAccountTariffLog->insert_time))->format('Y-m-d');
                if ($dateActualFromYmd < $insertTimeYmd) {
                    $insertTimeYmd = UsageInterface::MIN_DATE; // ну, надо же хоть как-нибудь посчитать этот идиотизм, когда тариф меняют задним числом
//                    throw new \LogicException('Тариф нельзя менять задним числом: ' . $uniqueAccountTariffLog->id);
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
            $accountLogPeriods[$count - 1]->dateFrom = $dateActualFrom;
            $accountLogPeriods[$count - 1]->tariffPeriod = $uniqueAccountTariffLog->tariffPeriod;
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
        $accountLogHugePeriods = $this->getAccountLogHugeFromToTariffs();
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
                // текущий день по таймзоне аккаунта
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

            } while ($dateFrom->format('Y-m-d') <= $dateToLimited->format('Y-m-d'));

        }

        if (!$isWithCurrent &&
            ($count = count($accountLogPeriods)) &&
            !$dateTo && $dateFrom > (new DateTimeImmutable())
        ) {
            // если count, то $dateTo и $dateFrom определены
            // если тариф действующий (!$dateTo) и следующий должен начаться не сегодня ($dateFrom > (new DateTimeImmutable()))
            //      значит, последний период еще длится - удалить из расчета
            unset($accountLogPeriods[$count - 1]);
            return $accountLogPeriods;
        }

        return $accountLogPeriods;
    }

    /**
     * @param DateTime $dateTime
     * @return AccountTariffLog
     */
    public function getActiveAccountTariffLog($dateTime = null)
    {
        if (!$dateTime) {
            $dateTime = new DateTime();
        }
        return $this->hasMany(AccountTariffLog::className(), ['account_tariff_id' => 'id'])
            ->where('actual_from <= :actual_from', [':actual_from' => $dateTime->format('Y-m-d')])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
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
            foreach ($accountLogs as $accountLog) {
                $accountLog->delete();
            }
        }

        return $untarificatedPeriods;
    }

    /**
     * Вернуть даты периодов, по которым не произведен расчет абонентки
     *
     * @param AccountLogPeriod[] $accountLogClassName уже обработанные
     * @return AccountLogFromToTariff[]
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
                $dateToTmp = $accountLogFromToTariff->dateTo->format('Y-m-d');
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
            // Иногда менеджеры меняются тариф задним числом. Почему - это другой вопрос. Надо решить, как это билинговать
            // Решили пока игнорировать
            printf(PHP_EOL . 'Error. There are unknown calculated accountLogPeriod for accountTariffId %d: %s' . PHP_EOL, $this->id, implode(', ', array_keys($accountLogs)));
            foreach ($accountLogs as $accountLog) {
                $accountLog->delete();
            }
        }

        return $untarificatedPeriods;
    }

    /**
     * Вернуть даты периодов, по которым не произведен расчет по ресурсам
     *
     * @param AccountLogResource[] $accountLogs уже обработанные
     * @return AccountLogFromToTariff[]
     */
    public function getUntarificatedResourcePeriods($accountLogs)
    {
        $untarificatedPeriods = [];
        $chargePeriod = Period::findOne(['id' => Period::ID_DAY]);
        $accountLogFromToTariffs = $this->getAccountLogFromToTariffs($chargePeriod, false); // все

        // вычитанием получим необработанные
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {
            $dateFromYmd = $accountLogFromToTariff->dateFrom->format('Y-m-d');
            if (isset($accountLogs[$dateFromYmd])) {
                // такой период рассчитан
                unset($accountLogs[$dateFromYmd]);
            } else {
                // этот период не рассчитан
                $untarificatedPeriods[] = $accountLogFromToTariff;
            }
        }

        if (count($accountLogs)) {
            // остался неизвестный период, который уже рассчитан
            // Иногда менеджеры меняются тариф задним числом. Почему - это другой вопрос. Надо решить, как это билинговать
            // Решили пока игнорировать
            printf(PHP_EOL . 'Error. There are unknown calculated accountLogResource for accountTariffId %d: %s' . PHP_EOL, $this->id, implode(', ', array_keys($accountLogs)));
            foreach ($accountLogs as $accountLog) {
                $accountLog->delete();
            }
        }

        return $untarificatedPeriods;
    }

    /**
     * Вернуть ID неуниверсальной услуги
     * @return int
     */
    public function getNonUniversalId()
    {
        if ($this->id && $this->id < self::DELTA) {
            return $this->id - $this->serviceIdToDelta[$this->service_type_id];
        } else {
            return null;
        }
    }

    /**
     * Вернуть html-ссылку на неуниверсальную услугу
     * @return string
     */
    public function getNonUniversalUrl()
    {
        $id = $this->getNonUniversalId();
        if (!$id) {
            return '';
        }

        $url = $this->serviceIdToUrl[$this->service_type_id];
        if (!$url) {
            return $id;
        }

        return Html::a($id, sprintf($url, $id));
    }

    /**
     * @return string
     */
    public function getNextAccountTariffsAsString()
    {
        if ($this->nextAccountTariffs) {
            $strings = array_map(function (AccountTariff $nextAccountTariff) {
                return Html::a(
                    Html::encode($nextAccountTariff->getName(false)),
                    $nextAccountTariff->getUrl()
                );
            }, $this->nextAccountTariffs);
            return implode('<br />', $strings);
        } else {
            return Yii::t('common', '(not set)');
        }
    }

    /**
     * Можно ли отменить последнюю смену тарифа
     * @return bool
     */
    public function isCancelable()
    {
        $accountTariffLogs = $this->accountTariffLogs;
        $accountTariffLog = reset($accountTariffLogs);
        if (!$accountTariffLog) {
            return false;
        }

        $dateTimeNow = $this->clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента
        return $accountTariffLog->actual_from > $dateTimeNow->format('Y-m-d');
    }

    /**
     * сгруппировать одинаковые город-тариф-пакеты по строчкам
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
     * @return string
     */
    public function getHash()
    {
        $hashes = [];

        // город
        $hashes[] = $this->city_id;

        // лог тарифа и даты
        foreach ($this->accountTariffLogs as $accountTariffLog) {
            $hashes[] = $accountTariffLog->tariff_period_id ?: '';
            $hashes[] = $accountTariffLog->actual_from;

            if (strtotime($accountTariffLog->actual_from) < time()) {
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

                if (strtotime($accountTariffPackageLog->actual_from) < time()) {
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
     * @return DateTime
     */
    public static function getMinLogDatetime()
    {
        return (new DateTime())->setTime(0, 0, 0)->modify('first day of this month -1 months');
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
     * Валидировать, что tariff_period_id соответствует service_type_id
     * @param string $attribute
     * @param [] $params
     */
    public function validatorServiceType($attribute, $params)
    {
        if (!$this->isNewRecord && $this->serviceTypeIdOld != $this->service_type_id) {
            $this->addError($attribute, 'Нельзя менять service_type_id');
            return;
        }
    }

    /**
     * Валидировать, что tariff_period_id соответствует service_type_id
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
            $this->addError($attribute, 'Неправильный tariff_period_id');
            return;
        }
        if ($tariffPeriod->tariff->service_type_id != $this->service_type_id) {
            $this->addError($attribute, 'Tariff_period_id не соответствует service_type_id');
            return;
        }
    }
}