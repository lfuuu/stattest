<?php

use app\models\mail\MailJob;
use app\models\mail\MailObject;

/**
 * Class m171002_120118_mail_from_email
 */
class m171002_120118_mail_from_email extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(MailJob::tableName(), 'from_email', $this->string()->notNull()->defaultValue('info@mcn.ru'));
        $this->update(MailJob::tableName(), ['from_email' => 'info@mcn.ru']);
        $this->addColumn(MailObject::tableName(), 'is_pdf', $this->boolean()->defaultValue(false));

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(MailJob::tableName(), 'from_email');
        $this->dropColumn(MailObject::tableName(), 'is_pdf');
    }
}
