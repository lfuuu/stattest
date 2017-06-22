<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property int $country_code
 * @property int $cnt
 *
 * @property Country $country
 */
class Operator extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const MIN_CNT = 1000;

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
            'country_code' => 'Страна',
            'cnt' => 'Кол-во номеров',
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
            [['name'], 'string'],
            [['country_code'], 'integer'],
            [['name', 'country_code'], 'required'],
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
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
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
    ) {
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

    /**
     * Обновить столбец cnt
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public static function updateCnt()
    {
        $numberRangeTableName = NumberRange::tableName();
        $operatorTableName = Operator::tableName();
        $sql = <<<SQL
            UPDATE {$operatorTableName} SET cnt = 0
SQL;
        self::getDb()->createCommand($sql)->execute();

        $sql = <<<SQL
            UPDATE {$operatorTableName}
            SET cnt = operator_stat.cnt
            FROM 
                (SELECT operator_id, SUM(number_to - number_from + 1) AS cnt FROM {$numberRangeTableName} WHERE operator_id IS NOT NULL GROUP BY operator_id) operator_stat
            WHERE {$operatorTableName}.id = operator_stat.operator_id
SQL;
        return self::getDb()->createCommand($sql)->execute();
    }
}