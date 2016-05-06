<?php

namespace app\classes\uu\model;

use app\classes\Html;
use app\classes\uu\forms\AccountLogFromToTariff;
use app\models\City;
use app\models\ClientAccount;
use app\models\Region;
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

    public $serviceIdToDelta = [
        ServiceType::ID_VPBX => self::DELTA_VPBX,
        ServiceType::ID_VOIP => self::DELTA_VOIP,
        ServiceType::ID_VOIP_PACKAGE => self::DELTA_VOIP_PACKAGE,
    ];

    public $serviceIdToUrl = [
        ServiceType::ID_VPBX => '/pop_services.php?table=usage_virtpbx&id=%d',
        ServiceType::ID_VOIP => '/usage/voip/edit?id=%d',
        ServiceType::ID_VOIP_PACKAGE => '',
    ];

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
     *  - только те, которые не переопределены в тот же день другим тарифом
     *  - в порядке возрастания
     *  - только активные на данный момент
     *
     * @return AccountTariffLog[]
     */
    public function getUniqueAccountTariffLogs()
    {
        $accountTariffLogs = [];
        foreach ($this->accountTariffLogs as $accountTariffLog) {
            if (isset($accountTariffLogs[$accountTariffLog->actual_from])) {
                // неактивный тариф, потому что в тот же день переопределен другим
                continue;
            }
            if ($accountTariffLog->actual_from > date('Y-m-d')) {
                // еще не наступил
                continue;
            }
            $accountTariffLogs[$accountTariffLog->actual_from] = $accountTariffLog;
        }
        ksort($accountTariffLogs); // по возрастанию. Это важно для расчета периодов и цен
        return $accountTariffLogs;
    }

    /**
     * Вернуть большие периоды, разбитые только по смене тарифов
     * У последнего тарифа getDateFrom может быть null (не ограничен по времени)
     *
     * @return AccountLogFromToTariff[]
     */
    public function getAccountLogHugeFromToTariffs()
    {
        /** @var AccountLogFromToTariff[] $accountLogPeriods */
        $accountLogPeriods = [];
        $uniqueAccountTariffLogs = $this->getUniqueAccountTariffLogs();
        foreach ($uniqueAccountTariffLogs as $uniqueAccountTariffLog) {

            // начало нового периода
            $dateActualFrom = new DateTimeImmutable($uniqueAccountTariffLog->actual_from);

            if (($count = count($accountLogPeriods)) > 0) {
                // закончить предыдущий период
                $accountLogPeriods[$count - 1]->setDateTo($dateActualFrom->modify('-1 day'));
            }

            if (!$uniqueAccountTariffLog->tariffPeriod) {
                // услуга закрыта
                break;
            }

            // начать новый период
            $accountLogPeriods[] = new AccountLogFromToTariff();
            $count = count($accountLogPeriods);
            $accountLogPeriods[$count - 1]->setDateFrom($dateActualFrom);
            $accountLogPeriods[$count - 1]->setTariffPeriod($uniqueAccountTariffLog->tariffPeriod);
        }

        return $accountLogPeriods;
    }

    /**
     * Вернуть периоды, разбитые не более периода списания
     * Разбиты по логу тарифов, периодам списания, 1-м числам месяца.
     * По возможности - по периодам списания, но иногда и меньше (от подключения до первого числа, а также при переключении тарифов).
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

        // взять больший периоды, разбитые только по смене тарифов
        // и разбить по периодам списания и первым числам
        $accountLogHugePeriods = $this->getAccountLogHugeFromToTariffs();
        foreach ($accountLogHugePeriods as $accountLogHugePeriod) {

            $dateTo = $accountLogHugePeriod->getDateTo();
            if ($dateTo && $dateTo < $minLogDatetime) {
                // слишком старый. Для оптимизации считать не будем
                continue;
            }

            $tariffPeriod = $accountLogHugePeriod->getTariffPeriod();
            $chargePeriod = $chargePeriodMain ?: $tariffPeriod->chargePeriod;
            $dateFrom = $accountLogHugePeriod->getDateFrom();
            $dateToLimited = $dateTo ?:
                (new DateTimeImmutable())
                    ->modify($chargePeriod->monthscount ? 'last day of this month' : '-1 day'); // помесячно - до конца месяца, посуточно - до вчерашнего дня

            if (
                $chargePeriod->monthscount >= 1 &&
                (
                    // даты в разных месяцах, если разница больше 31 дня или месяц не совпадает
                    $dateToLimited->diff($dateFrom)->days > 31 ||
                    $dateFrom->format('m') !== $dateToLimited->format('m')
                )
            ) {
                // если период списания не менее месяца, а даты начала и конца - в разных месяцах, то разбить по первым числам месяца
                $accountLogPeriod = new AccountLogFromToTariff();
                $accountLogPeriod->setTariffPeriod($tariffPeriod);
                $accountLogPeriod->setDateFrom($dateFrom);

                // следующий период будет начинаться 1 числа следующего месяца
                $dateFrom = $dateFrom->setDate($dateFrom->format('Y'), 1 + (int)$dateFrom->format('m'), 1);
                $accountLogPeriod->setDateTo($dateFrom->modify('-1 day'));

                if ($accountLogPeriod->getDateTo() >= $minLogDatetime) {
                    // Для оптимизации считаем только нестарые
                    $accountLogPeriods[] = $accountLogPeriod;
                }
            }

            do {
                // начать новый период
                $accountLogPeriod = new AccountLogFromToTariff();
                $accountLogPeriod->setTariffPeriod($tariffPeriod);
                $accountLogPeriod->setDateFrom($dateFrom);

                // следующий период будет начинаться через заданный период
                $dateFrom = $dateFrom->modify($chargePeriod->getModify());
                $accountLogPeriod->setDateTo(min($dateFrom->modify('-1 day'), $dateToLimited));

                if ($accountLogPeriod->getDateTo() >= $minLogDatetime) {
                    // Для оптимизации считаем только нестарые
                    $accountLogPeriods[] = $accountLogPeriod;
                }
            } while ($dateFrom < $dateToLimited);

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

        // вычитанием получим необработанные
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

            $dateFrom = $accountLogFromToTariff->getDateFrom();
            if ($dateFrom < $minLogDatetime) {
                // слишком старый. Для оптимизации считать не будем
                continue;
            }

            $dateFromYmd = $dateFrom->format('Y-m-d');
            if (isset($accountLogs[$dateFromYmd])) {
                unset($accountLogs[$dateFromYmd]);
            } else {
                // этот период не рассчитан
                $untarificatedPeriods[] = $accountLogFromToTariff;
            }
        }

        if (count($accountLogs)) {
            // остался неизвестный период, который уже рассчитан
            throw new RangeException(sprintf('There are unknown calculated accountLogSetup: %s',
                implode(', ', array_keys($accountLogs))));
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

            $dateFrom = $accountLogFromToTariff->getDateFrom();
            $dateFromYmd = $dateFrom->format('Y-m-d');
            if (isset($accountLogs[$dateFromYmd])) {
                // такой период рассчитан
                // проверим, все ли корректно
                $accountLog = $accountLogs[$dateFromYmd];
                $dateToTmp = $accountLogFromToTariff->getDateTo()->format('Y-m-d');
                if ($accountLog->date_to !== $dateToTmp) {
                    throw new RangeException(sprintf('Calculated accountLogPeriod date %s is not equal %s',
                        $accountLog->date_to, $dateToTmp));
                }

                $tariffPeriodId = $accountLogFromToTariff->getTariffPeriod()->id;
                if ($accountLog->tariff_period_id !== $tariffPeriodId) {
                    throw new RangeException(sprintf('Calculated accountLogPeriod %s is not equal %s',
                        $accountLog->tariff_period_id, $tariffPeriodId));
                }
                unset($accountLogs[$dateFromYmd]);
            } else {
                // этот период не рассчитан
                $untarificatedPeriods[] = $accountLogFromToTariff;
            }
        }

        if (count($accountLogs)) {
            // остался неизвестный период, который уже рассчитан
            throw new RangeException(sprintf('There are unknown calculated accountLogPeriod: %s',
                implode(', ', array_keys($accountLogs))));
        }

        return $untarificatedPeriods;
    }

    /**
     * Вернуть даты периодов, по которым не произведен расчет по ресурсам
     *
     * @param AccountLogPeriod[] $accountLogClassName уже обработанные
     * @return AccountLogFromToTariff[]
     */
    public function getUntarificatedResourcePeriods($accountLogs)
    {
        $untarificatedPeriods = [];
        $chargePeriod = Period::findOne(['id' => Period::ID_DAY]);
        $accountLogFromToTariffs = $this->getAccountLogFromToTariffs($chargePeriod, false); // все

        // вычитанием получим необработанные
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {
            $dateFromYmd = $accountLogFromToTariff->getDateFrom()->format('Y-m-d');
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
            throw new RangeException(sprintf('There are unknown calculated accountLogResource: %s',
                implode(', ', array_keys($accountLogs))));
        }

        return $untarificatedPeriods;
    }

    /**
     * Вернуть ID неуниверсальной услуги
     * @return int
     */
    public function getNonUniversalId()
    {
        return $this->id - $this->serviceIdToDelta[$this->service_type_id];
    }

    /**
     * Вернуть html-ссылку на неуниверсальную услугу
     * @return string
     */
    public function getNonUniversalUrl()
    {
        $url = $this->serviceIdToUrl[$this->service_type_id];
        if (!$url) {
            return '';
        }

        $id = $this->getNonUniversalId();
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
        return strtotime(reset($accountTariffLogs)->actual_from) >= time();
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

            $hashes = [];

            // город
            $hashes[] = $accountTariff->city_id;

            // лог тарифа и даты
            foreach ($accountTariff->accountTariffLogs as $accountTariffLog) {
                $hashes[] = $accountTariffLog->tariff_period_id ?: '';
                $hashes[] = $accountTariffLog->actual_from;

                if (strtotime($accountTariffLog->actual_from) < time()) {
                    // показываем только текущий. Старье не нужно
                    break;
                }
            }

            // Пакет. Лог тарифа  и даты
            foreach ($accountTariff->nextAccountTariffs as $accountTariffPackage) {
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

            $hash = md5(implode('_', $hashes));
            !isset($rows[$hash]) && $rows[$hash] = [];
            $rows[$hash][$accountTariff->id] = $accountTariff;
        }
        return $rows;
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
}