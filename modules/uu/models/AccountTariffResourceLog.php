<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\uu\behaviors\AccountTariffBiller;
use app\modules\uu\classes\AccountLogFromToResource;
use app\modules\uu\tarificator\AccountLogResourceTarificator;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\db\ActiveQuery;

/**
 * Лог ресурсов универсальной услуги
 * По аналогии с AccountTariffLog. Это не инкрементационный лог, а действует последнее значение
 * Значение абсолютное (как на платформе). Для билинга из него надо вычесть включенное количество в тариф
 *
 * @property int $id
 * @property int $account_tariff_id
 * @property int $resource_id
 * @property float $amount
 * @property string $actual_from_utc
 * @property string $sync_time
 *
 * @property AccountTariff $accountTariff
 * @property \app\modules\uu\models\Resource $resource
 * @property string $actual_from
 */
class AccountTariffResourceLog extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Методы для полей insert_time, insert_user_id
    use \app\classes\traits\InsertUserTrait;

    /** @var int Код ошибки для АПИ */
    public $errorCode = null;

    private $_countLogs = null;

    protected $isAttributeTypecastBehavior = true;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff_resource_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['account_tariff_id', 'resource_id', 'amount'], 'required'],
            [['account_tariff_id', 'resource_id'], 'integer'],
            ['resource_id', 'validateTariffResource'],
            [['amount'], 'number'],
            [['amount'], 'validatorOther', 'skipOnEmpty' => false],
            ['actual_from', 'date', 'format' => 'php:' . DateTimeZoneHelper::DATE_FORMAT],
            ['actual_from', 'validatorFuture', 'skipOnEmpty' => false],
            ['id', 'validatorBalance', 'skipOnEmpty' => false],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return parent::behaviors() + [
                'AccountTariffBiller' => AccountTariffBiller::className(), // Пересчитать транзакции, проводки и счета
            ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(Resource::className(), ['id' => 'resource_id']);
    }

    /**
     * Установить actual_from из date в таймзоне клиента в datetime UTC
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
     *
     * @return string|null
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
        }

        return new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
    }

    /**
     * Валидировать ресурс
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateTariffResource($attribute, $params)
    {
        if (!$this->resource) {
            $this->addError($attribute, 'Указан несуществующий ресурс.');
            $this->errorCode = AccountTariff::ERROR_CODE_RESOURCE_WRONG;
            return;
        }

        $tariffPeriod = $this->accountTariff->tariffPeriod;
        if ($tariffPeriod && $this->resource->service_type_id != $tariffPeriod->tariff->service_type_id) {
            $this->addError($attribute, 'Этот ресурс "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" от другого типа услуги.');
            $this->errorCode = AccountTariff::ERROR_CODE_RESOURCE_TYPE_WRONG;
            return;
        }

        if (!$this->resource->isOption()) {
            $this->addError($attribute, 'Этот ресурс "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" - трафик, а не опция. Его нельзя установить заранее.');
            $this->errorCode = AccountTariff::ERROR_CODE_RESOURCE_TRAFFIC;
            return;
        }
    }

    /**
     * Валидировать дату смены количества ресурса
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorFuture($attribute, $params)
    {
        Yii::trace('AccountTariffResourceLog. Before validatorFuture', 'uu');

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
            $this->addError($attribute, 'Нельзя менять количество ресурса "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" задним числом.');
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_PREV;
            return;
        }

        if (
            $this->actual_from_utc == $currentDateTimeUtc
            && self::find()
                ->where([
                    'account_tariff_id' => $this->account_tariff_id,
                    'resource_id' => $this->resource_id,
                ])
                ->andWhere(['=', 'actual_from_utc', $currentDateTimeUtc])
                ->count()
        ) {
            $this->addError($attribute, 'Сегодня количество ресурса уже меняли. Теперь можно сменить его не ранее завтрашнего дня.');
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_TODAY;
            return;
        }

        if (self::find()
            ->where([
                'account_tariff_id' => $this->account_tariff_id,
                'resource_id' => $this->resource_id,
            ])
            ->andWhere(['>', 'actual_from_utc', $currentDateTimeUtc])
            ->count()
        ) {
            $this->addError($attribute,
                'Уже назначена смена количество ресурса "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" в будущем. Если вы хотите установить новое количество ресурса - сначала отмените эту смену.');
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_FUTURE;
            return;
        }

        Yii::trace('AccountTariffResourceLog. After validatorFuture', 'uu');
    }

    /**
     * Валидировать, что меняется на другое значение
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorOther($attribute, $params)
    {
        Yii::trace('AccountTariffResourceLog. Before validatorOther', 'uu');

        if (!$this->isNewRecord) {
            // При обновлении не проверяем. Клиент обновить все равно не может. Он может только удалить (если дата еще не наступила) или добавить новый (если дата ужа наступила)
            return;
        }

        $resource = $this->resource;
        if ($this->amount < $resource->min_value) {
            $this->addError($attribute, 'Значение ' . $this->amount . ' ресурса "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" меньше минимально допустимого значения ' . $resource->min_value . '.');
            $this->errorCode = AccountTariff::ERROR_CODE_RESOURCE_AMOUNT_MIN;
            return;
        }

        if ($resource->max_value && $this->amount > $resource->max_value) {
            $this->addError($attribute, 'Значение ' . $this->amount . ' ресурса "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" больше максимально допустимого значения ' . $resource->max_value . '.');
            $this->errorCode = AccountTariff::ERROR_CODE_RESOURCE_AMOUNT_MAX;
            return;
        }

        /** @var self $prev */
        $prev = self::find()
            ->where([
                'account_tariff_id' => $this->account_tariff_id,
                'resource_id' => $this->resource_id,
            ])
            ->orderBy([
                'actual_from_utc' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();

        if ($prev && $this->amount == $prev->amount) {
            $this->addError($attribute, 'Нет смысла менять значение ресурса "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" на тот же самый. Выберите другое значение.');
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_SAME;
            return;
        }

        Yii::trace('AccountTariffResourceLog. After validatorCreateNotClose', 'uu');
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        if (!$this->resource) {
            return null;
        }

        if ($this->resource->isNumber()) {
            return (string)$this->amount;
        }

        return $this->amount ? '+' : '-';
    }

    /**
     * Валидировать, что realtime balance больше обязательного платежа по ресурсу
     *
     * @param string $attribute
     * @param array $params
     * @return AccountLogResource|null
     * @throws \RangeException
     * @throws \Exception
     */
    public function validatorBalance($attribute, $params)
    {
        Yii::trace('AccountTariffResourceLog. Before validatorBalance', 'uu');

        if (!$this->isNewRecord) {
            return null;
        }

        $accountTariff = $this->accountTariff;
        if (!$accountTariff) {
            $this->addError($attribute, 'Услуга не указана.');
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_EMPTY;
            return null;
        }

        $clientAccount = $accountTariff->clientAccount;
        if (!$clientAccount) {
            $this->addError($attribute, 'ЛС не указан.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_EMPTY;
            return null;
        }

        if ($clientAccount->account_version != ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $this->addError($attribute, 'Универсальную услугу можно добавить только ЛС, тарифицируемому универсально.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_IS_NOT_UU;
            return null;
        }

        if (!$this->_getCountLogs()) {
            // при инициализации ресурсов денег не списывается. Проверять дальше не смысла
            return null;
        }

        $tariffPeriod = $accountTariff->tariffPeriod;
        if (!$tariffPeriod) {
            // Это еще не значит, что услуга закрыта. Возможно, она только что создана и еще не успела проставиться
            // Возьмем тариф из лога. Он нужен для расчета срока списания денег за ресурс, но без фактического списывания
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            if (count($accountTariffLogs) === 1) {
                // Только если одна запись, которая при создании.
                // Если ни одной записи - что-то не так. Лог ресурсов должен создаваться из FillAccountTariffResourceLog, который вызывается из AccountTariffLog
                // Если две записи - это уже не создание. В этом случае тариф должен быть у услуги. Значит, что-то не так.
                $tariffPeriod = reset($accountTariffLogs)->tariffPeriod;
            }
        }

        if (!$tariffPeriod) {
            $this->addError($attribute, 'Услуга закрыта.');
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_EMPTY;
            return null;
        }

        $credit = $clientAccount->credit; // кредитный лимит
        $realtimeBalance = $clientAccount->balance; // $clientAccount->billingCounters->getRealtimeBalance()
        $realtimeBalanceWithCredit = ($realtimeBalance + $credit);

        $warnings = $clientAccount->getVoipWarnings();

        if ($clientAccount->is_blocked) {
            $this->_shiftActualFrom('ЛС заблокирован');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_BLOCKED_PERMANENT;
            return null;
        }

        if (isset($warnings[ClientAccount::WARNING_OVERRAN])) {
            $this->_shiftActualFrom('ЛС заблокирован из-за превышения лимитов');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_BLOCKED_TEMPORARY;
            return null;
        }

        if ($realtimeBalanceWithCredit < 0 || isset($warnings[ClientAccount::WARNING_FINANCE]) || isset($warnings[ClientAccount::WARNING_CREDIT])) {
            $error = sprintf('ЛС находится в финансовой блокировке. На счету %.2f %s и кредит %.2f %s', $realtimeBalance, $clientAccount->currency, $credit, $clientAccount->currency);
            $this->_shiftActualFrom($error);
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_BLOCKED_FINANCE;
            return null;
        }

        $accountLogFromToResource = new AccountLogFromToResource;
        $accountLogFromToResource->dateFrom = new DateTimeImmutable($this->actual_from);
        $accountLogFromToResource->dateTo = $tariffPeriod->chargePeriod->getMinDateTo($accountLogFromToResource->dateFrom);
        $accountLogFromToResource->tariffPeriod = $tariffPeriod;
        $accountLogFromToResource->amountOverhead = $this->amount;
        $accountLogResource = (new AccountLogResourceTarificator())->getAccountLogPeriod($accountTariff, $accountLogFromToResource, $this->resource_id);
        $priceResources = $accountLogResource->price;

        if ($realtimeBalanceWithCredit < $priceResources) {
            $error = sprintf(
                'На ЛС %.2f %s и кредит %.2f %s, что меньше стоимости ресурсов %.2f',
                $realtimeBalance,
                $clientAccount->currency,
                $credit,
                $clientAccount->currency,
                $priceResources
            );
            $this->_shiftActualFrom($error);
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_MONEY;
            return $accountLogResource;
        }

        // все хорошо - денег хватает
        // на самом деле мы не знаем, сколько клиент уже потратил на звонки сегодня. Но это дело низкоуровневого биллинга. Если денег не хватит - заблокирует финансово
        // транзакции не сохраняем, деньги пока не списываем. Подробнее см. AccountTariffBiller
        Yii::trace('AccountTariffResourceLog. After validatorBalance', 'uu');
        return $accountLogResource;
    }

    /**
     * Сдвинуть actual_from на завтра
     *
     * @param string $error
     */
    private function _shiftActualFrom($error)
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
     * Вернуть кол-во предыдущих логов
     *
     * @return int
     */
    private function _getCountLogs()
    {
        if (!is_null($this->_countLogs)) {
            return $this->_countLogs;
        }

        return $this->_countLogs = self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->count();
    }
}