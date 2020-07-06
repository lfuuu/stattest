<?php

namespace app\modules\nnp2\models;

use app\classes\Connection;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp\models\Country;
use app\modules\nnp2\classes\NumberRangeMassUpdater;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $country_code
 * @property string $name
 * @property string $name_translit
 * @property string $group
 * @property int $cnt bigint
 * @property int $parent_id
 * @property boolean $is_valid
 *
 * @property-read Country $country
 * @property-read Operator $parent
 * @property-read Operator[] $childs
 */
class Operator extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const MIN_CNT = 0;

    /*
     * Группа оператора:
     *
     * - Regular (RGL) (по умолчанию)
     * - Major (MJR)
     * - Selfservice (SFS)
     * - Suspend (SPD)
     * - International (INT)
     * - Arhive (ARH)
     * */

    const GROUP_RGL = 0;
    const GROUP_MJR = 1;
    const GROUP_SFS = 2;
    const GROUP_SPD = 3;
    const GROUP_INT = 4;
    const GROUP_ARH = 5;

    public static $groups = [
        self::GROUP_RGL => 'Regular (RGL)',
        self::GROUP_MJR => 'Major (MJR)',
        self::GROUP_SFS => 'Selfservice (SFS)',
        self::GROUP_SPD => 'Suspend (SPD)',
        self::GROUP_INT => 'International (INT)',
        self::GROUP_ARH => 'Arhive (ARH)',
    ];

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
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Страна',
            'name' => 'Название',
            'name_translit' => 'Название транслитом',
            'group' => 'Группа оператора',
            'cnt' => 'Кол-во номеров',
            'parent_id' => 'Оператор-родитель',
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
        return 'nnp2.operator';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit'], 'string'],
            [['country_code', 'group', 'parent_id'], 'integer'],
            [['is_valid'], 'boolean'],
            [['name', 'country_code'], 'required'],
        ];
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'id':
                return Html::a($value, self::getUrlById($value));

            case 'country_code':
                if ($country = Country::findOne(['code' => $value])) {
                    return $country->getLink();
                }
                break;
        }

        return $value;
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
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
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
    public function getGroupName()
    {
        return self::$groups[$this->group] ?? '';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
        return Url::to(['/nnp2/operator/edit', 'id' => $id]);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int|int[] $countryCode
     * @param bool $isMainOnly
     * @param bool $isFormatted
     * @param int $minCnt
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $countryCode = null,
        $isMainOnly = true,
        $isFormatted = false,
        $minCnt = self::MIN_CNT
    ) {
        $list = self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                $isMainOnly ? ['is_valid' => 1] : [],
                $isMainOnly ? ['parent_id' => null] : [],
                $countryCode ? ['country_code' => $countryCode] : [],
                $minCnt ? ['>=', 'cnt', $minCnt] : []
            ]
        );

        if (!$isFormatted) {
            return $list;
        }

        foreach ($list as $id => &$item) {
            if ($id > 0) {
                $item = sprintf("%s (%s)", $item, $id);
            }
        }

        return $list;
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
                NumberRangeMassUpdater::me()->update(null, null, $this->id);
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
}