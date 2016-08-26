<?php

namespace app\classes\uu\model;

use Yii;
use app\models\Language;
use yii\db\ActiveQuery;
use app\classes\uu\resourceReader\DummyResourceReader;
use app\classes\uu\resourceReader\ResourceReaderInterface;
use app\classes\uu\resourceReader\VoipCallsResourceReader;
use app\classes\uu\resourceReader\VpbxAbonentResourceReader;
use app\classes\uu\resourceReader\VpbxDiskResourceReader;
use app\classes\uu\resourceReader\VpbxExtDidResourceReader;

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
    const ID_VPBX_WEB_CALL = 5; // ВАТС. Звонки с сайта
    const ID_VPBX_FAX = 6; // ВАТС. Факс

    const ID_VOIP_LINE = 7; // Телефония. Линия
    const ID_VOIP_CALLS = 8; // Телефония. Звонки

    const ID_INTERNET_TRAFFIC = 9; // Интернет. Трафик

    const ID_COLLOCATION_TRAFFIC_RUSSIA = 10; // Collocation. Трафик Russia
    const ID_COLLOCATION_TRAFFIC_RUSSIA2 = 11; // Collocation. Трафик Russia2
    const ID_COLLOCATION_TRAFFIC_FOREINGN = 12; // Collocation. Трафик Foreign

    const ID_VPN_TRAFFIC = 13; // VPN. Трафик

    const ID_SMS = 14; // SMS

    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NUMBER = 'number';

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
     */
    public static function getReader($id)
    {
        $idToClassName = [
            self::ID_VPBX_DISK => VpbxDiskResourceReader::className(),
            self::ID_VPBX_ABONENT => VpbxAbonentResourceReader::className(),
            self::ID_VPBX_EXT_DID => VpbxExtDidResourceReader::className(),
            self::ID_VPBX_RECORD => DummyResourceReader::className(), // @todo
            self::ID_VPBX_WEB_CALL => DummyResourceReader::className(), // @todo
            self::ID_VPBX_FAX => DummyResourceReader::className(), // @todo

            self::ID_VOIP_LINE => DummyResourceReader::className(), // @todo
            self::ID_VOIP_CALLS => VoipCallsResourceReader::className(),

            self::ID_INTERNET_TRAFFIC => DummyResourceReader::className(), // @todo

            self::ID_COLLOCATION_TRAFFIC_RUSSIA => DummyResourceReader::className(), // @todo
            self::ID_COLLOCATION_TRAFFIC_RUSSIA2 => DummyResourceReader::className(), // @todo
            self::ID_COLLOCATION_TRAFFIC_FOREINGN => DummyResourceReader::className(), // @todo

            self::ID_VPN_TRAFFIC => DummyResourceReader::className(), // @todo

            self::ID_SMS => DummyResourceReader::className(), // @todo
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
    public function getFullName($langCode = Language::LANGUAGE_DEFAULT)
    {
        $dictionary = 'models/' . self::tableName();

        return Yii::t($dictionary, 'title', [
            'resource' => Yii::t($dictionary, $this->id, [], $langCode),
            'id' => $this->id,
        ], $langCode);
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
