<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\classes\validators\ArrayValidator;

/**
 * @property int $person_id
 * @property string $lang_code
 * @property string $field
 * @property string $value
 */
class PersonI18N extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'person_i18n';
    }

    /**
     * @return array
     */
    public static function primaryKey()
    {
        return ['person_id', 'lang_code', 'field'];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

}