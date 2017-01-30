<?php
use app\models\ClientContact;

/**
 * Class m170127_103909_add_contact_is_validate
 */
class m170127_103909_add_contact_is_validate extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientContact::tableName(), 'is_validate', $this->smallInteger(1)->notNull()->defaultValue(1));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientContact::tableName(), 'is_validate');
    }
}
