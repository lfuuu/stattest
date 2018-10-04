<?php

namespace app\modules\sim\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\models\billing\Trunk;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property integer $term_trunk_id
 * @property integer $orig_trunk_id
 * @property integer $is_active
 *
 * @property-read Imsi[] $imsies
 * @property-read Trunk $termTrunk
 * @property-read Trunk $origTrunk
 *
 * @method static ImsiPartner findOne($condition)
 * @method static ImsiPartner[] findAll($condition)
 */
class ImsiPartner extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const ROAMABILITY = 4;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.sim_imsi_partner';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [['term_trunk_id', 'orig_trunk_id', 'is_active'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
            ]
        );
    }

    /**
     * @return ActiveQuery
     */
    public function getImsies()
    {
        return $this->hasMany(Imsi::class, ['partner_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getTermTrunk()
    {
        return $this->hasOne(Trunk::class, ['id' => 'term_trunk_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrigTrunk()
    {
        return $this->hasOne(Trunk::class, ['id' => 'orig_trunk_id']);
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
        return Url::to(['/sim/imsi-partner/edit', 'id' => $id]);
    }

    /**
     * Вернуть html: имя + ссылка
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getLink()
    {
        return Html::a(Html::encode($this->name), $this->getUrl());
    }
}
