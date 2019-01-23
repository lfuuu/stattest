<?php

namespace app\forms\usage;

use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\helpers\DateTimeZoneHelper;
use app\models\Number;
use app\models\TariffVoip;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;

/**
 * Class UsageVoipForm
 */
class UsageVoipForm extends Form
{
    public $id;
    public $client_account_id;
    public $city_id;
    public $country_id;
    public $connection_point_id;
    public $status;
    public $did;
    public $did_group_id;
    public $connecting_date;
    public $disconnecting_date;
    public $tariff_change_date;
    public $no_of_lines;
    public $address = '';
    public $line7800_id;
    public $address_from_datacenter_id;
    public $ndc_type_id = NdcType::ID_GEOGRAPHIC;
    public $usage_comment = '';

    /** @var UsageVoip */
    public $usage;

    public $mass_change_tariff;
    public $tariff_main_status;
    public $tariff_main_id;
    public $tariff_local_mob_id;
    public $tariff_russia_id;
    public $tariff_russia_mob_id;
    public $tariff_intern_id;
    public $tariff_group_local_mob;
    public $tariff_group_russia;
    public $tariff_group_intern;
    public $tariff_group_local_mob_price;
    public $tariff_group_russia_price;
    public $tariff_group_intern_price;
    public $tariff_group_price;

