<?php

namespace app\classes\uu\model;

use app\classes\uu\resourceReader\CollocationTrafficForeignResourceReader;
use app\classes\uu\resourceReader\CollocationTrafficRussia2ResourceReader;
use app\classes\uu\resourceReader\CollocationTrafficRussiaResourceReader;
use app\classes\uu\resourceReader\DummyResourceReader;
use app\classes\uu\resourceReader\InternetTrafficResourceReader;
use app\classes\uu\resourceReader\ResourceReaderInterface;
use app\classes\uu\resourceReader\SmsResourceReader;
use app\classes\uu\resourceReader\VoipCallsResourceReader;
use app\classes\uu\resourceReader\VoipLinesResourceReader;
use app\classes\uu\resourceReader\VpbxAbonentResourceReader;
use app\classes\uu\resourceReader\VpbxDiskResourceReader;
use app\classes\uu\resourceReader\VpbxExtDidResourceReader;
use app\classes\uu\resourceReader\VpbxFaxResourceReader;
use app\classes\uu\resourceReader\VpbxRecordResourceReader;
use app\classes\uu\resourceReader\VpnTrafficResourceReader;
use app\models\Language;
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
class Resource extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const ID_VPBX_DISK = 1; // ВАТС. Дисковое пространство
    const ID_VPBX_ABONENT = 2; // ВАТС. Абоненты
    const ID_VPBX_EXT_DID = 3; // ВАТС. Подключение номера другого оператора
    const ID_VPBX_RECORD = 4; // ВАТС. Запись звонков с сайта
    const ID_VPBX_FAX = 6; // ВАТС. Факс

    const ID_VOIP_LINE = 7; // Телефония. Линия
    const ID_VOIP_CALLS = 8; // Телефония. Звонки

    const ID_INTERNET_TRAFFIC = 9; // Интернет. Трафик

    const ID_COLLOCATION_TRAFFIC_RUSSIA = 10; // Collocation. Трафик Russia
    const ID_COLLOCATION_TRAFFIC_RUSSIA2 = 11; // Collocation. Трафик Russia2
    const ID_COLLOCATION_TRAFFIC_FOREIGN = 12; // Collocation. Трафик Foreign

    const ID_VPN_TRAFFIC = 13; // VPN. Трафик

    const ID_SMS = 14; // SMS

    const ID_VM_COLLOCATION_PROCESSOR = 15; // VM collocation. Процессор
    const ID_VM_COLLOCATION_HDD = 16; // VM collocation. Постоянная память
    const ID_VM_COLLOCATION_RAM = 17; // VM collocation. Оперативная память

    const ID_ONE_TIME = 18; // Разовая услуга

    const ID_VPBX_MIN_ROUTE = 19; // ВАТС. Маршрутизация по минимальной цене
    const ID_VPBX_GEO_ROUTE = 20; // ВАТС. Маршрутизация по географии

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NUMBER = 'number';

    const DEFAULT_UNIT = '¤';

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
     * @return ResourceReaderInterface
     * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=13336881
     */
    public static function getReader($id)
    {
        $idToClassName = [
            self::ID_VPBX_DISK => VpbxDiskResourceReader::className(),
            // Дисковое пространство (Гб, float). Берется из virtpbx_stat.use_space
            self::ID_VPBX_ABONENT => VpbxAbonentResourceReader::className(),
            // Абоненты (шт, int). Берется из virtpbx_stat.numbers
            self::ID_VPBX_EXT_DID => VpbxExtDidResourceReader::className(),
            // Подключение номера другого оператора (шт, int). Берется из virtpbx_stat.ext_did_count
            self::ID_VPBX_RECORD => VpbxRecordResourceReader::className(),
            // Запись звонков (call recording) (bool). Берется из virtpbx_stat.call_recording_enabled
            self::ID_VPBX_FAX => VpbxFaxResourceReader::className(),
            // Факс (bool). Берется из virtpbx_stat.faxes_enabled

            self::ID_VOIP_LINE => VoipLinesResourceReader::className(),
            // Линии (шт, int). https://vpbx.mcn.ru/core/swagger/index.html , vpbx, /get_int_number_usage
            self::ID_VOIP_CALLS => VoipCallsResourceReader::className(),
            // Звонки (у.е, float). Берется из calls_aggr.calls_aggr

            self::ID_INTERNET_TRAFFIC => InternetTrafficResourceReader::className(),
            // Трафик (Мб., float). nispd.traf_flows_1d;

            self::ID_COLLOCATION_TRAFFIC_RUSSIA => CollocationTrafficRussiaResourceReader::className(),
            // Трафик Russia (Мб., float). nispd.traf_flows_1d.in_r - входящий, nispd.traf_flows_1d.out_r - исходящий;
            self::ID_COLLOCATION_TRAFFIC_RUSSIA2 => CollocationTrafficRussia2ResourceReader::className(),
            // Трафик Russia2 (Мб., float). nispd.traf_flows_1d.in_r2 - входящий, nispd.traf_flows_1d.out_r2 - исходящий;
            self::ID_COLLOCATION_TRAFFIC_FOREIGN => CollocationTrafficForeignResourceReader::className(),
            // Трафик Foreign (Мб., float). nispd.traf_flows_1d.in_f - входящий, nispd.traf_flows_1d.out_f - исходящий;

            self::ID_VPN_TRAFFIC => VpnTrafficResourceReader::className(),
            // Трафик (Мб., float). nispd.mod_traf_1d, но таблицы пустые, походу никто их не использует давно. Какой-то рудимент. Видимо из-за повального использования безлимитных тарифов;

            self::ID_SMS => SmsResourceReader::className(),
            // СМС (шт, int). nispd.sms_stat - количество СМСок по дням;

            self::ID_VM_COLLOCATION_PROCESSOR => DummyResourceReader::className(),
            // VM collocation. Процессор
            self::ID_VM_COLLOCATION_HDD => DummyResourceReader::className(),
            // VM collocation. Постоянная память
            self::ID_VM_COLLOCATION_RAM => DummyResourceReader::className(),
            // VM collocation. Оперативная память

            self::ID_ONE_TIME => DummyResourceReader::className(),
            // Разовая услуга

            self::ID_VPBX_MIN_ROUTE => DummyResourceReader::className(),
            // ВАТС. Маршрутизация по минимальной цене
            self::ID_VPBX_GEO_ROUTE => DummyResourceReader::className(),
            // ВАТС. Маршрутизация по географии
        ];
        $className = $idToClassName[$id];
        return new $className();
    }

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @return string[]
     */
    public static function getList($serviceTypeId, $isWithEmpty = false)
    {
        $query = self::find()
            ->indexBy('id')
            ->orderBy([
                'service_type_id' => SORT_ASC,
                'name' => SORT_ASC,
            ]);
        $serviceTypeId && $query->where(['service_type_id' => $serviceTypeId]);
        $list = $query->all();

        if (!$serviceTypeId) {
            array_walk($list, function (\app\classes\uu\model\Resource &$resource) {
                $resource = $resource->getFullName();
            });
        }

        if ($isWithEmpty) {
            $list = ['' => ''] + $list;
        }

        return $list;
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Вернуть полное имя (с типом услуги)
     * @param string $langCode
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

}
