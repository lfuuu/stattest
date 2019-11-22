<?php

use app\modules\sbisTenzor\models\SBISAttachment;

/**
 * Class m191105_114507_alter_sbis_attachment
 */
class m191105_114507_alter_sbis_attachment extends \app\classes\Migration
{
    public $tableName;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISAttachment::tableName();

        $this->alterColumn($this->tableName, 'link', $this->string(512));
        $this->alterColumn($this->tableName, 'url_online', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISAttachment::tableName();

        $this->alterColumn($this->tableName, 'link', $this->string(255));
        $this->alterColumn($this->tableName, 'url_online', $this->string(128));
    }
}
