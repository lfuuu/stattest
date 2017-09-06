<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\Language;
use app\modules\uu\behaviors\ResourceFiller;
use app\modules\uu\resourceReader\ResourceReaderInterface;
use app\modules\uu\resourceReader\TrunkCallsResourceReader;
use app\modules\uu\resourceReader\VoipPackageCallsResourceReader;
use app\modules\uu\resourceReader\VpbxDiskResourceReader;
use Yii;
use yii\db\ActiveQuery;

/**
 * Ресурс (дисковое пространство, абоненты, линии и пр.)
 *
 * @property integer $id
 * @property string $name
 * @property float $min_value
 * @property float $max_value
 * @property integer $service_type_id
 * @property string $unit
 *
 * @property ServiceType $serviceType
 */
class Resource extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const ID_VPBX_DISK = 1; // ВАТС. Дисковое пространство
    const ID_VPBX_ABONENT = 2; // ВАТС. Абоненты
    const ID_VPBX_EXT_DID = 3; // ВАТС. Подключение номера другого оператора
    const ID_VPBX_RECORD = 4; // ВАТС. Запись звонков
    const ID_VPBX_FAX = 6; // ВАТС. Факс
    const ID_VPBX_MIN_ROUTE = 19; // ВАТС. Маршрутизация по минимальной цене
    const ID_VPBX_GEO_ROUTE = 20; // ВАТС. Маршрутизация по географии
    const ID_VPBX_SUB_ACCOUNT = 39; // ВАТС. Лимиты по субсчетам

    const ID_VOIP_LINE = 7; // Телефония. Линия
    const ID_VOIP_FMC = 38; // Телефония. FMC

    const ID_VOIP_PACKAGE_CALLS = 40; // Пакеты телефонии. Звонки

    const ID_INTERNET_TRAFFIC = 9; // Интернет. Трафик

    const ID_COLLOCATION_TRAFFIC = 10; // Collocation. Трафик

    const ID_VPN_TRAFFIC = 13; // VPN. Трафик

    const ID_SMS = 14; // SMS

    const ID_VM_COLLOCATION_PROCESSOR = 15; // VM collocation. Процессор
    const ID_VM_COLLOCATION_HDD = 16; // VM collocation. Постоянная память
    const ID_VM_COLLOCATION_RAM = 17; // VM collocation. Оперативная память

    const ID_ONE_TIME = 18; // Разовая услуга

    const ID_TRUNK_PACKAGE_ORIG_CALLS = 41; // Ориг-пакеты транка. Звонки

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NUMBER = 'number';

    const DEFAULT_UNIT = '¤';

    public $fillerPricePerUnit = 100;

    protected $isAttributeTypecastBehavior = true;

    public static $calls = [
        Resource::ID_VOIP_PACKAGE_CALLS => Resource::ID_VOIP_PACKAGE_CALLS,
        Resource::ID_TRUNK_PACKAGE_ORIG_CALLS => Resource::ID_TRUNK_PACKAGE_ORIG_CALLS,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_resource';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['min_value', 'max_value'], 'number'],
            [['service_type_id'], 'integer'],
            [['name', 'unit'], 'string', 'max' => 50]
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return parent::behaviors() + [
                'ResourceFiller' => ResourceFiller::className(),
            ];
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::className(), ['id' => 'service_type_id']);
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return ($this->min_value == 0 && $this->max_value == 1) ? self::TYPE_BOOLEAN : self::TYPE_NUMBER;
    }

    /**
     * @return bool
     */
    public function isNumber()
    {
        return $this->getDataType() === self::TYPE_NUMBER;
    }

    /**
     * @param int $id
     * @return ResourceReaderInterface|null
     * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=13336881
     */
    public static function getReader($id)
    {
        $idToClassName = self::getReaderNames();

        if (!isset($idToClassName[$id])) {
            return null;
        }

        $className = $idToClassName[$id];
        return new $className();
    }

    /**
     * @return string[]
     */
    public static function getReaderNames()
    {
        return [
            // Дисковое пространство (Гб, float). Берется из virtpbx_stat.use_space
            self::ID_VPBX_DISK => VpbxDiskResourceReader::className(),

            // Звонки по пакетам телефонии (у.е, float). Берется из calls_raw
            self::ID_VOIP_PACKAGE_CALLS => VoipPackageCallsResourceReader::className(),

            // Звонки по ориг-пакета транка (у.е, float). Берется из calls_raw
            self::ID_TRUNK_PACKAGE_ORIG_CALLS => TrunkCallsResourceReader::className(),
        ];
    }

    /**
     * Опция? Иначе ресурс
     *
     * @return bool
     */
    public function isOption()
    {
        $readerNames = self::getReaderNames();
        return !isset($readerNames[$this->id]);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param int $serviceTypeId
     * @param bool $isWithEmpty
     * @return \string[]
     */
    public static function getList($serviceTypeId, $isWithEmpty = false)
    {
        $query = self::find()
            ->where($serviceTypeId ? ['service_type_id' => $serviceTypeId] : [])
            ->indexBy('id')
            ->orderBy(
                [
                    'service_type_id' => SORT_ASC,
                    'name' => SORT_ASC,
                ]
            );

        $list = $query->all();

        if (!$serviceTypeId) {
            array_walk(
                $list,
                function (\app\modules\uu\models\Resource &$resource) {
                    $resource = $resource->getFullName();
                }
            );
        }

        if ($isWithEmpty) {
            $list = (['' => ''] + $list);
        }

        return $list;
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Вернуть полное имя (с типом услуги)
     *
     * @param string $langCode
     * @param bool $isTextFull
     * @return string
     */
    public function getFullName($langCode = Language::LANGUAGE_DEFAULT, $isTextFull = false)
    {
        $dictionary = 'models/' . self::tableName();

        return
            ($isTextFull ? Yii::t($dictionary, 'Resource consumption limit exceedance', [], $langCode) . ': ' : '') .
            Yii::t($dictionary, 'Resource #' . $this->id, [], $langCode);
    }

    /**
     * Вернуть ресурсы, сгруппированные по типу услуги
     *
     * @return self[][]
     */
    public static function getGroupedByServiceType()
    {
        $resources = [];
        $resourceQuery = self::find();
        /** @var self $resource */
        foreach ($resourceQuery->each() as $resource) {
            $resources[$resource->service_type_id][] = $resource;
        }

        return $resources;
    }

    /**
     * @return string
     */
    public function getMinValue()
    {
        return $this->isNumber() ? (string)$this->min_value : '';
    }

    /**
     * @return string
     */
    public function getMaxValue()
    {
        return $this->isNumber() ?
            (string)($this->max_value ?: '∞') :
            '';
    }

    /**
     * @return string
     */
    public function getValueRange()
    {
        return $this->isNumber() ?
            $this->getMinValue() . ' - ' . $this->getMaxValue() . ' ' . $this->getUnit() :
            '';
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->isNumber() ?
            $this->unit :
            '';
    }

    /**
     * Можно ли поменять количество ресурса в принципе
     * А фактически еще надо вызвать AccountTariff::isResourceEditable для доп. проверок по логу
     *
     * @return bool
     */
    public function isEditable()
    {
        return $this->isOption();
    }

    /**
     * Добавить этот ресурс в тариф
     *
     * @param float $amount
     * @param float $pricePerUnit
     * @param float $priceMin
     * @throws \yii\db\Exception
     */
    public function addTariffResource($amount, $pricePerUnit = 1.0, $priceMin = 0.0)
    {
        $db = self::getDb();
        $resourceId = $this->id;
        $serviceTypeId = $this->service_type_id;

        $tariffTableName = Tariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $accountTariffResourceLogTableName = AccountTariffResourceLog::tableName();

        $sql = <<<SQL
            INSERT INTO {$tariffResourceTableName}
                (amount, price_per_unit, price_min, resource_id, tariff_id)
            SELECT {$amount}, {$pricePerUnit}, {$priceMin}, {$resourceId}, id
            FROM {$tariffTableName}
            WHERE service_type_id = {$serviceTypeId};
SQL;
        $db->createCommand($sql)->execute();


        if ($this->isOption()) {
            $sql = <<<SQL
            INSERT INTO {$accountTariffResourceLogTableName}
                (account_tariff_id, resource_id, amount, actual_from_utc, insert_time, insert_user_id)
            SELECT
                {$accountTariffLogTableName}.account_tariff_id,
                {$resourceId},
                {$amount},
                {$accountTariffLogTableName}.actual_from_utc,
                {$accountTariffLogTableName}.insert_time,
                {$accountTariffLogTableName}.insert_user_id
            FROM
                {$accountTariffLogTableName}, 
                {$tariffPeriodTableName}, 
                {$tariffTableName}
            WHERE 
                {$accountTariffLogTableName}.tariff_period_id IS NOT NULL
                AND {$accountTariffLogTableName}.tariff_period_id = {$tariffPeriodTableName}.id
                AND {$tariffPeriodTableName}.tariff_id = {$tariffTableName}.id
                AND {$tariffTableName}.service_type_id = {$serviceTypeId};
SQL;
            $db->createCommand($sql)->execute();
        }
    }
}
