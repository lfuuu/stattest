<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int id
 * @property string name
 * @property int country_code
 * @property int cnt
 */
class Operator extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

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
     * @return Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
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
     * Вернуть список всех доступных моделей
     *
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @param int $countryCode
     * @param int $minCnt
     * @return Operator[]
     */
    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false, $countryCode = null, $minCnt = self::MIN_CNT)
    {
        $activeQuery = self::find();
        $countryCode && $activeQuery->andWhere(['country_code' => $countryCode]);
        $minCnt && $activeQuery->andWhere(['>=', 'cnt', $minCnt]);
        $list = $activeQuery
            ->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * Обновить столбец cnt
     *
     * @return int
     */
    public static function updateCnt()
    {
        $numberRangeTableName = NumberRange::tableName();
        $operatorTableName = Operator::tableName();
        $sql = <<<SQL
            UPDATE {$operatorTableName}
            SET cnt = (SELECT COALESCE(SUM(number_to - number_from), 0) FROM {$numberRangeTableName} WHERE operator_id = {$operatorTableName}.id AND is_active)
SQL;
        return self::getDb()->createCommand($sql)->execute();
    }
}