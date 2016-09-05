<?php

namespace app\classes\uu\model;

use Yii;
use yii\db\ActiveQuery;

/**
 * Тип услуги (ВАТС, телефония, интернет и пр.)
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 *
 * @property ServiceType $parent
 * @property Resource[] $resources
 */
class ServiceType extends \yii\db\ActiveRecord
{
    const ID_VPBX = 1; // ВАТС
    const ID_VOIP = 2; // Телефония
    const ID_VOIP_PACKAGE = 3; // Телефония. Пакет

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

    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    // какие id конвертировать из старых
    public static $ids = [
        self::ID_VPBX,
        self::ID_VOIP,
        self::ID_VOIP_PACKAGE,

        self::ID_INTERNET,
        self::ID_COLLOCATION,
        self::ID_VPN,

        self::ID_IT_PARK,
        self::ID_DOMAIN,
        self::ID_MAILSERVER,
        self::ID_ATS,
        self::ID_SITE,
        self::ID_USPD,
        self::ID_WELLSYSTEM,
        self::ID_WELLTIME_PRODUCT,
        self::ID_EXTRA,
        self::ID_SMS_GATE,

        self::ID_SMS,

        self::ID_WELLTIME_SAAS,

        self::ID_CALL_CHAT,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_service_type';
    }

    /**
     */
    public function rules()
    {
        return [
            [['id', 'parent_id'], 'integer'],
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
     * По какому полю сортировать для getList()
     * @return []
     */
    public static function getListOrderBy()
    {
        return ['id' => SORT_ASC];
    }
}
