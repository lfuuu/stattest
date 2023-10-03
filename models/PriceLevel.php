<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\traits\GridSortTrait;
use Yii;

 /**
 * Class PriceLevel
 * @property int $id
 * @property string $name
 * @property integer $order
 */
class PriceLevel extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }
    use GridSortTrait;

    public static $primaryField = 'id';

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['order'], 'integer'],
            [['name'], 'required'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'clients_price_level';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }


    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['order' => SORT_ASC],
            $where = []
        );
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
        return \yii\helpers\Url::toRoute(['/dictionary/price-level/edit', 'id' => $id]);
    }
}