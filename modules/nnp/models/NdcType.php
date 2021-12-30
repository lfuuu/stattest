<?php

namespace app\modules\nnp\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property boolean $is_city_dependent
 */
class NdcType extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ID_GEOGRAPHIC = 1;
    const ID_MOBILE = 2;
    const ID_NOMADIC = 3;
    const ID_FREEPHONE = 4;
    const ID_PREMIUM = 5;
    const ID_SHORT_CODE = 6;
    const ID_REST = 7;
    const ID_MCN_LINE = 11;

    const DEFAULT_HOLD = '6 month';

    private static $_holdList = [
//        self::ID_FREEPHONE => '1 day',
    ];

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'is_city_dependent' => 'Зависит от города'
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.ndc_type';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['is_city_dependent'], 'boolean'],
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
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/ndc-type/edit', 'id' => $id]);
    }

    /**
     * Вернуть html: имя + ссылка
     *
     * @return string
     */
    public function getLink()
    {
        return Html::a(Html::encode($this->name), $this->getUrl());
    }

    /**
     * Возвращает время "отстоя" номера
     *
     * @return \DateInterval
     */
    public function getHold()
    {
        $interval = self::$_holdList[$this->id] ?? self::DEFAULT_HOLD;

        return \DateInterval::createFromDateString($interval);
    }

    /**
     * Зависим ли тип от города
     *
     * @param integer $ndcTypeId
     * @return bool
     * @throws \LogicException
     */
    public static function isCityDependent($ndcTypeId)
    {
        /** @var NdcType $ndcType */
        $ndcType = NdcType::findOne(['id' => $ndcTypeId]);

        if (!$ndcType) {
            throw new \LogicException('Неизвестный тип NDC: ' . $ndcTypeId);
        }

        return $ndcType->is_city_dependent;
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param null|bool $isCityDepended null - любое, bool - только указанное
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $isCityDepended = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = is_null($isCityDepended) ? [] : ['is_city_dependent' => $isCityDepended]
        );
    }

}