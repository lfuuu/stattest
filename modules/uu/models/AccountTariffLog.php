<?php

namespace app\modules\uu\models;

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\uu\behaviors\AccountTariffBiller;
use app\modules\uu\behaviors\FillAccountTariffResourceLog;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\tarificator\AccountLogPeriodTarificator;
use app\modules\uu\tarificator\AccountLogSetupTarificator;
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
    use \app\classes\traits\AttributeLabelsTraits;

    // Методы для полей insert_time, insert_user_id
    use \app\classes\traits\InsertUserTrait;

    protected $countLogs = null;

    /** @var int Код ошибки для АПИ */
    public $errorCode = null;

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
            ['tariff_period_id', 'validatorDoublePackage'],
            ['tariff_period_id', 'validatorDefaultPackage'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'AccountTariffBiller' => AccountTariffBiller::className(), // Пересчитать транзакции, проводки и счета
            'FillAccountTariffResourceLog' => FillAccountTariffResourceLog::className(), // Создать лог ресурсов при создании услуги. Удалить при удалении
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
     *
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
     *
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
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorFuture($attribute, $params)
    {
        Yii::info('AccountTariffLog. Before validatorFuture', 'uu');

        if (!$this->isNewRecord) {
            return;
        }

        $accountTariff = $this->accountTariff;
        $clientAccount = $accountTariff->clientAccount;
        if (!$clientAccount) {
            $this->addError($attribute, 'ЛС не указан.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_EMPTY;
            return;
        }

        $currentDateTimeUtc = $clientAccount
            ->getDatetimeWithTimezone()
            ->setTime(0, 0, 0)
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if ($this->actual_from_utc < $currentDateTimeUtc) {
            $this->addError($attribute, 'Нельзя менять тариф задним числом.');
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_PREV;
            return;
        }

        if (
            $this->actual_from_utc == $currentDateTimeUtc
            && self::find()
                ->where(['account_tariff_id' => $this->account_tariff_id])
                ->andWhere(['=', 'actual_from_utc', $currentDateTimeUtc])
                ->count()
        ) {
            $this->addError($attribute, 'Сегодня тариф уже меняли. Теперь можно сменить его не ранее завтрашнего дня.');
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_TODAY;
            return;
        }

        if (self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->andWhere(['>', 'actual_from_utc', $currentDateTimeUtc])
            ->count()
        ) {
            $this->addError($attribute, 'Уже назначена смена тарифа в будущем. Если вы хотите установить новый тариф - сначала отмените эту смену.');
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_FUTURE;
            return;
        }

        if (!$this->tariff_period_id && $this->actual_from < ($minEditDate = $accountTariff->getDefaultActualFrom())) {
            $this->addError($attribute, 'Закрыть можно, начиная с ' . $minEditDate);
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_PAID;
            return;
        }

        Yii::info('AccountTariffLog. After validatorFuture', 'uu');
    }

    /**
     * Валидировать, что при создании сразу же не закрытый
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorCreateNotClose($attribute, $params)
    {
        Yii::info('AccountTariffLog. Before validatorCreateNotClose', 'uu');

        if (!$this->tariff_period_id && !$this->getCountLogs()) {
            $this->addError($attribute, 'Не указан тариф/период.');
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_EMPTY;
            return;
        }

        if (!$this->isNewRecord) {
            return;
        }

        if ($this->getCountLogs() && !$this->accountTariff->isLogEditable()) {
            $this->addError($attribute, 'Услуга нередактируемая.');
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_NOT_EDITABLE;
            return;
        }

        $accountTariffLogs = $this->accountTariff->accountTariffLogs;
        $accountTariffLog = reset($accountTariffLogs);
        if ($accountTariffLog && $this->tariff_period_id == $accountTariffLog->tariff_period_id) {
            $this->addError($attribute, 'Нет смысла менять период/тариф на тот же самый. Выберите другой период/тариф.');
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_SAME;
            return;
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
     *
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
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_EMPTY;
            return;
        }

        $clientAccount = $accountTariff->clientAccount;
        if (!$clientAccount) {
            $this->addError($attribute, 'ЛС не указан.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_EMPTY;
            return;
        }

        if ($clientAccount->account_version != ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $this->addError($attribute, 'Универсальную услугу можно добавить только ЛС, тарифицируемому универсально.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_IS_NOT_UU;
            return;
        }

        $tariffPeriod = $this->tariffPeriod;
        if ($tariffPeriod) {
            $tariff = $tariffPeriod->tariff;
            if ($tariff->currency_id != $clientAccount->currency) {
                $this->addError($attribute, sprintf('Валюта акаунта %s и тарифа %s не совпадают.', $clientAccount->currency, $tariffPeriod->tariff->currency_id));
                $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_CURRENCY;
                return;
            }

            if ($clientAccount->is_postpaid != $tariff->is_postpaid) {
                $this->addError($attribute, 'ЛС и тариф должны быть либо оба предоплатные, либо оба постоплатные.');
                $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_POSTPAID;
                return;
            }
        }

        $credit = $clientAccount->credit; // кредитный лимит
        $realtimeBalance = $clientAccount->balance; // $clientAccount->billingCounters->getRealtimeBalance()
        $realtimeBalanceWithCredit = ($realtimeBalance + $credit);

        $warnings = $clientAccount->getVoipWarnings();
        $isCountLogs = $this->getCountLogs(); // смена тарифа или закрытие услуги

        if (!$tariffPeriod) {
            if ($isCountLogs) {
                // закрытие услуги
                return;
            } else {
                // подключение новой услуги
                $this->addError($attribute, 'Не указан тариф/период.');
                $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_EMPTY;
                return;
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
                $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_BLOCKED_PERMANENT;
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
                $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_BLOCKED_TEMPORARY;
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
                $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_BLOCKED_FINANCE;
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
            $accountLogSetup->price = max(0, ($accountLogSetup->price - $accountLogSetup->price_number));
            $accountLogSetup->price_number = 0;
        }

        $accountLogPeriod = (new AccountLogPeriodTarificator())->getAccountLogPeriod($accountTariff, $accountLogFromToTariff);
        $priceMin = ($tariffPeriod->price_min * $accountLogPeriod->coefficient);
        $tariffPrice = ($accountLogSetup->price + $accountLogPeriod->price + $priceMin);

        if ($realtimeBalanceWithCredit < $tariffPrice) {
            $error = sprintf(
                'На ЛС %.2f %s и кредит %.2f %s, что меньше первичного платежа по тарифу, который составляет %.2f %s (подключение %.2f %s + абонентская плата %.2f %s до конца периода + минимальная стоимость ресурсов %.2f %s)',
                $realtimeBalance,
                $clientAccount->currency,
                $credit,
                $clientAccount->currency,
                $tariffPrice,
                $clientAccount->currency,
                $accountLogSetup->price,
                $clientAccount->currency,
                $accountLogPeriod->price,
                $clientAccount->currency,
                $priceMin,
                $clientAccount->currency
            );
            if ($isCountLogs) {
                // смена тарифа или закрытие услуги
                $this->shiftActualFrom($error);
            } else {
                // подключение новой услуги
                $this->addError($attribute, $error);
                $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_MONEY;
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
     *
     * @param string $error
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
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_MAIN;
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
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_TARIFF;
            return;
        }

        Yii::info('AccountTariffLog. After validatorPackage', 'uu');
    }

    /**
     * Установить actual_from из date в таймзоне клиента в datetime UTC
     * Для совместимости, ибо старое поле actual_from удалено
     *
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
     *
     * @return string
     */
    public function getActual_from()
    {
        if (!$this->actual_from_utc) {
            return null;
        }

        return (new DateTime($this->actual_from_utc, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->setTimezone($this->getClientTimeZone())
            ->format(DateTimeZoneHelper::DATE_FORMAT);

    }

    /**
     * Вернуть DateTime в таймзоне клиента
     *
     * @param string $date в таймзоне клиента
     * @return DateTimeImmutable
     */
    public function getClientDateTime($date = 'now')
    {
        return new DateTimeImmutable($date, $this->getClientTimeZone());
    }

    /**
     * Вернуть DateTimeZone клиента
     *
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

    /**
     * Валидировать, что пакет подключен только 1 раз
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorDoublePackage($attribute, $params)
    {
        if (!$this->tariff_period_id) {
            // закрытие услуги всегда можно
            return;
        }

        $accountTariff = $this->accountTariff;
        if (!$accountTariff->prev_account_tariff_id) {
            // не пакет
            return;
        }

        if (AccountTariff::find()
            ->where(
                [
                    'service_type_id' => $accountTariff->service_type_id,
                    'prev_account_tariff_id' => $accountTariff->prev_account_tariff_id,
                    'tariff_period_id' => $this->tariff_period_id,
                ]
            )
            ->andWhere(['!=', 'id', (int)$this->account_tariff_id])// кроме себя же
            ->count()
        ) {
            $this->addError($attribute, 'Этот пакет уже подключен на эту же базовую услугу. Повторное подключение не имеет смысла.');
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_DOUBLE_PREV;
            return;
        }

        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        if (AccountTariffLog::find()
            ->joinWith('accountTariff')
            ->where(
                [
                    $accountTariffTableName . '.service_type_id' => $accountTariff->service_type_id,
                    $accountTariffTableName . '.prev_account_tariff_id' => $accountTariff->prev_account_tariff_id,
                    $accountTariffLogTableName . '.tariff_period_id' => $this->tariff_period_id,
                ]
            )
            ->andWhere(
                [
                    '>=',
                    $accountTariffLogTableName . '.actual_from_utc',
                    (new DateTime())->modify('-1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT)
                ]
            )// "-1 day" для того, чтобы не мучиться с таймзоной клиента, а гарантированно получить нужное
            ->count()
        ) {
            $this->addError($attribute, 'Этот пакет уже запланирован на подключение на эту же базовую услугу. Повторное подключение не имеет смысла.');
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_DOUBLE_FUTURE;
            return;
        }
    }

    /**
     * Валидировать, что базовый пакет только один
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorDefaultPackage($attribute, $params)
    {
        if (!$this->tariff_period_id) {
            // закрытие услуги всегда можно
            return;
        }

        if (!$this->tariffPeriod->tariff->is_default) {
            // не дефолтный
            return;
        }

        $accountTariff = $this->accountTariff;

        // базовая услуга
        $baseAccountTariff = $accountTariff->prevAccountTariff;
        if (!$baseAccountTariff) {
            // не пакет
            return;
        }

        // все пакеты
        $nextAccountTariffs = $baseAccountTariff->nextAccountTariffs;
        foreach ($nextAccountTariffs as $nextAccountTariff) {

            if ($accountTariff->id == $nextAccountTariff->id) {
                // с собой не сравниваем
                continue;
            }

            if ($nextAccountTariff->tariffPeriod->tariff->is_default) {
                $this->addError($attribute, 'Нельзя подключить второй базовый пакет на ту же услугу.');
                $this->errorCode = AccountTariff::ERROR_CODE_USAGE_DOUBLE_PREV;
                return;
            }
        }
    }
}