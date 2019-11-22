<?php

use app\modules\sbisTenzor\models\SBISDocument;

/**
 * Class m191105_115008_alter_sbis_document
 */
class m191105_115008_alter_sbis_document extends \app\classes\Migration
{
    public $tableName;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = SBISDocument::tableName();

        $this->alterColumn($this->tableName, 'url_our', $this->string(255));
        $this->alterColumn($this->tableName, 'url_external', $this->string(255));
        $this->alterColumn($this->tableName, 'url_pdf', $this->string(2048));
        $this->alterColumn($this->tableName, 'url_archive', $this->string(2048));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->tableName = SBISDocument::tableName();

        $this->alterColumn($this->tableName, 'url_our', $this->string(100));
        $this->alterColumn($this->tableName, 'url_external', $this->string(100));
        $this->alterColumn($this->tableName, 'url_pdf', $this->string(1024));
        $this->alterColumn($this->tableName, 'url_archive', $this->string(1024));
    }
}
