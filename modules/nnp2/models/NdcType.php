<?php

namespace app\modules\nnp2\models;

use app\classes\Connection;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp2\classes\NumberRangeMassUpdater;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property boolean $is_city_dependent
 * @property int $parent_id
 * @property boolean $is_valid
 *
 * @property-read NdcType $parent
 * @property-read NdcType[] $childs
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
        self::ID_FREEPHONE => '1 day',
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
            'is_city_dependent' => 'Зависит от города',
            'parent_id' => 'Родитель',
            'is_valid' => 'Подтверждён',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp2.ndc_type';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['parent_id'], 'integer'],
            [['is_city_dependent', 'is_valid'], 'boolean'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp2;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
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
        return Url::to(['/nnp2/ndc-type/edit', 'id' => $id]);
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
        if (isset(self::$_holdList[$this->id])) {
            $interval = self::$_holdList[$this->id];
        } else {
            $interval = self::DEFAULT_HOLD;
        }

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
     * @param bool $isMainOnly
     * @param null|bool $isCityDepended null - любое, bool - только указанное
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $isMainOnly = true,
        $isCityDepended = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                $isMainOnly ? ['is_valid' => 1] : [],
                $isMainOnly ? ['parent_id' => null] : [],
                is_null($isCityDepended) ? [] : ['is_city_dependent' => $isCityDepended],
            ]
        );
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $validation = parent::validate($attributeNames, $clearErrors);

        if ($validation) {
            if ($this->parent_id) {
                if (!$this->parent->is_valid) {
                    $this->addError('parent_id', 'Родитель не подтверждён');

                    return false;
                }

                return true;
            }

            if (!$this->is_valid && $this->childs) {
                $this->addError('is_valid', 'Есть синонимы');

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param boolean|true $runValidation
     * @param null|array $attributeNames
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = self::getDb();
        $transaction = $dbPgNnp->beginTransaction();
        try {

            $oldIsValid = $this->getOldAttribute('is_valid');
            parent::save($runValidation, $attributeNames);
            if ($this->is_valid !== $oldIsValid) {
                NumberRangeMassUpdater::me()->update(null, $this->id);
            }

            if (!$this->parent_id) {
                $this->parent_id = null;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            return sprintf('%s %s', $e->getMessage(), $e->getTraceAsString());
        }
    }

}