    public
        $tariffGroupLocalMobPrice,
        $tariffGroupRussiaPrice,
        $tariffGroupInternPrice;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'client_account_id',
                    'city_id',
                    'country_id',
                    'connection_point_id',
                    'did_group_id',
                    'line7800_id',
                    'no_of_lines',
                    'ndc_type_id',
                ],
                'integer'
            ],
            [['connecting_date', 'tariff_change_date', 'address', 'status'], 'string'],
            [['mass_change_tariff'], 'boolean'],
            [
                [
                    'did',
                    'tariff_main_id',
                    'tariff_local_mob_id',
                    'tariff_russia_id',
                    'tariff_russia_mob_id',
                    'tariff_intern_id'
                ],
                'integer'
            ],
            [['tariff_main_status'], 'string'],
            [['tariff_group_local_mob', 'tariff_group_russia', 'tariff_group_intern'], 'integer'],
            [
                [
                    'tariff_group_local_mob_price',
                    'tariff_group_russia_price',
                    'tariff_group_intern_price',
                    'tariff_group_price'
                ],
                'integer', 'integerOnly' => false
            ],
            ['status', 'default', 'value' => 'connecting'],
            [['connecting_date'], 'validateDate', 'on' => 'edit'],
            [['connecting_date'], 'validateUsageDate'],
            [['disconnecting_date'], 'validateDependPackagesDate', 'on' => 'edit'],
            [['disconnecting_date'], 'validateDisconnectingDate', 'on' => 'edit'],
            ['tariff_change_date', 'validateChangeTariffDate', 'on' => 'change-tariff'],
            [
                ['tariff_main_id',
                    'tariff_local_mob_id',
                    'tariff_russia_id',
                    'tariff_russia_mob_id',
                    'tariff_intern_id'
                ],
                'validateChangeTariff',
                'on' => ['add', 'change-tariff']

            ],
            ['usage_comment', FormFieldValidator::class],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'city_id' => 'Город',
            'country_id' => 'Страна',
            'connection_point_id' => 'Регион (точка подключения)',
            'did_group_id' => 'DID-группа',
            'connecting_date' => 'Дата подключения',
            'disconnecting_date' => 'Дата отключения',
            'tariff_change_date' => 'Дата изменения тарифа',
            'did' => 'Номер',
            'no_of_lines' => 'Количество линий',
            'address' => 'Адрес',
            'line7800_id' => 'Линия без номера',
            'status' => 'Статус',
            'mass_change_tariff' => 'Массово изменить тариф у всех услуг с этим тарифом',
            'tariff_main_status' => 'Тип тарифа',
            'tariff_main_id' => 'Тариф Основной',
            'tariff_local_mob_id' => 'Тариф Местные мобильные',
            'tariff_russia_id' => 'Тариф Россия стационарные',
            'tariff_russia_mob_id' => 'Тариф Россия мобильные',
            'tariff_intern_id' => 'Тариф Международка',
            'tariff_group_local_mob' => 'Набор',
            'tariff_group_russia' => 'Набор',
            'tariff_group_intern' => 'Набор',
            'tariff_group_local_mob_price' => 'Гарантированный платеж',
            'tariff_group_russia_price' => 'Гарантированный платеж',
            'tariff_group_intern_price' => 'Гарантированный платеж',
            'tariff_group_price' => 'Гарантированный платеж (Набор)',
            'ndc_type_id' => 'Тип NDC',
            'usage_comment' => 'Комментарий',
        ];
    }

    /**
     * Проверка даты выключения
     */
    public function validateDate()
    {
        $expireDt = new \DateTime($this->usage->actual_to . ' 23:59:59');
        $nowDt = new \DateTime('now');

        // не включеную услугу в будущем можно менять.
        if (!$this->usage->isActive() && $expireDt < $nowDt) {
            $this->addError('disconnecting_date', 'Услуга отключена ' . ($expireDt->format('d.m.Y')));
        }
    }

    /**
     * Проверка изменения даты
     */
    public function validateUsageDate()
    {
        $from = $this->connecting_date;
        $to = $this->disconnecting_date ?: UsageInterface::MAX_POSSIBLE_DATE;

        // включен в услугах
        $queryVoip = UsageVoip::find()
            ->andWhere('(actual_from between :from and :to) or (actual_to between :from and :to)',
                [':from' => $from, ':to' => $to])
            ->andWhere(['E164' => $this->did]);

        if ($this->id) {
            $queryVoip->andWhere('id != :id', [':id' => $this->id]);
        }

        $usage = $queryVoip->one();

        if ($usage) {
            $this->addError('did',
                "Номер пересекается с id: {$usage->id}, клиент: {$usage->clientAccount->id}, c {$usage->actual_from} по {$usage->actual_to}"
            );

            return;
        }

        // включен в универсальных услугах
        $uUsage = AccountTariff::find()
            ->where([
                'service_type_id' => ServiceType::ID_VOIP,
                'voip_number' => $this->did
            ])
            ->andWhere(['IS NOT', 'tariff_period_id', null])
            ->one();

        if ($uUsage) {
            $this->addError('did',
                "Номер пересекается с uid: {$uUsage->id}, клиент: {$uUsage->clientAccount->id}"
            );

            return;
        }

        // включен в будущем в универсальных услугах
        $uUsages = AccountTariff::find()
            ->where([
                'service_type_id' => ServiceType::ID_VOIP,
                'voip_number' => $this->did,
            ])
            ->with('accountTariffLogs');

        $now = DateTimeZoneHelper::getUtcDateTime();

        /** @var AccountTariff $uUsage */
        foreach ($uUsages->each() as $uUsage) {
            if (
                $uUsage->tariff_period_id ||
                (
                    ($accountTariffLogs = $uUsage->accountTariffLogs) &&
                    ($accountTariffLog = reset($accountTariffLogs)) &&
                    $accountTariffLog->tariff_period_id &&
                    strtotime($accountTariffLog->actual_from_utc) > $now->getTimestamp()
                )
            ) {
                $this->addError('did',
                    "Номер включится в будущем с uid: {$uUsage->id}, клиент: {$uUsage->clientAccount->id}"
                );

                return;
            }
        }
    }

    /**
     * Проверка подключенных пакетов
     *
     * @param string $attr
     */
    public function validateDependPackagesDate($attr)
    {
        if ($this->disconnecting_date != UsageInterface::MAX_POSSIBLE_DATE) {
            if ($this->usage
                ->getPackages()
                ->andWhere('actual_from >= :from',
                    [':from' => $this->disconnecting_date])
                ->count()
            ) {
                $this->addError($attr, 'После даты отключения есть не включеные пакеты');
            }
        }
    }

    /**
     * Валидация даты изменения тарифа
     */
    public function validateChangeTariffDate()
    {
        if ($this->tariff_change_date > ($this->disconnecting_date ?: UsageInterface::MAX_POSSIBLE_DATE) || $this->tariff_change_date < $this->connecting_date) {
            $this->addError('tariff_change_date', 'Дата начала тарифа должна быть во время действия услуги');
            return;
        }

        $tariffDate = new \DateTime($this->tariff_change_date);
        $firstDayOfThisMonth = (new \DateTime('now'))->modify('first day of this month')->setTime(0, 0, 0);

        if ($tariffDate < $firstDayOfThisMonth) {
            $this->addError('tariff_change_date', 'Разрешено изменение даты тарифа не раньше начала текущего месяца');
        }
    }

    /**
     * Валидация тарифа
     *
     * @param $attr
     */
    public function validateChangeTariff($attr)
    {
        $tariff = TariffVoip::findOne(['id' => $this->$attr]);

        if (!$tariff) {
            $this->addError($attr, 'Тариф не найден');
            return;
        }

        if (NdcType::isCityDependent($this->ndc_type_id) && $tariff->connection_point_id != $this->connection_point_id) {
            $this->addError($attr, 'Регион услуги и тарифа не совпадают');
            return;
        }

        if ($tariff->currency_id != $this->clientAccount->currency) {
            $this->addError($attr, 'Валюта ЛС и тарифа не совпадает');
            return;
        }

        // Ошибка, если NDC тарифа и номера не совпадают.
        // Исключение, если это номер FREEPHONE и тариф GEOGRAPHIC
        if (
            ($number = Number::findOne(['number' => $this->did]))
            && $tariff->ndc_type_id != $number->ndc_type_id &&
            ($number->ndc_type_id != NdcType::ID_FREEPHONE || $tariff->ndc_type_id != NdcType::ID_GEOGRAPHIC)
        ) {
            $this->addError($attr, 'Не совпадает NDC у номера и тарифа');
            return;
        }
    }

    /**
     * Валидация даты отключения услуги
     */
    public function validateDisconnectingDate()
    {
        $disconnectDate = new \DateTime($this->disconnecting_date ?: UsageInterface::MAX_POSSIBLE_DATE);
        $now = (new \DateTime('now'))->setTime(0, 0, 0);

        if ($disconnectDate < $now) {
            $this->addError('disconnecting_date', 'Отключить услугу можно только в будущем, или сегодня');
        }
    }
}
