<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Тип услуги (ВАТС, телефония, интернет и пр.)
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 * @property int $close_after_days
 *
 * @property-read ServiceType $parent
 * @property-read Resource[] $resources
 *
 * @method static ServiceType findOne($condition)
 * @method static ServiceType[] findAll($condition)
 */
class ServiceType extends ActiveRecord
{
    const ID_VPBX = 1; // ВАТС
    const ID_VOIP = 2; // Телефония
    const ID_VOIP_PACKAGE_CALLS = 3; // Телефония. Пакет звонков
    const ID_VOIP_PACKAGE_INTERNET = 25; // Телефония. Пакет интернета

    const ID_INTERNET = 4; // Интернет
    const ID_COLLOCATION = 5; // Collocation
    const ID_VPN = 6; // VPN

    const ID_IT_PARK = 7; // IT Park
    const ID_DOMAIN = 8; // Регистрация доменов. domain
    const ID_MAILSERVER = 9; // Виртуальный почтовый сервер. mailserver
    const ID_ATS = 10; // Старый ВАТС. phone_ats
    const ID_SITE = 11; // Сайт. site
    const ID_USPD = 12; // Провайдер. uspd
    const ID_WELLSYSTEM = 13; // Wellsystem. wellsystem
    const ID_WELLTIME_PRODUCT = 14; // Welltime как продукт. welltime, ip
    const ID_EXTRA = 15; // Дополнительные услуги
    const ID_SMS_GATE = 16; // СМС. sms_gate

    const ID_SMS = 17; // SMS

    const ID_WELLTIME_SAAS = 18; // Welltime как сервис. welltime

    const ID_CALL_CHAT = 19; // Звонок-чат

    const ID_VM_COLLOCATION = 20; // VM collocation

    const ID_ONE_TIME = 21; // Разовая услуга

    const ID_TRUNK = 22; // транк
    const ID_TRUNK_PACKAGE_ORIG = 23; // пакет ориг-транк
    const ID_TRUNK_PACKAGE_TERM = 24; // пакет терм-транк

    const ID_INFRASTRUCTURE = 26; // Инфраструктура

    const CLOSE_AFTER_DAYS = 60;

    public static $packages = [
        self::ID_VOIP_PACKAGE_CALLS => self::ID_VOIP,
        self::ID_VOIP_PACKAGE_INTERNET => self::ID_VOIP,
        self::ID_TRUNK_PACKAGE_ORIG => self::ID_TRUNK,
        self::ID_TRUNK_PACKAGE_TERM => self::ID_TRUNK,
    ];

    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_service_type';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'parent_id', 'close_after_days'], 'integer'],
            [['name'], 'string'],
            [['name'], 'required'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getResources()
    {
        return $this->hasMany(Resource::className(), ['service_type_id' => 'id']);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['id' => SORT_ASC],
            $where = []
        );
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($id)
    {
        return Url::to(['/uu/service-type/edit', 'id' => $id]);
    }

    /**
     * @return string
     */
    public function getColorClass()
    {
        switch ($this->id) {
            case self::ID_VPBX:
                return 'success';

            case self::ID_VOIP:
            case self::ID_VOIP_PACKAGE_CALLS:
            case self::ID_VOIP_PACKAGE_INTERNET:
                return 'info';

            case self::ID_TRUNK:
            case self::ID_TRUNK_PACKAGE_ORIG:
            case self::ID_TRUNK_PACKAGE_TERM:
                return 'primary';

            default:
                return 'warning';
        }
    }

    /**
     * @param int $serviceTypeId
     * @return int[]
     */
    public static function getPackageIds($serviceTypeId)
    {
        $serviceTypeIds = [];
        foreach (ServiceType::$packages as $serviceTypeIdPackage => $serviceTypeIdMain) {
            if ($serviceTypeId == $serviceTypeIdMain) {
                $serviceTypeIds[] = $serviceTypeIdPackage;
            }
        }

        return $serviceTypeIds;
    }
}
