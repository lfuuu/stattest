<?php

/**
 * Class m250618_144707_template_content_textresize
 */
class m250618_144707_template_content_textresize extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\document\PaymentTemplate::tableName(), 'content', 'LONGTEXT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\document\PaymentTemplate::tableName(), 'content', $this->text());
    }
}
