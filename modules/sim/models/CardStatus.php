<?php

namespace app\modules\sim\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\models\Number;
use app\modules\sim\classes\query\CardStatusQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 *
 * @property-read Card[] $cards
 *
 * @method static CardStatus findOne($condition)
 * @method static CardStatus[] findAll($condition)
 */
class CardStatus extends ActiveRecord
{
    const ID_DEFAULT = 1;

    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.sim_card_status';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
        ];
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
     * @return CardStatusQuery
     */
    public static function find()
    {
        return new CardStatusQuery(get_called_class());
    }

    /**
     * @return CardStatus
     */
    public static function getVirtByNumberModel(Number $number) {
        return self::find()
            ->isVirt()
            ->regionId($number->region)
            ->one();
    }

    /**
     * @return CardStatus
     */
    public static function getVirtByRegionId($regionId) {
        return self::find()
            ->isVirt()
            ->regionId($regionId)
            ->one();
    }

    /**
     * @return ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::class, ['status_id' => 'id'])
            ->indexBy('id');
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
        return Url::to(['/sim/card-status/edit', 'id' => $id]);
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
