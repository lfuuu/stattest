<?php
use app\modules\notifier\models\templates\Template;
use app\modules\notifier\models\templates\TemplateContent;

/**
 * Class m170516_155213_message_template_content_type
 */
class m170516_155213_message_template_content_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(
            TemplateContent::tableName(),
            'type',
            "ENUM('email', 'sms', 'phone', 'email_inner')"
        );

        TemplateContent::updateAll([
            'type' => Template::CLIENT_CONTACT_TYPE_PHONE,
        ], [
            'type' => 'sms',
        ]);

        $this->alterColumn(
            TemplateContent::tableName(),
            'type',
            "ENUM('email', 'phone', 'email_inner')"
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(
            TemplateContent::tableName(),
            'type',
            "ENUM('email', 'sms', 'phone', 'email_inner')"
        );

        TemplateContent::updateAll([
            'type' => 'sms',
        ], [
            'type' => Template::CLIENT_CONTACT_TYPE_PHONE,
        ]);

        $this->alterColumn(
            TemplateContent::tableName(),
            'type',
            "ENUM('email', 'sms', 'email_inner')"
        );
    }
}
