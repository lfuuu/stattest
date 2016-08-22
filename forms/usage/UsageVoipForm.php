<?php
namespace app\forms\usage;

use app\classes\Form;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;

class UsageVoipForm extends Form
{
    public $id;
    public $client_account_id;
    public $city_id;
    public $connection_point_id;
    public $type_id;
    public $status;
    public $did;
    public $number_tariff_id;
    public $connecting_date;
    public $disconnecting_date;
    public $tariff_change_date;
    public $no_of_lines;
    public $address = '';
    public $line7800_id;
    public $address_from_datacenter_id;

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

    public function rules()
    {
        return [
            [
                [
                    'id',
                    'client_account_id',
                    'city_id',
                    'connection_point_id',
                    'number_tariff_id',
                    'line7800_id',
                    'no_of_lines'
                ],
                'integer'
            ],
            [['type_id', 'did', 'connecting_date', 'tariff_change_date', 'address', 'status'], 'string'],
            [['mass_change_tariff'], 'boolean'],
            [
                [
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
                'number'
            ],
            ['status', 'default', 'value' => 'connecting'],
            [['connecting_date'], 'validateDate', 'on' => 'edit'],
            [['connecting_date'], 'validateUsageDate'],
            [['disconnecting_date'], 'validateDependPackagesDate', 'on' => 'edit'],
            [['disconnecting_date'], 'validateDisconnectingDate', 'on' => 'edit'],
            ['tariff_change_date', 'validateChangeTariff', 'on' => 'change-tariff'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'city_id' => 'Город',
            'connection_point_id' => 'Точка присоединения',
            'type_id' => 'Тип',
            'number_tariff_id' => 'DID группа',
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
        ];
    }

    public function validateDate()
    {
        $expireDt = new \DateTime($this->usage->actual_to . ' 23:59:59');
        $nowDt = new \DateTime('now');

        // не включеную услугу в будущем можно менять.
        if (!$this->usage->isActive() && $expireDt < $nowDt) {
            $this->addError('disconnecting_date', 'Услуга отключена ' . ($expireDt->format('d.m.Y')));
        }
    }

    public function validateUsageDate()
    {
        $from = $this->connecting_date;
        $to = $this->disconnecting_date ?: UsageInterface::MAX_POSSIBLE_DATE;

        $queryVoip =
            UsageVoip::find()
                ->andWhere('(actual_from between :from and :to) or (actual_to between :from and :to)',
                    [':from' => $from, ':to' => $to])
                ->andWhere(['E164' => $this->did]);
        if ($this->id) {
            $queryVoip->andWhere('id != :id', [':id' => $this->id]);
        }

        foreach ($queryVoip->all() as $usage) {
            $this->addError('did',
                "Номер пересекается с id: {$usage->id}, клиент: {$usage->clientAccount->client}, c {$usage->actual_from} по {$usage->actual_to}");
        }
    }

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
    public function validateChangeTariff()
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
