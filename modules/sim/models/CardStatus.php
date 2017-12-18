<?php

namespace app\modules\sim\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
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
        return 'sim_card_status';
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
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::className(),
            ]
        );
    }

    /**
     * @return ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['status_id' => 'id'])
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
