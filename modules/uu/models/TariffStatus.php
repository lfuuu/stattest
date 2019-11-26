<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Статусы тарифа (публичный, специальный, архивный и пр.)
 *
 * @property int $id
 * @property string $name
 * @property int $service_type_id Тип услуги. Если null, то для всех
 *
 * @property-read ServiceType $serviceType
 *
 * @method static TariffStatus findOne($condition)
 * @method static TariffStatus[] findAll($condition)
 */
class TariffStatus extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ID_PUBLIC = 1;
    const ID_SPECIAL = 2;
    const ID_ARCHIVE = 3;
    const ID_TEST = 4;
    const ID_ARCHIVE_TEST = 57;

    const ID_VOIP_8800 = 5;
    const ID_VOIP_8800_TEST = 9;
    const ID_VOIP_OPERATOR = 6;
    const ID_VOIP_TRANSIT = 7;
    const ID_VOIP_FMC = 13;
    const ID_VOIP_MVNO = 14;

    const ID_INTERNET_ADSL = 8;

    const TEST_LIST = [
        self::ID_TEST, self::ID_VOIP_8800_TEST, self::ID_ARCHIVE_TEST
    ];

    const ARCHIVE_LIST = [
        self::ID_ARCHIVE, self::ID_ARCHIVE_TEST
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_status';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'service_type_id'], 'integer'],
            [['name'], 'string'],
            [['name'], 'required'],
        ];
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param int $serviceTypeId
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $serviceTypeId = null,
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['id' => SORT_ASC],
            $where = $serviceTypeId ?
                [
                    'OR',
                    ['IS', 'service_type_id', null],
                    ['service_type_id' => $serviceTypeId]
                ] :
                []
        );
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::class, ['id' => 'service_type_id']);
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
        return Url::to(['/uu/tariff-status/edit', 'id' => $id]);
    }
}