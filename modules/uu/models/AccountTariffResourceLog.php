<?php

namespace app\modules\uu\models;

use app\helpers\DateTimeZoneHelper;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
            [['amount'], 'number'],
            [['amount'], 'validatorOther', 'skipOnEmpty' => false],
            ['resource_id', 'validateTariffResource'],
            ['actual_from', 'date', 'format' => 'php:' . DateTimeZoneHelper::DATE_FORMAT],
            ['actual_from', 'validatorFuture', 'skipOnEmpty' => false],
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
        }

        return new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
    }

    /**
     * Валидировать ресурс
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validateTariffResource($attribute, $params)
    {
        if (!$this->resource) {
            $this->addError($attribute, 'Указан несуществующий ресурс.');
            $this->errorCode = AccountTariff::ERROR_CODE_RESOURSE_WRONG;
            return;
        }

        $tariffPeriod = $this->accountTariff->tariffPeriod;
        if ($tariffPeriod && $this->resource->service_type_id != $tariffPeriod->tariff->service_type_id) {
            $this->addError($attribute, 'Этот ресурс "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" от другого типа услуги.');
            $this->errorCode = AccountTariff::ERROR_CODE_RESOURSE_TYPE_WRONG;
        }
    }

    /**
     * Валидировать дату смены количества ресурса
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorFuture($attribute, $params)
    {
        Yii::info('AccountTariffResourceLog. Before validatorFuture', 'uu');

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

        /*
            if (
                $this->actual_from_utc == $currentDateTimeUtc
                && self::find()
                    ->where(['account_tariff_id' => $this->account_tariff_id])
                    ->andWhere(['=', 'actual_from_utc', $currentDateTimeUtc])
                    ->count()
            ) {
                $this->addError($attribute, 'Сегодня количество ресурса уже меняли. Теперь можно сменить его не ранее завтрашнего дня.');
                $this->errorCode = AccountTariff::ERROR_CODE_DATE_TODAY;
                return;
            }
        */

        if (self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->andWhere(['resource_id' => $this->resource_id])
            ->andWhere(['>', 'actual_from_utc', $currentDateTimeUtc])
            ->count()
        ) {
            $this->addError($attribute,
                'Уже назначена смена количество ресурса "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" в будущем. Если вы хотите установить новое количество ресурса - сначала отмените эту смену.');
            $this->errorCode = AccountTariff::ERROR_CODE_DATE_FUTURE;
            return;
        }

        /*
            $currentAmount = (int)$this->accountTariff->getResourceValue($this->resource_id);
            if ($this->amount < $currentAmount && $this->actual_from < ($minEditDate = $accountTariff->getDefaultActualFrom())) {
                $this->addError($attribute, 'Уменьшить количество ресурса "' . ($this->resource ? $this->resource->name : $this->resource_id) . '" можно, начиная с ' . $minEditDate);
                $this->errorCode = AccountTariff::ERROR_CODE_DATE_PAID;
                return;
            }
        */

        Yii::info('AccountTariffResourceLog. After validatorFuture', 'uu');
    }

    /**
     * Валидировать, что меняется на другое значение
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validatorOther($attribute, $params)
    {
        Yii::info('AccountTariffResourceLog. Before validatorOther', 'uu');

        if (!$this->isNewRecord) {
            // При обновлении не проверяем. Клиент обновить все равно не может. Он может только удалить (если дата еще не наступила) или добавить новый (если дата ужа наступила)
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

        Yii::info('AccountTariffResourceLog. After validatorCreateNotClose', 'uu');
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
}