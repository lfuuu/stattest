<?php
use app\models\DidGroup;

/**
 * Class m170330_120700_did_group_postfix
 */
class m170330_120700_did_group_postfix extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(DidGroup::tableName(), 'comment', $this->string()->defaultValue(''));
        $this->update(DidGroup::tableName(), ['comment' => '495'], ['id' => DidGroup::ID_MOSCOW_STANDART_495]);
        $this->update(DidGroup::tableName(), ['comment' => '499'], ['id' => DidGroup::ID_MOSCOW_STANDART_499]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(DidGroup::tableName(), 'comment');
    }
}
