<?php

namespace app\modules\nnp\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $name_translit
 * @property string $group
 * @property int $country_code
 * @property int $cnt
 * @property int $type
 * @property string $operator_src_code
 * @property int $parent_id
 *
 * @property-read Country $country
 * @property-read Operator $parent
 */
class Operator extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const MIN_CNT = 0;

    // temporary constants ;)
    const ID_DENI_CALL = 5090;

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
            'name' => 'Название',
            'name_translit' => 'Название транслитом',
            'country_code' => 'Страна',
            'cnt' => 'Кол-во номеров',
            'group' => 'Группа оператора',
            'partner_code' => 'Код партнера',
            'operator_src_code' => 'Код оператора портирования',
            'parent_id' => 'Оператор-родитель',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.operator';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit', 'partner_code'], 'string'],
            [['country_code', 'group', 'parent_id'], 'integer'],
            [['name', 'country_code'], 'required'],
            ['operator_src_code', 'safe'],
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
        return Yii::$app->dbPgNnp;
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

    public function beforeSave($isInsert)
    {
        if (!$this->operator_src_code) {
            $this->operator_src_code = null;
        }

        return parent::beforeSave($isInsert);
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
        return Url::to(['/nnp/operator/edit', 'id' => $id]);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int|int[] $countryCode
     * @param int $minCnt
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $countryCode = null,
        $minCnt = self::MIN_CNT
    )
    {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                $countryCode ? ['country_code' => $countryCode] : [],
                $minCnt ? ['>=', 'cnt', $minCnt] : []
            ]
        );
    }
}