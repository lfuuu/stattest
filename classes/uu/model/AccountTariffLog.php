<?php

namespace app\classes\uu\model;

use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;
use app\classes\uu\forms\AccountLogFromToTariff;
use app\classes\uu\tarificator\AccountEntryTarificator;
use app\classes\uu\tarificator\AccountLogPeriodTarificator;
use app\classes\uu\tarificator\AccountLogSetupTarificator;
use app\classes\uu\tarificator\BillTarificator;
use app\models\ClientAccount;
use DateTimeImmutable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Лог тарифов универсальной услуги
 *
 * @property int $id
 * @property int $account_tariff_id
 * @property int $tariff_period_id если null, то закрыто
 * @property string $actual_from !
 *
 * @property TariffPeriod $tariffPeriod
 * @property AccountTariff $accountTariff
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
            ['actual_from', 'date', 'format' => 'php:Y-m-d'],
            ['actual_from', 'validatorFuture', 'skipOnEmpty' => false],
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
        if (!$this->isNewRecord) {
            return;
        }

        $currentDate = (new DateTimeWithUserTimezone())->format('Y-m-d');
        !$this->actual_from && $this->actual_from = $currentDate;

        if ($this->actual_from < $currentDate) {
            $this->addError($attribute, 'Нельзя менять тариф задним числом.');
        }

        if (self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->andWhere(['=', 'actual_from', $currentDate])
            ->count()
        ) {
            $this->addError($attribute, 'Сегодня тариф уже меняли. Теперь можно сменить его не ранее завтрашнего дня.');
        }

        if (self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->andWhere(['>', 'actual_from', $currentDate])
            ->count()
        ) {
            $this->addError($attribute, 'Уже назначена смена тарифа в будущем. Если вы хотите установить новый тариф - сначала отмените эту смену.');
        }
    }

    /**
     * Валидировать, что при создании сразу же не закрытый
     * @param string $attribute
     * @param [] $params
     */
    public function validatorCreateNotClose($attribute, $params)
    {
        if (!$this->tariff_period_id && !$this->getCountLogs()) {
            $this->addError($attribute, 'Не указан период тарифа.');
        }
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
            $this->addError($attribute, 'Аккаунт не указан.');
            return;
        }

        if ($clientAccount->account_version != ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $this->addError($attribute, 'Универсальную услугу можно добавить только аккаунту, тарифицируемому универсально.');
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
        $realtimeBalance = $clientAccount->billingCounters->getRealtimeBalance();
        $realtimeBalanceWithCredit = $realtimeBalance + $credit;

        $isNewRecord = !$this->getCountLogs();
        if (!$isNewRecord) {
            // смена тарифа или закрытие услуги. А все последующие проверки только при создании услуги

            $datimeNow = $clientAccount->getDatetimeWithTimezone();
            if ($tariffPeriod && $realtimeBalanceWithCredit < 0 && $datimeNow->format('Y-m-d') == $this->actual_from) {
                // сегодня смена тарифа при отрицательном балансе. Откладывает +1 день, пока деньги не появятся
                $this->actual_from = $datimeNow->modify('+1 day')->format('Y-m-d');
            }
            return;
        }

        // создание услуги
        if (!$tariffPeriod) {
            $this->addError($attribute, 'Период тарифа не указан.');
            return;
        }

        if ($realtimeBalanceWithCredit < 0) {
            $this->addError(
                $attribute,
                sprintf('Аккаунт находится в финансовой блокировке. На счету %.2f %s и кредит %.2f %s',
                    $realtimeBalance, $clientAccount->currency,
                    $credit, $clientAccount->currency)
            );
            return;
        }

        $accountLogFromToTariff = new AccountLogFromToTariff();
        $accountLogFromToTariff->dateFrom = new DateTimeImmutable($this->actual_from);
        $accountLogFromToTariff->dateTo = $tariffPeriod->chargePeriod->getMinDateTo($accountLogFromToTariff->dateFrom);
        $accountLogFromToTariff->tariffPeriod = $tariffPeriod;
        $accountLogFromToTariff->isFirst = true;

        $accountLogSetup = (new AccountLogSetupTarificator())->getAccountLogSetup($accountTariff, $accountLogFromToTariff);
        $accountLogPeriod = (new AccountLogPeriodTarificator())->getAccountLogPeriod($accountTariff, $accountLogFromToTariff);

        // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
        // @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
        $isTest = $tariffPeriod->tariff->tariff_status_id == TariffStatus::ID_TEST;

        $priceSetup = $isTest ? 0 : $accountLogSetup->price;
        $pricePeriod = $isTest ? 0 : $accountLogPeriod->price;
        $priceMin = $tariffPeriod->price_min * $accountLogPeriod->coefficient;

        $tariffPrice = $priceSetup + $pricePeriod + $priceMin;

        if ($realtimeBalanceWithCredit < $tariffPrice) {
            $this->addError($attribute,
                sprintf('У аккаунта на счету %.2f %s и кредит %.2f %s, что меньше первичного платежа по тарифу, который составляет %.2f %s (подключение %.2f %s + абонентская плата %.2f %s до конца периода + минимальная стоимость ресурсов %.2f %s)',
                    $realtimeBalance, $clientAccount->currency,
                    $credit, $clientAccount->currency,
                    $tariffPrice, $clientAccount->currency,
                    $priceSetup, $clientAccount->currency,
                    $pricePeriod, $clientAccount->currency,
                    $priceMin, $clientAccount->currency
                ));
            return;
        }

        // списать деньги (транзакции)
        if (!$accountLogSetup->save()) {
            $this->addError($attribute, implode('. ', $accountLogSetup->getFirstErrors()));
        }
        if (!$accountLogPeriod->save()) {
            $this->addError($attribute, implode('. ', $accountLogPeriod->getFirstErrors()));
        }
        // пересчитать проводки
        ob_start();
        (new AccountEntryTarificator)->tarificateAll($this->account_tariff_id);
        (new BillTarificator)->tarificateAll($this->account_tariff_id);
        ob_end_clean();

        // @todo надо отправить данные на платформу
    }
}