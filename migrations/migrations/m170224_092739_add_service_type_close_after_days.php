<?php
use app\modules\uu\models\ServiceType;

/**
 * Class m170224_092739_add_service_type_close_after_days
 */
class m170224_092739_add_service_type_close_after_days extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = ServiceType::tableName();
        $this->addColumn($tableName, 'close_after_days', $this->integer()->notNull()->defaultValue(60));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = ServiceType::tableName();
        $this->dropColumn($tableName, 'close_after_days');
    }
}
