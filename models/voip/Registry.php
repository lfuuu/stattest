<?php

namespace app\models\voip;

use app\classes\behaviors\CreatedAt;
use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\model\ActiveRecord;
use app\classes\traits\GetListTrait;
use app\dao\VoipRegistryDao;
use app\models\City;
use app\models\Country;
use app\modules\nnp\models\NdcType;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * Class Registry
 *
 * @property integer $id
 * @property integer $country_id
 * @property integer $city_id
 * @property integer $ndc
 * @property string $source
 * @property integer $ndc_type_id
 * @property string $solution_number
 * @property integer $numbers_count
 * @property string $solution_date
 * @property string $number_from
 * @property string $number_to
 * @property string $number_full_from
 * @property string $number_full_to
 * @property integer $account_id
 * @property string $created_at
 * @property string $comment
 * @property integer $fmc_trunk_id
 * @property integer $mvno_trunk_id
 * @property integer $mvno_partner_id
 * @property-read City $city
 * @property-read Country $country
 * @property-read NdcType $ndcType
 * @property string $status
 * @property int $nnp_operator_id
 */
class Registry extends ActiveRecord
{
    use GetListTrait {
        getList as getListTrait;
    }

    const STATUS_EMPTY = 'empty';
    const STATUS_PARTLY = 'partly';
    const STATUS_FULL = 'full';

    public static $names = [
        self::STATUS_EMPTY => 'Пусто',
        self::STATUS_PARTLY => 'Частично',
        self::STATUS_FULL => 'Заполнено',
    ];

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'CreatedAt' => CreatedAt::class,
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];

    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => '#',
            'country_id' => 'Страна',
            'city_id' => 'Город',
            'source' => 'Источник',
            'ndc_type_id' => 'Тип номера',
            'number_from' => 'Номер "с"',
            'number_to' => 'Номер "по"',
            'account_id' => 'ЛС',
            'created_at' => 'Создано',
            'ndc' => 'NDC',
            'fmc_trunk_id' => 'FMC транк',
            'mvno_trunk_id' => 'MVNO транк',
            'mvno_partner_id' => 'MVNO партнер',
            'nnp_operator_id' => 'ННП оператор',
            'solution_number' => 'Номер решения',
            'numbers_count' => 'Количество номеров',
            'solution_date' => 'Дата',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip_registry';
    }

    /**
     * @return VoipRegistryDao
     */
    public static function dao()
    {
        return VoipRegistryDao::me();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNdcType()
    {
        return $this->hasOne(NdcType::class, ['id' => 'ndc_type_id']);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return self::dao()->getStatus($this);
    }

    /**
     * @inheritdoc
     */
    public function getPassMap()
    {
        return self::dao()->getPassMap($this);
    }

    /**
     * @inheritdoc
     */
    public function fillNumbers()
    {
        return self::dao()->fillNumbers($this);
    }

    /**
     * @inheritdoc
     */
    public function getStatusInfo()
    {
        return self::dao()->getStatusInfo($this);
    }

    /**
     * @inheritdoc
     */
    public function toSale()
    {
        return self::dao()->toSale($this);
    }

    /**
     * @return mixed
     */
    public function attachNumbers()
    {
        return self::dao()->attachNumbers($this);
    }

    /**
     * @return mixed
     */
    public function setDidGroup()
    {
        return self::dao()->setDidGroup($this);
    }

    /**
     * Это реестр портированных номеров?
     *
     * @return bool
     */
    public function isSourcePotability()
    {
        return in_array($this->source, [
            VoipRegistrySourceEnum::PORTABILITY_NOT_FOR_SALE,
            VoipRegistrySourceEnum::PORTABILITY_INNONET,
            VoipRegistrySourceEnum::OPERATOR_NOT_FOR_SALE,
        ]);
    }

    /**
     * Это служебная группа
     *
     * @return bool
     */
    public function isService()
    {
        return isset(VoipRegistrySourceEnum::$service[$this->source]);
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->status == self::STATUS_EMPTY;
    }

    /**
     * @return bool
     */
    public function isSubmitable()
    {
        return $this->status != self::STATUS_FULL;
    }

    public static function getList()
    {
        return self::getListTrait(true, true, 'id' , (new Expression('concat(\'Реестр №\', id) as name ')), ['id' => SORT_ASC]);
    }

    public function getUrl()
    {
        return Url::to(['voip/registry/edit', 'id' => $this->id]);
    }
}
