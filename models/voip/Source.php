<?php

namespace app\models\voip;

use app\classes\model\ActiveRecord;
use app\classes\traits\GridSortTrait;
use app\classes\validators\FormFieldValidator;
use Yii;

 /**
 * Class VoipSource
 * @property int $code
 * @property string $name
 * @property integer $is_service
 * @property integer $order
 */
class Source extends ActiveRecord
{
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }
    use GridSortTrait;

    public static $primaryField = 'code'; // for sorting in grid

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'name' => 'Название',
            'is_service' => 'Служебный?',
            'order' => 'Порядок',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code', 'name'], 'string'],
            [['code', 'name'], FormFieldValidator::class],
            [['is_service', 'order'], 'integer'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip_source';
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['code'];
    }

    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'code',
            $select = 'name',
            $orderBy = ['order' => SORT_ASC],
            $where = []
        );
    }

    public function beforeSave($isInsert)
    {
        if ($isInsert) {
            $this->order = self::find()->max('`order`')+1;
        }

        return parent::beforeSave($isInsert);
    }

    public function __toString()
    {
        return $this->name;
    }
}