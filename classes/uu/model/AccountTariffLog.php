<?php

namespace app\classes\uu\model;

use app\classes\behaviors\uu\AccountTariffBiller;
use app\classes\Html;
use app\classes\uu\forms\AccountLogFromToTariff;
use app\classes\uu\tarificator\AccountLogPeriodTarificator;
use app\classes\uu\tarificator\AccountLogSetupTarificator;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Лог тарифов универсальной услуги
 *
 * @property int $id
 * @property int $account_tariff_id
 * @property int $tariff_period_id если null, то закрыто
 * @property string $actual_from_utc
 *
 * @property TariffPeriod $tariffPeriod
 * @property AccountTariff $accountTariff
 * @property string $actual_from
 */
class AccountTariffLog extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits {
        attributeLabels as attributeLabelsFromTrait;
    }

    // Методы для полей insert_time, insert_user_id
    use \app\classes\traits\InsertUserTrait;

    public $tariffPeriodFieldName = '';

    protected $countLogs = null;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['account_tariff_id', 'tariff_period_id'], 'integer'],
            [['account_tariff_id'], 'required'],
            ['actual_from', 'date', 'format' => 'php:' . DateTimeZoneHelper::DATE_FORMAT],
            ['actual_from', 'validatorFuture', 'skipOnEmpty' => false],
            ['actual_from', 'validatorPackage', 'skipOnEmpty' => false],
            ['tariff_period_id', 'validatorCreateNotClose', 'skipOnEmpty' => false],
            ['id', 'validatorBalance', 'skipOnEmpty' => false],
        ];
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        $attributeLabels = $this->attributeLabelsFromTrait();
        $this->tariffPeriodFieldName && $attributeLabels['tariff_period_id'] = $this->tariffPeriodFieldName;
        return $attributeLabels;
    }

    /**
     * @return []
     */
    public function behaviors()
    {
        return [
            'AccountTariffBiller' => AccountTariffBiller::className(), // Пересчитать транзакции, проводки и счета
        ];
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
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_tariff_id']);
    }

    /**
     * Вернуть сгенерированное имя
     * @return string
     */
    public function getName()
    {
        return $this->tariffPeriod ?
            $this->tariffPeriod->getName() :
            Yii::t('common', 'Switched off');
    }

    /**
     * Вернуть html: имя + ссылка на тариф
     * @return string
     */
    public function getTariffPeriodLink()
    {
        return $this->tariff_period_id ?
            Html::a(
                Html::encode($this->getName()),
                $this->tariffPeriod->getUrl()
            ) :
            Yii::t('common', 'Switched off');
    }

    /**
     * Валидировать дату смены тарифа
     * @param string $attribute
     * @param [] $params
     */
    public function validatorFuture($attribute, $params)
    {
        Yii::info('AccountTariffLog. Before validatorFuture', 'uu');

        if (!$this->isNewRecord) {
            return;
        }

        $clientAccount = $this->accountTariff->clientAccount;
        if (!$clientAccount) {
            $this->addError($attribute, 'ЛС не указан.');
            return;
        }

        $currentDateTimeUtc = $clientAccount
            ->getDatetimeWithTimezone()
            ->setTime(0, 0, 0)
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        !$this->actual_from && $this->actual_from = $currentDateTimeUtc;

        if ($this->actual_from < $currentDateTimeUtc) {
            $this->addError($attribute, 'Нельзя менять тариф задним числом.');
        }

        if ($this->actual_from_utc == $currentDateTimeUtc && self::find()
                ->where(['account_tariff_id' => $this->account_tariff_id])
                ->andWhere(['=', 'actual_from_utc', $currentDateTimeUtc])
                ->count()
        ) {
            $this->addError($attribute, 'Сегодня тариф уже меняли. Теперь можно сменить его не ранее завтрашнего дня.');
        }

        if (self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->andWhere(['>', 'actual_from_utc', $currentDateTimeUtc])
            ->count()
        ) {
            $this->addError($attribute, 'Уже назначена смена тарифа в будущем. Если вы хотите установить новый тариф - сначала отмените эту смену.');
        }

        Yii::info('AccountTariffLog. After validatorFuture', 'uu');
    }

    /**
     * Валидировать, что при создании сразу же не закрытый
     * @param string $attribute
     * @param [] $params
     */
    public function validatorCreateNotClose($attribute, $params)
    {
        Yii::info('AccountTariffLog. Before validatorCreateNotClose', 'uu');

        if (!$this->tariff_period_id && !$this->getCountLogs()) {
            $this->addError($attribute, 'Не указан тариф/период.');
        }

        Yii::info('AccountTariffLog. After validatorCreateNotClose', 'uu');
    }

    /**
     * Вернуть кол-во предыдущих логов
     */
    protected function getCountLogs()
    {
        if (!is_null($this->countLogs)) {
            return $this->countLogs;
        }

        return $this->countLogs = self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->count();
    }

    /**
     * Вернуть уникальный Id
     * Поле id хоть и уникальное, но не подходит для поиска нерассчитанных данных при тарификации
     * @return string
     */
    public function getUniqueId()
    {
        return $this->actual_from . '_' . $this->tariff_period_id;
    }

    /**
     * Валидировать, что realtime balance больше обязательного платежа по услуге (подключение + абонентская плата + минимальная плата за ресурсы)
     * В логе, а не услуге, потому что нужна дата включения
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorBalance($attribute, $params)
    {
        Yii::info('AccountTariffLog. Before validatorBalance', 'uu');

        if (!$this->isNewRecord) {
            return;
        }

        $accountTariff = $this->accountTariff;
        if (!$accountTariff) {
            $this->addError($attribute, 'Услуга не указана.');
            return;
        }

        $clientAccount = $accountTariff->clientAccount;
        if (!$clientAccount) {
            $this->addError($attribute, 'ЛС не указан.');
            return;
        }

        if ($clientAccount->account_version != ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $this->addError($attribute, 'Универсальную услугу можно добавить только ЛС, тарифицируемому универсально.');
            // а конвертация из неуниверсальных услуг идет с помощью SQL и сюда не попадает
            return;
        }

        $tariffPeriod = $this->tariffPeriod;
        if ($tariffPeriod && $tariffPeriod->tariff->currency_id != $clientAccount->currency) {
            $this->addError($attribute,
                sprintf('Валюта акаунта %s и тарифа %s не совпадают.', $clientAccount->currency, $tariffPeriod->tariff->currency_id)
            );
        }

        $credit = $clientAccount->credit; // кредитный лимит
        $realtimeBalance = $clientAccount->balance; // $clientAccount->billingCounters->getRealtimeBalance()
        $realtimeBalanceWithCredit = $realtimeBalance + $credit;

        $warnings = $clientAccount->getVoipWarnings();
        $isCountLogs = $this->getCountLogs(); // смена тарифа или закрытие услуги

        if (!$tariffPeriod) {
            if ($isCountLogs) {
                // закрытие услуги
                return;
            } else {
                // подключение новой услуги
                $this->addError($attribute, 'Не указан тариф/период.');
            }
        }

        if ($clientAccount->is_blocked) {
            $error = 'ЛС заблокирован';
            if ($isCountLogs) {
                // смена тарифа или закрытие услуги
                $this->shiftActualFrom($error);
            } else {
                // подключение новой услуги
                $this->addError($attribute, $error);
            }
            return;
        }

        if (isset($warnings[ClientAccount::WARNING_OVERRAN])) {
            $error = 'ЛС заблокирован из-за превышения лимитов';
            if ($isCountLogs) {
                // смена тарифа или закрытие услуги
                $this->shiftActualFrom($error);
            } else {
                // подключение новой услуги
                $this->addError($attribute, $error);
            }
            return;
        }

        if ($realtimeBalanceWithCredit < 0 || isset($warnings[ClientAccount::WARNING_FINANCE]) || isset($warnings[ClientAccount::WARNING_CREDIT])) {
            $error = sprintf('ЛС находится в финансовой блокировке. На счету %.2f %s и кредит %.2f %s', $realtimeBalance, $clientAccount->currency, $credit, $clientAccount->currency);
            if ($isCountLogs) {
                // смена тарифа или закрытие услуги
                $this->shiftActualFrom($error);
            } else {
                // подключение новой услуги
                $this->addError($attribute, $error);
            }
            return;
        }

        $accountLogFromToTariff = new AccountLogFromToTariff();
        $accountLogFromToTariff->dateFrom = new DateTimeImmutable($this->actual_from);
        $accountLogFromToTariff->dateTo = $tariffPeriod->chargePeriod->getMinDateTo($accountLogFromToTariff->dateFrom);
        $accountLogFromToTariff->tariffPeriod = $tariffPeriod;
        $accountLogFromToTariff->isFirst = true;

        // AccountLogSetupTarificator и AccountLogPeriodTarificator сейчас нельзя вызвать, потому что записи в логе тарифов еще нет
        // AccountLogResourceTarificator пока нет
        $accountLogSetup = (new AccountLogSetupTarificator())->getAccountLogSetup($accountTariff, $accountLogFromToTariff);
        if ($isCountLogs) {
            // смена тарифа или закрытие услуги
            // плата за номер не взимается
            $accountLogSetup->price = max(0, $accountLogSetup->price - $accountLogSetup->price_number);
            $accountLogSetup->price_number = 0;
        }
        $accountLogPeriod = (new AccountLogPeriodTarificator())->getAccountLogPeriod($accountTariff, $accountLogFromToTariff);
        $priceMin = $tariffPeriod->price_min * $accountLogPeriod->coefficient;
        $tariffPrice = $accountLogSetup->price + $accountLogPeriod->price + $priceMin;

        if ($realtimeBalanceWithCredit < $tariffPrice) {
            $error = sprintf('На ЛС %.2f %s и кредит %.2f %s, что меньше первичного платежа по тарифу, который составляет %.2f %s (подключение %.2f %s + абонентская плата %.2f %s до конца периода + минимальная стоимость ресурсов %.2f %s)',
                $realtimeBalance, $clientAccount->currency,
                $credit, $clientAccount->currency,
                $tariffPrice, $clientAccount->currency,
                $accountLogSetup->price, $clientAccount->currency,
                $accountLogPeriod->price, $clientAccount->currency,
                $priceMin, $clientAccount->currency
            );
            if ($isCountLogs) {
                // смена тарифа или закрытие услуги
                $this->shiftActualFrom($error);
            } else {
                // подключение новой услуги
                $this->addError($attribute, $error);
            }
            return;
        }

        // все хорошо - денег хватает
        // на самом деле мы не знаем, сколько клиент уже потратил на звонки сегодня. Но это дело низкоуровневого биллинга. Если денег не хватит - заблокирует финансово
        // транзакции не сохраняем, деньги пока не списываем. Подробнее см. AccountTariffBiller

        Yii::info('AccountTariffLog. After validatorBalance', 'uu');
    }

    /**
     * Сдвинуть actual_from на завтра
     */
    protected function shiftActualFrom($error)
    {
        $accountTariff = $this->accountTariff;
        $clientAccount = $accountTariff->clientAccount;
        $datimeNow = $clientAccount->getDatetimeWithTimezone();
        if ($datimeNow->format(DateTimeZoneHelper::DATE_FORMAT) == $this->actual_from) {
            // с сегодня откладываем на завтра
            $this->actual_from = $datimeNow->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT);
            Yii::$app->session->setFlash('error', $error . '. Дата включения сдвинута на завтра.');
        }

    }

    /**
     * Валидировать, что дата включения пакета не раньше даты включения услуги
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorPackage($attribute, $params)
    {
        Yii::info('AccountTariffLog. Before validatorPackage', 'uu');

        $isNewRecord = !$this->getCountLogs();
        if (!$isNewRecord) {
            // смена тарифа или закрытие услуги. А все последующие проверки только при создании услуги
            return;
        }

        $accountTariff = $this->accountTariff;
        if ($accountTariff->service_type_id != ServiceType::ID_VOIP_PACKAGE) {
            // не пакет телефонии
            return;
        }

        $prevAccountTariff = $accountTariff->prevAccountTariff;
        if (!$prevAccountTariff) {
            $this->addError($attribute, 'Не указана основная услуга телефонии для пакета телефонии');
            return;
        }

        $prevAccountTariffLogs = $prevAccountTariff->accountTariffLogs;
        if (count($prevAccountTariffLogs) > 1) {
            // основная услуга уже действует
            return;
        }

        $prevAccountTariffLog = reset($prevAccountTariffLogs);
        if ($prevAccountTariffLog->actual_from > $this->actual_from) {
            $this->addError($attribute, sprintf('Пакет телефонии может начать действовать (%s) только после начала действия (%s) основной услуги телефонии', $this->actual_from, $prevAccountTariffLog->actual_from));
            return;
        }

        Yii::info('AccountTariffLog. After validatorPackage', 'uu');
    }

    /**
     * Установить actual_from из date в таймзоне клиента в datetime UTC
     * Для совместимости, ибо старое поле actual_from удалено
     * @param string $date
     */
    public function setActual_from($date)
    {
        $this->actual_from_utc = $this->getClientDateTime($date)
            ->setTime(0, 0, 0)
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

    }

    /**
     * Вернуть actual_from в виде date в таймзоне клиента, а не datetime UTC
     * Для совместимости, ибо старое поле actual_from удалено
     * @return string
     */
    public function getActual_from()
    {
        return (new DateTime($this->actual_from_utc, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->setTimezone($this->getClientTimeZone())
            ->format(DateTimeZoneHelper::DATE_FORMAT);

    }

    /**
     * Вернуть DateTime в таймзоне клиента
     * @param string $date в таймзоне клиента
     * @return DateTimeImmutable
     */
    public function getClientDateTime($date = 'now')
    {
        return new DateTimeImmutable($date, $this->getClientTimeZone());
    }

    /**
     * Вернуть DateTimeZone клиента
     * @return DateTimeZone
     */
    public function getClientTimeZone()
    {
        if ($this->accountTariff && $this->accountTariff->clientAccount) {
            return $this->accountTariff->clientAccount->getTimezone();
        } else {
            return new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
        }
    }
}