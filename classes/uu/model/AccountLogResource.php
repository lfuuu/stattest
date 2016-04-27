<?php

namespace app\classes\uu\model;

use app\classes\uu\resourceReader\ResourceReaderInterface;
use DateTimeImmutable;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Предварительное списание платы за ресурсы
 *
 * @property int $id
 * @property string $date
 * @property int $tariff_period_id  кэш accountTariff -> accountTariffLog -> tariff_period_id
 * @property int $tariff_resource_id
 * @property int $account_tariff_id
 * @property float $amount_use кэш из пребиллера (например, virtpbx_stat)
 * @property float $amount_free кэш tariffResource -> amount
 * @property int $amount_overhead кэш ceil(amount_use - amount_free)
 * @property float $price_per_unit кэш tariffResource -> price_per_unit
 * @property float $price кэш amount_overhead * price_per_unit
 * @property string $insert_time
 *
 * @property AccountTariff $accountTariff
 * @property TariffPeriod $tariffPeriod
 * @property TariffResource $tariffResource
 */
class AccountLogResource extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /** @var TariffResource[] кэш */
    protected static $tariffIdToTariffResources = [];

    /** @var ResourceReaderInterface[] кэш */
    protected static $resourceIdToReader = [];

    public static function tableName()
    {
        return 'uu_account_log_resource';
    }

    public function rules()
    {
        return [
            [['id', 'tariff_period_id', 'account_tariff_id', 'tariff_resource_id'], 'integer'],
            [['price'], 'double'],
            [['date'], 'string', 'max' => 255],
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
    public function getTariffPeriod()
    {
        return $this->hasOne(TariffPeriod::className(), ['id' => 'tariff_period_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffResource()
    {
        return $this->hasOne(TariffResource::className(), ['id' => 'tariff_resource_id']);
    }

    /**
     * Рассчитать плату всех услуг
     */
    public static function tarificateAll()
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить старые данные
        self::deleteAll(['<', 'date', $minLogDatetime->format('Y-m-d')]);

        // расcчитать по ресурсам, для которых раньше не было данных
        // обязательно до расчета новых, чтобы не делать двойную работу
        // @todo не надо считать, потому что нерассчитанные решил не добавлять в БД
//        self::tarificateOld();

        // рассчитать новое по каждой универсальной услуге
        $accountTariffs = AccountTariff::find();
        foreach ($accountTariffs->each() as $accountTariff) {
            echo '. ';

            /** @var AccountTariffLog $accountTariffLog */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from < $minLogDatetime->format('Y-m-d'))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            self::tarificateAccountTariff($accountTariff);
        }
    }

    /**
     * Рассчитать плату по ресурсам, для которых раньше не было данных
     * Для упрощения считаем только по не очень старым (до месяца)
     */
    public static function tarificateOld()
    {
        $accountLogResources = self::find()
            ->where('price IS NULL');

        /** @var AccountLogResource $accountLogResource */
        foreach ($accountLogResources->each() as $accountLogResource) {
            $resourceId = $accountLogResource->tariffResource->resource_id;
            if (!isset(self::$resourceIdToReader[$resourceId])) {
                // записать в кэш
                self::$resourceIdToReader[$resourceId] = Resource::getReader($resourceId);
            }

            /** @var ResourceReaderInterface $reader */
            $reader = self::$resourceIdToReader[$resourceId];
            $amountUse = $reader->read($accountLogResource->accountTariff, new DateTimeImmutable($accountLogResource->date));
            if ($amountUse === null) {
                // данных все равно нет. Пересчитывать нечего
//                echo '- ';
                continue;
            }

            // данные есть - пересчитать
            echo '+ ';
            $accountLogResource->amount_overhead = (int)ceil($accountLogResource->amount_use - $accountLogResource->amount_free);
            $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit;
            $accountLogResource->save();

        }
    }

    /**
     * Рассчитать плату по конкретной услуге
     * @param AccountTariff $accountTariff
     */
    public static function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        /** @var AccountLogResource[] $accountLogs */
        $accountLogs = self::find()
            ->where('account_tariff_id = :account_tariff_id', [':account_tariff_id' => $accountTariff->id])
            ->indexBy('date')
            ->all(); // по которым произведен расчет

        $untarificatedPeriods = $accountTariff->getUntarificatedResourcePeriods($accountLogs);
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $date = $untarificatedPeriod->getDateFrom();
            $tariffPeriod = $untarificatedPeriod->getTariffPeriod();

            $tariffId = $tariffPeriod->tariff_id;
            if (!isset(self::$tariffIdToTariffResources[$tariffId])) {
                // записать в кэш
                self::$tariffIdToTariffResources[$tariffId] = $tariffPeriod->tariff->tariffResources;
            }
            /** @var TariffResource[] $tariffResources */
            $tariffResources = self::$tariffIdToTariffResources[$tariffId];

            foreach ($tariffResources as $tariffResource) {

                $resourceId = $tariffResource->resource_id;
                if (!isset(self::$resourceIdToReader[$resourceId])) {
                    // записать в кэш
                    self::$resourceIdToReader[$resourceId] = Resource::getReader($resourceId);
                }
                /** @var ResourceReaderInterface $reader */
                $reader = self::$resourceIdToReader[$resourceId];

                $accountLogResource = new self();
                $accountLogResource->date = $date->format('Y-m-d');
                $accountLogResource->tariff_period_id = $tariffPeriod->id;
                $accountLogResource->account_tariff_id = $accountTariff->id;
                $accountLogResource->tariff_resource_id = $tariffResource->id;
                $accountLogResource->amount_use = $reader->read($accountTariff, $date);
                $accountLogResource->amount_free = $tariffResource->amount;
                $accountLogResource->price_per_unit = $tariffResource->price_per_unit / $date->format('t'); // это "цена за месяц", а надо перевести в "цену за день"
                if ($accountLogResource->amount_use !== null) {
                    $accountLogResource->amount_overhead = (int)ceil($accountLogResource->amount_use - $accountLogResource->amount_free);
                    $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit;
                } else {
                    continue; // @todo надо потом досчитывать вариант, когда один ресурс посчитался, а другой нет
                }
                $accountLogResource->save();
            }
        }
    }
}
