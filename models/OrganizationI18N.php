<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $organization_record_id
 * @property string $lang_code
 * @property string $field
 * @property string $value
 */
class OrganizationI18N extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'organization_i18n';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

}